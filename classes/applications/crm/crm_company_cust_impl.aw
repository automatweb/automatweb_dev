<?php

class crm_company_cust_impl extends class_base
{
	function crm_company_cust_impl()
	{
		$this->init();
	}

	function do_projects_table_header(&$table, $data = false, $skip_sel = false)
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
				if (trim($row["project_orderer"]) != "")
				{
					foreach(explode(",", strip_tags($row["project_orderer"])) as $ord_pt)
					{
						$filt["project_orderer"][] = trim($ord_pt);
					}
				}
				if (trim($row["project_impl"]) != "")
				{
					foreach(explode(",", strip_tags($row["project_impl"])) as $ord_pt)
					{
						$filt["project_impl"][] = trim($ord_pt);
					}
				}
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
			"filter" => array_unique($filt["project_orderer"])
		));
		$table->define_field(array(
			"name" => "project_impl",
			"caption" => t("Teostaja"),
			"sortable" => 1,
			"filter" => array_unique($filt["project_impl"])
		));
		
		$table->define_field(array(
			"name" => "project_deadline",
			"caption" => t("T&auml;htaeg"),
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y"
		));
		
		if ($_GET["group"] == "org_projects_archive")
		{
			$table->define_field(array(
				"name" => "project_end",
				"caption" => t("L&otilde;pp"),
				"sortable" => 1,
				"type" => "time",
				"numeric" => 1,
				"format" => "d.m.Y"
			));
		}
		
		$table->define_field(array(
			"name" => "project_participants",
			"caption" => t("Osalejad"),
			"sortable" => 1,
			"filter" => array_unique($filt["project_participants"])
		));
		
		$table->define_field(array(
			"name" => "project_created",
			"caption" => t("Loodud"),
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i",
			"numeric" => 1
		));

		if (!$skip_sel)
		{
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
	}

	function _get_my_projects($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$i = get_instance(CL_CRM_COMPANY);
		if (!is_array($arr["prj"]))
		{
			$prj = $i->get_my_projects();
		}
		else
		{
			$prj = $arr["prj"];
		}
		
		if (!count($prj))
		{
			$conns_ol = new object_list();
		}
		else
		{
			$conns_ol = new object_list(array("oid" => $prj));
			if ($conns_ol->count())
			{
				$conns_ol = new object_list(array(
					"oid" => $conns_ol->ids(),
					"class_id" => CL_PROJECT,
					"state" => new obj_predicate_not(PROJ_DONE),
					"lang_id" => array()
				));
			}
		}
		
		if ($arr["request"]["do_proj_search"] /*&& $conns_ol->count() */)
		{
			$filt = $this->_get_my_proj_search_filt($arr["request"], $conns_ol->ids());
			$conns_ol = new object_list($filt);
		}

		foreach ($conns_ol->arr() as $project_obj)
		{
			if (is_oid($cpi = $project_obj->prop("contact_person_implementor")) && $this->can("view", $cpi))
			{
				$impl = html::get_change_url($cpi, array("return_url" => get_ru()), parse_obj_name($project_obj->prop_str("contact_person_implementor")));
			}
			else
			{
				$impl = $this->_get_linked_names($project_obj->connections_from(array("type" => "RELTYPE_IMPLEMENTOR")));
			}
			$data[] = array(
				"project_name" => html::get_change_url($project_obj->id(), array("return_url" => get_ru()), parse_obj_name($project_obj->name())),
				"project_participants"	=> $this->_get_linked_names($project_obj->connections_from(array("type" => "RELTYPE_PARTICIPANT"))),
				"project_created" => $project_obj->created(),
				"project_orderer" => $this->_get_linked_names($project_obj->connections_from(array("type" => "RELTYPE_ORDERER"))),
				"project_impl" => $impl,
				"project_deadline" => $project_obj->prop("deadline"),
				"project_end" => $project_obj->prop("end"),
				"oid" => $project_obj->id()
			);
		}
	
		$this->do_projects_table_header($table, $data, isset($arr["prj"]));
		foreach($data as $row)
		{
			$table->define_data($row);
		}
		return PROP_OK;
	}

	function _org_table_header($tf)
	{
		$tf->define_field(array(
			"name" => "name",
			"caption" => t("Kliendi nimi"),
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

		$tf->define_field(array(
			"name" => "client_manager",
			"caption" => t("Kliendihaldur"),
			"sortable" => 1,
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

		/*if (!is_oid($arr['request']['category']))
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
		}*/
		
		if ($filter)
		{
			$orglist = $this->make_keys($filter);
		}
		else
		{
			if ($arr["request"]["customer_search_submit"] != "")
			{
				$ol = new object_list($this->_get_customer_search_filter($arr["request"]));
				$orglist = $this->make_keys($ol->ids());
			}
			else
			{
				$u = get_instance(CL_USER);
				$p = obj($u->get_current_person());
				$ol = new object_list($this->_get_customer_search_filter(array("customer_search_cust_mgr" => $p->name())));
				$orglist = $this->make_keys($ol->ids());
			}
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

			$phone = "";
			if ($o->class_id() == CL_CRM_COMPANY)
			{
				$ceo = html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $o->prop("firmajuht"),
					),CL_CRM_PERSON),
					"caption" => $juht,
				));
				if (is_oid($o->prop("email_id")) && $this->can("view", $o->prop("email_id")))
				{
					$mail_obj = new object($o->prop("email_id"));
					$mail = html::href(array(
						"url" => "mailto:" . $mail_obj->prop("mail"),
						"caption" => $mail_obj->prop("mail"),
					));
				};
				if (is_oid($o->prop("url_id")))
				{
					$url = html::href(array(
						"url" => $o->prop_str("url_id"),
						"caption" => $o->prop_str("url_id"),
					));
				}
				$phone = $o->prop_str("phone_id");
			}
			else
			{
				$ceo = $o->name();
				if (is_oid($o->prop("email")) && $this->can("view", $o->prop("email")))
				{
					$mail_obj = new object($o->prop("email"));
					$mail = html::href(array(
						"url" => "mailto:" . $mail_obj->prop("mail"),
						"caption" => $mail_obj->prop("mail"),
					));
				};
				if ($this->can("view", $o->prop("url")))
				{
					$urlo = obj($o->prop("url"));
					$ru = $urlo->prop_str("url");
					if (substr($ru, 0, 4) != "http")
					{
						$ru = "http://".$ru;
					}
					$url = html::href(array(
						"url" => $ru,
						"caption" => $urlo->prop_str("url"),
					));
				}
				if ($this->can("view", $o->prop("phone")))
				{
					$urlo = obj($o->prop("phone"));
					$phone = $urlo->name();
				}
			}

			$tf->define_data(array(
				"id" => $o->id(),
				"name" => html::get_change_url($o->id(), array("return_url" => get_ru()), $o->name()." ".$vorm),
				"reg_nr" => $o->prop("reg_nr"),
				"pohitegevus" => $o->prop_str("pohitegevus"),
				"corpform" => $vorm,
				"address" => $o->class_id() == CL_CRM_COMPANY ? $o->prop_str("contact") : $o->prop_str("address"),
				"ceo" => $ceo,
				"phone" => $phone,
				"url" => $url,
				"email" => $mail,
				'rollid' => $roles,
				'client_manager' => html::obj_change_url($o->prop("client_manager")),
			));
		}

		$tf->set_default_sortby("name");
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
	
	function _get_my_customers_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$alias_to = $arr["obj_inst"]->id();
		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Klient'),
			'link' => $this->mk_my_orb('new',array(
					'parent' => $arr['obj_inst']->id(),
					'alias_to' => $alias_to,
					'reltype' => 22, // crm_company.CUSTOMER,
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
					'reltype' => 22, // crm_company.CUSTOMER,
					'return_url' => get_ru()
				),
				CL_CRM_PERSON
			)
		));
		$tb->add_sub_menu(array(
			"parent" => "add_item",
			"name" => "add_proj",
			"text" => t("Projekt")
		));
		$tb->add_menu_item(array(
			'parent'=>'add_proj',
			'text' => t('Teostajana'),
			"action" => "add_proj_to_co_as_impl"
		));
		$tb->add_menu_item(array(
			'parent'=>'add_proj',
			'text' => t('Tellijana'),
			"action" => "add_proj_to_co_as_ord"
		));
		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Toimetus'),
			"action" => "add_task_to_co"
		));
		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Arve'),
			"action" => "go_to_create_bill"
		));

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
		$i = get_instance(CL_CRM_COMPANY);
		$this->_get_customer($arr);
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
		if (!$arr["request"]["search_all_proj"])
		{
			return PROP_IGNORE;
		}
		return $this->_get_offers_listing_tree($arr);
	}

	function _get_projects_listing_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$this->do_projects_table_header(&$table);
		
		if ($arr["request"]["search_all_proj"])
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
		if (!$arr["request"]["search_all_proj"] && $ol->count())
		{
			if (!$arr["request"]["aps_sbt"])
			{
				$u = get_instance(CL_USER);
				$p = obj($u->get_current_person());
				$arr["request"]["all_proj_search_part"] = $p->name();
				$arr["request"]["all_proj_search_state"] = PROJ_DONE;
			}
			$filt = $this->_get_my_proj_search_filt($arr["request"], $ol->ids(), "all_");
			$ol = new object_list($filt);
		}
		else
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
				"project_name" => html::obj_change_url($project),
				"project_participants"	=> $this->_get_linked_names($project->connections_from(array("type" => "RELTYPE_PARTICIPANT"))),
				"project_created" => $project->created(),
				"roles" => $roles,
				"project_orderer" => $this->_get_linked_names($project->connections_from(array("type" => "RELTYPE_ORDERER"))),
				"project_impl" => $this->_get_linked_names($project->connections_from(array("type" => "RELTYPE_IMPLEMENTOR"))),
				"project_deadline" => $project->prop("deadline"),
				"project_end" => $project->prop("end"),
				"oid" => $project->id()
			));
		}
		$table->set_default_sortby("project_end");
		$table->set_default_sorder("desc");
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
			"text" => t("Projekt teostajana"),
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
			"text" => t("Projekt tellijana"),
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
			'action' => "add_task_to_proj"
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

		$tb->add_button(array(
			"name" => "delete",
			"img" => 'delete.gif',
			"tooltip" => t("Kustuta"),
			"confirm" => t("Oled kindel et soovid valitud projekte kustutada?"),
			"action" => "delete_projs"
		));
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
			"lang_id" => array(),
			"site_id" => array(),
			//"oid" => $oids
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
			$ret["CL_PROJECT.RELTYPE_PARTICIPANT.name"] = map("%%%s%%", explode(",", $ar[$prefix."proj_search_part"]));
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

		if ($ar[$prefix."proj_search_dl_from"] > 1 && $ar[$prefix."proj_search_dl_to"] > 1)
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


		$ar[$prefix."proj_search_end_from"] = date_edit::get_timestamp($ar[$prefix."proj_search_end_from"]);
		$ar[$prefix."proj_search_end_to"] = date_edit::get_timestamp($ar[$prefix."proj_search_end_to"]);

		if ($ar[$prefix."proj_search_end_from"] > 1 && $ar[$prefix."proj_search_end_to"] > 1)
		{
			$ret["end"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $ar[$prefix."proj_search_end_from"], $ar[$prefix."proj_search_end_to"]);
		}
		else
		if ($ar[$prefix."proj_search_end_from"] > 1)
		{
			$ret["end"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $ar[$prefix."proj_search_end_from"]);
		}
		else
		if ($ar[$prefix."proj_search_end_to"] > 1)
		{
			$ret["end"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $ar[$prefix."proj_search_end_to"]);
		}

		if ($ar[$prefix."proj_search_state"])
		{
			$ret["state"] = $ar[$prefix."proj_search_state"];
		}

		if ($ar[$prefix."proj_search_contact_person"])
		{
			$ret["CL_PROJECT.contact_person_implementor.name"] = "%".$ar[$prefix."proj_search_contact_person"]."%";
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
		if ($arr["request"]["search_all_proj"])
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

	function _get_customer_search_cust_mgr($arr)
	{
		if ($arr["request"]["customer_search_submit"] == "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			$v = $p->name();
		}
		else
		{
			$v = $arr["request"]["customer_search_cust_mgr"];
		}
		$arr["prop"]["value"] = html::textbox(array(
			"name" => "customer_search_cust_mgr",
			"value" => $v,
			"size" => 25
		))."<a href='javascript:void(0)' onClick='document.changeform.customer_search_cust_mgr.value=\"\"'><img src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0></a>";
		return PROP_OK;
	}

	function _get_customer_search_filter($r, $oids)
	{
		$ret = array(
			"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
		);

		if ($r["customer_search_name"] != "")
		{
			$ret["name"] = "%".$r["customer_search_name"]."%";
		}

		if ($r["customer_search_reg"] != "")
		{
			$ret["reg_nr"] = "%".$r["customer_search_reg"]."%";
		}

		if ($r["customer_search_worker"] != "")
		{
			$ret["CL_CRM_COMPANY.RELTYPE_WORKERS.name"] = "%".$r["customer_search_worker"]."%";
		}

		if ($r["customer_search_address"] != "")
		{
			$ret["CL_CRM_COMPANY.contact.name"] = "%".$r["customer_search_address"]."%";
		}

		if ($r["customer_search_city"] != "")
		{
			$ret["CL_CRM_COMPANY.contact.linn.name"] = "%".$r["customer_search_city"]."%";
		}

		if ($r["customer_search_county"] != "")
		{
			$ret["CL_CRM_COMPANY.contact.maakond.name"] = "%".$r["customer_search_county"]."%";
		}

		if ($r["customer_search_ev"] != "")
		{
			$ret["CL_CRM_COMPANY.ettevotlusvorm.name"] = "%".$r["customer_search_ev"]."%";
		}

		if (empty($r["customer_search_is_co"]["is_co"]) && !empty($r["customer_search_is_co"]["is_person"]))
		{
			$ret["class_id"] = CL_CRM_PERSON;
			$ret["is_customer"] = 1;
		}
		else
		if (!empty($r["customer_search_is_co"]["is_co"]) && empty($r["customer_search_is_co"]["is_person"]))
		{
			$ret["class_id"] = CL_CRM_COMPANY;
		}
		else
		{
			$ret[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.is_customer" => 1,
					"CL_CRM_COMPANY.reg_nr" => "%" // this is here to match all companies, otherwise we'd just get persons
				)
			));
		}

		if ($r["customer_search_cust_mgr"] != "")
		{
			$ret[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_COMPANY.client_manager.name" => map("%%%s%%", explode(",", $r["customer_search_cust_mgr"])),
					"CL_CRM_PERSON.client_manager.name" => map("%%%s%%", explode(",", $r["customer_search_cust_mgr"]))
				)
			));
		}

		return $ret;
	}

	function _get_customer_search_is_co($arr)
	{
		$arr["prop"]["options"] = array(
			"is_co" => t("Organisatsioon"),
			"is_person" => t("Eraisik")
		);
		if (empty($arr["request"]["customer_search_submit"]))
		{
			$arr["prop"]["value"] = array("is_co" => "is_co", "is_person" => "is_person");
		}
		else
		{
			$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
		}
	}
}
?>
