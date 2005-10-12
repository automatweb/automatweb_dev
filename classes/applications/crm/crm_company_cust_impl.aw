<?php

class crm_company_cust_impl extends class_base
{
	function crm_company_cust_impl()
	{
		$this->init();
	}

	function do_projects_table_header(&$table, $data = false)
	{
		$table->define_field(array(
			"name" => "project_name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		if (is_array($data))
		{
			$filt = array();
			foreach($data as $row)
			{
				$filt["project_orderer"][] = $row["project_orderer"];
				$filt["project_impl"][] = $row["project_impl"];
				$part = strip_tags($row["project_participants"]);
				foreach(explode(",", $part) as $nm)
				{
					$filt["project_participants"][] = trim($nm);
				}
			}
		}

		$filt["project_participants"] = array_unique($filt["project_participants"]);

		$table->define_field(array(
			"name" => "project_orderer",
			"caption" => t("Tellija"),
			"sortable" => 1,
			"filter" => $filt["project_orderer"]
		));
		$table->define_field(array(
			"name" => "project_impl",
			"caption" => t("Teostaja"),
			"sortable" => 1,
			"filter" => $filt["project_impl"]
		));
		
		$table->define_field(array(
			"name" => "project_deadline",
			"caption" => t("T&auml;htaeg"),
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		
		$table->define_field(array(
			"name" => "project_participants",
			"caption" => t("Osalejad"),
			"sortable" => 1,
			"filter" => $filt["project_participants"]
		));
		
		$table->define_field(array(
			"name" => "project_created",
			"caption" => t("Loodud"),
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i",
			"numeric" => 1
		));

		$table->define_field(array(
			"name" => "roles",
			"caption" => t("Rollid"),
			"sortable" => 0,
		));

		$table->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_my_projects($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		/*$conns = new connection();
		$conns_ar = $conns->find(array(
			"from.class_id" => CL_PROJECT,
			"to" => aw_global_get("uid_oid"),
			"type" =>  2,
		));
		$conns_ol = new object_list();
		foreach($conns_ar as $con)
		{
			$conns_ol->add($con["from"]);
		}*/
		$i = get_instance(CL_CRM_COMPANY);
		$prj = $i->get_my_projects();
		if (!count($prj))
		{
			$conns_ol = new object_list();
		}
		else
		{
			$conns_ol = new object_list(array("oid" => $prj));
		}

		if ($arr["request"]["do_proj_search"] && $conns_ol->count())
		{
			$filt = $this->_get_my_proj_search_filt($arr["request"], $conns_ol->ids());
			$conns_ol = new object_list($filt);
		}

		if ($conns_ol->count())
		{
			$conns_ol = new object_list(array(
				"oid" => $conns_ol->ids(),
				"class_id" => CL_PROJECT,
				"state" => new obj_predicate_not(PROJ_DONE)
			));
		}
		foreach ($conns_ol->arr() as $project_obj)
		{
			if (is_oid($cpi = $project_obj->prop("contact_person_implementor")) && $this->can("view", $cpi))
			{
				$impl = html::get_change_url($cpi, array("return_url" => get_ru()), $project_obj->prop_str("contact_person_implementor"));
			}
			else
			{
				$impl = $this->_get_linked_names($project_obj->connections_from(array("type" => "RELTYPE_IMPLEMENTOR")));
			}
			$data[] = array(
				"project_name" => html::get_change_url($project_obj->id(), array("return_url" => get_ru()), $project_obj->name()),
				"project_participants"	=> $this->_get_linked_names($project_obj->connections_from(array("type" => "RELTYPE_PARTICIPANT"))),
				"project_created" => $project_obj->created(),
				"project_orderer" => $this->_get_linked_names($project_obj->connections_from(array("type" => "RELTYPE_ORDERER"))),
				"project_impl" => $impl,
				"project_deadline" => $project_obj->prop("deadline"),
				"oid" => $project_obj->id()
			);
		}
	
		$this->do_projects_table_header($table, $data);
		foreach($data as $row)
		{
			$table->define_data($row);
		}
		return PROP_OK;
	}

	function _get_customer_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$alias_to = $arr['obj_inst']->id();

		if (!(int)$arr['request']['category'])
		{
			$f_cat = $this->_get_first_cust_cat($arr["obj_inst"]);
			if ($f_cat)
			{
				(int)$arr['request']['category'] = $f_cat->id();
			}
		}

		if((int)$arr['request']['category'])
		{
			$alias_to = $arr['request']['category'];
			$parent = (int)$arr['request']['category'];
		}

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Kategooria'),
			'link' => $this->mk_my_orb('new',array(
					'parent' => $arr['obj_inst']->id(),
					'alias_to' => $alias_to,
					'reltype' => 30, //RELTYPE_CATEGORY
					'return_url' => urlencode(aw_global_get('REQUEST_URI'))
				),
				'crm_category'
			)	
		));

		if (is_oid($arr["request"]["category"]))
		{
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => t('Klient'),
				'link' => $this->mk_my_orb('new',array(
						'parent' => $arr['obj_inst']->id(),
						'alias_to' => $alias_to,
						'reltype' => 3, // crm_category.CUSTOMER,
						'return_url' => get_ru()
					),
					'crm_company'
				)
			));
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => t('Klient (eraisik)'),
				'link' => $this->mk_my_orb('new',array(
						'parent' => $arr['obj_inst']->id(),
						'alias_to' => $alias_to,
						'reltype' => 3, // crm_category.CUSTOMER,
						'return_url' => get_ru()
					),
					CL_CRM_PERSON
				)
			));
		}

		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			'action' => 'submit_delete_customer_relations',
		));

		$tb->add_button(array(
			'name' => 'Search',
			'img' => 'search.gif',
			'tooltip' => t('Otsi'),
			'action' => 'search_for_customers'
		));
		
		if($arr['request']['customer_search'])
		{
			$tb->add_button(array(
				'name' => 'Save',
				'img' => 'save.gif',
				'tooltip' => t('Salvesta'),
				'action' => 'save_customer_search_results'
			));
		}

		$tb->add_separator();

		$tb->add_button(array(
			"name" => "add_proj_impl",
			"tooltip" => t("Lisa projekt teostajana"),
			"action" => "add_proj_to_co_as_impl"
		));

		$tb->add_button(array(
			"name" => "add_proj_ord",
			"tooltip" => t("Lisa projekt tellijana"),
			"action" => "add_proj_to_co_as_ord"
		));

		$tb->add_button(array(
			"name" => "add_task",
			"tooltip" => t("Lisa toimetus"),
			"action" => "add_task_to_co"
		));

		return PROP_OK;
	}

	function _get_customer_listing_tree($arr)
	{
		if ($arr["request"]["customer_search"])
		{
			return PROP_IGNORE;
		}
		$tree_inst = &$arr['prop']['vcl_inst'];
		$tree_inst->set_only_one_level_opened(1);

		$node_id = 0;

		// get first cat
		$f_cat = $this->_get_first_cust_cat($arr["obj_inst"]);

		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['category'] ? (int)$arr['request']['category'] : ($f_cat ? $f_cat->id() : 0);
		$i->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_CATEGORY',
			'skip' => array(CL_CRM_COMPANY),
			'attrib' => 'category',
			'leafs' => false,
			'style' => 'nodetextbuttonlike',
		));

		return PROP_OK;
	}

	function _org_table_header($tf)
	{
		$tf->define_field(array(
			"name" => "name",
			"caption" => t("Organisatsioon"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "pohitegevus",
			"caption" => t("Põhitegevus"),
			"sortable" => 1,
		));

		/*$tf->define_field(array(
			"name" => "corpform",
			"caption" => t("Õiguslik vorm"),
			"sortable" => 1,
		));*/

		$tf->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "url",
			"caption" => t("WWW"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "phone",
			"caption" => t('Telefon'),
		));

		$tf->define_field(array(
			"name" => "ceo",
			"caption" => t("Juht"),
			"sortable" => 1,
		));
		
		$tf->define_field(array(
			"name" => "rollid",
			"caption" => t("Rollid"),
			"sortable" => 0,
		));

		$tf->define_chooser(array(
			"field" => "id",
			"name" => "check",
		));
	}

	function _get_customer($arr, $filter = NULL)
	{
		if ($arr["request"]["customer_search"])
		{
			return PROP_IGNORE;
		}

		$tf = &$arr["prop"]["vcl_inst"];
		$this->_org_table_header(&$tf);

		//will list the companys from the category
		//if category is selected
		$organization = &$arr['obj_inst'];

		if (!is_oid($arr['request']['category']))
		{
			$f_cat = $this->_get_first_cust_cat($arr["obj_inst"]);
			if ($f_cat)
			{
				$arr['request']['category'] = $f_cat->id();
			}
		}

		if($arr['request']['category']!='parent' && is_oid($arr['request']['category']))
		{
			$organization = new object($arr['request']['category']);
		}
		$orgs = $organization->connections_from(array(
			"type" => 'RELTYPE_CUSTOMER',
		));

		$orglist = array();
		foreach($orgs as $org)
		{
			$orglist[$org->prop("to")] = $org->prop("to");
		}
		if ($filter)
		{
			$orglist = $this->make_keys($filter);
		}

		$rs_by_co = array();
		$role_entry_list = new object_list(array(
			"class_id" => CL_CRM_COMPANY_ROLE_ENTRY,
			"company" => $arr["request"]["id"],
			"client" => $orglist,
			"project" => new obj_predicate_compare(OBJ_COMP_LESS, 1)
		));
		foreach($role_entry_list->arr() as $role_entry)
		{
			$rc_by_co[$role_entry->prop("client")][$role_entry->prop("person")][] = html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"unit" => $role_entry->prop("unit"),
					), 
					parse_obj_name($role_entry->prop_str("unit"))
				)
				."/".
				html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"cat" => $role_entry->prop("role")
					), 
					parse_obj_name($role_entry->prop_str("role"))
				);
		}

		foreach($orglist as $org)
		{
			if($filter)
			{
				if(!in_array($org,$filter))
				{
					continue;
				}
			}
			$o = obj($org);
			// aga ülejäänud on kõik seosed!
			$vorm = $tegevus = $contact = $juht = $juht_id = $phone = $url = $mail = "";
			if (is_oid($o->prop("ettevotlusvorm")))
			{
				$tmp = new object($o->prop("ettevotlusvorm"));
				$vorm = $tmp->prop('shortname');
			};

			if (is_oid($o->prop("email_id")) && $this->can("view", $o->prop("email_id")))
			{
				$mail_obj = new object($o->prop("email_id"));
				$mail = html::href(array(
					"url" => "mailto:" . $mail_obj->prop("mail"),
					"caption" => $mail_obj->prop("mail"),
				));

			};

			$roles = $this->_get_role_html(array(
				"from_org" => $arr["request"]["id"],
				"to_org" => $o->id(),
				"rc_by_co" => $rc_by_co
			));

			$juht = "";
			if (is_oid($o->prop("firmajuht")) && $this->can("view", $o->prop("firmajuht")))
			{
				$tmp = obj($o->prop("firmajuht"));
				$juht = $tmp->name();
			}

			$tf->define_data(array(
				"id" => $o->id(),
				"name" => html::get_change_url($o->id(), array("return_url" => get_ru()), $o->name()." ".$vorm),
				"reg_nr" => $o->prop("reg_nr"),
				"pohitegevus" => $o->prop_str("pohitegevus"),
				"corpform" => $vorm,
				"address" => $o->prop_str("contact"),
				"ceo" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $o->prop("firmajuht"),
					),CL_CRM_PERSON),
					"caption" => $juht,
				)),
				"phone" => $o->prop_str("phone_id"),
				"url" => html::href(array(
					"url" => $o->prop_str("url_id"),
					"caption" => $o->prop_str("url_id"),
				)),
				"email" => $mail,
				'rollid' => $roles,
			));
		}
	}

	function _get_role_html($arr)
	{
		extract($arr);
		$role_url = $this->mk_my_orb("change", array(
			"from_org" => $from_org,
			"to_org" => $to_org,
			"to_project" => $to_project
		), "crm_role_manager");

		$roles = array();
			
		$iter = safe_array($rc_by_co[$to_org]);
		if (!empty($to_project))
		{
			$iter = safe_array($rc_by_co[$to_org][$to_project]);
		}
		foreach($iter as $r_p_id => $r_p_data)
		{
			$r_p_o = obj($r_p_id);
			$roles[] = html::get_change_url($r_p_o->id(), array(), parse_obj_name($r_p_o->name())).": ".join(",", $r_p_data);
		}
		$roles = join("<br>", $roles);

		$roles .= ($roles != "" ? "<br>" : "" ).html::popup(array(
			"url" => $role_url,
			'caption' => t('Rollid'),
			"width" => 800,
			"height" => 600,
			"scrollbars" => "auto"
		));
		return $roles;
	}

	function _get_customer_search_only($arr)
	{
		if (!$arr["request"]["customer_search"])
		{
			return PROP_IGNORE;
		}

		$us = get_instance(CL_USER);
		$users_person = obj($us->get_current_person());

		$obj = $arr["obj_inst"];
		$arr["prop"]['options'] = array(
			'all'=> t('Otsi kogu süsteemist'),
			'company' => sprintf(t('Otsi %s klientide hulgast'), $obj->prop('name')),
			'person' => sprintf(t('Otsi %s klientide hulgast'), $users_person->prop('name'))
		);

		if(in_array($arr['request']['customer_search_only'],array_keys($arr["prop"]['options'])))
		{
			$arr["prop"]['value'] = $arr['request']['customer_search_only'];
		}
		else
		{
			list($arr["prop"]['value']) = each($arr["prop"]['options']);
		}
	}

	function _get_customer_search_add($arr)
	{
		if (!$arr["request"]["customer_search"])
		{
			return PROP_IGNORE;
		}
		$filter = $this->construct_customer_search_filter($arr);
		$this->customer_search_results = &$this->get_customer_search_results($filter);

		if ($this->customer_search_results && 
			sizeof($this->customer_search_results->ids()) || 
			$arr['request']['no_results'])
		{
			return PROP_IGNORE;
		}
	}

	/*
		constructs the xfilter for get_customer_search_results
	*/
	function construct_customer_search_filter(&$arr)
	{
		//i'll try the search from crm_org_search.aw
		$searchable_fields = array(
			'customer_search_name' => 'name',
			'customer_search_reg' => 'reg_nr',
			'customer_search_address'=> 'address',
			'customer_search_city' => 'linn',
			'customer_search_county' => 'maakond',
			'customer_search_field' => 'pohitegevus',
			'customer_search_leader' => 'firmajuht'
		);

		$search_params = array('class_id'=>CL_CRM_COMPANY,'limit'=>100,'sort_by'=>'name');

		foreach($searchable_fields as $key=>$value)
		{
			if($arr['request'][$key])
			{
				//let's clean up the item
				$tmp_arr = explode(',',$arr['request'][$key]);
				array_walk($tmp_arr,create_function('&$param','$param = trim($param);'));
				array_walk($tmp_arr,create_function('&$param','$param = "%".$param."%";'));
				$search_params[$value] = $tmp_arr;
			}
		}

		if(!in_array($arr['request']['customer_search_only'], array('all','company','person')))
		{
			$search_params['customer_search_only'] = 'all';
		}
		else
		{
			$search_params['customer_search_only'] = $arr['request']['customer_search_only'];
		}

		$search_params['company_id'] = $arr['request']['id'];

		if($arr['request']['no_results'])
		{
			$search_params['no_results'] = true;
			return $search_params;
		}
		else
		{
			return $search_params;
		}
	}

	/*
		constructs a object list base on the xfilter
	*/
	function get_customer_search_results($xfilter)
	{	
		$company_id = $xfilter['company_id'];
		unset($xfilter['company_id']);
		if (!sizeof($xfilter))
		{
			return false;
		};

		if($xfilter['no_results'])
		{
			return false;
		}

		if (sizeof($xfilter['firmajuht']))
		{
			// search by ceo name? first create a list of all crm_persons
			// that match the search criteria and after that create a list
			// of crm_companies that have one of the results as a ceo
			$ceo_filter = array(
				"class_id" => CL_CRM_PERSON,
				"limit" => 100,
				"name" => "%" . $xfilter['firmajuht'] . "%",
			);
			$ceo_list = new object_list($ceo_filter);
			if (sizeof($ceo_list->ids()) > 0)
			{
				$xfilter['firmajuht'] = &$filter['firmajuht'];
			};
		};

		$addr_xfilter = array();
		$no_results = false;

		if(sizeof($xfilter['linn']))
		{
			$city_list = new object_list(array(
				'class_id'=>CL_CRM_CITY,
				'limit' => 100,
				'name' => $xfilter['linn'],
			));
							
			if(sizeof($city_list->ids()))
			{
				$addr_xfilter['linn'] = $city_list->ids();
			}
			else
			{
				$no_results = true;
			}
			unset($xfilter['linn']);
		}

		if(sizeof($xfilter['maakond']))
		{
			$county_list = new object_list(array(
				'class_id' => CL_CRM_COUNTY,
				'limit' => 100,
				'name' => $xfilter['maakond']
			));
			if(sizeof($county_list->ids()))
			{
				$addr_xfilter['maakond'] = $county_list->ids();
			}
			else
			{
				$no_results = true;
			}
			unset($xfilter['maakond']);
		}
	
		if(sizeof($xfilter['address']))
		{
			$addr_xfilter['name'] = &$xfilter['address'];
			unset($xfilter['address']);
		}

		if (sizeof($addr_xfilter)>0)
		{
			$addr_xfilter['class_id'] = CL_CRM_ADDRESS;
			$addr_xfilter['limit'] = 100;

			$addr_list = new object_list($addr_xfilter);

			if (sizeof($addr_list->ids()) > 0)
			{
				$xfilter['contact'] = $addr_list->ids();
			}
			else
			{
				$no_results=true;
			}
		}


		if(sizeof($xfilter['pohitegevus']))
		{
			$tmp_filter['class_id'] = CL_CRM_SECTOR;
			$tmp_filter['limit'] = 100;
			$tmp_filter['name'] = $xfilter['pohitegevus'];
			$tmp_list = new object_list($tmp_filter);
			unset($xfilter['pohitegevus']);
			if(sizeof($tmp_list->ids())>0)
			{
				$xfilter['pohitegevus'] = $tmp_list->ids();
			}
			else
			{
				$no_results=true;
			}
		}
		
		if($xfilter['customer_search_only']=='company')
		{
			//have to get the list of all the clients for this company
			$company = new object($company_id);
			$data = array();
			$i = get_instance(CL_CRM_COMPANY);
			$i->get_customers_for_company($company, &$data);
			foreach($data as $value)
			{
				$xfilter['oid'][$value] = $value;	
			}
		}
		else if($xfilter['customer_search_only']=='person')
		{
			//have to get the list of all the companys for
			//this users person
			$us = get_instance(CL_USER);
			$person = obj($us->get_current_person());
			//if the user has a person's object associated with him
			if($person)
			{
				//genereerin listi persooni kõikidest firmadest
				$person = new object($person);
				$conns=$person->connections_from(array(
					"type" => "RELTYPE_HANDLER",
				));
				foreach($conns as $conn)
				{
					$xfilter['oid'][$conn->prop('to')] = $conn->prop('to');
				}
			}
			else
			{
				//@todo võix visata errori, aga peax mõtlema kuidas see error peax välja nägema
				//
			}
		}
		unset($xfilter['customer_search_only']);
		if(!$no_results)
		{
			return new object_list($xfilter);
		}
		else
		{
			return new object_list(NULL);
		}
	}
	
	function _init_customer_search_res_t(&$tf)
	{
		$tf->define_field(array(
			"name" => "name",
			"caption" => t("Organisatsioon"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "corpform",
			"caption" => t("Õiguslik vorm"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "url",
			"caption" => t("WWW"),
			"sortable" => 1,
		));
		$tf->define_field(array(
			"name" => "phone",
			"caption" => t('Telefon'),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "ceo",
			"caption" => t("Juht"),
			"sortable" => 1,
		));

		$tf->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function _get_customer_search_results($arr)
	{
		if (!$arr["request"]["customer_search"])
		{
			return PROP_IGNORE;
		}
		if ($this->customer_search_results)
		{
			$results = $this->customer_search_results;
		
			$tf = &$arr["prop"]["vcl_inst"];
			$this->_init_customer_search_res_t(&$tf);

			$count = 0;
			foreach($results->arr() as $o)
			{
				$count++;
				// aga ülejäänud on kõik seosed!
				$vorm = $tegevus = $contact = $juht = $juht_id = $phone = $url = $mail = "";
				if (is_oid($o->prop("ettevotlusvorm")) && $this->can("view", $o->prop("ettevotlusvorm")))
				{
					$tmp = new object($o->prop("ettevotlusvorm"));
					$vorm = $tmp->name();
				};

				if (is_oid($o->prop("firmajuht")))
				{
					$juht_obj = new object($o->prop("firmajuht"));
					$juht = $juht_obj->name();
					$juht_id = $juht_obj->id();
				};

				if (is_oid($o->prop("url_id")) && $this->can("view", $o->prop("url_id")))
				{
					$url_obj = new object($o->prop("url_id"));
					$url = $url_obj->prop("url");
					// I dunno, sometimes people write url into the name field and expect this to work
					if (empty($url))
					{
						$url = $url_obj->name();
					};
				};

				if (is_oid($o->prop("email_id")) && $this->can("view", $o->prop("email_id")))
				{
					$mail_obj = new object($o->prop("email_id"));
					$mail = html::href(array(
						"url" => "mailto:" . $mail_obj->prop("mail"),
						"caption" => $mail_obj->prop("mail"),
					));
				};

				$tf->define_data(array(
					"id" => $o->id(),
					"name" => html::href(array(
						"url" => $this->mk_my_orb("change",array(
							"id" => $o->id(),
						),$o->class_id()),
						"caption" => $o->name(),
					)),
					"reg_nr" => $o->prop("reg_nr"),
					"corpform" => $vorm,
					"address" => $o->prop_str("contact"),
					"ceo" => html::href(array(
						"url" => $this->mk_my_orb("change",array(
							"id" => $juht_id,
						),CL_CRM_PERSON),
						"caption" => $juht,
					)),
					"phone" => $o->prop_str("phone_id"),
					"url" => html::href(array(
						"url" => $url,
						"caption" => $url,
					)),
					"email" => $mail,
				));
			}

			if ($count == 0)
			{
				$tf->set_header("Otsing ei leidnud ühtegi objekti");
			};
		}
	}

	function _get_my_customers_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			'action' => 'submit_delete_my_customers_relations',
		));
	}

	function _get_my_customers_listing_tree($arr)
	{
		$tree_inst = &$arr['prop']['vcl_inst'];	
		$node_id = 0;

		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['category'];

		$i->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_CATEGORY',
			'skip' => array(CL_CRM_COMPANY),
			'attrib' => 'category',
			'leafs' => 'false',
			'style' => 'nodetextbuttonlike',
		));
				
		//need to delete every category of the tree that the person doesn't
		//have a relation with
		$my_data = array();
		$us = get_instance(CL_USER);
		$person = obj($us->get_current_person());
		$conns = $person->connections_from(array(
			'type' => "RELTYPE_HANDLER",
		));
				
		foreach($conns as $conn)
		{
			$my_data[$conn->prop('to')] = $conn->prop('to');
		}
		//$this->_clean_up_the_tree(&$tree_inst->items, 0, &$my_data);
	}

	function _clean_up_the_tree($tree_items, $arrkey, $my_data)
	{
		$ret = false;
		foreach($tree_items[$arrkey] as $key=>$value)
		{
			//these are toplevel nodes
			//checking if one has sub_elements
			if(array_key_exists($value['id'], $tree_items))
			{
				//has subelements
				$ret = $this->_clean_up_the_tree(&$tree_items, $value['id'], &$my_data);
				$keep_it = false;

				foreach($my_data as $key2=>$value2)
				{
					if(in_array($value2, $value['oid']))
					{
						$keep_it = true;
						$ret = true;
					}
				}

				if(!$ret && !$keep_it)
				{
					unset($tree_items[$arrkey][$key]);
				}
			}
			//no sub elements, now if this node isn't useful to me
			//it will get deleted :)
			else
			{
				$keep_it = false;
				foreach($my_data as $key2=>$value2)
				{
					if(in_array($value2, $value['oid']))
					{
						$keep_it = true;
					}
				}
				if(!$keep_it)
				{
					unset($tree_items[$arrkey][$key]);
				}
				return $keep_it;
			}
		}
		return $ret;
	}

	function _get_my_customers_table($arr)
	{
		/*$us = get_instance(CL_USER);
		$person = obj($us->get_current_person());
		$conns = $person->connections_from(array(
			"type" => "RELTYPE_HANDLER",
		));
		$filter = array();
		foreach($conns as $conn)
		{
			$filter[$conn->prop('to')] = $conn->prop('to');
		}*/
		$i = get_instance(CL_CRM_COMPANY);
		$this->_get_customer($arr, $i->get_my_customers());
	}

	function _get_offers_listing_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));
		
		$params = array(
			'alias_to'=> $arr['obj_inst']->id(),
			'reltype'=> 9, //RELTYPE_OFFER,
			'org' => $arr['obj_inst']->id(),
			'alias_to_org' => $arr['request']['org_id'],
			"return_url" => get_ru()
		);
		
		$tb->add_menu_item(array(
				'disabled' => $arr['request']['org_id']? false : true,
				'parent'=>'add_item',
				'text'=>t('Pakkumine'),
				'url' => html::get_new_url(CL_CRM_OFFER, $arr['obj_inst']->id(), $params),
		));
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_selected_objects",
			"confirm" => t("Kas oled kindel, et soovid valitud pakkumise(d) kustutada?"),
			"tooltip" => t("Kustuta")
		));
	}	

	function _get_offers_listing_tree($arr)
	{
		get_instance("core/icons");

		// list all child rels
		$parents = array();
		$c = new connection();
		foreach($c->find(array("from" => $data, "type" => 7 /* "RELTYPE_CHILD_ORG" */)) as $rel)
		{
			$parents[$rel["to"]] = $rel["from"];
		}

		$tree = &$arr["prop"]["vcl_inst"];
		$node_id = 0;
		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['category'];
		$i->generate_tree(array(
			'tree_inst' => &$tree,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_CATEGORY',
			'attrib' => 'category',
			'leafs' => "do_offer_tree_leafs",
			'style' => 'nodetextbuttonlike',
			'parent2chmap' => $parents
		));
		
		$node_id++;
		$tree->add_item(0, array(
			'id' => $node_id,
			'name' => t('Kõik organisatsioonid'),
			'url' => '',
		));
		
		$all_org_parent = $node_id;
		
		$data = array();
		$i->get_customers_for_company($arr["obj_inst"], &$data);

		foreach ($data as $customer)
		{
			$obj = &obj($customer);
			$pt = $all_org_parent;
			if (isset($parents[$customer]))
			{
				$pt = "ao".$parents[$customer];
			}
			$tree->add_item($pt, array(
				'id' => "ao".$customer,
				'name' => $obj->id()==$arr["request"]["org_id"]?"<b>".$obj->name()."</b>":$obj->name(),
				'iconurl' => icons::get_icon_url($obj->class_id()),
				'url' => aw_url_change_var(array('org_id' => $obj->id())),
			));
		}
		
	}

	function _get_offers_listing_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		if(!$arr["request"]["org_id"])
		{
			$table->define_field(array(
				"name" => "org",
				"caption" => t("Organisatsioon"),
				"sortable" => "1",
				"align" => "center",
			));
		}
		
		$table->define_field(array(
			"name" => "offer_name",
			"caption" => t("Nimi"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "salesman",
			"caption" => t("Koostaja"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "offer_made",
			"caption" => t("Lisatud"),
			"sortable" => "1",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "offer_sum",
			"caption" => t("Summa"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "offer_status",
			"caption" => t("Staatus"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"field" => "select",
			"caption" => t("X"),
		));
		
		$offer_inst = get_instance(CL_CRM_OFFER);
		if($arr["request"]["org_id"])
		{
			$offers = &$offer_inst->get_offers_for_company($arr["request"]["org_id"], $arr["obj_inst"]->id());
		}
		else
		{
			$params = array(
				"preformer" => $arr["obj_inst"]->id(),
				"offer_status" => array(0,1,2),
				"class_id" => CL_CRM_OFFER,
			);
			
			if(is_oid($arr["request"]["category"]))
			{
				$cat = &obj($arr["request"]["category"]);
				$data = array();
				$i = get_instance(CL_CRM_COMPANY);
				$i->get_customers_for_company($cat,&$data,true);
				foreach ($data as $org)
				{
					$offer_obj = $offer_inst->get_offers_for_company($org, $arr["obj_inst"]->id());
					foreach ($offer_obj->arr() as $tmp)
					{
						$ids[] = $tmp->id();
					}
				}
				$params["oid"] = $ids;
				if(count($ids)>0)
				{
					$offers = new object_list($params);
				}
			}
			if(!$arr["request"]["org_id"] && !$arr["request"]["category"])
			{
				$offers = new object_list($params);
			}
		}
		
		if(is_object($offers))
		{
			if($offers->count() > 0)
			{
				$statuses = array(
					t("Koostamisel"), 
					t("Saadetud"), 
					t("Esitletud"), 
					t("Tagasilükatud"), 
					t("Positiivelt lõppenud")
				);
				foreach ($offers->arr() as $offer)
				{
					//Do not list brother offers
					if($offer->is_brother())
					{
						continue;
					}
					$org = &obj($offer->prop("orderer"));
					if($this->can("view", $offer->prop("salesman")))
					{
						$salesman = &obj($offer->prop("salesman"));
						$salesmanlink = html::get_change_url($salesman->id(), array(), $salesman->name());
					}
					$table->define_data(array(
						"org" => is_object($org)?html::get_change_url($org->id(), array(), $org->name()):false,
						"salesman" => $salesmanlink,
						"offer_name" => html::get_change_url($offer->id(), array(), $offer->name()),
						"offer_made" => $offer->created(),
						"offer_sum" => $offer->prop("sum"),//$offer_inst->total_sum($offer->id()),
						"select" => $offer->id(),
						"offer_status" => $statuses[$offer->prop("offer_status")],
						"offer_nr_status" => $offer->prop("offer_status"),
					));
					$table->set_default_sortby("offer_made");
					$table->set_default_sorder('desc');
				}
			}
		}
	}

	function _get_projects_listing_tree($arr)
	{
		if ($arr["request"]["search_all_proj"])
		{
			return PROP_IGNORE;
		}
		return $this->_get_offers_listing_tree($arr);
	}

	function _get_projects_listing_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$this->do_projects_table_header(&$table);
		
		if (!$arr["request"]["search_all_proj"])
		{
			$project_conns = new connection();
		
			if(!$arr["request"]["org_id"])
			{
				$i = get_instance(CL_CRM_COMPANY);
				$cust = array();
				$i->get_customers_for_company($arr["obj_inst"], &$cust);

				$project_conns = $project_conns->find(array(
					"to" => $cust,
					"reltype" => 10,
					"from.class_id" => CL_PROJECT
				));
			}
			else
			{
				$project_conns = $project_conns->find(array(
					"to" => $arr["request"]["org_id"],
					"reltype" => 10,
					"from.class_id" => CL_PROJECT
				));
			}
		}
		else
		{
			// get all customers, then get all projs for those
			$i = get_instance(CL_CRM_COMPANY);
			$cust = array();
			$i->get_customers_for_company($arr["obj_inst"], &$cust);
			$c = new connection();
			$project_conns = $c->find(array(
				"to" => $cust,
				"reltype" => array(10,9),
				"from.class_id" => CL_PROJECT
			));
		}

		if(count($project_conns) == 0)
		{
			return 0;
		}
		
		foreach ($project_conns as $conn)
		{
			$tmp_ids[] = $conn["from"];
		}
		
		$ol = new object_list(array(
			"oid" => $tmp_ids,
		));
		if ($arr["request"]["search_all_proj"] && $ol->count())
		{
			$filt = $this->_get_my_proj_search_filt($arr["request"], $ol->ids(), "all_");
			$ol = new object_list($filt);
		}

		if ($ol->count())
		{
			$ol = new object_list(array(
				"oid" => $ol->ids(),
				"class_id" => CL_PROJECT,
				"state" => $arr["request"]["group"] == "org_projects_archive" ? PROJ_DONE : new obj_predicate_not(PROJ_DONE)
			));
		}

		$rs_by_co = array();
		$role_entry_list = new object_list(array(
			"class_id" => CL_CRM_COMPANY_ROLE_ENTRY,
			"company" => $arr["request"]["id"],
			"client" => $arr["request"]["org_id"],
			"project" => $ol->ids()
		));
		foreach($role_entry_list->arr() as $role_entry)
		{
			$rc_by_co[$role_entry->prop("client")][$role_entry->prop("project")][$role_entry->prop("person")][] = html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"unit" => $role_entry->prop("unit"),
					), 
					parse_obj_name($role_entry->prop_str("unit"))
				)
				."/".
				html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"cat" => $role_entry->prop("role")
					), 
					parse_obj_name($role_entry->prop_str("role"))
				);
		}
		
		foreach ($ol->arr() as $project)
		{
			$roles = $this->_get_role_html(array(
				"from_org" => $arr["request"]["id"],
				"to_org" => $arr["request"]["org_id"],
				"rc_by_co" => $rc_by_co,
				"to_project" => $project->id()
			));

			$table->define_data(array(
				"project_name" => html::get_change_url($project->id(), array("return_url" => get_ru()), $project->name()),
				"project_participants"	=> $this->_get_linked_names($project->connections_from(array("type" => "RELTYPE_PARTICIPANT"))),
				"project_created" => $project->created(),
				"roles" => $roles,
				"project_orderer" => $this->_get_linked_names($project->connections_from(array("type" => "RELTYPE_ORDERER"))),
				"project_impl" => $this->_get_linked_names($project->connections_from(array("type" => "RELTYPE_IMPLEMENTOR"))),
				"project_deadline" => $project->prop("deadline"),
				"oid" => $project->id()
			));
		}
	}

	function _get_offers_current_org_id($arr)
	{
		$arr["prop"]["value"] = $arr["request"]["org_id"];
	}

	function _get_org_proj_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			"name" => "new",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			'parent' => 'new',
			"text" => t("Lisa projekt teostajana"),
			'link' => html::get_new_url(
				CL_PROJECT, 
				$arr["obj_inst"]->id(), 
				array(
					"connect_impl" => $arr["obj_inst"]->id(),
					"return_url" => get_ru(),
					"connect_orderer" => $arr["request"]["org_id"],
				)
			),
		));

		$tb->add_menu_item(array(
			'parent' => 'new',
			"text" => t("Lisa projekt tellijana"),
			'link' => html::get_new_url(
				CL_PROJECT, 
				$arr["obj_inst"]->id(), 
				array(
					"connect_orderer" => $arr["obj_inst"]->id(),
					"return_url" => get_ru(),
					"connect_impl" => $arr["request"]["org_id"],
				)
			),
		));

		$tb->add_menu_item(array(
			'parent' => 'new',
			"text" => t("P&auml;eva raport"),
			'link' => html::get_new_url(
				CL_CRM_DAY_REPORT, 
				$arr["obj_inst"]->id(), 
				array(
					"alias_to" => $arr["obj_inst"]->id(),
					"reltype" => 39,
					"return_url" => get_ru()
				)
			),
		));

		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$url = $this->mk_my_orb('new',array(
			'alias_to_org' => $arr['obj_inst']->id(),
			'reltype_org' => 13,
			'class' => 'planner',
			'id' => $this->cal_id,
			'group' => 'add_event',
			'clid' => CL_TASK,
			'action' => 'change',
			'title' => t("Toimetus"),
			'parent' => $arr["obj_inst"]->id(),
			'return_url' => get_ru()
		));

		$tb->add_menu_item(array(
			'parent'=>'new',
			'text' => t('Toimetus'),
			'link' => $url
		));

		$tb->add_button(array(
			"name" => "mark_done",
			"img" => 'save.gif',
			"tooltip" => t("M&auml;rgi tehtuks"),
			"action" => "mark_proj_done"
		));

		if ($arr["request"]["group"] == "org_projects")
		{
			$tb->add_button(array(
				"name" => "search",
				"img" => "search.gif",
				"tooltip" => t("Otsi projekte"),
				"url" => aw_url_change_var(array(
					"search_all_proj" => 1,
					"category" => NULL,
					"org_id" => NULL
				))
			));
		}
	}

	function _get_linked_names($conns)
	{
		$res = array();
		foreach ($conns as $conn)
		{
			$res[] = html::href(array(
				"url" => html::get_change_url($conn->prop("to"), array("return_url" => get_ru())),
				"caption" => $conn->prop("to.name"),
			)); 
		}
		return join(", ", $res);
	}

	function _get_my_proj_search_filt($ar, $oids, $prefix = "")
	{
		$ret = array(
			"class_id" => CL_PROJECT,
			"oid" => $oids
		);

		if ($ar[$prefix."proj_search_cust"] != "")
		{
			$ret[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_PROJECT.RELTYPE_IMPLEMENTOR.name" => "%".$ar[$prefix."proj_search_cust"]."%",
					"CL_PROJECT.RELTYPE_ORDERER.name" => "%".$ar[$prefix."proj_search_cust"]."%",
				)
			));
		}

		if ($ar[$prefix."proj_search_part"] != "")
		{
			$ret["CL_PROJECT.RELTYPE_PARTICIPANT.name"] = "%".$ar[$prefix."proj_search_part"]."%";
		}

		if ($ar[$prefix."proj_search_name"] != "")
		{
			$ret["name"] = "%".$ar[$prefix."proj_search_name"]."%";
		}

		if ($ar[$prefix."proj_search_code"] != "")
		{
			$ret["code"] = "%".$ar[$prefix."proj_search_code"]."%";
		}

		if ($ar[$prefix."proj_search_task_name"] != "")
		{
			$ret["CL_PROJECT.RELTYPE_TASK.name"] = "%".$ar[$prefix."proj_search_task_name"]."%";
		}

		$ar[$prefix."proj_search_dl_from"] = date_edit::get_timestamp($ar[$prefix."proj_search_dl_from"]);
		$ar[$prefix."proj_search_dl_to"] = date_edit::get_timestamp($ar[$prefix."proj_search_dl_to"]);

		if ($ar[$prefix."proj_search_dl_from"] > 1 && $ar[$prefix."proj_search_dl_to"])
		{
			$ret["deadline"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $ar[$prefix."proj_search_dl_from"], $ar[$prefix."proj_search_dl_to"]);
		}
		else
		if ($ar[$prefix."proj_search_dl_from"] > 1)
		{
			$ret["deadline"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $ar[$prefix."proj_search_dl_from"]);
		}
		else
		if ($ar[$prefix."proj_search_dl_to"] > 1)
		{
			$ret["deadline"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $ar[$prefix."proj_search_dl_to"]);
		}

		if ($ar[$prefix."proj_search_state"])
		{
			$ret["state"] = $ar[$prefix."proj_search_state"];
		}
		return $ret;
	}

	function _get_org_proj_arh_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "search",
			"img" => "search.gif",
			"tooltip" => t("Otsi projekte"),
			"url" => aw_url_change_var(array(
				"search_all_proj" => 1,
				"category" => NULL,
				"org_id" => NULL
			))
		));
	}

	function _get_first_cust_cat($o)
	{
		$ol = new object_list($o->connections_from(array(
			"type" => "RELTYPE_CATEGORY",
		)));
		$ol->sort_by(array("prop" => "ord"));
		return $ol->begin();
	}

	function _init_report_list_t(&$t)
	{
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y",
			"numeric" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "reporter",
			"caption" => t("Esitaja"),
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
			"align" => "center"
		));
	}

	function _get_report_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_report_list_t(&$t);

		if ($arr["request"]["group"] == "all_reports")
		{
			$reps = new object_list(array(
				"class_id" => CL_CRM_DAY_REPORT,
				"parent" => $arr["obj_inst"]->id(),
			));
		}
		else
		{
			$u = get_instance(CL_USER);
			$reps = new object_list(array(
				"class_id" => CL_CRM_DAY_REPORT,
				"parent" => $arr["obj_inst"]->id(),
				"reporter" => $u->get_current_person()
			));
		}

		foreach($reps->arr() as $r)
		{
			$rep = "";
			if ($this->can("view", $r->prop("reporter")))
			{
				$o = obj($r->prop("reporter"));
				$rep = html::get_change_url($o->id(), array("return_url" => get_ru()), $o->name());
			}
			$t->define_data(array(
				"date" => $r->prop("date"),
				"reporter" => $rep,
				"name" => html::get_change_url($r->id(), array("return_url" => get_ru()), $r->name())
			));
		}

		$t->set_default_sortby("date");
		$t->set_default_sorder("desc");
	}

	function _get_all_proj_search_part($arr)
	{
		if (!$arr["request"]["search_all_proj"])
		{
			return PROP_IGNORE;
		}

		if ($arr["request"]["all_proj_search_dl_from"] == "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			$v = $p->name();
		}
		else
		{
			$v = $arr["request"]["all_proj_search_part"];
		}
		$arr["prop"]["value"] = html::textbox(array(
			"name" => "all_proj_search_part",
			"value" => $v,
			"size" => 25
		))."<a href='javascript:void(0)' onClick='document.changeform.all_proj_search_part.value=\"\"'><img src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0></a>";
		return PROP_OK;
	}

	function _get_proj_search_part($arr)
	{
		if ($arr["request"]["proj_search_dl_from"] == "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			$v = $p->name();
		}
		else
		{
			$v = $arr["request"]["proj_search_part"];
		}
		$arr["prop"]["value"] = html::textbox(array(
			"name" => "proj_search_part",
			"value" => $v,
			"size" => 25
		))."<a href='javascript:void(0)' onClick='document.changeform.proj_search_part.value=\"\"'><img src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0></a>";
		return PROP_OK;
	}
}
?>