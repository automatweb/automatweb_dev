<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/persons_webview.aw,v 1.6 2006/06/20 12:22:58 markop Exp $
// persons_webview.aw - Kliendihaldus 
/*

@classinfo syslog_type=ST_PERSONS_WEBVIEW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property company type=relpicker reltype=RELTYPE_COMPANY
@caption Ettevõte

@property departments type=relpicker multiple=1 reltype=RELTYPE_DEPARTMENT
@caption Osakonnad

//----------------------------------------------
@groupinfo view caption=Näitamine
@default group=view

- Isikute järjestamisprintsiip (saab valida mitu, peale salvestamist tekib uus valik), Listbox Omadus (perenimi, ametinimetuse jrk, isiku jrk), mille kõrval Listbox Järjestamine (Väiksem enne/Suurem enne)
@property persons_principe type=callback callback=callback_get_persons_principe
@caption Isikute järjestamisprintsiip

property persons_principe_property type=select
caption Isikute järjestamisprintsiibi omadus

property persons_principe_direct type=select
caption .

- Grupeerimine osakonna järgi (märkeruut)
@property department_grouping type=checkbox ch_value=1
@caption Grupeerimine osakonna järgi

- Grupeerimise järjestamisprintsiip (analoogne isikutega, osakonna jrk/osakonna nimi alusel)
@property grouping_principe type=callback callback=callback_get_grouping_principe
@caption Grupeerimise järjestamisprintsiip

- Vaadete tabel (salvestamisel uus rida)
@property view_table callback=callback_get_view_table
@caption Vaadete tabel

-- Vaade 1 (tekst)
@property view1 type=textbox
@caption Vaade 1

-- templeit (tekstikast, viide failisüsteemis olevale templeidile, kataloog on eeldefineeritud)
@property template type=textbox
@caption Template

-- osakondade tasemeid (tekstikast, mitu osakondade taset sellel lehel sisse on vaja lugeda), võib olla ka kujul 1-2 (ehk loetakse sisse ning kuvatakse 1 ja 2 taseme osakonnad, kui on kirjutatud 1, siis kuvatakse ainult Osakonnad propertys valitud osakonnad)
@property department_levels type=textbox
@caption Osakondade tasemed

-- raadionupud (ainult osakonnad/koos isikutega) - selleks, et ei loetaks tingimata sisse nende osakondade isikuid, kui on aja näidata ainult osakondade andmeid.
@property with_without_persons type=chooser orient=vertical store=yes method=serialize
@caption Sisse lugeda

-- tulpade arv (tekstikast) - mitmes tulbas kuvatakse inimeste andmeid
@property columns type=textbox
@caption Tulpade arv

-- read ametinimetuste alusel (märkeruut) - sama ametinimega isikuid üritatakse sama rea peale paigutada
@property rows_by type=checkbox ch_value=1 
@caption Read ametinimetuse alusel

-- min tulpade arv (tekstikast) - erineva tasemega ametinimetusele vastavad isikud võib ka kõrvuti panna, kui min tulpade arv mingis reas ei ole saavutatud.
@property min_cols type=textbox
@caption Minimaalne tulpade arv

@reltype COMPANY value=1 clid=CL_CRM_COMPANY
@caption Registri andmed

@reltype DEPARTMENT value=2 clid=CL_CRM_SECTION,CL_CRM_COMPANY
@caption Seadete vorm
*/
class persons_webview extends class_base
{
	function persons_webview()
	{
		$this->init(array(
			"tpldir" => "crm/persons_webview",
			"clid" => CL_PERSONS_WEBVIEW
		));
	
		$this->persons_sort_order = array(
			0 => "",
			"last_name" => t("perenimi"),
			"proffession" => t("ametinimetuse jrk"),
			"jrk" => t("isiku jrk"),
		);
		
		$this->department_sort_order = array(
			0 => "",
			"jrk" => t("osakonna jrk"),
			"name" => t("osakonna nimi"),
		);
	
		$this->order = array (
			"ASC" => t("Kasvav"),
			"DESC" => t("Kahanev"),
		);
		
		$this->education["options"] = array(
			0 => t("-- vali --"),
			1 => t("põhi"),
			2 => t("kesk"),
			3 => t("kesk-eri"),
			4 => t("kõrgem"),
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "with_without_persons":
				$prop["options"] = array(
					0 => t("ainult osakonnad"),
					1 => t("koos isikutega"),
				);
				break;
			case "departments":
				$company = obj($arr["obj_inst"]->prop("company"));
				$comp = get_instance("crm/crm_company");
				foreach($comp->get_all_org_sections($company) as $section_id)
				{
					$section = obj($section_id);
					$prop["options"][$section_id] = $section->name();
				}
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
			case "persons_principe":
				$this->submit_meta($arr);
				break;
		}
		return $retval;
	}

