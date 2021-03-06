<?php
/*
@classinfo maintainer=markop
*/
define("CRM_ALL_PERSONS_CAT", 999999);

class crm_company_people_impl extends class_base
{
	function crm_company_people_impl()
	{
		$this->init();
	}

	function _get_contact_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$alias_to = $arr['obj_inst']->id();

		if((int)automatweb::$request->arg('unit'))
		{
			$alias_to = automatweb::$request->arg('unit');
		}

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('T&ouml;&ouml;taja'),
			'link'=>aw_url_change_var(array(
				'action' => 'create_new_person',
				'parent' => $arr['obj_inst']->id(),
				'alias_to' => $alias_to,
				'reltype' => !empty($arr['request']['unit']) ? 2 : 8,
				'return_url' => get_ru(),
				"class" => "crm_company",
				"profession" => automatweb::$request->arg("cat") == CRM_ALL_PERSONS_CAT ? 0 : automatweb::$request->arg("cat")
			))
		));

		//uus k&otilde;ne
		$tb->add_button(array(
			'name' => 'Kone',
			'img' => 'class_223.gif',
			'tooltip' => t('Tee k&otilde;ne'),
			'action' => 'submit_new_call'
		));

		//uus date
		$tb->add_button(array(
			'name' => 'Kohtumine',
			'img' => 'class_224.gif',
			'tooltip' => t('Uus kohtumine'),
			'action' => 'submit_new_meeting'
		));

		//uus task
		$tb->add_button(array(
			'name' => 'Toimetus',
			'img' => 'class_244.gif',
			'tooltip' => t('Uus toimetus'),
			'action' => 'submit_new_task'
		));

		$tb->add_separator();

		$tb->add_menu_button(array(
			'name' => 'Search',
			'img' => 'search.gif',
			'tooltip' => t('Otsi'),
			'action' => 'search_for_contacts'
		));

		$tb->add_menu_item(array(
			'parent'=>'Search',
			'text' => t('Isikuid'),
			'link'=> "javascript:submit_changeform('search_for_contacts')"
		));

		if(!empty($arr["request"]["contacts_search_show_results"]))
		{
			$tb->add_button(array(
				'name' => 'Salvesta',
				'img' => 'save.gif',
				'tooltip' => t('Lisa isikud organisatisooni'),
				'action' => 'save_contact_rels'
			));
		}

		$tb->add_separator();

		$tb->add_menu_button(array(
			"name" => "important",
			"img" => "important.png",
			"tooltip" => t("Olulisus"),
		));

		$tb->add_menu_item(array(
			"parent" => "important",
			"text" => t("M&auml;rgi oluliseks"),
			"action" => "mark_p_as_important",
		));

		$tb->add_menu_item(array(
			"parent" => "important",
			"text" => t("Eemalda olulisuse m&auml;rge"),
			"action" => "unmark_p_as_important",
		));

		$seti = get_instance(CL_CRM_SETTINGS);
		$sts = $seti->get_current_settings();
		if ($sts && $sts->prop("send_mail_feature"))
		{
			$tb->add_button(array(
				'name'=>'send_email',
				'tooltip'=> t('Saada kiri'),
				"img" => "mail_send.gif",
				'action' => 'send_mails',
			));
		}

		$tb->add_separator();

		$c = get_instance("vcl/popup_menu");
		$c->begin_menu("crm_co_ppl_filt");

		$c->add_item(array(
			"text" => t("T&uuml;hista"),
			"link" => aw_url_change_var("filt_p", null)
		));
		for($i = ord('A'); $i < ord("Z"); $i++)
		{
			$c->add_item(array(
				"text" => chr($i).(automatweb::$request->arg("filt_p") == chr($i) ? t(" (Valitud)") : "" ),
				"link" => aw_url_change_var("filt_p", chr($i))
			));
		}

		$tb->add_cdata(t("Vali filter:").$c->get_menu().(!empty($arr["request"]["filt_p"]) ? t("Valitud:").$arr["request"]["filt_p"] : "" ));
	}

	function _get_unit_listing_tree($arr)
	{
		if (automatweb::$request->arg("contact_search") == 1)
		{
			return PROP_IGNORE;
		}
		$tree_inst = &$arr['prop']['vcl_inst'];
		$node_id = 0;
		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['unit'];
		if(is_oid($arr['request']['cat']))
		{
			$i->active_node = $arr['request']['cat'];
		}
		$i->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_SECTION',
			'attrib' => 'unit',
			'leafs' => true,
		));

		$nm = t("K&otilde;ik t&ouml;&ouml;tajad");
		$tree_inst->add_item(0, array(
			"id" => CRM_ALL_PERSONS_CAT,
			"name" => $arr["request"]["cat"] == CRM_ALL_PERSONS_CAT ? "<b>".$nm."</b>" : $nm,
			"url" => aw_url_change_var(array(
				"cat" =>  CRM_ALL_PERSONS_CAT,
				"unit" =>  NULL,
			))
		));

		if ($_SESSION["crm"]["people_view"] == "edit")
		{
			classload("core/icons");
			$tree_inst->set_root_name($arr["obj_inst"]->name());
			$tree_inst->set_root_icon(icons::get_icon_url(CL_CRM_COMPANY));
			$tree_inst->set_root_url(aw_url_change_var("cat", NULL, aw_url_change_var("unit", NULL)));
		}
	}

	function callb_human_name($arr)
	{
		$fn = $arr["firstname"];
		$ln = $arr["lastname"];

		if(empty($arr["firstname"]) && empty($arr["lastname"]))
			$name = $arr["name"];
		else
			$name = $fn." ".$ln;

		return html::get_change_url(
			$arr["id"],
			array("return_url" => (empty($this->hr_tbl_return_url) ? get_ru() : $this->hr_tbl_return_url)),
			parse_obj_name($name)
		);
	}

	function _init_human_resources_table(&$t, $fields = false)
	{
		$fields_data = array(
			array(
				"name" => "cal",
				"caption" => t("&nbsp;"),
				"width" => 1
			),
			array(
				'name' => 'image',
				'caption' => t('&nbsp;'),
				"chgbgcolor" => "cutcopied",
				"align" => "center",
				"width" => 1
			),
			array(
				'name' => 'name',
				'caption' => t('Nimi'),
				'sortable' => '1',
				"chgbgcolor" => "cutcopied",
				'callback' => array(&$this, 'callb_human_name'),
				'callb_pass_row' => true,
			),
			array(
				'name' => 'phone',
				"chgbgcolor" => "cutcopied",
				'caption' => t('Telefon'),
				'sortable' => '1',
			),
			array(
				'name' => 'email',
				"chgbgcolor" => "cutcopied",
				'caption' => t('E-post'),
				'sortable' => '1',
			),
			array(
				'name' => 'section',
				"chgbgcolor" => "cutcopied",
				'caption' => t('&Uuml;ksus'),
				'sortable' => '1',
			),
			array(
				'name' => 'rank',
				"chgbgcolor" => "cutcopied",
				'caption' => t('Ametinimetus'),
				'sortable' => '1',
			),
			array(
				'name' => 'work_relation',
				"chgbgcolor" => "cutcopied",
				'caption' => t('T&ouml;&ouml;suhe'),
				'sortable' => '1',
			),
			array(
				'name' => 'authorized',
				"chgbgcolor" => "cutcopied",
				'caption' => t('Volitatud'),
				'sortable' => '1',
			)
		);
		foreach($fields_data as $field_data)
		{
			if($fields === false || in_array($field_data["name"], $fields))
			{
				$t->define_field($field_data);
			}
		}

		$t->define_chooser(array(
			'name'=>'check',
			'field'=>'id',
			"chgbgcolor" => "cutcopied",
		));
		$t->set_default_sortby("name");
	}

	function _get_human_resources($arr)
	{
		if($arr['request']['contact_search'] || $arr["request"]["prof_search"])
		{
			return PROP_IGNORE;
		}

		if(!empty($arr["caller_ru"]))
		{
			$this->hr_tbl_return_url = $arr["caller_ru"];
		}

		$u = get_instance(CL_USER);
		classload("core/icons");
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_human_resources_table($t, isset($arr["prop"]["fields"]) ? $arr["prop"]["fields"] : false);
		$format = t('%s t&ouml;&ouml;tajad');
		$t->set_caption(sprintf($format, $arr['obj_inst']->name()));

		$crmp = get_instance(CL_CRM_PERSON);

		// http://intranet.automatweb.com/automatweb/orb.aw?class=planner&action=change&alias_to_org=87521&reltype_org=RELTYPE_ISIK_KOHTUMINE&id=46394&clid=224&group=add_event&title=Kohtumine:%20Anti%20Veeranna&parent=46398

		// to get those adding links work, I need
		// 1. id of my calendar
		// 2. relation type
		// alias_to_org oleks isiku id
		// reltype_org oleks vastava seose id

		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user(array('uid'=>aw_global_get('uid')));

		// XXX: I should check whether $this->cal_id exists and only include those entries
		// when it does.

		// call : rel=9 : clid=CL_CRM_CALL
		// meeting : rel=8 : clid=CL_CRM_MEETING
		// task : rel=10 : clid=CL_TASK
		$persons = array();
		$professions = array();
		$sections = array();
		//if section present, i'll get all the professions

//-------------------osakonna inimesed-------------
		if(is_oid($arr['request']['unit']))
		{
			$tmp_obj = new object($arr['request']['unit']);
			$sections[] = $arr['request']['unit'];
			if(!is_oid($arr['request']['cat'])) //kui miski amet v6i nii, siis leiab isikud hiljem
			{
				$worker_ol = $tmp_obj->get_workers();
				$persons = $worker_ol->ids();
			}
		}
//----------------------- teatud ameti inimesed--------------------------------
		if(is_oid($arr['request']['cat']) && $arr["request"]["cat"] != CRM_ALL_PERSONS_CAT)
		{
			$tmp_obj = new object($arr['request']['cat']);
			$worker_ol = $tmp_obj->get_workers_for_section($arr['request']['unit']);
			$persons = $worker_ol->ids();
			$professions = array($arr['request']['cat']);
		}

//------------------------- ainult olulisteks m2rgitud inimesed-------------------
		if(!$arr['request']['cat'] && !$arr['request']['unit'])
		{
			$section_ol = $arr["obj_inst"]->get_sections();
			$sections = $section_ol->ids();
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			if(is_oid($p->id()))
			{
				$worker_ol = $p->get_important_persons($arr["obj_inst"]->id());
				$persons = $worker_ol->ids();
			}
		}

//---------------------- k6ik asutuse inimesed------------------kas siis kui tahetud k6iki v6i siis kui ei saanud yhtgi tulemust ja on m22ratud et sellisel juhul l2hevad k6ik
		if ($arr["request"]["cat"] == CRM_ALL_PERSONS_CAT || ($arr["request"]["all_if_empty"] && !($worker_ol && $worker_ol->count())))
		{
			$worker_ol = $arr["obj_inst"]->get_workers();
			$persons = $worker_ol->ids();
			$section_ol = $arr["obj_inst"]->get_sections();
			$sections = $section_ol->ids();

		}

		//if listing from a specific unit, then the reltype is different
/*		if((int)$arr['request']['unit'])
		{
			$obj = new object((int)$arr['request']['unit']);
			$conns = $obj->connections_from(array(
				"type" => "RELTYPE_WORKERS",
			));
		}
		else
		{
			$cur_p = obj($u->get_current_person());
			$conns = $cur_p->connections_from(array(
				"type" => "RELTYPE_IMPORTANT_PERSON",
			));

			$i = get_instance(CL_CRM_COMPANY);
			$all_persons = array();
			$i->get_all_workers_for_company($arr["obj_inst"], $all_persons);

			// leave only conns that point to people in this company
			foreach($conns as $idx => $c)
			{
				if (!isset($all_persons[$c->prop("to")]))
				{
					unset($conns[$idx]);
				}
			}
		}

		if ($arr["request"]["all_if_empty"] && !count($conns))
		{
			$all_persons = array();
			$i->get_all_workers_for_company($arr["obj_inst"], $all_persons);
			$persons = array_keys($all_persons);
		}
		else
		{
			foreach($conns as $conn)
			{
				$persons[] = $conn->prop('to');
			}
		}

		if (isset($arr["person_filter"]) && is_array($arr["person_filter"]))
		{
			$tmp = array();
			foreach($persons as $person)
			{
				if (isset($arr["person_filter"][$person]))
				{
					$tmp[] = $person;
				}
			}
			$persons = $tmp;
		}

		if ($arr["request"]["cat"] == CRM_ALL_PERSONS_CAT)
		{
			$i = get_instance(CL_CRM_COMPANY);
			$persons = array();
			$i->get_all_workers_for_company($arr["obj_inst"], $persons);
		}

		if ($arr["disp_persons"])
		{
			$persons = $arr["disp_persons"];
		}

		// preload ranks from persons
		$c = new connection();
		$r_conns = $c->find(array(
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_RANK",
			"from" => $persons
		));
		$p2r = array();
		foreach($r_conns as $r_con)
		{
			$p2r[$r_con["from"]][$r_con["to"]] = $r_con["to.name"];
		}

		// preload sections from persons
		$c = new connection();
		$r_conns = $c->find(array(
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_SECTION",
			"from" => $persons
		));
		$p2s = array();
		foreach($r_conns as $r_con)
		{
			$p2s[$r_con["from"]][$r_con["to"]] = $r_con["to.name"];
		}
*/
		// get calendars for persons

		$pers2cal = $this->_get_calendars_for_persons($persons);
		exit_function("ghr::ll");
		enter_function("ghr::loop");
		foreach($persons as $person)
		{
			$tdata = array();
			$person = new object($person);
/*			if (!empty($arr["request"]["filt_p"]))
			{
				$nm = $person->name();
				if ($nm[0] != $arr["request"]["filt_p"])
				{
					continue;
				}
			}
			$idat = $crmp->fetch_all_data($person->id());
			$pdat["ranks_arr"] = $p2r[$person->id()];
			$pdat["sections_arr"] = $p2s[$person->id()];
			$pdat["rank"] = join(", ", $pdat["ranks_arr"]);
			if(is_oid($arr['request']['cat']))
			{
				//persons only from this category
				if($arr["request"]["cat"] != CRM_ALL_PERSONS_CAT && !in_array($arr['request']['cat'], array_keys($pdat['ranks_arr'])))
				{
					continue;
				}
			}

			if(is_oid($arr['request']['cat']) || is_oid($arr['request']['unit']) || (!$pdat["rank"] && $person->rank))
			{
				$pdat["rank"] = html::obj_change_url($person->rank);
			}

			$sections_professions = array();
			$section = '';
			foreach($pdat['sections_arr'] as $key => $value)
			{
				$crm_section = get_instance(CL_CRM_SECTION);
				$sections_professions[$key] = $crm_section->get_professions($key);
				$tmp_arr = array_intersect(array_keys($pdat['ranks_arr']),array_keys($pdat['ranks_arr']));
				$tmp_arr2 = array();
				foreach($tmp_arr as $key2=>$value2)
				{
					$tmp_arr2[] = $pdat['ranks_arr'][$value2];
				}
				$section = current($pdat['sections_arr']);;
				break;
			}

			//kui amet kuulub $pdat['sections_arr'] olevasse sektsiooni ja persoon on seotud
			//selle ametiga, siis seda n&auml;idata kujul
*/
			$tdata["cutcopied"] = (isset($_SESSION["crm_copy_p"][$person->id()]) || isset($_SESSION["crm_cut_p"][$person->id()]) ? "#E2E2DB" : "");	
			$tdata["cal"] = "";
			if ($pers2cal[$person->id()])
			{
				$calo = obj($pers2cal[$person->id()]);
				$tdata["cal"] = html::href(array(
					"url" => html::get_change_url($calo->id(), array("return_url" => get_ru(), "group" => "views", "viewtype" => "week"))."#today",
					"caption" => html::img(array(
						"url" => icons::get_icon_url(CL_PLANNER),
						"border" => 0
					))
				));
			}
/*
			$econns = $person->connections_from(array(
				"type" => 11,
			));
			$emails = array();
			foreach($econns as $conn)
			{
				$to_obj = $conn->to();
				$emails[] = $to_obj->prop("mail");
			};
			$pdat["email"] = join(", ", $emails);

			$econns = $person->connections_from(array(
				"type" => "RELTYPE_PHONE",
			));
			$phs = array();
			foreach($econns as $conn)
			{
				$to_obj = $conn->to();
				$phs[] = $to_obj->prop("name");
			};
			$pdat["phone"] = join(", ", $phs);

*/


/*			$imgo = $person->get_first_obj_by_reltype("RELTYPE_PICTURE");
			$img = "";
			if ($imgo)
			{
				$img_i = $imgo->instance();
				$img = $img_i->make_img_tag_wl($imgo->id(),"","",array("width" => 60));
			}
/*
			// This will cause huge problems when there are spaces in first or last name.
//			list($fn, $ln) = explode(" ", $person->prop('name'));
			$fn = $person->prop("firstname");
			$ln = $person->prop("lastname");*/
			$aol = new object_list(array(
				"class_id" => CL_CRM_AUTHORIZATION,
				"lang_id" => array(),
				"site_id" => array(),
				"our_company" => $u->get_current_company(),
				"customer_company" => $arr["obj_inst"]->id(),
				"authorized_person" => $person->id(),

	//			"CL_CRM_AUTHORIZATION.RELTYPE_PERSON.id" => $person->id(),
	//			"CL_CRM_AUTHORIZATION.RELTYPE_OUR_COMPANY.id" => $arr["obj_inst"]->id(),
	//			"CL_CRM_AUTHORIZATION.RELTYPE_CUSTOMER_COMPANY.id" => $arr["obj_inst"]->id(),
			));
			$a_links = array();
			foreach($aol->arr() as $aut)
			{
				$a_links[] = html::href(array(
						"url" => html::get_change_url($aut->id()),
						"caption" => (strlen($aut->name()) > 0)?$aut->name():t("(Nimetu)"),
					));
			}
			$authoirization = join(", " , $a_links);
			$tdata["authorized"] = html::checkbox(array(
				"name" => "authorized[".$person->id()."]",
				"value" => 1,
				"checked" => 0,
				"onclick" => 'Javascript:window.open("'.html::get_new_url(
					CL_CRM_AUTHORIZATION,
					$person->id(),
					array(
						"return_url" => get_ru(),
						"person" => $person->id(),
						"our_company" => $u->get_current_company(),
						"customer_company" => $arr["obj_inst"]->id(),
						"return_after_save" => 1,
					)
				)
				.'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=800, width=720")',
			))." " .$authoirization;


			$rel = $person->get_work_relation_id(array(
				"company" => $arr["obj_inst"]->id(),
				"section" => $arr['request']['unit'],
				"profession" => $arr['request']['cat'] == 999999 ? null : $arr['request']['cat'],
			));
			$tdata["work_relation"] = $rel ? html::href(array(
				"caption" => t("Muuda"),
				"url" => html::get_change_url($rel, array(
						"return_url" => get_ru(),
				))
			)) : html::href(array(
				"caption" => t("Lisa"),
				"url" => html::get_new_url(
					CL_CRM_PERSON_WORK_RELATION,
					$person->id(),
					array(
						"return_url" => get_ru(),
						"person" => $person->id(),
						"company" => $arr["obj_inst"]->id(),
						'alias_to' => $person->id(),
						'reltype' => 67,
					))
			));

			$tdata["id"] = $person->id();
			$tdata["phone"] = $person->get_phone($arr["obj_inst"]->id() , $section);
			$tdata["rank"] = join(", " , $person->get_profession_names($arr["obj_inst"]->id() , $professions));
			$tdata["section"] = join(", " , $person->get_section_names($arr["obj_inst"]->id() , $sections));
			$tdata["email"] = $person->get_mail_tag($arr["obj_inst"]->id() , $section);
			$tdata["firstname"] = $person->prop("firstname");
			$tdata["lastname"] = $person->prop("lastname");
			$tdata["name"] = $person->name();
			$tdata["image"] = $person->get_image_tag();
			$t->define_data($tdata);
		};
	}

	function _add_edit_stuff_to_table($arr)
	{
		$arr["prop"]["vcl_inst"]->set_sortable(false);
		$parent = $arr["request"]["cat"];
		if (!$parent)
		{
			$parent = $arr["request"]["unit"];
		}

		if ($parent && $parent != 999999)
		{
			$o = obj($parent);
		}
		else
		{
			$o = $arr["obj_inst"];
		}

		$section_img = html::img(array("url" => icons::get_icon_url(CL_CRM_SECTION), "border" => "0", "alt" => t("&Uuml;ksus")));
		$prof_img = html::img(array("url" => icons::get_icon_url(CL_CRM_PROFESSION), "border" => "0", "alt" => t("Amet")));

		foreach($o->connections_from(array("type" => "RELTYPE_SECTION")) as $c)
		{
			$ccp = (isset($_SESSION["crm_copy_p"][$c->prop("to")]) || isset($_SESSION["crm_cut_p"][$c->prop("to")]) ? "#E2E2DB" : "");
			// This produces an error if there are more than 2 words in the name.
//			list($fn, $ln) = explode(" ", $c->prop("to.name"));
			$arr["prop"]["vcl_inst"]->define_data(array(
				"image" => $section_img,
				"name" => $c->prop("to.name"),
				"id" => $c->prop("to"),
				"cutcopied" => $ccp
			));
		}

		foreach($o->connections_from(array("type" => "RELTYPE_PROFESSIONS")) as $c)
		{
			$ccp = (isset($_SESSION["crm_copy_p"][$c->prop("to")]) || isset($_SESSION["crm_cut_p"][$c->prop("to")]) ? "#E2E2DB" : "");
			// This produces an error if there are more than 2 words in the name.
//			list($fn, $ln) = explode(" ", $c->prop("to.name"));
			$arr["prop"]["vcl_inst"]->define_data(array(
				"image" => $prof_img,
				"name" => $c->prop("to.name"),
				"id" => $c->prop("to"),
				"cutcopied" => $ccp
			));
		}
	}

	function _get_contacts_search_results($arr)
	{
		if(!($arr['request']['contact_search'] && $arr['request']['contacts_search_show_results']))
		{
			return PROP_IGNORE;
		}

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi'),
			'sortable' => '1',
			'callback' => array(&$this, 'callb_human_name'),
			'callb_pass_row' => true,
		));
		$t->define_field(array(
			'name' => 'phone',
			'caption' => t('Telefon'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'email',
			'caption' => t('E-post'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'section',
			'caption' => t('&Uuml;ksused'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'rank',
			'caption' => t('Ametinimetused'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'orgs',
			'caption' => t('Organisatsioonid'),
			'sortable' => '1',
		));
		/*$t->define_chooser(array(
			'name'=>'check',
			'field'=>'id',
		));*/

		$format = t('%s t&ouml;&ouml;tajate otsingu tulemused');
		$t->set_caption(sprintf($format, $arr['obj_inst']->name()));


		$search_params = array(
			'class_id' => CL_CRM_PERSON,
			'limit' => 50,
			'sort_by'=>'name',
			"lang_id" => array(),
			"site_id" => array()
		);

		if($arr['request']['contact_search_name'])
		{
			$search_params['name'] = '%'.urldecode($arr['request']['contact_search_name']).'%';
		}

		if($arr['request']['contact_search_firstname'])
		{
			$search_params['firstname'] = '%'.urldecode($arr['request']['contact_search_firstname']).'%';
		}

		if($arr['request']['contact_search_lastname'])
		{
			$search_params['lastname'] = '%'.urldecode($arr['request']['contact_search_lastname']).'%';
		}

		if($arr['request']['contact_search_code'])
		{
			$search_params['personal_id'] = '%'.urldecode($arr['request']['contact_search_code']).'%';
		}

		if($arr['request']['contact_search_ext_id_alphanum'])
		{
			$search_params['ext_id_alphanumeric'] = "%" . urldecode($arr['request']['contact_search_ext_id_alphanum']) . "%";
		}

		if($arr['request']['contact_search_ext_id'])
		{
			$search_params['ext_id'] = (int) urldecode($arr['request']['contact_search_ext_id']);
		}

		$ol = new object_list($search_params);

		$pl = get_instance(CL_PLANNER);
		$person = get_instance(CL_CRM_PERSON);
		$cal_id = $pl->get_calendar_for_user(array('uid'=>aw_global_get('uid')));

		foreach($ol->arr() as $o)
		{
//			$person_data = $person->fetch_person_by_id(array(
//				'id' => $o->id(),
//				'cal_id' => $calid
//			));
			$phones = $o->phones();
			$person_data['phone'] = join(",", $phones->names());
			$cos = array();
			$orgs = $o->get_all_orgs();
			foreach($orgs->arr() as $orgid)
			{
				$cos[] = html::href(array("url" => html::get_change_url($orgid->id()), "caption" => $orgid->name()));
			}

			$t->define_data(array(
				"firstname" => $o->prop("firstname"),
				"lastname" => $o->prop("lastname"),
				"name" => $o->prop('name'),
				"id" => $o->id(),
				"phone" => $person_data['phone'],
				"rank" => join(", ", $o->get_profession_names()),//$person_data["rank"],
				'section' => join(", ", $o->get_section_names()),
				'orgs' => join(", ", $cos),
				"oid" => $o->id(),
				"email" => join(", ", $o->get_all_mail_tags()),
			));
		}
	}

	function _get_personal_offers_toolbar($arr)
	{
		$toolbar =& $arr["prop"]["vcl_inst"];
		$toolbar->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=>t('Uus')
		));

		$toolbar->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud t&ouml;&ouml;pakkumised'),
			'action' => 'delete_selected_objects',
			'confirm' => t("Kas oled kindel et soovid valitud t&ouml;&ouml;pakkumised kustudada?")
		));

		if($arr["request"]["cat"] && $arr["request"]["unit"] && $arr["request"]["cat"] != CRM_ALL_PERSONS_CAT)
		{
			$alias_to =  $arr["request"]["unit"];
			$reltype = 4;
		}
		else
		{
			$alias_to = $arr["obj_inst"]->id();
			$reltype = 19;
		}

		$toolbar->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('T&ouml;&ouml;pakkumine'),
			'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $reltype,
					'return_url'=>get_ru(),
					'cat' => $arr["request"]["cat"] != CRM_ALL_PERSONS_CAT ? $arr["request"]["cat"] : NULL,
					'unit' => $arr["request"]["unit"],
					'org' => $arr['obj_inst']->id(),
			), CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
		));
	}

	function _get_unit_listing_tree_personal($arr)
	{
		$tree_inst = &$arr['prop']['vcl_inst'];
		$node_id = 0;

		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['unit'];

		$i->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_SECTION',
			'attrib' => 'unit',
			'leafs' => true,
		));
	}

	function _get_personal_offers_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "osakond",
			"caption" => t("Osakond"),
			"sortable" => "1",
		));

		$table->define_field(array(
			"name" => "ametinimi",
			"caption" => t("Ametinimi"),
			"sortable" => "1",
		));

		$table->define_field(array(
			"name" => "comments",
			"caption" => t("Kommentaar"),
			"sortable" => "1",
		));

		$table->define_field(array(
			"name" => "kehtiv_alates",
			"caption" => t("Kehtiv alates"),
			"sortable" => "1",
			"width" => 80,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));

		$table->define_field(array(
			"name" => "kehtiv_kuni",
			"caption" => t("Kehtiv kuni"),
			"sortable" => "1",
			"width" => 80,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));

		$table->define_chooser(array(
			"name" => "select",
			"field" => "job_id",
			"caption" => t("X"),
			"width" => 20,
			"align" => "center"
		));

		$format = t('%s t&ouml;&ouml;pakkumised');
		$table->set_caption(sprintf($format, $arr['obj_inst']->name()));

		$section_cl = get_instance(CL_CRM_SECTION);

		if(is_oid($arr['request']['unit']))
		{
			$jobs_ids = $section_cl->get_section_job_ids_recursive($arr['request']['unit']);
		}
		else
		{
			$jobs_ids = $section_cl->get_all_org_job_ids($arr["obj_inst"]->id());
			$professions = $section_cl->get_all_org_proffessions($arr["obj_inst"]->id(), true);
		}

		if(!$jobs_ids)
		{
			return;
		}

		$job_obj_list = new object_list(array(
			"oid" => array_keys($jobs_ids),
			"profession" => $arr["request"]["cat"] != CRM_ALL_PERSONS_CAT ? $arr["request"]["cat"] : NULL,
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER
		));
		$job_obj_list = $job_obj_list->arr();
		foreach ($job_obj_list as $job)
		{
			if($arr['request']['unit'])
			{
				$professions = $section_cl->get_professions($arr['request']['unit'], true);
			}

			if(!$professions[$job->prop("profession")])
			{
				$professin_cap = t("M&auml;&auml;ramata");
			}
			else
			{
				$professin_cap = $professions[$job->prop("profession")];
			}

			$table->define_data(array(
				"osakond" => $jobs_ids[$job->id()],
				"kehtiv_kuni" => $job->prop("deadline"),
				"ametinimi" => html::href(array(
					"caption" => $professin_cap,
					"url" => $this->mk_my_orb("change", array("id" =>$job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
				)),
				"kehtiv_alates" => $job->prop("beginning"),
				"job_id" => $job->id(),
				"comments" => $job->prop("comment"),
			));
		}
	}

	function _get_personal_candidates_toolbar($arr)
	{
		$toolbar =& $arr["prop"]["vcl_inst"];
		$toolbar->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud t&ouml;&ouml;pakkumised'),
		));
	}

	function _get_unit_listing_tree_candidates($arr)
	{
		$tree_inst = &$arr['prop']['vcl_inst'];
		$node_id = 0;
		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['unit'];

		$i->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_SECTION',
			'attrib' => 'unit',
			'leafs' => true,
		));
	}

	function _get_personal_candidates_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "person_name",
			"caption" => t("Kandideerija nimi"),
			"sortable" => "1",
		));

		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => t("Ametikoht"),
			"sortable" => "1",
		));

		$table->define_field(array(
			"name" => "osakond",
			"caption" => t("Osakond"),
			"sortable" => "1",
		));

		$format = t('%ssse t&ouml;&ouml;le kandideerijad');
		$table->set_caption(sprintf($format, $arr['obj_inst']->name()));

		$section_cl = get_instance(CL_CRM_SECTION);

		if(is_oid($arr['request']['unit']))
		{
			$jobs_ids = $section_cl->get_section_job_ids_recursive($arr['request']['unit']);
		}
		else
		{
			$jobs_ids = $section_cl->get_all_org_job_ids($arr["obj_inst"]->id());
			$professions = $section_cl->get_all_org_proffessions($arr["obj_inst"]->id(), true);
		}

		if(!$jobs_ids)
		{
			return;
		}

		$candidate_conns = new connection();
		$candidate_conns = $candidate_conns->find(array(
        	"from" => array_keys($jobs_ids),
        	"to.class_id" => CL_CRM_PERSON,
        	"reltype" => 66666, //RELTYPE_CANDIDATE
		));

		$professions = $section_cl->get_all_org_proffessions($arr["obj_inst"]->id(), true);

		foreach ($candidate_conns as $candidate_conn)
		{
			$table->define_data(array(
				"person_name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $candidate_conn['to']), CL_CRM_PERSON),
					"caption" => $candidate_conn['to.name'],
				)),
				"ametikoht" => $candidate_conn['from.name'],
				"osakond" => $jobs_ids[$candidate_conn['from']],
			));
		}
	}

	function _get_contact_search_desc($arr)
	{
		$arr["prop"]["value"] = "<span style=\"border: 1px black;\"><b>" . t("Otsi isikuid") . "</b>";
	}

	function _get_calendars_for_persons($persons)
	{
		$ret = array();

		$c = new connection();
		$cs = $c->find(array(
			"from.class_id" => CL_USER,
			"to.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_PERSON",
			"to" => $persons
		));

		$users = array();
		$u2p = array();
		foreach($cs as $c)
		{
			$users[] = $c["from"];
			$u2p[$c["from"]] = $c["to"];
		}

		$c = new connection();
		$owners = $c->find(array(
			"from.class_id" => CL_PLANNER,
			"to.class_id" => CL_USER,
			"to" => $users
		));

		foreach($owners as $owner)
		{
			$ret[$u2p[$owner["to"]]] = $owner["from"];
		}

		return $ret;
	}

	function _get_cedit_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$alias_to = $arr['obj_inst']->id();

		if((int)$arr['request']['unit'])
		{
			$alias_to = $arr['request']['unit'];
		}

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('T&ouml;&ouml;taja'),
			'link'=>aw_url_change_var(array(
				'action' => 'create_new_person',
				'parent' => $arr['obj_inst']->id(),
				'alias_to' => $alias_to,
				'reltype' => $arr['request']['unit'] ? 2 : 8,
				'return_url' => get_ru(),
				"class" => "crm_company",
				"profession" => $arr["request"]["cat"] == CRM_ALL_PERSONS_CAT ? 0 : $arr["request"]["cat"]
			))
		));

		if ($arr["request"]["cat"] != 999999)
		{
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => t('&Uuml;ksus'),
				'link'=>$this->mk_my_orb('new',array(
						'parent'=>$arr['obj_inst']->id(),
						'alias_to'=>$alias_to,
						'reltype'=> $arr["request"]["unit"] ? 1 : 28,
						'return_url'=>get_ru()
					),
					'crm_section'
				)
			));
		}

		if ($arr["request"]["unit"] != "" && $arr["request"]["cat"] != 999999)
		{
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => t('Ametinimetus'),
				'link'=>$this->mk_my_orb('new',array(
						'parent'=>$arr['obj_inst']->id(),
						'alias_to'=>$alias_to,
						'reltype'=> (int)$arr['request']['unit'] ? 3 : 29,
						'return_url'=>get_ru()
					),
					'crm_profession'
				)
			));
		}

		//delete button
		$tb->add_menu_button(array(
			'name'=>'delete_item',
			'tooltip'=> t('Kustuta'),
			"img" => "delete.gif"
		));

		$tb->add_menu_item(array(
			"parent" => "delete_item",
			"text" => t("Eemalda isikud organisatsioonist"),
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			"confirm" => t("Oled kindel et soovid kustutada valitud t&ouml;&ouml;tajad?"),
			'action' => 'submit_delete_relations',
		));

		$tb->add_menu_item(array(
			"parent" => "delete_item",
			"text" => t("Kustuta isikud s&uuml;steemist"),
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			"confirm" => t("Oled kindel et soovid kustutada valitud t&ouml;&ouml;tajad?"),
			'action' => 'submit_delete_ppl',
		));

		$tb->add_separator();

		if ($arr["request"]["unit"])
		{
			$tb->add_menu_button(array(
				'name' => 'Search',
				'img' => 'search.gif',
				'tooltip' => t('Otsi'),
				'action' => 'search_for_contacts'
			));

			if ($arr["request"]["cat"])
			{
				$url = $this->mk_my_orb("do_search", array(
					"clid" => CL_CRM_PERSON,
					"pn" => "sbt_data"
				), "popup_search");
				$tb->add_menu_item(array(
					'parent'=>'Search',
					'text' => t('Isikuid'),
					'link'=> "#",
					"onClick" => html::popup(array(
						"url" => $url,
						"resizable" => true,
						"scrollbars" => "auto",
						"height" => 500,
						"width" => 600,
						"no_link" => true,
						"quote" => "'"
					))
				));
			}

			$url = $this->mk_my_orb("do_search", array(
				"clid" => CL_CRM_PROFESSION,
				"pn" => "sbt_data2"
			), "popup_search");

			$tb->add_menu_item(array(
				'parent'=>'Search',
				'text' => t('Ametinimetusi'),
				'link'=> "#",
				"onClick" => html::popup(array(
					"url" => $url,
					"resizable" => true,
					"scrollbars" => "auto",
					"height" => 500,
					"width" => 600,
					"no_link" => true,
					"quote" => "'"
				))
			));
		}

		if($arr['request']['contact_search'])
		{
			$tb->add_button(array(
				'name' => 'Save',
				'img' => 'save.gif',
				'tooltip' => t('Salvesta'),
				'action' => 'save_search_results'
			));
		}

		$tb->add_separator();

		$tb->add_button(array(
			"name" => "cut",
			"img" => "cut.gif",
			"tooltip" => t("L&otilde;ika"),
			"action" => "cut_p",
		));

		$tb->add_button(array(
			"name" => "copy",
			"img" => "copy.gif",
			"tooltip" => t("Kopeeri"),
			"action" => "copy_p",
		));

		if (is_array($_SESSION["crm_cut_p"]) || is_array($_SESSION["crm_copy_p"]))
		{
			$tb->add_button(array(
				"name" => "paste",
				"img" => "paste.gif",
				"tooltip" => t("Kleebi"),
				"action" => "paste_p",
			));
		}

		$tb->add_separator();

		$tb->add_menu_button(array(
			"name" => "important",
			"img" => "important.png",
			"tooltip" => t("Olulisus"),
		));

		$tb->add_menu_item(array(
			"parent" => "important",
			"text" => t("M&auml;rgi oluliseks"),
			"action" => "mark_p_as_important",
		));

		$tb->add_menu_item(array(
			"parent" => "important",
			"text" => t("Eemalda olulisuse m&auml;rge"),
			"action" => "unmark_p_as_important",
		));

	}

	function _get_cedit_tree($arr)
	{
		$tree_inst = &$arr['prop']['vcl_inst'];
		$node_id = 0;
		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['unit'];
		if(is_oid($arr['request']['cat']))
		{
			$i->active_node = $arr['request']['cat'];
		}
		$i->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_SECTION',
			'attrib' => 'unit',
			'leafs' => true,
		));

		$nm = t("K&otilde;ik t&ouml;&ouml;tajad");
		$tree_inst->add_item(0, array(
			"id" => CRM_ALL_PERSONS_CAT,
			"name" => $arr["request"]["cat"] == CRM_ALL_PERSONS_CAT ? "<b>".$nm."</b>" : $nm,
			"url" => aw_url_change_var(array(
				"cat" =>  CRM_ALL_PERSONS_CAT,
				"unit" =>  NULL,
			))
		));

		classload("core/icons");
		$tree_inst->set_root_name($arr["obj_inst"]->name());
		$tree_inst->set_root_icon(icons::get_icon_url(CL_CRM_COMPANY));
		$tree_inst->set_root_url(aw_url_change_var("cat", NULL, aw_url_change_var("unit", NULL)));
	}

	//see funktsioon tegelt vist ebaoluline, a 2kki l2heb vaja ikka kui seda volituse lisamist muuta
	//niiet kui aasta on juba 2008 ja sa ikka veel seda kirja n2ed lugeda, siis kustuta see authorization funktsioon maha, kui tundub hea m6te
	/**
		@attrib name=authorization all_args=1
	**/
	function authorization($arr)
	{
//		if(!$arr["authorization"])
//		{
//		;
//		}
//		else
//		{
			print "Jigaboo";
			die();
//		}
		return $arr["post_ru"];
	}

	function _get_cedit_table($arr)
	{
		$this->_add_edit_stuff_to_table($arr);
		$this->_get_human_resources($arr);
	}
}
?>
