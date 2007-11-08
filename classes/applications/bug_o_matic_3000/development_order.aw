<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/development_order.aw,v 1.8 2007/11/08 12:53:58 robert Exp $
// development_order.aw - Arendustellimus 
/*

@classinfo syslog_type=ST_DEVELOPMENT_ORDER relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_dev_orders master_table=objects master_index=brother_of index=aw_oid
@default table=aw_dev_orders
@default group=general

	@layout name type=vbox closeable=1 area_caption=L&uuml;hikirjeldus

		@property name type=textbox table=objects no_caption=1 parent=name
	
	@layout settings_wrap type=vbox closeable=1 area_caption=M&auml;&auml;rangud
	@layout settings type=hbox parent=settings_wrap

		@layout settings_col1 type=vbox parent=settings
		
		@property bug_status type=select parent=settings_col1 captionside=top
		@caption Staatus

		@property bug_feedback_p type=relpicker reltype=RELTYPE_FEEDBACK_P parent=settings_col1 captionside=top field=aw_bug_feedback_p store=connect
		@caption Tagasiside kellelt

		@property bug_priority type=select parent=settings_col1 captionside=top
		@caption Prioriteet
		
		@property bug_type type=classificator store=connect reltype=RELTYPE_BUGTYPE parent=settings_col1 captionside=top
		@caption T&uuml;&uuml;p

		@layout settings_col2 type=vbox parent=settings

		@property bug_app type=select field=meta method=serialize captionside=top parent=settings_col2 table=objects
		@caption Rakendus

		@property deadline type=date_select default=-1 parent=settings_col2 captionside=top
		@caption Soovitav aeg

		@property prognosis type=date_select default=-1 parent=settings_col2 captionside=top
		@caption Prognoos

	@layout settings_col3 type=vbox parent=settings

		@property monitors type=relpicker reltype=RELTYPE_MONITOR multiple=1 size=5 store=connect parent=settings_col3 captionside=top
		@caption J&auml;lgijad	

	@layout h_split type=hbox width=50%:50%
	@layout comments type=vbox parent=h_split closeable=1 area_caption=Sisu
		
		@property reason type=textarea rows=3 cols=50 field=aw_content parent=comments captionside=top
		@caption Tellimuse eesm&auml;rk

		@property com type=textarea rows=23 cols=60 parent=comments captionside=top no_caption=1
		@caption Sisu

 		@property add_comm type=textarea rows=10 cols=60 parent=comments store=no editonly=1 captionside=top
		@caption Lisa kommentaar

	@layout data type=vbox parent=h_split closeable=1 area_caption=Andmed

		@property contactperson type=relpicker reltype=RELTYPE_CONTACT parent=data
		@caption Esindaja

		@property customer type=relpicker reltype=RELTYPE_CUSTOMER field=aw_customer parent=data
		@caption Klient
	
		@property project type=relpicker reltype=RELTYPE_PROJECT field=aw_project parent=data
		@caption Projekt

		@property orderer type=relpicker reltype=ORDERER field=aw_orderer parent=data multiple=1 size=3 store=connect
		@caption Tellija

		@property orderer_co type=relpicker reltype=ORDERER_CO field=aw_orderer_co parent=data
		@caption Tellija organisatsioon
	
		@property orderer_unit type=relpicker reltype=UNIT field=aw_orderer_unit parent=data
		@caption Tellija &uuml;ksus
	
		@property fileupload type=releditor reltype=RELTYPE_FILE1 rel_id=first use_form=emb field=aw_f1 parent=data
		@caption Fail1
	
		@property fileupload2 type=releditor reltype=RELTYPE_FILE2 rel_id=first use_form=emb field=aw_f2 parent=data
		@caption Fail2
	
		@property fileupload3 type=releditor reltype=RELTYPE_FILE3 rel_id=first use_form=emb field=aw_f3 parent=data
		@caption Fail3

@default group=reqs

	@property reqs_tb type=toolbar no_caption=1 store=no
	@property reqs_table type=table store=no no_caption=1

@default group=reqs_cart

	@property reqs_cart_tb type=toolbar no_caption=1 store=no
	@property reqs_cart_table type=table store=no no_caption=1

@default group=problems

	@property problems_tb type=toolbar no_caption=1 store=no
	@property problems_table type=table store=no no_caption=1

@default group=bugs
	
	@property bugs_tb type=toolbar no_caption=1 store=no
	@property bugs_table type=table no_caption=1 store=no

@groupinfo reqs caption="K&otilde;ik n&otilde;uded" submit=no
@groupinfo reqs_cart caption="Tellimuste korv" submit=no
@groupinfo problems caption="Probleemid"
@groupinfo bugs caption="&Uuml;lesanded" submit=no

@reltype CUSTOMER value=1 clid=CL_CRM_COMPANY
@caption Klient

@reltype PROJECT value=2 clid=CL_PROJECT
@caption Projekt

@reltype FILE1 value=3 clid=CL_FILE
@caption Fail1

@reltype FILE2 value=4 clid=CL_FILE
@caption Fail2

@reltype FILE3 value=5 clid=CL_FILE
@caption Fail3

@reltype ORDERER_CO value=11 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype UNIT value=12 clid=CL_CRM_SECTION
@caption &Uuml;ksus

@reltype REQ value=13 clid=CL_PROCUREMENT_REQUIREMENT
@caption N&otilde;ue

@reltype PROBLEM value=14 clid=CL_CUSTOMER_PROBLEM_TICKET
@caption Probleem

@reltype MAIN_BUG value=15 clid=CL_BUG
@caption Alus&uuml;lesanne

@reltype MONITOR value=16 clid=CL_CRM_PERSON
@caption J&auml;lgija

@reltype BUGTYPE value=17 clid=CL_META
@caption Tellimuse t&uuml;&uuml;p

@reltype CONTACT value=18 clid=CL_CRM_PERSON
@caption Esindaja

@reltype COMMENT value=19 clid=CL_BUG_COMMENT
@caption Kommentaar

@reltype ORDERER value=20 clid=CL_CRM_PERSON
@caption Tellija

@reltype BUG value=21 clid=CL_BUG
@caption &Uuml;lesanne

@reltype FEEDBACK_P value=22 clid=CL_CRM_PERSON
@caption Tagasiside isik
*/

