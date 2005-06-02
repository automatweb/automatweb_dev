<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_offer.aw,v 1.34 2005/06/02 11:47:29 kristo Exp $
// pakkumine.aw - Pakkumine 
/*

@classinfo syslog_type=ST_CRM_OFFER relationmgr=yes no_status=1

@tableinfo planner index=id master_table=objects master_index=brother_of
@tableinfo aw_crm_offer index=aw_oid master_table=objects master_index=oid

@default table=objects

@default group=general

	@property orderer type=select table=aw_crm_offer datatype=int
	@caption Tellija

	@property start1 type=datetime_select field=start table=planner
	@caption Algus

	@property preformer type=relpicker reltype=RELTYPE_PREFORMER table=aw_crm_offer 
	@caption Täitja

	@property salesman type=select table=aw_crm_offer datatype=int
	@caption Pakkumise koostaja

	@property offer_status type=select table=aw_crm_offer datatype=int
	@caption Staatus

	@property content type=textarea cols=60 rows=20 table=planner field=description
	@caption Sisu

	@property prev_status type=hidden store=no

	@property sum type=textbox table=aw_crm_offer size=7 datatype=int
	@caption Hind (ilma KM)

	@property end type=datetime_select field=end table=planner
	@caption L&otilde;pp

	@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
	@caption Tehtud

	@default method=serialize
-------- Sisu ----
@default group=content

	@layout vbox_others type=hbox group=content width=20%:80%

	@layout vbox_tree type=vbox group=content parent=vbox_others
	@layout vbox_tbl type=vbox group=content parent=vbox_others 

	@property content_toolbar type=toolbar no_caption=1 store=no 

	@property content_tree type=treeview no_caption=1 store=no parent=vbox_tree
	@caption Puu

	@property content_list type=table store=no no_caption=1 parent=vbox_tbl
	@caption Pakkumised

-------- Kalendrid ----

	@property calendar_selector type=calendar_selector store=no group=calendars
	@caption Kalendrid

-------- Projektid -----

	@property project_selector type=project_selector store=no group=projects
	@caption Projektid



-------PAKKUMISE AJALUGU---------
@default group=history

	@property offer_history type=table no_caption=1 store=no group=history


@default group=offer

	@property offer type=text no_caption=1

@groupinfo content caption="Sisu" submit=no
@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid
@groupinfo products_show caption=Tooted submit=no
@groupinfo history caption=Ajalugu submit=no
@groupinfo offer caption="Pakkumine" submit=no

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype ORDERER value=2 clid=CL_CRM_COMPANY
@caption Tellija

@reltype PREFORMER value=3 clid=CL_CRM_COMPANY
@caption Täitja

@reltype SALESMAN value=4 clid=CL_CRM_PERSON
@caption Pakkumise koostaja

@reltype PRODUCT value=5 clid=CL_SHOP_PRODUCT
@caption Toode

@reltype OFFER_MGR value=7 clid=CL_CRM_OFFER_MGR
@caption Pakkumiste haldus
*/

/*
CREATE TABLE `aw_crm_offer` (
`aw_oid` INT UNSIGNED NOT NULL ,
`orderer` INT UNSIGNED NOT NULL ,
`preformer` INT UNSIGNED NOT NULL,
`salesman` INT UNSIGNED NOT NULL ,
`sum` INT NOT NULL ,
`offer_status` TINYINT NOT NULL ,
PRIMARY KEY ( `aw_oid` )
);
*/