	function submit_meta($arr = array())
	{
		$arr["obj_inst"]->set_meta("persons_principe", array($arr["request"]["persons_principe"]));
 		$arr["obj_inst"]->set_meta("grouping_principe", array($arr["request"]["grouping_principe"]));
 		$arr["obj_inst"]->set_meta("view_table", array($arr["request"]["view_table"]));
 	}

	function callback_get_persons_principe($arr)
	{
		$principe = $arr["obj_inst"]->meta("persons_principe");
		$count = sizeof($principe);
		if($count > 0 && !$principe[$count-1]["principe"])$count--;
		if($count > 0 && !$principe[$count-1]["principe"])$count--;
		$ret = array();
		
		for($i = 0; $i < $count+1; $i++)
		{
			$nm = "persons_principe[".$i."][principe]";
			$ret[$nm] = array("name" => $nm, "caption" => t("Isikute järjestamisprintsiip $i"), "type" => "select", "options" => $this->persons_sort_order, "value" => $principe[$i]["principe"]);
			$nm = "persons_principe[".$i."][order]";
			$ret[$nm] = array("name" => $nm,  "type" => "select", "options" => $this->order, "value" => $principe[$i]["order"]);
		}
		return $ret;
	}
	
	function callback_get_grouping_principe($arr)
	{
		$principe = $arr["obj_inst"]->meta("grouping_principe");
		$count = sizeof($principe);
		if($count > 0 && !$principe[$count-1]["principe"])$count--;
		if($count > 0 && !$principe[$count-1]["principe"])$count--;
		$ret = array();
		for($i = 0; $i < $count+1; $i++)
		{
			$nm = "grouping_principe[".$i."][principe]";
			$ret[$nm] = array("name" => $nm, "caption" => t("Osakondade järjestamisprintsiip $i"), "type" => "select", "options" => $this->department_sort_order, "value" => $principe[$i]["principe"]);
			$nm = "grouping_principe[".$i."][order]";
			$ret[$nm] = array("name" => $nm,  "type" => "select", "options" => $this->order, "value" => $principe[$i]["order"]);
		}
		return $ret;
	}
	
	function callback_get_view_table($arr)
	{
		$view_table = $arr["obj_inst"]->meta("view_table");
		$count = sizeof($view_table);
		if($count > 0 && !$view_table[$count-1])$count--;
		if($count > 0 && !$view_table[$count-1])$count--;
		$ret = array();
		
		for($i = 0; $i < $count+1; $i++)
		{
			if($i==0) $caption = "Vaadete tabelid";
			else $caption = "";
			$nm = "view_table[".$i."]";
			$ret[$nm] = array("name" => $nm, "caption" => t($caption), "type" => "textbox", "value" => $view_table[$i]);
		}
		return $ret;
	}
	
	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function sort_by($args)
	{
		extract($args);
		switch($orderby)
		{
			case "last_name":
				foreach($workers as $worker)
				{
					$workers_tmp[] = array("sort" => $worker["worker"]->prop("lastname"), "data" => $worker);
				}
				break;
			case "proffession":
				foreach($workers as $worker)
				{
					$jrk = 0;
					if(is_oid($worker["worker"]->prop("rank")))
					{
						$profession_obj = obj($worker["worker"]->prop("rank"));
						$jrk = $profession_obj->prop("jrk");
					}
					$workers_tmp[] = array("sort" => $jrk, "data" => $worker);
				}
				break;
			case "jrk":
// 				foreach($workers as $worker)
// 				{
// 					$workers_tmp[] = array("sort" => $worker["worker"]->prop("lastname"), "data" => $worker);
// 				}
// 				break;
		}
	
		foreach ($workers_tmp as $key => $row) {
			$data[$key]  = $row['data'];
			$sort[$key] = $row['sort'];
		}
		if($sort_order == "ASC") $sort_order = SORT_ASC;
		else $sort_order = SORT_DESC;
		array_multisort($sort, $sort_order, $workers_tmp);
		
		$workers = array();
		foreach ($workers_tmp as $data)// teeb massiivi vanale kujule tagasi
		{
			$workers[] = $data["data"];
		}
		return $workers;
	}
	