class development_order extends class_base
{
	function development_order()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/development_order",
			"clid" => CL_DEVELOPMENT_ORDER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "bug_feedback_p":
				if ($arr["obj_inst"]->prop("bug_status") != 10)
				{
					return PROP_IGNORE;
				}
				break;

			case "orderer":
				$u = get_instance(CL_USER);
				if($arr["new"])
				{
					$cur = obj($u->get_current_person());
				}
				else
				{
					$cur = $u->get_person_for_uid($arr["obj_inst"]->createdby());
				}
				$sections = $cur->connections_from(array(
					"class_id" => CL_CRM_SECTION,
					"type" => "RELTYPE_SECTION"
				));
				$ppl = array();
				foreach($sections as $s)
				{
					$sc = obj($s->conn["to"]);
					$profs = $sc->connections_from(array(
						"class_id" => CL_CRM_PROFESSION,
						"type" => "RELTYPE_PROFESSIONS"
					));
					foreach($profs as $p)
					{
						$professions[$p->conn["to"]] = $p->conn["to"];
					}
				}
				$c = new connection();
				$people = $c->find(array(
					"from.class_id" => CL_CRM_PERSON,
					"type" => "RELTYPE_RANK",
					"to" => $professions
				));
				foreach($people as $person)
				{
					$ob = obj($person["from"]);
					if(!$highest)
					{
						$highest = $ob;
						
					}
					elseif($ob->prop("jrk") > $highest->prop("jrk"))
					{
						$highest = $ob;
					}
					$ppl[$ob->id()] = $ob->name();
				}
				$prop["options"] = array("" => t("--vali--"));
				if($prop["value"])
				{
					foreach($prop["value"] as $val)
					{
						$cur = obj($val);
						if(!strlen(array_search($cur->id(),$ppl)))
						{
							$prop["options"] += array($cur->id() => $cur->name());
						}
					}
				}
				else
				{
					$prop["value"] = array($highest->id() => $highest->id());
				}
				$prop["options"] += $ppl;
				break;

