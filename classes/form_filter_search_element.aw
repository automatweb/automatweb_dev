<?php
// $Header

lc_load("form");
	class form_filter_search_element extends form_search_element
	{
		function form_filter_search_element()
		{
			$this->form_search_element();
		}

		function gen_admin_html()
		{
			global $lc_form;
			if (is_array($lc_form))
			{
				$this->vars($lc_form);
			}
			$this->read_template("admin_element.tpl");

			$this->do_core_admin();
			
			$this->gen_partcache();

			global $partcache;
			//echo("partcache=<pre>");print_r($partcache);echo("</pre>");//dbg
			$this->vars(array(
				"parts" => $this->picker($this->arr["part"]==""?-1:$this->arr["part"], $partcache),
			));

			$this->vars(array("FILTER_PART_LB" => $this->parse("FILTER_PART_LB")));
			return $this->parse();
		}

		function gen_partcache()
		{
			global $partsloaded,$partcache;
			//echo("gen_partcache() partsloaded=$partsloaded<br>");//dbg
			if (!$partsloaded)
			{
				$partcache=Array(-1=>"");
				$partsloaded=1;
				if ($this->form->arr["search_filter"])
				{
					classload("search_filter");
					$sf=new search_filter();
					$sf->id=$this->form->arr["search_filter"];
					//fuck! i need to load the m.a. to get fake form element names. damn.
					$sf->__load_data();
					$sf->build_master_array();
					//damn, damn ,damn
					$this->tables=$sf->master_array;

					//Build reverse arrays for finding fake names from real names
					//And fill in missing "real" keys, if a field/table has no fake name
					$this->reverse=array();
					if (is_array($this->tables))
					foreach ($this->tables as $tfakename => $tdata)
					{
						if (!isset($tdata["real"]))
						{
							$tdata["real"]=$this->tables[$tfakename]["real"]=$tfakename;
						};
						$a=array();
						// reverse fields to real => fake too

						if (is_array($tdata["fields"]))
						foreach($tdata["fields"] as $ffakename => $fdata)
						{
							if (!isset($fdata["real"]))
							{
								$fdata["real"]=$this->tables[$tfakename]["fields"][$ffakename]["real"]=$ffakename;
							};

							$a["fields"][$fdata["real"]]["ref"]=&$this->tables[$tfakename]["fields"][$ffakename];
							$a["fields"][$fdata["real"]]["fake"]=$ffakename;
						};

						$a["fake"]=$tfakename;
						$this->reverse[$tdata["real"]]=$a;
					};

					$sf->__load_filter();
					for ($i=0; $i< (int)$sf->filter["nump"]; $i++)
					{
						$part=$sf->filter["p$i"];
						//extract realtable.realfield to faketable.fakefield
						list($realtable,$realfield)=explode(".",$part["field"]);
						
						$faketable=$this->reverse[$realtable]["fake"];
						$fakefield=$this->reverse[$realtable]["fields"][$realfield]["fake"];
						$partcache[$i]="$faketable.$fakefield ".$part["op"]." ''";
					};
				};
			};
		}

		function save(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);

			$ret = $this->do_core_save(&$arr);

			$base = "element_".$this->id;
			
			$var=$base."_part";
			$this->arr["part"] = $$var;

			$this->arr["ver2"] = true;

			return $ret;
		}

		function gen_user_html_not($prefix = "",$elvalues = array(),$no_submit = false)		// function that doesn't use templates
		{
			global $awt;
			$awt->start("form_filter_search_element::gen_user_html_not");
			$awt->count("form_filter_search_element::gen_user_html_not");

			$r = $this->do_core_userhtml($prefix,$elvalues,$no_submit);
			$awt->stop("form_filter_search_element::gen_user_html_not");
			return $r;
		}

		// lauri lisas siia 20.aug.2001 prefix muutuja, mis puudu oli
		function process_entry(&$entry, $id, $prefix)
		{
			global $awt;
			$awt->start("form_filter_search_element::process_entry");
			$awt->count("form_filter_search_element::process_entry");

			$r=  $this->core_process_entry(&$entry,$id,$prefix);
			$awt->stop("form_filter_search_element::process_entry");
			return $r;
		}

		function gen_show_html()
		{
			echo("gen_show_html");
			if (!$this->entry_id)
				return "";

			// damn! ma ei tea kuidas php-s runtime typecasti teha!
			// muidu tuleks siin teha
			// return ((form_entry_element)$this)->gen_show_html();
			// aga oi jah, php-s vist polegi vtablet ja veel vähem mingeid typecaste.
			classload("form_entry_element");
			//return ((form_entry_element)$this)->gen_show_html();
			$el=new form_entry_element();
			$el->id=$this->id;
			$el->parent=$this->parent;
			$el->form=&$this->form;
			$el->currency=&$this->currency;
			$el->arr=&$this->arr;
			$el->entry=&$this->entry;
			return $el->gen_show_html();// OO my azz
		}

	
	}
?>