	function person_sort($workers)
	{
		enter_function("person_webview::person_sort");
		$principe = $this->view_obj->prop("persons_principe");
		$count = sizeof($principe);
		while($count>=0)
		{
			if($principe[$count]["principe"])
			{
				$workers = $this->sort_by(array(
					"workers" => $workers,
					"orderby" => $principe[$count]["principe"],
					"sort_order" => $principe[$count]["order"],
				));
			}
			$count--;
		}
		exit_function("person_webview::person_sort");
		return ($workers);
	}
	
	function sort_sections($sections)
	{
		enter_function("person_webview::section_sort");
		if(sizeof($sections) < 2) return $sections;
		$principe = $this->view_obj->prop("grouping_principe");
		$count = sizeof($principe);
		while($count>=0)
		{
			if($principe[$count]["principe"])
			{
				$sections = $this->section_sort_by(array(
					"sections" => $sections,
					"orderby" => $principe[$count]["principe"],
					"sort_order" => $principe[$count]["order"],
				));
			}
			$count--;
		}
		exit_function("person_webview::section_sort");
		return ($sections);
	}

	function section_sort_by($args)
	{
		extract($args);
		switch($orderby)
		{
			case "name":
 				foreach($sections as $section)
 				{
 					$sections_tmp[] = array("sort" => $section->name(), "data" => $section);
 				}
 				break;
			case "jrk":
 				foreach($sections as $section)
 				{
 					$sections_tmp[] = array("sort" => $section->prop("jrk"), "data" => $section);
 				}
 				break;
		}
	
		foreach ($sections_tmp as $key => $row) {
			$data[$key]  = $row['data'];
			$sort[$key] = $row['sort'];
		}
		if($sort_order == "ASC") $sort_order = SORT_ASC;
		else $sort_order = SORT_DESC;
		array_multisort($sort, $sort_order, $sections_tmp);
		$sections = array();
		foreach ($sections_tmp as $data)// teeb massiivi vanale kujule tagasi
		{
			$sections[] = $data["data"];
		}
		return $sections;
	}

	function set_levels()
	{
		$levels = $this->view_obj->prop("department_levels");
		$possible_levels = explode("," , $levels);
		if(sizeof($possible_levels) > 1)
		{
			$levels = $possible_levels;
		}
		else
		{
			$from_to = explode("-" , $levels);
			$possible_levels = array();
			while($from_to[0] <= $from_to[1])
			{
				$possible_levels[] = $from_to[0];
				$from_to[0]++;
			}
			if(sizeof($possible_levels)>0) $levels = $possible_levels;
			else $levels = array($levels);
		}
		$this->levels = $levels;
	}
	
	function parse_alias($arr)
	{
		$view_obj = $this->view_obj = obj($arr["alias"]["to"]);
		$company_id = $view_obj->prop("company");
		$departments = $view_obj->prop("departments");
		if(!is_oid($company_id)) return t("pole asutust valitud");
		$company = obj($company_id);
		$template = $view_obj->prop("template");
	
		$this->read_template($template);

		$this->set_levels();//teeb siis erinevatest tasemetest massiivi, mida üldse kuvada ja paneb selle muutujasse $this->levels
		if($view_obj->prop("department_grouping"))
		{
			if($this->is_template("DEPARTMENT"))
			{
				$this->jrks = array();
				$sections = $this->get_sections(array("section" => $company , "jrk" => 0));
				foreach($sections as $section)
				{
					if((!in_array($section->id(), $view_obj->prop("departments")) || !sizeof($view_obj->prop("departments"))>0)) continue;
					if($view_obj->prop("with_without_persons"))
					{
						$workers = $this->get_workers($section);
						$this->parse_persons(array("workers" => $workers, "view_obj" => $view_obj));
					}
					//if(sizeof($workers) > 0)
					$this->parse_section($section);
					if($this->is_template("LEVEL".$this->jrks[$section->id()]."DEPARTMENT"))
						$department .= $this->parse("LEVEL".$this->jrks[$section->id()]."DEPARTMENT");
					else $department .= $this->parse("DEPARTMENT");
				}
				$this->vars(array("DEPARTMENT" => $department));
			}
		}
		else
		{
			if($view_obj->prop("with_without_persons"))
			{
				$workers = $this->get_workers($company);
				$this->parse_persons(array("workers" => $workers, "view_obj" => $view_obj));
			}
			if($this->is_template("DEPARTMENT"))//juhuks kui DEPARTMENT sub sisse on jäänud... mida tegelt pole vaja
			{
				$department .= $this->parse("DEPARTMENT");
				$this->vars(array("DEPARTMENT" => $department));
			}
		}
		$this->vars(array(
			"name" => $company->prop("name"),
		));
		return $this->parse();
	}