			case "monitors":
				if ($arr["new"] || true)
				{
					foreach($this->parent_options[$prop["name"]] as $key => $val)
					{
						$key_o = obj($key);
						if ($key_o->class_id() == CL_CRM_PERSON)
						{
							$tmp[$key] = $val;
						}
					}
					// also, the current person
					$u = get_instance(CL_USER);
					$p = obj($u->get_current_person());
					$tmp[$p->id()] = $p->name();

					if ($prop["multiple"] == 1 && $arr["new"])
					{
					//	$prop["value"] = $this->make_keys(array_keys($tmp));
						$prop["value"] = array($p->id(), $p->id());
					}

					// find tracker for the bug and get people list from that
					$po = obj($arr["request"]["parent"] ? $arr["request"]["parent"] : $arr["request"]["id"]);
					$pt = $po->path();
					foreach($pt as $pi)
					{
						if ($pi->class_id() == CL_BUG_TRACKER)
						{
							$bt = $pi->instance();
							foreach($bt->get_people_list($pi) as $pid => $pnm)
							{
								$tmp[$pid] = $pnm;
							}
						}
					}
					$prop["options"] = array("" => t("--vali--")) + $tmp;
				}
				if ($this->can("view", $prop["value"]) && !isset($prop["options"][$prop["value"]]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}

				if (is_array($prop["value"]))
				{
					foreach($prop["value"] as $val)
					{
						if ($this->can("view", $val))
						{
							$tmp = obj($val);
							$prop["options"][$tmp->id()] = $tmp->name();
						}
					}
				}
				break;

			case "prognosis":
				if(!$prop["value"])
				{
					$prop["value"] = time();
				}
				break;

			case "contactperson":
				if(!$prop["value"])
				{
					$prop["value"] = $arr["obj_inst"]->createdby();
				}
				break;

			case "com":
				if (!$arr["new"])
				{
					$b = get_instance(CL_BUG);
					$prop["value"] = "<br>".$b->_get_comment_list($arr["obj_inst"], "asc", true, 0)."<br>";
					$prop["type"] = "text";
				}
				break;

			case "bug_status":
				$prop["options"] = $this->get_status_list();
				break;

			case "bug_app":
				$ol = new object_list(array(
					"parent" => $parent,
					"class_id" => array(CL_BUG_APP_TYPE)
				));
				$options = array(0=>" ");
				foreach($ol->list as $oid)
				{
					$o = obj($oid);
					$options[$oid] = $o->name();
				}
				$prop["options"] = $options;
				break;
			
			case "bug_priority":
				$b = get_instance(CL_BUG);
				$prop["options"] = $b->get_priority_list();
				break;

			case "orderer_co":
				if ($arr["new"])
				{
					$co = get_current_company();
					$prop["options"] = array("" => t("--vali--"), $co->id() => $co->name());
					$prop["value"] = $co->id();
				}
				else
				{
					$co = get_current_company();
					$prop["options"][$co->id()] = $co->name();
				}
				break;

			case "orderer_unit":
				$co = get_current_company();
				$co_i = $co->instance();
				$sects = $co_i->get_all_org_sections($co);
				$prop["options"] = array("" => t("--vali--"));
				if (count($sects))
				{
					$ol = new object_list(array("oid" => $sects));
					$prop["options"] += $ol->names();
				}
				$p = get_current_person();
				if ($arr["new"])
				{
					$prop["value"] = $p->prop("org_section");
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
			case "bug_feedback_p":
				if ($arr["obj_inst"]->prop("bug_status") != 10)
				{
					return PROP_IGNORE;
				}

				if ($this->_set_feedback)
				{
					$prop["value"] = $this->_set_feedback;
				}

				$nv = "";
				if ($this->can("view", $prop["value"]))
				{
					$nvo = obj($prop["value"]);
					$nv = $nvo->name();
				}
				$old = $arr["obj_inst"]->prop_str($prop["name"]);
				if ($old != $nv && !$arr["new"])
				{
					$com = sprintf(t("Tagaiside kellelt muudeti %s => %s"), $old, $nv);
					$this->add_comments[] = $com;
				}
				break;

			case "add_comm":
				if (trim($prop["value"]) != "" && !$arr["new"])
				{
					$this->add_comments[] = $prop["value"];
				}
				break;

			case "bug_priority":
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"] && !$arr["new"])
				{
					$com = sprintf(t("Prioriteet muudeti %s => %s"), $old, $prop["value"]);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;

			case "bug_status":
				$this->_ac_old_state = $arr["obj_inst"]->prop("bug_status");
				$this->_ac_new_state = $prop["value"];
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"] && !$arr["new"])
				{
					$statuses = $this->get_status_list();
					$com = sprintf(t("Staatus muudeti %s => %s"), $statuses[$old], $statuses[$prop["value"]]);
					$this->add_comments[] = $com;
				}
				if ($prop["value"] == 10 && $this->_ac_old_state != 10)
				{
					$bug = $arr["obj_inst"];
					$u = get_instance(CL_USER);
					if(!$arr["new"])
					{
						// set the creator as the feedback from person
						$p = $u->get_person_for_uid($bug->createdby());
					}
					else
					{
						$p = obj($u->get_current_person());
					}
					$bug->set_prop("bug_feedback_p", $p->id());
					$this->_set_feedback = $p->id();
				}
				break;
		}
		return $retval;
	}	
	
	function _get_bugs_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_BUG), $arr["obj_inst"]->id(), '',array());
		$tb->add_search_button(array(
			"pn" => "add_bug",
			"multiple" => 1,
			"clid" => CL_BUG
		));
		$tb->add_delete_button();
	}

	function _init_bugs_table(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t(""),
		));
		$t->define_field(array(
			"name" => "id",
			"caption" => t("Id"),
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));
		$bugi = get_instance(CL_BUG);
		$t->define_field(array(
			"name" => "bug_status",
			"caption" => t("Staatus"),
			"sortable" => 1,
//			"callback" => array(&$this, "show_status"),
//			"callb_pass_row" => 1,
			"filter" => $bugi->get_status_list()
		));
		$t->define_field(array(
			"name" => "who",
			"caption" => t("Kellele"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "bug_priority",
			"caption" => t("Prioriteet"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this, "show_priority"),
			"callb_pass_row" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
		));
		$t->define_field(array(
			"name" => "bug_severity",
			"caption" => t("T&otilde;sidus"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this, "show_severity"),
			"callb_pass_row" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "deadline",
			"caption" => t("T&auml;htaeg"),
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y / H:i"
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => t("K"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this,"comment_callback"),
			"callb_pass_row" => 1,
		));
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function _get_bugs_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_bugs_table($t);

		$ol = new object_list(array(
			"class_id" => CL_BUG,
			"parent" => $arr["obj_inst"]->id()
		));
		classload("core/icons");
		$u = get_instance(CL_USER);
		$us = get_instance("users");
		$bug_i = get_instance(CL_BUG);
		$states = $bug_i->get_status_list();
		$bug_list = $ol->arr();
		$user_list = array();
		foreach($bug_list as $bug)
		{
			$user_list[] = $bug->createdby();
		}
		$u2p = array();
		if (count($user_list))
		{
			$oid_list = array_flip($us->get_oid_for_uid_list($user_list));
			$c = new connection();
			$u2p_conns = $c->find(array(
				"from.class_id" => CL_USER,
				"from" => array_keys($oid_list),
				"type" => "RELTYPE_PERSON"
			));
			$person_oids = array();
			foreach($u2p_conns as $con)
			{
				$person_oids[] = $con["to"];
				$u2p[$oid_list[$con["from"]]] = $con["to"];
			}

			$person_ol = new object_list(array("class_id" => CL_CRM_PERSON, "oid" => $person_oids, "lang_id" => array(), "site_id" => array()));
			$person_ol->arr();
		}

		if (!$ol->count())
		{
			$comment_ol = new object_list();
		}
		else
		{
			$comment_ol = new object_list(array(
				"parent" => $ol->ids(),
				"class_id" => CL_BUG_COMMENT,
				"lang_id" => array(),
				"site_id" => array()
			));
		}
		$comments_by_bug = array();
		foreach($comment_ol->arr() as $comm)
		{
			$comments_by_bug[$comm->parent()]++;
		}
		foreach($bug_list as $bug)
		{
			$crea = $bug->createdby();
			$p = obj($u2p[$crea]);
			$nl = html::obj_change_url($bug);
			$opurl = aw_url_change_var("b_id", $bug->id());
			if ($params["path"])
			{
				$nl = $bug->path_str(array(
					"to" => $params["bt"]->id(),
					"path_only" => true
				))." / ".$nl;
			}

			$col = "";
			$dl = $bug->prop("deadline");
			if ($dl > 100 && time() > $dl)
			{
				$col = "#ff0000";
			}
			else
			if ($dl > 100 && date("d.m.Y") == date("d.m.Y", $dl)) // today
			{
				$col = "#f3f27e";
			}

			$t->define_data(array(
				"id" => $bug->id(),
				"name" => $nl,
				"bug_status" => $states[$bug->prop("bug_status")],
				"who" => $bug->prop_str("who"),
				"bug_priority" => $bug->class_id() == CL_MENU ? "" : $bug->prop("bug_priority"),
				"bug_severity" => $bug->class_id() == CL_MENU ? "" : $bug->prop("bug_severity"),
				"createdby" => $p->name(),
				"created" => $bug->created(),
				"deadline" => $bug->prop("deadline"),
				"num_hrs_guess" => $bug->prop("num_hrs_guess"),
				"id" => $bug->id(),
				"oid" => $bug->id(),
				"sort_priority" => $bug_i->get_sort_priority($bug),
				"icon" => icons::get_icon($bug),
				"obj" => $bug,
				"comment_count" => (int)$comments_by_bug[$bug->id()],
				"comment" => (int)$comments_by_bug[$bug->id()],
				"col" => $col
			));
		}
	}

	function _set_bugs_table($arr)
	{
		if($arr["request"]["add_bug"])
		{
			$ids = explode(",",$arr["request"]["add_bug"]);
			foreach($ids as $oid)
			{
				$bug = obj($oid);
				$bug->set_parent($arr["obj_inst"]->id());
				$bug->save();
			}
		}
	}


	function callback_post_save($arr)
	{
		if (is_array($this->add_comments) && count($this->add_comments))
		{
			$b = get_instance(CL_BUG);
			$b->_add_comment($arr["obj_inst"], join("\n", $this->add_comments), $this->_ac_old_state, $this->_ac_new_state, $this->_acc_add_wh);
		}
	}

	function get_status_list()
	{
		$statuses = array(
			1 => "Koosk�lastamisel",
			13 => "�levaatamisel",
			2 => "Tellitud",
			3 => "Valmis",
			12 => "Testimisel",
			4 => "Testitud",
			5 => "Suletud",
			6 => "Vale teade",
			7 => "Kordamatu",
			8 => "Parandamatu",
			9 => "Ei paranda",
			10 => "Vajab tagasisidet",
			11 => "Fatal error"
		);
		return $statuses;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["set_req"] = "0";
		$arr["set_problems"] = "0";
		$arr["add_bug"] = "0";
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_dev_orders(aw_oid int primary key, aw_customer int, aw_project int, aw_content mediumtext, aw_f1 int, aw_f2 int, aw_f3 int)");
			return true;
		}

		switch($f)
		{
			case "aw_f1":
			case "aw_f2":
			case "aw_f3":
			case "aw_orderer_co":
			case "aw_orderer_unit":
			case "bug_status":
			case "bug_priority":
			case "deadline":
			case "prognosis":
			case "contactperson":
			case "aw_orderer":
			case "aw_bug_feedback_p":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
			case "com":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "text",
				));
				return true;
		}
	}

	function _get_reqs_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$ps = get_instance("vcl/popup_search");
		$tb->add_cdata($ps->get_popup_search_link(array(
			"pn" => "set_req",
			"clid" => CL_PROCUREMENT_REQUIREMENT
		)));
		$tb->add_delete_rels_button();
		$tb->add_separator();
		$tb->add_button(array(
			"name" => "export",
			"tooltip" => t("Ekspordi"),
			"img" => "export.gif",
			"action" => "export_req",
		));
		$tb->add_separator();
		$tb->add_button(array(
			"name" => "add_to_cart",
			"tooltip" => t("Lisa korvi"),
//			"img" => "export.gif",
			"action" => "add_to_cart",
		));
	}

	function _get_reqs_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_REQ")));
		$t->table_from_ol($ol, array("name", "created", "pri", "req_co", "req_p", "project", "process", "planned_time"), CL_PROCUREMENT_REQUIREMENT);
	}

	function _set_reqs_table($arr)
	{
		$ps = get_instance("vcl/popup_search");
		$ps->do_create_rels($arr["obj_inst"], $arr["request"]["set_req"], "RELTYPE_REQ");
	}

	function _get_problems_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$ps = get_instance("vcl/popup_search");
		$tb->add_cdata($ps->get_popup_search_link(array(
			"pn" => "set_problems",
			"clid" => CL_CUSTOMER_PROBLEM_TICKET
		)));
		$tb->add_delete_rels_button();
	}

	function _get_problems_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PROBLEM")));
		$t->table_from_ol($ol, array("name", "createdby", "created", "orderer_co", "orderer_unit", "customer", "project", "requirement", "from_dev_order", "from_bug"), CL_CUSTOMER_PROBLEM_TICKET);
	}

	function _set_problems_table($arr)
	{
		$ps = get_instance("vcl/popup_search");
		$ps->do_create_rels($arr["obj_inst"], $arr["request"]["set_problems"], "RELTYPE_PROBLEM");
	}

	/**
		@attrib name=export_req
	**/
	function export_req($arr)
	{
		$o = obj($arr["id"]);
		$ol = new object_list($o->connections_from(array("type" => "RELTYPE_REQ")));
		classload("vcl/table");
		$t = new vcl_table();
		$t->table_from_ol($ol, array("name", "created", "pri", "req_co", "req_p", "project", "process", "planned_time", "desc", "state", "budget"), CL_PROCUREMENT_REQUIREMENT);
		header('Content-type: application/octet-stream');
		header('Content-disposition: root_access; filename="req.csv"');
		print $t->get_csv_file();
		die();
	}

	function _get_reqs_cart_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "remove_from_cart",
			"tooltip" => t("Eemalda korvist"),
			"img" => "delete.gif",
			"action" => "remove_from_cart",
		));
		$tb->add_save_button();
	}

	function _set_reqs_cart_table($arr)
	{
		$arr["obj_inst"]->set_meta("cart", $arr["request"]["d"]);
	}

	function _get_reqs_cart_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$all_ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_REQ")));
		$ol = new object_list();
		$cart = $arr["obj_inst"]->meta("cart");
		foreach($all_ol->arr() as $o)
		{
			if (isset($cart[$o->id()]))
			{
				$ol->add($o);
			}
		}
		$t->table_from_ol($ol, array("name", "created", "pri", "req_co", "req_p", "project", "process", "planned_time"), CL_PROCUREMENT_REQUIREMENT);
		$t->define_field(array(
			"name" => "hrs",
			"caption" => t("T&ouml;&ouml;tunde"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("L&otilde;ppt&auml;htaeg"),
			"align" => "center"
		));
		foreach($t->get_data() as $idx => $row)
		{
			$row["hrs"] = html::textbox(array(
				"name" => "d[".$row["oid"]."][hrs]",
				"size" => 5,
				"value" => $cart[$row["oid"]]["hrs"]
			));
			$row["price"] = html::textbox(array(
				"name" => "d[".$row["oid"]."][price]",
				"size" => 5,
				"value" => $cart[$row["oid"]]["price"]
			));
			$row["date"] = html::date_select(array(
				"format" => array("day_textbox", "month_textbox","year_textbox"),
				"value" => $cart[$row["oid"]]["date"] > 10 ? $cart[$row["oid"]]["date"] : -1,
				"name" => "d[".$row["oid"]."][date]"
			));
			$t->set_data($idx, $row);
		}
	}

	/**
		@attrib name=add_to_cart
	**/
	function add_to_cart($arr)
	{
		$o = obj($arr["id"]);
		$cart = $o->meta("cart");
		foreach(safe_array($arr["sel"]) as $id)
		{
			if (!isset($cart[$id]))
			{
				$cart[$id] = array("price" => 0);
			}
		}
		$o->set_meta("cart", $cart);
		$o->save();
		return $arr["post_ru"];
	}

	/**
		@attrib name=remove_from_cart
	**/
	function remove_from_cart($arr)
	{
		$o = obj($arr["id"]);
		$cart = $o->meta("cart");
		foreach(safe_array($arr["sel"]) as $id)
		{
			unset($cart[$id]);
		}
		$o->set_meta("cart", $cart);
		$o->save();
		return $arr["post_ru"];
	}
}
?>
