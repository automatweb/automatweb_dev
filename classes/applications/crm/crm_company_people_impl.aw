<?php


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

		if((int)$arr['request']['unit'])
		{
			$alias_to = $arr['request']['unit'];
		}

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Töötaja'),
			'link'=>aw_url_change_var(array(
				'action' => 'create_new_person',
				'parent' => $arr['obj_inst']->id(),
				'alias_to' => $alias_to,
				'reltype' => $arr['request']['unit'] ? 2 : 8,
				'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				"class" => "crm_company",
				"profession" => $arr["request"]["cat"] == CRM_ALL_PERSONS_CAT ? 0 : $arr["request"]["cat"]
			))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Üksus'),
			'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $arr["request"]["unit"] ? 1 : 28,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
				),
				'crm_section'
			)
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Ametinimetus'),
			'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> (int)$arr['request']['unit'] ? 3 : 29,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
				),
				'crm_profession'
			)
		));

		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			"confirm" => t("Oled kindel et soovid kustutada valitud t&ouml;&ouml;tajad?"),
			'action' => 'submit_delete_relations',
		));

		//uus kõne
		$tb->add_button(array(
			'name' => 'Kone',
			'img' => 'class_223.gif',
			'tooltip' => t('Tee kõne'),
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

		$tb->add_menu_item(array(
			'parent'=>'Search',
			'text' => t('Ametinimetusi'),
			'link'=> "javascript:submit_changeform('search_for_profs')"
		));

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

		$tb->add_button(array(
			"name" => "mark_important",
			"img" => "important.png",
			"tooltip" => t("Oluliseks"),
			"action" => "mark_p_as_important",
		));
	}

	function _get_unit_listing_tree($arr)
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
	}

	function callb_human_name($arr)
	{
		list($ln, $fn) = explode(" ", $arr["name"]);
		return html::get_change_url(
			$arr["id"],
			array("return_url" => get_ru()),
			parse_obj_name($fn." ".$ln)
		);
	}

	function _init_human_resources_table(&$t)
	{
		$t->define_field(array(
			"name" => "cal",
			"caption" => t(""),
			"width" => 1
		));

		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi'),
			'sortable' => '1',
			"chgbgcolor" => "cutcopied",
			'callback' => array(&$this, 'callb_human_name'),
			'callb_pass_row' => true,
		));
		$t->define_field(array(
        	'name' => 'phone',
			"chgbgcolor" => "cutcopied",
			'caption' => t('Telefon'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'email',
			"chgbgcolor" => "cutcopied",
			'caption' => t('E-post'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'section',
			"chgbgcolor" => "cutcopied",
			'caption' => t('Üksus'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'rank',
			"chgbgcolor" => "cutcopied",
			'caption' => t('Ametinimetus'),
            'sortable' => '1',
		));

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

		classload("core/icons");
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_human_resources_table($t);

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
		//if section present, i'll get all the professions
		if(is_oid($arr['request']['unit']))
		{
			$tmp_obj = new object($arr['request']['unit']);
			$conns = $tmp_obj->connections_from(array(
				"type" => "RELTYPE_PROFESSIONS"
			));
			foreach($conns as $conn)
			{
				$professions[$conn->prop('to')] = $conn->prop('to.name');
			}
		}

		if(is_oid($arr['request']['cat']) && $arr["request"]["cat"] != CRM_ALL_PERSONS_CAT)
		{
			$professions = array();
			$tmp_obj = new object($arr['request']['cat']);
			$professions[$tmp_obj->id()] = $tmp_obj->prop('name');
		}

		if ($arr["request"]["cat"] == CRM_ALL_PERSONS_CAT)
		{
			// get all units and all professions from those
			$units = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_SECTION")));
			$c = new connection();
			$p_conns = $c->find(array(
				"from.class_id" => CL_CRM_SECTION,
				"from" => $units->ids(),
				"type" => "RELTYPE_PROFESSIONS",
			));
			$professions = array();
			foreach($p_conns as $p_con)
			{
				$professions[$p_con["to"]] = $p_con["to.name"];
			}
		}

		//if listing from a specific unit, then the reltype is different
		if((int)$arr['request']['unit'])
		{
			$obj = new object((int)$arr['request']['unit']);
			$conns = $obj->connections_from(array(
				"type" => "RELTYPE_WORKERS",
			));
		}
		else
		{
			$u = get_instance(CL_USER);
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

		foreach($conns as $conn)
		{
			$persons[] = $conn->prop('to');
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

		// get calendars for persons
		$pers2cal = $this->_get_calendars_for_persons($persons);

		foreach($persons as $person)
		{
			$person = new object($person);
			$idat = $crmp->fetch_all_data($person->id());
			$pdat = $crmp->fetch_person_by_id(array(
				"id" => $person->id(),
				"cal_id" => $cal_id,
			));
			if(is_oid($arr['request']['cat']))
			{
				//persons only from this category
				if($arr["request"]["cat"] != CRM_ALL_PERSONS_CAT && !in_array($arr['request']['cat'], array_keys($pdat['ranks_arr'])))
				{
					continue;
				}
			}

			if(is_oid($arr['request']['cat']) || is_oid($arr['request']['unit']))
			{
				//showing only the professions that the unit AND the person is associated with
				//in php 4.3 it would be a one-liner with intersect_assoc
				/*$tmp_arr = array_intersect(array_keys($professions),array_keys($pdat['ranks_arr']));
				$tmp_arr2 = array();
				foreach($tmp_arr as $key=>$value)
				{
					$tmp_arr2[] = $professions[$value];
				}
				//getting the professions that the professions of the person are associated with
				foreach($pdat['ranks_arr'] as $key=>$rank)
				{
					$tmp_obj = new object($key);
					$conns=$tmp_obj->connections_from(array(
						'type' => "RELTYPE_SIMILARPROFESSION",
					));
					$tmp_arr = array();
					foreach($conns as $conn)
					{
						if(!in_array($conn->prop('to'), array_keys($tmp_arr2)))
						{
							$tmp_arr2[$conn->prop('to')] = $conn->prop('to.name');
						}
					}
				}
				$pdat['rank'] = join(', ',$tmp_arr2);*/
				$ol = new object_list($person->connections_from(array("type" => "RELTYPE_RANK")));
				$pdat["rank"] = html::obj_change_url($ol->ids());
			}

			$sections_professions = array();
			$section = '';
			foreach($pdat['sections_arr'] as $key=>$value)
			{
				$crm_section = get_instance(CL_CRM_SECTION);
				$sections_professions[$key] = $crm_section->get_professions($key);
				$tmp_arr = array_intersect(array_keys($pdat['ranks_arr']),array_keys($pdat['ranks_arr']));
				$tmp_arr2 = array();
				foreach($tmp_arr as $key2=>$value2)
				{
					$tmp_arr2[] = $pdat['ranks_arr'][$value2];
				}
				$section = current($pdat['sections_arr']);//.', '.join(', ',$tmp_arr2);
				//damn, i'm not sure if a person can have multiple sections?
				//until then the break stays here
				break;
			}

			//kui amet kuulub $pdat['sections_arr'] olevasse sektsiooni ja persoon on seotud
			//selle ametiga, siis seda näidata kujul

			$ccp = (isset($_SESSION["crm_copy_p"][$person->id()]) || isset($_SESSION["crm_cut_p"][$person->id()]) ? "#E2E2DB" : "");
			$cal = "";
			if ($pers2cal[$person->id()])
			{
				$calo = obj($pers2cal[$person->id()]);
				$cal = html::href(array(
					"url" => html::get_change_url($calo->id(), array("return_url" => get_ru(), "group" => "views", "viewtype" => "week"))."#today",
					"caption" => html::img(array(
						"url" => icons::get_icon_url(CL_PLANNER),
						"border" => 0
					))
				));
			}

			list($fn, $ln) = explode(" ", $person->prop('name'));
			$tdata = array(
				"name" => $ln." ".$fn,
				"cal" => $cal,
				"id" => $person->id(),
				"phone" => $pdat["phone"] != "" ? $pdat["phone"] : $arr["obj_inst"]->prop_str("phone_id"),
				"rank" => $pdat["rank"],
				'section' => $section,
				"email" => html::href(array(
					"url" => "mailto:" . $pdat["email"],
					"caption" => $pdat["email"],
				)),
				"cutcopied" => $ccp
			);
			$t->define_data($tdata);
		};
	}

	function _get_contacts_search_results($arr)
	{
		if(!($arr['request']['contact_search'] && $arr['request']['contacts_search_show_results']))
		{
			return IGNORE_PROP;
		}

		$t = &$arr["prop"]["vcl_inst"];
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
			'caption' => t('Üksus'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'rank',
			'caption' => t('Ametinimetus'),
			'sortable' => '1',
		));
		$t->define_chooser(array(
			'name'=>'check',
			'field'=>'id',
		));

		$search_params = array(
			'class_id' => CL_CRM_PERSON,
			'limit' => 50,
			'sort_by'=>'name'
		);

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

		//let's try to get certain fields
		$search_params['sort_by'] = 'name';

		$ol = new object_list($search_params);

		$pl = get_instance(CL_PLANNER);
		$person = get_instance(CL_CRM_PERSON);
		$cal_id = $pl->get_calendar_for_user(array('uid'=>aw_global_get('uid')));

		foreach($ol->arr() as $o)
		{
			$person_data = $person->fetch_person_by_id(array(
				'id' => $o->id(),
				'cal_id' => $calid
			));
			$t->define_data(array(
				"name" => $o->prop('name'),
				"id" => $o->id(),
				"phone" => $person_data['phone'],
				"rank" => $person_data["rank"],
				'section' => $person_data['section'],
				"email" => html::href(array(
					"url" => "mailto:" . $person_data['email'],
					"caption" => $person_data['email'],
				)),
			));
		}
	}

	function _get_prof_search_results($arr)
	{
		if(!($arr['request']['prof_search'] && $arr['request']['prof_search_show_results']))
		{
			return IGNORE_PROP;
		}

		$t = &$arr["prop"]["vcl_inst"];
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
			'caption' => t('Üksus'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'rank',
			'caption' => t('Ametinimetus'),
			'sortable' => '1',
		));
		$t->define_chooser(array(
			'name'=>'check',
			'field'=>'id',
		));

		$search_params = array(
			'class_id' => CL_CRM_PERSON,
			'limit' => 50,
			'sort_by'=>'name'
		);

		if($arr['request']['prof_search_firstname'])
		{
			$search_params['firstname'] = '%'.urldecode($arr['request']['contact_search_firstname']).'%';
		}

		if($arr['request']['prof_search_lastname'])
		{
			$search_params['lastname'] = '%'.urldecode($arr['request']['contact_search_lastname']).'%';
		}

		if($arr['request']['prof_search_code'])
		{
			$search_params['personal_id'] = '%'.urldecode($arr['request']['contact_search_code']).'%';
		}

		//let's try to get certain fields
		$search_params['sort_by'] = 'name';

		$ol = new object_list($search_params);

		$pl = get_instance(CL_PLANNER);
		$person = get_instance(CL_CRM_PERSON);
		$cal_id = $pl->get_calendar_for_user(array('uid'=>aw_global_get('uid')));

		foreach($ol->arr() as $o)
		{
			$person_data = $person->fetch_person_by_id(array(
				'id' => $o->id(),
				'cal_id' => $calid
			));
			$t->define_data(array(
				"name" => $o->prop('name'),
				"id" => $o->id(),
				"phone" => $person_data['phone'],
				"rank" => $person_data["rank"],
				'section' => $person_data['section'],
				"email" => html::href(array(
					"url" => "mailto:" . $person_data['email'],
					"caption" => $person_data['email'],
				)),
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
			'tooltip' => t('Kustuta valitud tööpakkumised'),
			'action' => 'delete_selected_objects',
			'confirm' => t("Kas oled kindel et soovid valitud tööpakkumised kustudada?")
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
			'text'=> t('Tööpakkumine'),
			'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $reltype,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI')),
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
				$professin_cap = t("Määramata");
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
			'tooltip' => t('Kustuta valitud tööpakkumised'),
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
		$arr["prop"]["value"] = "<span style=\"border: 1px black;\"><b>Otsi isikuid</b>";
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

	function _init_sect_edit_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_sect_edit($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sect_edit_t($t);

		classload("core/icons");
		$this->_req_draw_sects($arr["obj_inst"], $t);
		$t->set_sortable(false);
	}

	function _req_draw_sects($o, &$t)
	{
		$this->sect_level++;
		foreach($o->connections_from(array("type" => "RELTYPE_SECTION")) as $c)
		{
			$to = $c->to();
			$t->define_data(array(
				"name" => str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $this->sect_level-1).icons::get_icon($to).html::obj_change_url($to),
				"oid" => $c->prop("to")
			));

			foreach($to->connections_from(array("type" => "RELTYPE_PROFESSIONS")) as $c)
			{
				$t->define_data(array(
					"name" => str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $this->sect_level).icons::get_icon($c->to()).html::obj_change_url($c->to()),
					"oid" => $c->prop("to")
				));
			}
			$this->_req_draw_sects($to, $t);
		}
		$this->sect_level--;
	}

	function _get_sect_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			'action' => 'submit_delete_sects',
		));
	}
}
?>
