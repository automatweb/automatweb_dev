<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/persons_webview.aw,v 1.13 2006/07/19 12:49:23 markop Exp $
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
@property view callback=callback_get_view_table
@caption Vaadete tabel

-- Vaade 1 (tekst)
property view1 type=textbox
caption Vaade 1

-- templeit (tekstikast, viide failisüsteemis olevale templeidile, kataloog on eeldefineeritud)
property template type=textbox
caption Template

-- osakondade tasemeid (tekstikast, mitu osakondade taset sellel lehel sisse on vaja lugeda), võib olla ka kujul 1-2 (ehk loetakse sisse ning kuvatakse 1 ja 2 taseme osakonnad, kui on kirjutatud 1, siis kuvatakse ainult Osakonnad propertys valitud osakonnad)
property department_levels type=textbox
caption Osakondade tasemed

-- raadionupud (ainult osakonnad/koos isikutega) - selleks, et ei loetaks tingimata sisse nende osakondade isikuid, kui on aja näidata ainult osakondade andmeid.
property with_without_persons type=chooser orient=vertical store=yes method=serialize
caption Sisse lugeda

-- tulpade arv (tekstikast) - mitmes tulbas kuvatakse inimeste andmeid
property columns type=textbox
caption Tulpade arv

-- read ametinimetuste alusel (märkeruut) - sama ametinimega isikuid üritatakse sama rea peale paigutada
property rows_by type=checkbox ch_value=1 
caption Read ametinimetuse alusel