	function parse_section($section)
	{
		enter_function("person_webview::parse_section");
		$phone = "";
		if(is_oid($section->prop("phone_id"))) $phone_obj = obj($section->prop("phone_id"));
		else $phone_obj = $section->get_first_obj_by_reltype("RELTYPE_PHONE");
		if(is_object($phone_obj)) $phone = $phone_obj->name();
		
		$email = "";
		if(is_oid($section->prop("email_id"))) $email_obj = obj($section->prop("email_id"));
		else $email_obj = $section->get_first_obj_by_reltype("RELTYPE_EMAIL");
		if(is_object($email_obj)) $email = $email_obj->prop("mail");
		
		$fax = "";
		if(is_oid($section->prop("telefax_id"))) $fax_obj = obj($section->prop("telefax_id"));
		else $fax_obj = $section->get_first_obj_by_reltype("RELTYPE_TELEFAX");
		if(is_object($fax_obj)) $fax = $fax_obj->name();
		
		$address = "";
		$address_id = $section->prop("contact");
		if(is_oid($address_id))
		{
			$address_obj = obj($address_id);
			$address = $address_obj->name();
		}		
		$this->vars(array(
			"department_name" => $section->name(),
			"phone"	=> $phone,
			"email" => $email,
			"fax" => $fax,
			"address" => $address,
			"document" => $section->prop("link_document"),
		));
		exit_function("person_webview::parse_section");
	}

	function get_workers($section)
	{
		enter_function("person_webview::get_workers");
		$workers_list = new object_list($section->connections_from (array (
			"type" => "RELTYPE_WORKERS",
		)));
		//------------------------sorteerib kõvemad vennad ette;
		foreach($workers_list->arr() as $worker)
		{
			$jrk = 0;
			if(is_oid($worker->prop("rank")))
			{
				$profession_obj = obj($worker->prop("rank"));
				$jrk = $profession_obj->prop("jrk");
			}
			$workers[] = array("worker" => $worker, "jrk" => $jrk);
		}
		foreach ($workers as $key => $row) {
			$person[$key]  = $row['worker'];
			$jrk_[$key] = $row['jrk'];
		}
		array_multisort($jrk_, SORT_DESC, $person, SORT_DESC, $workers);
		$principe = $this->view_obj->prop("persons_principe");
		if($principe[0]["principe"]) $workers = $this->person_sort($workers);
		exit_function("person_webview::get_workers");
		return $workers;
	}

	function get_sections($args)
	{
		enter_function("person_webview::get_sections");
		extract($args);
		$sections = array();
		$section_list = new object_list($section->connections_from (array (
			"type" => "RELTYPE_SECTION",
		)));
		$section_arr = $this->sort_sections($section_list->arr());
		foreach($section_arr as $sec)
		{
			if(in_array(($jrk + 1) , $this->levels) && (sizeof($this->levels) > 0))$sections[] = $sec;
			$sections = array_merge($sections , $this->get_sections(array("section" => $sec, "jrk" => ($jrk+1))));
			$this->jrks[$sec->id()] = $jrk + 1;
		}
		enter_function("person_webview::get_sections");
		return $sections;
	}
	
