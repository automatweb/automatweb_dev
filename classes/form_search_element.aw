<?php
if (defined("FORM_SELEMENT_LOADED")) {
} else {
define(FORM_SELEMENT_LOADED,1);
	session_register("clipboard");

	$formcache = -1;	// array of forms
	$mcnt = 0;				// max number of elements in a form
	$chcnt = 1;				// max number of chars on element texts

	class form_search_element extends form_element
	{
		function form_search_element($id = -1)
		{
			$this->tpl_init("forms");
			$this->db_init();

			$this->entry_id = 0;
			$this->id = $id;
			if ($id != -1)
				$this->load($id);
		}

		function gen_admin_html(&$cell)
		{
			$this->read_template("admin_search_element.tpl");
			$chk_val = " CHECKED ";
		
			global $formcache,$mcnt,$chcnt;

			if (!is_array($formcache))
			{
				$formcache = array();
				$this->db_query("SELECT forms.*,objects.name as name FROM forms 
												 LEFT JOIN objects ON objects.oid = forms.id
												 WHERE forms.type = 1 AND objects.status != 0 
												 GROUP BY objects.oid");
				while ($row = $this->db_next())
				{
					$form = unserialize($row[content]);
					$fid = $row[id];
					$formcache[$row[id]] = array("id" => $row[id], "name" => $row[name], "content" => $form);

					$elnum = 0;
					for ($row = 0; $row <  $form[rows]; $row++)
					{
						for ($col = 0; $col <  $form[cols]; $col++)
						{
							$elar = $form[elements][$row][$col];
							if (is_array($elar))
							{
								reset($elar);
								while (list($elid, $el) = each($elar))
								{
									// el_el_id = elemendi NUMBER selle formi sees
									$this->vars(array("form_id" => $fid, "el_el_id" => $elnum++, "el_value" => $elid, 
																"el_text" => ($el[name] == "" ? $el[text] == "" ? $el[type] : $el[text] : $el[name]),
																"el_type"	=> $el[type]));
									$formcache[$fid][elements][$elid] = array("el_text" => $el[text], "el_type" => $el[type],"el_name" => $el[name]);
									$ed.=$this->parse("ELDEFS");
								}
							}
						}
					}
					if ($elnum > $max_els)
					{
						$max_els = $elnum;
					}
				}
				$this->vars(array("ELDEFS" => $ed));
				$this->vars(array("SCRIPT" => $this->parse("SCRIPT")));
			}
			else
				$this->vars(array("SCRIPT" => ""));

			$sel_form = -1; $lid = -1;
			reset($formcache);
			while (list($id,$v) = each($formcache))
			{
				$this->vars(array("sel_form_active" => ($this->arr[linked_form] == $id ? "SELECTED" : ""),
													"sel_form_value"	=> $id,	
													"sel_form_name"		=> $v[name]));
				$fs.=$this->parse("FORMSEL");

				if ($sel_form == -1)
					if ($this->arr[linked_form] == $id)
						$sel_form = $id;
				
				if ($lid == -1)
					$lid = $id;
			}
			$this->vars(array("FORMSEL" => $fs));

			if ($sel_form == -1)
				$sel_form = $lid;

			$cnt =0;
			if (is_array($formcache[$sel_form][elements]))
			{
				reset($formcache[$sel_form][elements]);
				while (list($id, $ar) = each($formcache[$sel_form][elements]))
				{
					$nc = $chcnt-strlen($ar[el_text]);
					$chs = $nc > 0 ? str_repeat("&nbsp;",$nc) : "";

					$this->vars(array("sel_el_active" => ($this->arr[linked_element] == $id ? "SELECTED" : ""), 
														"sel_el_value"	=> $id, 
														"sel_el_name"		=> $ar[el_name] == "" ? $ar[el_text].$chs : $ar[el_name]));
					$es.=$this->parse("ELSEL");
					$cnt++;
				}
			}

			for ($i=0; $i < ($max_els-$cnt); $i++)
			{
				$this->vars(array("sel_el_active" => "", "sel_el_value" => "", "sel_el_name" => str_repeat("&nbsp;",$chcnt)));
				$es.=$this->parse("ELSEL");
			}

			$this->vars(array("ELSEL" => $es));
			$this->vars(array("el_id" => "el_".$this->id, "el_text" => $this->arr[text]));

			$GLOBALS["script"] .= "ch_type(document.f1.el_".$this->id."_element,document.f1.el_".$this->id."_form,\"el_".$this->id."\");";

			return $this->parse();
		}

		function save(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);

			$base = "el_".$this->id;
			
			$var=$base."_text";
			$this->arr[text] = $$var;

			$var=$base."_form";
			$this->arr[linked_form] = $$var;

			$var=$base."_element";
			$this->arr[linked_element] = $$var;

			return true;
		}

		function gen_user_html_not(&$images)		// function that doesn't use templates
		{
			if ($this->arr[linked_element] > 0)
			{
				global $formcache;
				if (!isset($formcache[$this->arr[linked_form]]))
				{
					$formcache[$this->arr[linked_form]] = new form;
					$formcache[$this->arr[linked_form]]->load($this->arr[linked_form]);
				}
				$form = &$formcache[$this->arr[linked_form]];

				$t = $form->get_element_by_id($this->arr[linked_element]);

				$t->entry = $this->entry;
				$t->entry_id = $this->entry_id;
				if ($t->get_type() == 'listbox')
				{
					// add an empty element to the listbox so we can tell the difference, 
					// if nothing was selected and we can then ignore the lb in the search
					$t->arr[listbox_items][$t->arr[listbox_count]] = "";
					$t->arr[listbox_default] = $t->arr[listbox_count];
					$t->arr[listbox_count]++;
				}
				if ($this->arr[text] != "")
					$t->arr[text] = $this->arr[text];

				if (!($t->get_type() == 'file' || $t->get_type() == 'link'))
					return $t->gen_user_html_not(&$images);
				else
					return "";
			}
			else
				return "";
		}

		function process_entry(&$entry, $id)
		{
			if (!$this->arr[linked_element])
				return;

			global $formcache;
			if (!isset($formcache[$this->arr[linked_form]]))
			{
				$formcache[$this->arr[linked_form]] = new form;
				$formcache[$this->arr[linked_form]]->load($this->arr[linked_form]);
			}
			$form = &$formcache[$this->arr[linked_form]];

			$t = $form->get_element_by_id($this->arr[linked_element]);
			if ($t->get_type() != "listbox")
			{
				$te = array();
				$t->process_entry(&$te, $id);
				$entry[$this->id] = $te[$this->arr[linked_element]];
			}
			else
			{
				// check if the empty element that we added was selected and if it was, don't write anything to the db, 
				// so we can easily ignore the element in the search
				$var = $t->get_id();
				global $$var;

				if ($$var == "element_".$this->arr[linked_element]."_lbopt_".$t->arr[listbox_count])
					$entry[$this->id] = "";
				else
					$entry[$this->id] = $$var;
			}
		}

		function gen_show_html()
		{
			if (!$this->entry_id)
				return "";

			global $formcache;
			if (!isset($formcache[$this->arr[linked_form]]))
			{
				$formcache[$this->arr[linked_form]] = new form;
				$formcache[$this->arr[linked_form]]->load($this->arr[linked_form]);
			}
			$form = &$formcache[$this->arr[linked_form]];

			$t = $form->get_element_by_id($this->arr[linked_element]);
			$t->entry = $this->entry;
			$t->entry_id = $this->entry_id;
			return $t->gen_show_html();
		}
	}
}
?>