-- min tulpade arv (tekstikast) - erineva tasemega ametinimetusele vastavad isikud võib ka kõrvuti panna, kui min tulpade arv mingis reas ei ole saavutatud.
property min_cols type=textbox
caption Minimaalne tulpade arv

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
			"profession" => t("ametinimetuse jrk"),
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
		
		$this->help = nl2br(htmlentities(t("
			Osakondade tasemed, mida näidataks, saab märkida kujul '1,2,3' või '1-2' või '3'
			teplates peaks olema subid umbes kujul:
			<!-- SUB: DEPARTMENT -->
			{VAR:department_name}
			<table>
			<!-- SUB: ROW -->
				<tr>
				<!-- SUB: COL -->
					<td>
					<!-- SUB: worker -->
						{VAR:profession} {VAR:name}
					<!-- END SUB: worker -->
					</td>
				<!-- END SUB: COL -->
				</tr>
			<!-- END SUB: ROW -->
			</table>
			<!-- SUB: DEPARTMENT -->
			
			juhul kui miski taseme osakonda oleks vaja teistmoodi näidata, siis tuleks <!-- SUB: DEPARTMENT --> sisse teha <!-- SUB: LEVEL4DEPARTMENT --> (vastavalt taseme numbrile) , mis oleks muidu sama struktuuriga nagu DEPARTMENT

			muutujad mida saab kasutada:
			DEPARTMENT sub'is: department_name, address , phone , fax , email , next_level_link (link nägemas antud osakonda uues vaates).
			sub'is worker :
			name , name_with_email , email , emails, photo, contact,
			profession , profession_with_directive , professions , directive (ametijuhend), wage_doc , wage_doc_exist (palgaandmete dokument, kui on olemas),
			education (haridustase) , school , subject_field(valdkond) , speciality ,
			phone, phones , home_phone, home_phones, mobile_phone, mobile_phones, skype_phone, skype_phones, short_phone, short_phones, work_phone, work_phones, extension_phone, extension_phones,
			next_level_link (link j'gmise taseme vaatesse... kui tegu siis antud inimesega),
			company, section,
			url, urls
			ta1 - ta5 (kasutajadefineeritud muutujad).

			Kui lisada objekt menüüsse, siis esimeseks vaate infoks tuleb menüüs olev.
			Kui viimaseks vaateks on üks konkreetne isik, siis template sees ühtegi SUBi ei tohiks olla, kasutada saab samu muutujaid, mis muidu sub'is worker
			Template'ide tõlkimiseks kasutada faili persons_web_view.aw.
			")));
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
				if(is_oid($arr["obj_inst"]->prop("company")) && $this->can("view" , $arr["obj_inst"]->prop("company")))
				{;
					$company = obj($arr["obj_inst"]->prop("company"));
					$comp = get_instance("crm/crm_company");
					foreach($comp->get_all_org_sections($company) as $section_id)
					{
						$section = obj($section_id);
						$prop["options"][$section_id] = $section->name();
					}
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
 		$arr["obj_inst"]->set_meta("view", array($arr["request"]["view"]));
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
			$ret[$nm] = array(
				"name" => $nm,
				"caption" => t(($i + 1).". Isikute järjestamisprintsiip"),
				"type" => "text", 
				"value" => html::select(array(
					"name" => "persons_principe[".$i."][principe]",
					"options" => $this->persons_sort_order,
					"value" => $principe[$i]["principe"],
				)).html::select(array(
					"name" => "persons_principe[".$i."][order]",
					"options" => $this->order,
					"value" => $principe[$i]["order"],
				)),
//				"type" => "select",
//				"options" => $this->persons_sort_order,
//				"value" => $principe[$i]["principe"]
				);
//			$nm = "persons_principe[".$i."][order]";
//			$ret[$nm] = array("name" => $nm,  "type" => "select", "options" => $this->order, "value" => $principe[$i]["order"]);
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
			$ret[$nm] = array(
				"name" => $nm,
				"caption" => t(($i + 1).". Osakondade järjestamisprintsiip"),
				"type" => "text",
				"value" => html::select(array(
					"name" => $nm,
					"options" => $this->department_sort_order,
					"value" => $principe[$i]["principe"],
				)).html::select(array(
					"name" => "grouping_principe[".$i."][order]",
					"options" => $this->order,
					"value" => $principe[$i]["order"],
				)),
			);
	//		$nm = "grouping_principe[".$i."][order]";
	//		$ret[$nm] = array("name" => $nm,  "type" => "select", "options" => $this->order, "value" => $principe[$i]["order"]);
		}
		return $ret;
	}
	
	function callback_get_view_table($arr)
	{
		$view = $arr["obj_inst"]->meta("view");
		$count = sizeof($view);
		if($count > 0 && !$view[$count-1]["template"])$count--;
		if($count > 0 && !$view[$count-1]["template"])$count--;
		$ret = array();
		
		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Vaade"),
		));
		$t->define_field(array(
			"name" => "template",
			"caption" => t("Templeit"),
		));
		$t->define_field(array(
			"name" => "department_levels",
			"caption" => t("Osakondade tasemeid"),
		));
		$t->define_field(array(
			"name" => "with_persons",
			"caption" => t("Koos inimkoosseisuga"),
		));
		$t->define_field(array(
			"name" => "columns",
			"caption" => t("Tulpade arv"),
		));
		$t->define_field(array(
			"name" => "rows_by",
			"caption" => t("Read ametinimetuste alusel"),
		));
		$t->define_field(array(
			"name" => "min_cols",
			"caption" => t("Minimaalne tulpade arv"),
		));
		$nm = "view";
		for($i = 0; $i < $count+1; $i++)
		{
			if($i==0) $caption = "Vaadete tabelid";
			else $caption = "";
			$t->define_data(array(
				"name" => ($i + 1),
				"template" => html::textbox(array(
						"name" => "view[".$i."][template]",
						"value" => $view[$i]["template"],
						"size" => "10",
				)),
				"department_levels" => html::textbox(array(
						"name" => "view[".$i."][department_levels]",
						"value" => $view[$i]["department_levels"],
						"size" => "10",
				)),
				"with_persons"  => html::checkbox(array(
						"value" => 1,
						"checked" => $view[$i]["with_persons"],
						"name" => "view[".$i."][with_persons]",
				)),
				"columns" => html::textbox(array(
						"name" => "view[".$i."][columns]",
						"value" => $view[$i]["columns"],
						"size" => "1",
				)),
				"rows_by" => html::checkbox(array(
						"value" => 1,
						"checked" => $view[$i]["rows_by"],
						"name" => "view[".$i."][rows_by]",
				)),
				"min_cols"  => html::textbox(array(
						"name" => "view[".$i."][min_cols]",
						"value" => $view[$i]["min_cols"],
						"size" => "1",
				)),
			));
		}
		$ret[$nm] = array(
			"name" => $nm,
			"caption" => t($caption),
			"type" => "text",
			"value" => $t->draw().$this->help,
		);
		return $ret;
	}
	
	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function get_folders_as_object_list($o, $level, $parent)
	{
		$_SESSION["persons_webview"] = $o->id();
		$_SESSION["company"] = $o->prop("company");
		$this->view_obj = $o;
		$this->meta = $this->view_obj->meta();
		$this->view = $this->meta["view"][0];
		$company_id = $this->view_obj->prop("company");
		if(is_oid($company_id))	$company = obj($company_id);
		$departments = $this->view_obj->prop("departments");
		$this->set_levels(0);//teeb siis erinevatest tasemetest massiivi, mida üldse kuvada ja paneb selle muutujasse $this->levels
		$this->jrks = array();
		$sections = $this->get_sections(array("section" => $company , "jrk" => 0));
		if(in_array(0,$this->levels)) $sections = array_merge(array($company) ,$sections); //võibolla tahetakse ka asutust näha
		$ol = new object_list();
		foreach($sections as $section)
		{
			if((!in_array($section->id(), $this->view_obj->prop("departments")) || !sizeof($this->view_obj->prop("departments"))>0) && (!$section->id() == $company_id)) continue;
			$ol->add($section);
		}
		return $ol;
	}
	
	function make_menu_link($o, $ref = NULL)
	{
		
		return $this->mk_my_orb("parse_alias",
			array(
				"id" => $_SESSION["persons_webview"],
				"section" => $o->id(),
				"view" => 1,
				"level" => $this->jrks[$o->id()],
				"company_id" => $_SESSION["company"],
		),
		CL_PERSONS_WEBVIEW);
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
			case "profession":
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
				return $workers;
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

	//tekitab nimekirja tasemetest mida näidatakse... 
	function set_levels($level)
	{
		$levels = $this->view["department_levels"];
		$possible_levels = explode("," , $levels);
		if(sizeof($possible_levels) > 1)
		{
			$levels = array();
			foreach ($possible_levels as $val)
			{
				$levels[$val - $level] = $val - $level;
			}
		}
		else
		{
			$from_to = explode("-" , $levels);
			$possible_levels = array();
			while($from_to[0] <= $from_to[1])
			{
				$possible_levels[] = $from_to[0] - $level;
				$from_to[0]++;
			}
			if(sizeof($possible_levels)>0) $levels = $possible_levels;
			else $levels = array($levels - $level);
		}
		$this->levels = $levels;
	}
	
	//seda vist siiski ei lähe vaja seekord
	function request_execute ($this_object)
	{
		return $this->parse_alias (array (
			"alias" => array("to" => $this_object->id()),
		));
	}
	
	/** parse alias 
		@attrib name=parse_alias is_public="1"
	**/
	function parse_alias($arr)
	{
		global $view , $id, $section, $level, $company_id;
		if(is_oid($id) && is_oid($section)) // juhul kui asi pole dokumendi sees vaid tulev kuskiklt urlist
		{
			$this->view_obj = obj($id);
		}
		else
		{
			$this->view_obj = obj($arr["alias"]["to"]); // dokumendis aliasena
		}
		
		if(is_oid($company_id)) $this->company = obj($company_id);
		if(is_oid($section_id)) $this->section = obj($company_id);

		$this->meta = $this->view_obj->meta();
		$this->view_no = $view;
		if($view) $this->view = $this->meta["view"][$view]; // juhul kui tuleb kuskilt urlist miski tase,... 
		else $this->view = $this->meta["view"][0]; // algul paneb siis metasse esimese (default) taseme vaate,... 
		
		if(is_oid($section)){
			$section_obj = obj($section);
			if($section_obj->class_id() == CL_CRM_PERSON)
			{
				$template = $this->view["template"];
				lc_site_load("persons_web_view",$this);
				$this->read_template($template);
				$this->parse_worker($section_obj);
				return $this->parse();
			}
			if(($section_obj->class_id() == CL_CRM_SECTION)  || ($section_obj->class_id() == CL_CRM_COMPANY))
			{
				$company = $section_obj;
			}
		}

		if(!is_object($company))
		{
			$company_id = $this->view_obj->prop("company");
			if(!is_oid($company_id)) return t("pole asutust valitud");
			$company = obj($company_id);
		}

		//miskis lambikohas võib asutust ka vaja minna
		if($company->class_id() == CL_CRM_COMPANY) $this->company=$company;
		if(!$level)$level = 0;
		$this->level = $level;
		$this->set_levels($level);//teeb siis erinevatest tasemetest massiivi, mida üldse kuvada ja paneb selle muutujasse $this->levels
		return $this->parse_company($company);
	}
	
	function parse_company($company)
	{
		$departments = $this->view_obj->prop("departments");
		$template = $this->view["template"];
		$this->read_template($template);
		lc_site_load("persons_web_view",$this);
		if($this->view_obj->prop("department_grouping"))
		{
			if($this->is_template("DEPARTMENT"))
			{
				$this->jrks = array();
				if(in_array((0) , $this->levels) && (sizeof($this->levels) > 0)) $sections = array_merge(array($company) , $this->get_sections(array("section" => $company , "jrk" => 0)));
				else $sections = $this->get_sections(array("section" => $company , "jrk" => 0));
			foreach($sections as $section)
				{
					$this->section = $section; // eks seda läheb vast mujal ka vaja... ametinimetuses näiteks
					if(!(in_array($section->id(), $this->view_obj->prop("departments")))
						&& sizeof($this->view_obj->prop("departments"))>0 && array_sum($this->view_obj->prop("departments")) > 0) continue;
					if($this->view["with_persons"])
					{
						$workers = $this->get_workers($section);
						$this->parse_persons($workers);
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
		else //juhul kui osakondade järgi pole grupeeritud, siis saab veidi lihtsamini template jne teha
		{
			if($this->view["with_persons"])
			{
				$workers = $this->get_workers($company);
				$this->parse_persons($workers);
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
		
		$next_level_link = $this->mk_my_orb("parse_alias",
			array(
				"id" => $this->view_obj->id(),
				"section" => $section->id(),
				"view" => (1 + $this->view_no),
				"level" => $this->jrks[$section->id()],
				"company_id"	=> $this->company->id(),
		),
		CL_PERSONS_WEBVIEW);
		
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
			"next_level_link" => $next_level_link,
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
	
	function parse_profession($worker)
	{
		$profession = $directive = "";
		$profession_obj = $worker->get_first_obj_by_reltype("RELTYPE_RANK");
		//kõik ametid mis tüübil on
		$conns = $worker->connections_from(array(
			"type" => "RELTYPE_RANK",
		));

		$professions = "";
		//nüüd oleks vaja siis kindlaks teha, et kus ameti all tüüp antud sektsioonis asub
		//kui siit midagi asjalikku ei leia, siis jääb algul leitud amet.
		foreach($conns as $conn)
		{
			$tmp_profession_obj = obj($conn->prop("to"));
			if($professions != "") $professions .= ",";
			$professions .= $tmp_profession_obj->name();
			$section_conns = $profession_obj->connections_to(array(
				"type" => 3,
			));
			foreach($section_conns as $section_conn)
			{
				if($this->section && $section_conn->prop("from") == $this->section->id()) $profession_obj = $tmp_profession_obj;
			}
		}
		
		if(is_object($profession_obj))
		{
			$profession = $profession_obj->name();
			if(is_oid($profession_obj->prop("directive")) && $this->can("view", $profession_obj->prop("directive")))
			{
				$directive = $profession_obj->prop("directive");
			}
			else
			{
				$directive_obj = $profession_obj->get_first_obj_by_reltype("RELTYPE_DESC_FILE");
				if(is_object($directive_obj))
				$directive = $directive_obj->id();
			}
		}
		$profession_with_directive = $profession;
		if(is_oid($directive)) $profession_with_directive = '<a href ="'.$directive.'"> '. $profession_with_directive.' </a>';
		$this->vars(array(
			"profession" => $profession,
			"professions" => $professions,
			"directive" => $directive,
			"profession_with_directive" => $profession_with_directive,
			"rank" => $profession,
		));
	}

	function parse_persons($workers)
	{
		enter_function("person_webview::parse_persons");
		$this->count = 0;
		$col = 0;
		$this->max_col = $col_num = $max_col = $this->view["columns"];
		$column = "";
		$row = "";
		$row_num = 0;
		$this->min_col = $this->view["min_cols"];
		$this->calculated=0;
		$this->order_array=array();
		if($this->is_template("ROW") && $this->is_template("COL"))
		{
			foreach($workers as $val)
			{
				$worker = $val["worker"];
				if($this->view["rows_by"])//ametinimede kaupa grupeerimise porno, et erinevale reale õige arv tuleks jne
				{
					if(!$this->order_array) $this->make_order_array($workers);
					if(!$this->calculated) $col_num = $this->get_cols_num($row_num);
				} 
				$c = "";
				if($this->is_template("worker"))
				{
					$this->parse_worker($worker);
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

	function parse_worker($worker)
	{
		if(!$this->section && !$this->view_obj->prop("department_grouping")) $this->section = $worker->get_first_obj_by_reltype("RELTYPE_SECTION");
		if(!$this->section && !$this->view_obj->prop("department_grouping")) $this->section = $worker->get_first_obj_by_reltype("RELTYPE_COMPANY");

		//amet
		$this->parse_profession($worker);

		//pilt
		$photo="";
		$image_inst = get_instance(CL_IMAGE);
		if(is_oid($worker->prop("picture")) && $this->can("view", $worker->prop("picture")))
		{
			$photo = $image_inst->make_img_tag_wl($worker->prop("picture"));
		}
		else
		{
			$photo_obj = $worker->get_first_obj_by_reltype("RELTYPE_PICTURE");
			if(is_object($photo_obj)) $photo = $image_inst->make_img_tag_wl($photo_obj->id());
		}
		
		//igast telefoninumbrid
		$phone = $phone_obj = $phones= $home_phone= $work_phone=$short_phone=$cell_phone=$in_phone=$skype="";
		$phone_obj = $worker->get_first_obj_by_reltype("RELTYPE_PHONE");
		if(is_object($phone_obj)) $phone = $phone_obj->name();

		$phone_list = new object_list($worker->connections_from (array (
			"type" => "RELTYPE_PHONE",
		)));
		$phone_array = array();
		foreach($phone_list->arr() as $phone_obj)
		{
			if(strlen($phones) >0) $phones .= ' , ';
			$phones .= $phone_obj->name();
			$phone_array[$phone_obj->prop("type")."_phone"] = $phone_obj->name(); //need siis muutujad vastavalt erinevate telefonityypidele
			$phone_array[$phone_obj->prop("type")."_phones"] .= ", ".$phone_obj->name();
			if($phone_array[$phone_obj->prop("type")."_phones"][0] == ',')//see v]iks koma eest koristada
			{
				array_shift($phone_array[$phone_obj->prop("type")."_phones"]); array_shift($phone_array[$phone_obj->prop("type")."_phones"]);
			}
		}

		//url
		$url = $url_obj = $urls = "";
		$url_obj = $worker->prop("url");
		if(!is_object($url_obj)) $url_obj = $worker->get_first_obj_by_reltype("RELTYPE_URL");
		if(is_object($url_obj)) $url = $url_obj->prop("url");
		$url_list = new object_list($worker->connections_from (array (
			"type" => "RELTYPE_URL",
		)));
		foreach($url_list->arr() as $url_obj)
		{
			if(strlen($urls) > 0 ) $urls .= ', ';
			if(strlen($urls) > 0 ) $urls .= $url_obj->prop("url");
		}

		//mail	
		$email = $email_obj = $emails = "";
		$email_obj = $worker->prop("email");
		if(!is_object($email_obj)) $email_obj = $worker->get_first_obj_by_reltype("RELTYPE_EMAIL");
		$name_with_email = $worker->name();
		if(is_object($email_obj)) $email_obj->prop("mail");
		if(strlen($email) > 3)$name_with_email = '<a href =mailto:'.$email.'> '. $name_with_email.' </a>';
		$mail_list = new object_list($worker->connections_from (array (
			"type" => "RELTYPE_EMAIL",
		)));
		foreach($mail_list->arr() as $mail_obj)
		{
			if(strlen($emails) > 0 ) $emails .= ', ';
			if(strlen($emails) > 0 ) $emails .= $mail_obj->prop("mail");
		}

		//haridus
		$speciality = "";
		$speciality_obj = $worker->get_first_obj_by_reltype("RELTYPE_EDUCATION");
		if(is_object($speciality_obj))
		{
			$speciality = $speciality_obj->prop("speciality");
			$school = $speciality_obj->prop("school");
			$subject_field = $speciality_obj->prop("field");
		}

		//palk
		$wage_doc_exist = "";
		if(is_oid($worker->prop("wage_doc"))) $wage_doc_exist = '<a href ='.$worker->prop("wage_doc").'> '. t("Palk").' </a>';
		
		//järgmine tase
		$next_level_link = $this->mk_my_orb("parse_alias",
			array(
				"id" => $this->view_obj->id(),
				"section" => $worker->id(),
				"view" => (1 + $this->view_no),
				"company_id" => $this->company->id(),
				"secton_id"	=> $this->section->id()
		),
		CL_PERSONS_WEBVIEW);
		
		//organisatsioon
		if(!$this->company)
		{
			$this->company = $worker->get_first_obj_by_reltype("RELTYPE_COMPANY");
		}
		$company = $this->company->name();

		$person_inst = get_instance(CL_CRM_PERSON);
		$contact = $person_inst->get_short_description($worker->id());

		$this->vars($phone_array);
		$this->vars(array(
		//	"profession" => $profession,
			"name" => $worker->name(),
			"photo" => $photo,
			"phone" => $phone,
			"phones" => $phones,
			"contact" => $contact,
			"email" => $email,
			"emails" => $emails,
			"education" => $this->education["options"][$worker->prop("edulevel")],
			"speciality" => $speciality,
			"name_with_email" => $name_with_email,
			"wage_doc"	=> $worker->prop("wage_doc"),
			"wage_doc_exist" => $wage_doc_exist,
			"next_level_link" => $next_level_link,
			"company" => $company,
			"section" => $this->section->name(),
			"url"	=> $url,
			"urls"	=> $urls,
			"school" => $school,
			"subject_field" => $subject_field,
			"ta1" => $worker->prop("udef_ta1"),
			"ta2" => $worker->prop("udef_ta2"),
			"ta3" => $worker->prop("udef_ta3"),
			"ta4" => $worker->prop("udef_ta4"),
			"ta5" => $worker->prop("udef_ta5"),
			//	"directive" => $directive,
		));
	}

	function get_cols_num($row)
	{
//		if(!$this->order_array) $this->make_order_array($workers);
		$this->calculated = 1;
		return sizeof($this->order_array[$row]);
	}

	//hull keemia.... 
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
		while($x < 10)//miski suht random arv moment, et mitu korda ikka läbi käia nimekiri et siis iga sammuga tasandab maksimaalselt ühe võrra
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
		return $this->parse_alias();
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		lc_site_load("persons_web_view",$this);
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