	function parse_proffession($worker)
	{
		$rank = $directive = "";
		$rank_obj = $worker->get_first_obj_by_reltype("RELTYPE_RANK");
		if(is_object($rank_obj))
		{
			$rank = $rank_obj->name();
			if(is_oid($rank_obj->prop("directive")) && $this->can("view", $rank_obj->prop("directive")))
			{
				$directive = $rank_obj->prop("directive");
			}
			else
			{
				$directive_obj = $rank_obj->get_first_obj_by_reltype("RELTYPE_DESC_FILE");
				if(is_object($directive_obj))
				$directive = $directive_obj->id();
			}
		}
		$rank_with_directive = $rank;
		if(is_oid($directive))$rank_with_directive = '<a href ="'.$directive.'"> '. $rank_with_directive.' </a>';
		$this->vars(array(
			"rank" => $rank,
			"directive" => $directive,
			"rank_with_directive" => $rank_with_directive,
		));
	}

	function parse_persons($args)
	{
		enter_function("person_webview::parse_persons");
		extract($args);
		$this->count = 0;
		$col = 0;
		$this->max_col = $col_num = $max_col = $view_obj->prop("columns");
		$column = "";
		$row = "";
		$row_num = 0;
		$this->min_col = $view_obj->prop("min_cols");
		$image_inst = get_instance(CL_IMAGE);
		$this->calculated=0;
		
		if($this->is_template("ROW") && $this->is_template("COL"))
		{
			foreach($workers as $val)
			{
				$worker = $val["worker"];
				if($view_obj->prop("rows_by"))//ametinimede kaupa grupeerimise porno, et erinevale reale õige arv tuleks jne
				{
					if(!$this->order_array) $this->make_order_array($workers);
					if(!$this->calculated) $col_num = $this->get_cols_num($row_num);
				}
				$c = "";
				if($this->is_template("worker"))
				{
					$this->parse_proffession($worker);
					$photo="";
					if(is_oid($worker->prop("picture")) && $this->can("view", $worker->prop("picture")))
					{
						$photo = $image_inst->make_img_tag_wl($worker->prop("picture"));
					}
					else
					{
						$photo_obj = $worker->get_first_obj_by_reltype("RELTYPE_PICTURE");
						if(is_object($photo_obj))
						$photo = $image_inst->make_img_tag_wl($photo_obj->id());
					}
					
					$phone = "";
					$phone_obj = $worker->get_first_obj_by_reltype("RELTYPE_PHONE");
					if(is_object($phone_obj)) $phone = $phone_obj->name();
					$email = "";
					$email_obj = $worker->get_first_obj_by_reltype("RELTYPE_EMAIL");
					if(is_object($email_obj)) $email = $email_obj->prop("mail");
					
					$name_with_email = $worker->name();
					if(strlen($email) > 3)$name_with_email = '<a href =mailto:'.$email.'> '. $name_with_email.' </a>';
					$speciality = "";
					$speciality_obj = $worker->get_first_obj_by_reltype("RELTYPE_EDUCATION");
					if(is_object($speciality_obj)) $speciality = $speciality_obj->prop("speciality");
					$wage_doc_exist = "";
					if(is_oid($worker->prop("wage_doc"))) $wage_doc_exist = '<a href ='.$worker->prop("wage_doc").'> '. t("Palk").' </a>';
					$this->vars(array(
					//	"rank" => $rank,
						"name" => $worker->name(),
						"photo" => $photo,
						"phone" => $phone,
						"email" => $email,
						"education" => $this->education["options"][$worker->prop("edulevel")],
						"speciality" => $speciality,
						"name_with_email" => $name_with_email,
						"wage_doc"	=> $worker->prop("wage_doc"),
						"wage_doc_exist" => $wage_doc_exist,
					//	"directive" => $directive,
					));
					$c .= $this->parse("worker");
				}
				$this->vars(array(
					"worker" => $c,
				));
				$column .= $this->parse("COL");
				$col++;
				$parsed = 0;
				if($col == $col_num)
				{
					$this->vars(array(
						"COL" => $column,
					));
					$column = "";
					$row .= $this->parse("ROW");
					$col = 0;
					$parsed = 1;
					$col_num = $max_col;;
					$this->calculated = 0;
					$row_num++;
				}
			$this->count++;
			}
			if(!$parsed)//viimane rida võib olla tegemata
			{
				$this->vars(array(
					"COL" => $column,
				));
				$column = "";
				$row .= $this->parse("ROW");
				$col = 0;
				$parsed = 1;
			}
			$this->vars(array(
				"ROW" => $row,
			));
		exit_function("person_webview::parse_persons");
		}
	}