define("OFFER_ON_PROCESS",1);
define("OFFER_IS_SENT",2);
define("OFFER_IS_PREFORMED",3);
define("OFFER_IS_DECLINED",4);
define("OFFER_IS_POSITIVE",4);
class crm_offer extends class_base
{		
	var $u_i;
	var $statuses;
	function crm_offer()
	{
		$this->init(array(
			"clid" => CL_CRM_OFFER,
			"tpldir" => "crm/crm_offer"
		));
		$this->u_i = get_instance(CL_USER);
		$this->statuses =  array(
			t("Koostamisel"), 
			t("Saadetud"), 
			t("Esitletud"), 
			t("Tagasilükatud"), 
			t("Positiivelt lõppenud")
		);		

		$this->addable = array(
			CL_CRM_OFFER_CHAPTER => t("Peat&uuml;kk"), 
			CL_CRM_OFFER_GOAL => t("Eesm&auml;rk"), 
			CL_CRM_OFFER_PAYMENT_TERMS => t("Maksetingimused"),
			CL_CRM_OFFER_PRODUCTS_LIST => t("Toodete nimekiri"),
			CL_CRM_OFFER_COMPARE_TABLE => t("V&otilde;rdlustabel"),
			CL_PROJECT => t("Projekt")
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "start1":
			//	return PROP_IGNORE;
			break;
		
			case "orderer":
				$my_org = false;

				if(!($arr["new"] == 1))
				{
					$id = $arr["obj_inst"]->prop("preformer");
					if (is_oid($id) && $this->can("view", $id))
					{
						$my_org = obj($id);
					}
				}

				if (!$my_org)
				{
					$my_org = $this->u_i->get_current_company();
					$my_org = &obj($my_org);
				}
				$data = array();
				if($my_org)
				{
					$org_inst = get_instance(CL_CRM_COMPANY);
					$org_inst->get_customers_for_company($my_org, &$data);
				
				}
				foreach ($data as $key)
				{
					$obj = &obj($key);
					$options[$key] = $obj->name();
				}
				
				$prop["options"] = $options;
				
						
				if($arr["new"] == 1)
				{
					$prop["value"] = $arr["request"]["alias_to_org"];
				}
				elseif($arr["obj_inst"]->prop("orderer"))
				{
					$prop["value"] = $arr["obj_inst"]->prop("orderer");
				}
			break;
			
			case "offer_history":
				$this->do_offer_history($arr);
			break;
			
			case "salesman":
				$my_company = $this->u_i->get_current_company();
				$org = &obj($my_company);
				$workers = $org->connections_from(array("type" => "RELTYPE_WORKERS"));
				
				foreach ($workers as $worker)
				{
					$options[$worker->prop("to")] = $worker->prop("to.name");
				}
				$prop["options"] = $options;
				
				if(!$prop["value"])
				{
					$person_id = $this->u_i->get_current_person();
					$person_obj = &obj($person_id);
					$prop["value"] = $person_obj->id();
				}
				break;
			
			case "offer_status":
				$prop["options"] = $this->statuses;
				break;
			
			case "prev_status":
				if(is_object($arr["obj_inst"]))
				{
					$prop["value"] = $arr["obj_inst"]->prop("offer_status");
				}
				break;

			case "content_toolbar":
				$this->_content_toolbar($arr);
				break;

			case "content_tree":
				$this->_content_tree($arr);
				break;

			case "content_list":
				$this->_content_list($arr);
				break;

			case "offer";
				$prop["value"] = $this->generate_offer($arr["obj_inst"]);
				break;

			case "is_done":
				return PROP_IGNORE;

		};
		return $retval;
	}
	
	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "salesman":
				if($data["value"])
				{
					$arr["obj_inst"]->connect(array(
						"to" => $data["value"],
						"reltype" => "RELTYPE_SALESMAN",
					));
				}
				break;
			