	function get_cols_num($row)
	{
//		if(!$this->order_array) $this->make_order_array($workers);
		$this->calculated = 1;
		return sizeof($this->order_array[$row]);
/*		
		$jrk = $workers[$this->count]["jrk"];
		$count = 0;
		if(!($jrk > 0)) return $this->max_col;
		while($jrk == $workers[$this->count + $count]["jrk"])
		{
			$count++;
		}
		if($count == $this->max_col) return $this->max_col;
		if(($count - $this->max_col) > $this->min_col) return $this->max_col;
		if($count < $this->max_col)
		{
			$count_next = $count;
			$jrk_next = $workers[$this->count + $count]["jrk"];//arr($jrk_next);arr($workers[$this->count + $count_next]["jrk"]);
			while(($jrk_next > 0) && $jrk_next == $workers[$count_next + $this->count]["jrk"])
			{
				$count_next++;
			}
			$count_next = $count_next - $count;echo $count_next. ' ' . $count.'<br>';
			if(($count_next + $count) <= $this->max_col) return ($count_next + $count);
			
			if(($count >= $this->min_col)&&($count_next >= $this->min_col)) return $count;
		}
		if($count >= $this->min_col)
		{
			if(($count_next + $count) <= $this->max_col) return ($count_next + $count);
		}
		return $this->max_col;*/
	}

	function make_order_array($workers)
	{
		$this->order_array = array();
		$x = 0;
		$cols = 0;
		$jrk = $workers[0]["jrk"];
		foreach($workers as $data)
		{
			if($data["jrk"] != $jrk || $cols >= $this->max_col )
			{
				$cols = 0;
				$jrk = $data["jrk"];
				$x++;
			}
			$this->order_array[$x][] = $data;
			$cols++;
		}
		$x = 0;
		while($x < 10)//miski suht random arv moment, et mitu korda ikka läbi käia nimekiri
		{
			$small_rows = 0;
			$row_num = 0;
			foreach($this->order_array as $key => $row)
			{
				if(sizeof($this->order_array[$row_num]) < $this->min_col){
					$small_rows++;
					if((sizeof($this->order_array[$row_num-1]) > 0)
						&& (sizeof($this->order_array[$row_num-1]) < $this->max_col)
						&& (sizeof($this->order_array[$row_num-1])+ sizeof($this->order_array[$row_num]) <= $this->max_col)
						&& $x<2)
					{
						$this->order_array[$row_num-1] = array_merge($this->order_array[$row_num-1] , $this->order_array[$row_num]);
						unset($this->order_array[$row_num]);
					}
					elseif((sizeof($this->order_array[$row_num+1]) > 0)
						&& (sizeof($this->order_array[$row_num+1]) < $this->max_col)
						&& (sizeof($this->order_array[$row_num+1])+ sizeof($this->order_array[$row_num]) <= $this->max_col)
						&& $x<2)
					{
						$this->order_array[$row_num+1] = array_merge($this->order_array[$row_num+1] , $this->order_array[$row_num]);
						unset($this->order_array[$row_num]);
					}
					elseif(((sizeof($this->order_array[$row_num-1])+ sizeof($this->order_array[$row_num]))/2 <= $this->max_col)
						&& $x>1
						&& $this->order_array[$row_num-1][0]["jrk"] == $this->order_array[$row_num][0]["jrk"]
					)
					{
						$this->order_array[$row_num] = array_merge(array(0 => $this->order_array[$row_num-1][sizeof($this->order_array[$row_num-1])-1]) , $this->order_array[$row_num]);
						unset($this->order_array[$row_num-1][sizeof($this->order_array[$row_num-1])-1]);
					}
				}
				$row_num++;
			}
			if($small_rows == 0) break;
			$x++;
			}
		$tmp = array();
		foreach ($this->order_array as $data)
		{
			if(sizeof($data) > 0) $tmp[] = $data;
		}
		$this->order_array = $tmp;
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");

		
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