			case "orderer":
				if($data["value"])
				{
					$arr["obj_inst"]->connect(array(
						"to" => $data["value"],
						"reltype" => "RELTYPE_ORDERER",
					));
				}
				break;
		};
		return $retval;
	}
	
	/**
		Returns offers ids made for company
	**/
	function get_offers_for_company($orderer_id, $preformer_id = false)
	{
		if($orderer_id)
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_OFFER,
				"orderer" => $orderer_id,
				"preformer" => $preformer_id,
			));
			return $ol;
		}
	}
	
	function callback_pre_save($arr)
	{
		if($arr["request"]["offer_status"] == 3 || $arr["request"]["offer_status"] == 4)
		{
			$arr["obj_inst"]->set_prop("is_done", OBJ_IS_DONE);
		}
		else 
		{
			$arr["obj_inst"]->set_prop("is_done", 0);
		}
		//If offer status has been changed then lets write to log about it.
		if($arr["request"]["prev_status"] != $arr["request"]["offer_status"])
		{
			$status_data = $arr["obj_inst"]->meta("statuslog");
			$status_data[time()] = array(
				"prev_status" => $arr["request"]["prev_status"],
				"new_status" => $arr["request"]["offer_status"], 
				"uid" => aw_global_get("uid"),
			);
			$arr["obj_inst"]->set_meta("statuslog", $status_data);
		}
	}
	
	function callback_post_save($arr)
	{
		if($arr["new"]==1)
		{
			$users = get_instance("users");
			$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
			$conns = $user->connections_to(array(
				"type" => 8, //RELTYPE_CALENDAR_OWNERSHIP
			));
			if(count($conns))
			{
				$conn = current($conns);
				$calender = &obj($conn->prop("from"));
				$parent = $calender->prop("event_folder");
				if($parent)
				{
					$arr["obj_inst"]->create_brother($parent);
				}
			}
		}
	}
	
	function do_offer_history(&$arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "prev",
			"caption" => t("Algstaatus"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "next",
			"caption" => t("Lõppstaatus"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "time",
			"caption" => t("Muutuse aeg"),
			"sortable" => "1",
		));
	
		$table->define_field(array(
			"name" => "who",
			"caption" => t("Muutja"),
			"sortable" => "1",
		));
		
		$user = get_instance("users");
		if(!is_array($arr["obj_inst"]->meta("statuslog")))
		{
			return;
		}
		foreach ($arr["obj_inst"]->meta("statuslog") as $key => $logitem)
		{
			$uid = $user->get_oid_for_uid($logitem["uid"]);
			$user_obj = &obj($uid);
			$person_id = $this->u_i->get_person_for_user($user_obj);
			$person_obj = &obj($person_id);
			

			$table->define_data(array(
				"prev" => $this->statuses[$logitem['prev_status']],
				"next" => $this->statuses[$logitem['new_status']],
				"who" => $person_obj->name(),
				"time" => get_lc_date($key)." - kell: " .date("G:i", $key),
			));
		}
	}
	
	function _content_toolbar($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$t->add_menu_button(array(
			"name" => "new",
			"tooltip" => t("Lisa")
		));

		$clss = aw_ini_get("classes");

		foreach($this->addable as $clid => $tx)
		{
			$t->add_menu_item(array(
				"parent" => "new",
				"text" => $tx,
				"link" => html::get_new_url($clid, $arr["request"]["tf"] ? $arr["request"]["tf"] : $arr["obj_inst"]->id(), array("return_url" => get_ru()))
			));
		}

		$omgr = get_instance(CL_CRM_OFFER_MGR);
		$mgr_o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OFFER_MGR");
		$typicals = $omgr->get_typical_components($mgr_o);

		if (count($typicals))
		{
			$t->add_sub_menu(array(
				"parent" => "new",
				"name" => "new_tp",
				"text" => t("T&uuml;&uuml;pkomponendid")
			));

			foreach($typicals as $t_id => $t_nm)
			{
				$t->add_menu_item(array(
					"parent" => "new_tp",
					"text" => $t_nm,
					"link" => $this->mk_my_orb("add_based_on_typical", array(
						"id" => $arr["obj_inst"]->id(),
						"parent" => $arr["request"]["tf"],
						"based_on" => $t_id,
						"ru" => get_ru()
					))
				));
			}
		}		

		$t->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"action" => "save_cl",
			"tooltip" => t("Salvesta"),
		));

		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "del_parts",
			"tooltip" => t("Kustuta valitud osad"),
			"confirm" => t("Oled kindel et soovid valitud osad kustutada?")
		));
	}

	function _content_tree($arr)
	{
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "offer_t",
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => array_keys($this->addable),
				"parent" => $arr["obj_inst"]->id(),
			)),
			"var" => "tf",
			"icon" => icons::get_icon_url(CL_MENU)
		));
	}

	/**

		@attrib name=save_cl

	**/
	function save_cl($arr)
	{
		foreach(safe_array($arr["dat"]) as $oid => $inf)
		{
			if (is_oid($oid) && $this->can("view", $oid))
			{
				$o = obj($oid);
				if ($o->ord() != $inf["ord"])
				{
					$o->set_ord($inf["ord"]);
					$o->save();
				}
			}
		}
		return $arr["post_ru"];
	}

	function _cb_cl_ord($arr)
	{
		return html::textbox(array(
			"name" => "dat[".$arr["oid"]."][ord]",
			"value" => $arr["ord"],
			"size" => 5
		));
	}

	function _init_content_list_t(&$t)
	{	
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center",
			"callback" => array(&$this, "_cb_cl_ord"),
			"callb_pass_row" => 1
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "typical",
			"align" => "center"
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _content_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_content_list_t($t);

		$omgr = get_instance(CL_CRM_OFFER_MGR);
		$mgr_o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OFFER_MGR");
		$typicals = $omgr->get_typical_components($mgr_o);

		$clss = aw_ini_get("classes");

		$ol = new object_list(array(
			"parent" => $arr["request"]["tf"] ? $arr["request"]["tf"] : $arr["obj_inst"]->id(),
			"class_id" => array_keys($this->addable)
		));
		foreach($ol->arr() as $o)
		{
			if (isset($typicals[$o->id()]))
			{
				$typical = html::href(array(
					"url" => $this->mk_my_orb("remove_from_typical_component_list", array(
						"id" => $arr["obj_inst"]->id(), 
						"co" => $o->id(), 
						"ru" => get_ru()
					)),
					"caption" => t("Eemalda t&uuml;&uuml;pkomonentide nimekirjast")
				));
			}
			else
			{
				$typical = html::href(array(
					"url" => $this->mk_my_orb("add_to_typical_component_list", array(
						"id" => $arr["obj_inst"]->id(), 
						"co" => $o->id(), 
						"ru" => get_ru()
					)),
					"caption" => t("Tee t&uuml;&uuml;pkomponendiks")
				));
			}
			$t->define_data(array(
				"ord" => $o->ord(),
				"name" => parse_obj_name($o->name()),
				"class_id" => $clss[$o->class_id()]["name"],
				"change" => html::get_change_url($o->id(), array("return_url" => get_ru()), parse_obj_name($o->name())),
				"typical" => $typical,
				"oid" => $o->id()
			));
		}
		$t->set_default_sortby("ord");
		$t->sort_by();
	}

	/**

		@attrib name=del_parts

	**/
	function del_parts($arr)
	{
		if (count(safe_array($arr["sel"])))
		{
			$ol = new object_list(array(
				"oid" => $arr["sel"]
			));
			$ol->delete();
		}
		return $arr["post_ru"];
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _get_wh($o)
	{
		$mgr = $o->get_first_obj_by_reltype("RELTYPE_OFFER_MGR");
		if ($mgr)
		{
			$wh = $mgr->get_first_obj_by_reltype("RELTYPE_WAREHOUSE");
			if ($wh)
			{
				return $wh;
			}
		}
	}

	function generate_offer($o)
	{
		$this->read_template("offer_html.tpl");

		$html = "";

		// get offer subobjects
		$ot = new object_tree(array(
			"parent" => $o->id(),
			"class_id" => array_keys($this->addable),
			"sort_by" => "objects.jrk"
		));

		// go over tree and generate html
		$list = $ot->to_list();
		foreach($list->arr() as $item)
		{
			$item_i = $item->instance();
			$html .= $item_i->generate_html($o, $item);
		}
		
		$orderer = "";
		if (is_oid($o->prop("orderer")) && $this->can("view", $o->prop("orderer")))
		{
			$orderer_o = obj($o->prop("orderer"));
			$orderer = $orderer_o->name();
		}
		$implementor = "";
		$imp_o = $o->get_first_obj_by_reltype("RELTYPE_PREFORMER");
		if (is_object($imp_o))
		{
			$implementor = $imp_o->name();
		}

		$lg = "";
		if (($lg = $imp_o->prop("logo")))
		{
			$lg = html::img(array(
				"url" => $lg
			));
		}
		$this->vars(array(
			"content" => $html,
			"name" => $o->name(),
			"orderer" => $orderer,
			"implementor" => $implementor,
			"date" => locale::get_lc_date(date(), LC_DATE_FORMAT_LONG),
			"logo" => $lg
		));

		return $this->parse();
	}

	/**

		@attrib name=add_to_typical_component_list

		@param id required type=int acl=view
		@param co required type=int acl=view
		@param ru required

	**/
	function add_to_typical_component_list($arr)
	{
		// get manager
		$o = obj($arr["id"]);
		$mgr = $o->get_first_obj_by_reltype("RELTYPE_OFFER_MGR");
		// connect to obj
		if (!$mgr->is_connected_to(array("to" => $arr["co"], "type" => "RELTYPE_TYPICAL_COMPONENT")))
		{
			$mgr->connect(array(
				"to" => $arr["co"],
				"reltype" => "RELTYPE_TYPICAL_COMPONENT"
			));
		}
		return $arr["ru"];
	}

	/**

		@attrib name=remove_from_typical_component_list

		@param id required type=int acl=view
		@param co required type=int acl=view
		@param ru required

	**/
	function remove_from_typical_component_list($arr)
	{
		// get manager
		$o = obj($arr["id"]);
		$mgr = $o->get_first_obj_by_reltype("RELTYPE_OFFER_MGR");

		// connect to obj
		if ($mgr->is_connected_to(array("to" => $arr["co"], "type" => "RELTYPE_TYPICAL_COMPONENT")))
		{
			$mgr->disconnect(array(
				"from" => $arr["co"],
			));
		}
		return $arr["ru"];
	}

	/**

		@attrib name=add_based_on_typical

		@param id required type=int
		@param parent optional
		@param based_on required type=int acl=view
		@param ru optional
	**/
	function add_based_on_typical($arr)
	{
		// copy object
		$mgr = get_instance(CL_CRM_OFFER_MGR);
		$new = $mgr->_copy_object(obj($arr["based_on"]), $arr["parent"] ? $arr["parent"] : $arr["id"]);
		return $arr["ru"];
	}
}
?>
