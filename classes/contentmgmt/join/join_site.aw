<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/join/join_site.aw,v 1.1 2004/03/05 12:11:17 kristo Exp $
// join_site.aw - Saidiga Liitumine 
/*

@classinfo syslog_type=ST_JOIN_SITE relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@property after_join_url type=textbox field=meta method=serialize 
@caption P&auml;rast liitumist mine aadressile

@property obj_folder type=relpicker reltype=RELTYPE_OBJ_FOLDER field=meta method=serialize 
@caption Kataloog, kuhu salvestatakse objektid

@property autologin type=checkbox ch_value=1 field=meta method=serialize 
@caption Kas kasutaja logitakse automaatselt sisse liitumisel

@groupinfo props caption="Vormid"

@groupinfo sel_props parent=props caption="Vali elemendid"
@groupinfo mk_pages parent=props caption="Koosta lehed"
@groupinfo page_titles parent=props caption="Lehtede Pealkirjad"

@property join_properties type=table store=no group=sel_props
@caption Liitumisel k&uuml;sitavad v&auml;ljad

@property join_sep_pages type=checkbox field=meta method=serialize ch_value=1 group=sel_props
@caption Eraldi lehtedel

@property join_properties_pages type=table store=no group=mk_pages
@caption Liitumisel k&uuml;sitavad v&auml;ljad

@property join_properties_page_titles type=table store=no group=page_titles
@caption Liitumise Lehtede Pealkirjad


@groupinfo rules caption="Reeglid"

@groupinfo rules_show parent=rules caption="Nimekiri reeglitest"
@groupinfo rules_add parent=rules caption="Lisa Reegel"

@property rules_show type=table store=no group=rules_show
@caption Reeglid

@property rule_name type=textbox group=rules_add store=no
@caption Reegli nimi

@property rules_add type=text store=no group=rules_add
@caption Lisa Reegel

@property rule_to_grp type=objpicker clid=CL_GROUP group=rules_add store=no
@caption Vali grupp, kuhu reegel kasutaja paneb

@groupinfo preview caption="Eelvaade"

@property preview type=text store=no no_caption=1 group=preview

@reltype JOIN_CLASS value=1 clid=CL_OBJECT_TYPE
@caption liitumise vorm

@reltype JOIN_RULE value=2 clid=CL_JOIN_SITE_RULE
@caption gruppi kuuluvuse reegel

@reltype OBJ_FOLDER value=3 clid=CL_MENU
@caption objektide kataloog

*/

class join_site extends class_base
{
	function join_site()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/join/join_site",
			"clid" => CL_JOIN_SITE
		));

		$this->prop_types = array(
			"checkbox" => 1,
			"textbox" => 1,
			"datetime" => 1,
			"password" => 1
		);
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "join_properties":
				$this->_do_join_props($arr);
				break;

			case "join_properties_pages":
				$this->_do_join_props_pages($arr);
				break;

			case "join_properties_page_titles":
				$this->_do_join_props_pages_titles($arr);
				break;

			case "preview":
				$data["value"] = $this->show(array(
					"id" => $arr["obj_inst"]->id()
				));

			case "rules_show":
				$this->_do_rules_tbl($arr);
				break;

			case "rules_add":
				$data["value"] = $this->_do_add_rule($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "join_properties":
				$this->_save_join_properties($arr);
				break;	

			case "join_properties_pages":
				$this->_save_join_properties_pages($arr);
				break;	

			case "join_properties_page_titles":
				$this->_save_join_properties_page_titles($arr);
				break;

			case "rule_to_grp":
				$this->_save_rule($arr);
				break;
		}
		return $retval;
	}	

	function _save_rule($arr)
	{
		$ruled = $this->_update_sess_data($arr["request"], true);

		if ($arr["prop"]["value"])
		{
			// save rule
			$o = new object();
			$o->set_class_id(CL_JOIN_SITE_RULE);
			$o->set_parent($arr['obj_inst']->parent());
			$o->set_name($arr["request"]["rule_name"]);
			$o->set_meta("rule_data",$ruled);
			$o->set_prop("rule_to_grp", $arr["prop"]["value"]);
			$o->set_prop("join_conf", $arr["obj_inst"]->id());
			$rid = $o->save();

			$arr["obj_inst"]->connect(array(
				"to" => $rid,
				"reltype" => RELTYPE_JOIN_RULE
			));

			$o->connect(array(
				"to" => $arr["obj_inst"]->id(),
				"reltype" => 1 // RELTYPE_JOIN_CONF from join_site_rule
			));
		}
	}

	function _do_add_rule($arr)
	{
		$this->read_template("add_rule.tpl");
		$this->vars(array(
			"form" => $this->get_form_from_obj(array(
				"id" => $arr["obj_inst"]->id()
			))
		));
		return $this->parse();
	}


	function _init_rules_table(&$t)
	{
		$t->define_field(array(
			"name" => "rule",
			"caption" => "Reegel",
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "to_grp",
			"caption" => "Grupp",
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "edit",
			"caption" => "Muuda",
			"align" => "center",
		));
	}

	function _do_rules_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rules_table($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_JOIN_RULE)) as $c)
		{
			$to = $c->to();

			$go = obj($to->prop("rule_to_grp"));

			$t->define_data(array(
				"rule" => $to->name(),
				"to_grp" => $go->name(),
				"edit" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $to->id()), $to->class_id()),
					"caption" => "Muuda"
				))
			));
		}
	}

	function _init_jp_table(&$t)
	{
		$t->define_field(array(
			"name" => "prop",
			"caption" => "Omadus"
		));
		$t->define_field(array(
			"name" => "visible",
			"caption" => "T&auml;idetav",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "required",
			"caption" => "N&otilde;utav",
			"align" => "center"
		));
		$t->set_sortable(false);
	}

	function _init_jp_table_pages(&$t)
	{
		$t->define_field(array(
			"name" => "prop",
			"caption" => "Omadus"
		));
		$t->define_field(array(
			"name" => "page",
			"caption" => "Lehek&uuml;lg",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "ord",
			"caption" => "J&auml;rjekord",
			"align" => "center"
		));
		$t->set_sortable(false);
	}

	function _init_pages_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "page_name",
			"caption" => "Sisestatud number",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "page_title",
			"caption" => "Lehe nimi",
			"align" => "center"
		));
	}

	function _do_join_props_pages_titles($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_pages_tbl($t);

		foreach($this->_get_page_picker($arr["obj_inst"]) as $pgid => $pgstr)
		{
			$t->define_data(array(
				"page_name" => $pgid,
				"page_title" => html::textbox(array(
					"name" => "page_str[$pgid]",
					"value" => $pgstr
				))
			));
		}
	}

	function _do_join_props($arr)
	{
		$prop =& $arr["prop"];
		
		$this->_init_jp_table($prop["vcl_inst"]);

		$required = $arr["obj_inst"]->meta("required");
		$visible = $arr["obj_inst"]->meta("visible");

		foreach($this->_get_clids($arr["obj_inst"]) as $clid)
		{
			$cln = basename($this->cfg["classes"][$clid]["file"]);

			// get properties for clid
			$cfgu = get_instance("cfg/cfgutils");
			$props = $cfgu->load_properties(array(
				"file" => $cln,
				"clid" => $clid
			));

			$prop["vcl_inst"]->define_data(array(
				"prop" => "<b>".$this->cfg["classes"][$clid]["name"]."</b>",
				"visible" => "",
				"required" => ""
			));

			foreach($props as $nprop)
			{
				if (!$this->prop_types[$nprop["type"]])
				{
					continue;
				}

				$req = html::checkbox(array(
					"name" => "required[$clid][".$nprop["name"]."]",
					"value" => 1,
					"checked" => ($required[$clid][$nprop["name"]] == 1 || !is_array($required[$clid]))
				));
				if ($clid == CL_USER)
				{
					if ($nprop["name"] == "uid_entry" || $nprop["name"] == "passwd" || $nprop["name"] == "passwd_again")
					{
						$req = "Jah".html::hidden(array(
							"name" => "required[$clid][".$nprop["name"]."]",
							"value" => 1
						));
					}
				}

				$prop["vcl_inst"]->define_data(array(
					"prop" => str_repeat("&nbsp;", 10).$nprop["caption"]." (".$nprop["name"].")",
					"visible" => html::checkbox(array(
						"name" => "visible[$clid][".$nprop["name"]."]",
						"value" => 1,
						"checked" => ($visible[$clid][$nprop["name"]] == 1 || !is_array($visible[$clid]))
					)),
					"required" => $req
				));
			}
		}
	}

	function _do_join_props_pages($arr)
	{
		$prop =& $arr["prop"];
		
		$this->_init_jp_table_pages($prop["vcl_inst"]);

		$ord = $arr["obj_inst"]->meta("ord");
		$propn = $arr["obj_inst"]->meta("propn");
		$page = $arr["obj_inst"]->meta("page");
		$visible = $arr["obj_inst"]->meta("visible");

		foreach($this->_get_clids($arr["obj_inst"]) as $clid)
		{
			$cln = basename($this->cfg["classes"][$clid]["file"]);

			// get properties for clid
			$cfgu = get_instance("cfg/cfgutils");
			$props = $cfgu->load_properties(array(
				"file" => $cln,
				"clid" => $clid
			));

			$prop["vcl_inst"]->define_data(array(
				"prop" => "<b>".$this->cfg["classes"][$clid]["name"]."</b>",
				"visible" => "",
				"required" => ""
			));

			if (!is_array($propn[$clid]))
			{
				foreach($props as $nprop)
				{
					$propn[$clid][$nprop["name"]] = $nprop["caption"];
				}
			}

			foreach($props as $nprop)
			{
				if ($visible[$clid][$nprop["name"]] == 1)
				{
					$prop["vcl_inst"]->define_data(array(
						"prop" => html::textbox(array(
							"name" => "propn[$clid][".$nprop["name"]."]",
							"value" => $propn[$clid][$nprop["name"]]
						)),
						"page" => html::textbox(array(
							"name" => "page[$clid][".$nprop["name"]."]",
							"value" => $page[$clid][$nprop["name"]],
							"size" => 5
						)),
						"ord" => html::textbox(array(
							"name" => "ord[$clid][".$nprop["name"]."]",
							"value" => $ord[$clid][$nprop["name"]],
							"size" => 5
						)),
					));
				}
			}
		}
	}

	function _save_join_properties($arr)
	{
		$visible = array();
		$required = array();

		foreach($this->_get_clids($arr["obj_inst"]) as $clid)
		{
			$visible[$clid] = $arr["request"]["visible"][$clid];
			$required[$clid] = $arr["request"]["required"][$clid];
		}		
		$arr["obj_inst"]->set_meta("visible", $visible);
		$arr["obj_inst"]->set_meta("required", $required);
	}

	function _save_join_properties_pages($arr)
	{
		$ord = array();
		$propn = array();
		$page = array();

		foreach($this->_get_clids($arr["obj_inst"]) as $clid)
		{
			$ord[$clid] = is_array($arr["request"]["ord"][$clid]) ? $arr["request"]["ord"][$clid] : array();
			$propn[$clid] = is_array($arr["request"]["propn"][$clid]) ? $arr["request"]["propn"][$clid] : array();
			$page[$clid] = is_array($arr["request"]["page"][$clid]) ? $arr["request"]["page"][$clid] : array();
		}		
		$arr["obj_inst"]->set_meta("ord", $ord);
		$arr["obj_inst"]->set_meta("propn", $propn);
		$arr["obj_inst"]->set_meta("page", $page);
	}

	function _save_join_properties_page_titles($arr)
	{
		$arr["obj_inst"]->set_meta("page_str", $arr["request"]["page_str"]);
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	/** returns the html form from the join object $id, fills it withu data from objects in $data

		@param id 
	**/
	function get_form_from_obj($arr, $sessd = false)
	{
		extract($arr);
		
		$ob = new object($arr["id"]);

		$visible = $ob->meta("visible");
		$required = $ob->meta("required");
		$propn = $ob->meta("propn");

		$cfgu = get_instance("cfg/cfgutils");
		
		$ret = "";
		$first = false;
		$clss = array();

		if ($sessd == false)
		{
			$sessd = aw_global_get("site_join_status");
		}
		$je = aw_global_get("join_err");

		// for each cfgform related
		foreach($this->_get_clids($ob) as $clid)
		{
			$cln = basename($this->cfg["classes"][$clid]["file"]);

			$clss[$clid] = $this->cfg["classes"][$clid]["name"];

			if (!$first)
			{
				$first = $clid;
			}

			if ($ob->prop("join_sep_pages") && !($_GET["join_tab"] == $clid || (!$_GET["join_tab"] && $first == $clid)))
			{
				continue;
			}

			$np = array();
			
			// get properties for clid
			$props = $cfgu->load_properties(array(
				"file" => $cln,
				"clid" => $clid
			));

			$tp = array();
			foreach($props as $pid => $prop)
			{	
				if ($visible[$clid][$prop["name"]])
				{
					if (isset($cf_sd[$prop["name"]]))
					{
						$prop["value"] = $cf_sd[$prop["name"]];
					}
					$tp[$pid] = $prop;
				}
			}
		
			$wn = "typo_".$clid;

			$i = get_instance($clid);
			$xp = $i->parse_properties(array(
				"properties" => $tp,
				"name_prefix" => $wn
			));

			$cf_sd = $sessd[$wn];

			$htmlc = get_instance("cfg/htmlclient");
			$htmlc->start_output();
			foreach($xp as $xprop)
			{
				$oldn = str_replace($wn."[", "", str_replace("]", "", $xprop["name"]));

				if ($clid == CL_USER && $oldn == "uid_entry")
				{
					if ($je["gen"] != "")
					{
						$errp = array(
							"name" => "err_".$clid."_".$oldn,
							"type" => "text",
							"no_caption" => 1,
							"value" => "<font color='#FF0000'>".$je["gen"]."</font>"
						);
						$htmlc->add_property($errp);
					}
				}
				if ($je["prop"][$clid][$oldn])
				{
					$errp = array(
						"name" => "err_".$clid."_".$oldn,
						"type" => "text",
						"no_caption" => 1,
						"value" => "<font color='#FF0000'>J&auml;rgnev v&auml;li peab olema t&auml;idetud!</font>"
					);
					$htmlc->add_property($errp);
				}

				if (isset($cf_sd[$oldn]))
				{
					$xprop["value"] = $cf_sd[$oldn];
				}
				if ($propn[$clid][$oldn] != "")
				{
					$xprop["caption"] = $propn[$clid][$oldn];
				}
				$htmlc->add_property($xprop);
			}
		
			$htmlc->finish_output(array());

			$html .= $htmlc->get_result(array(
				"raw_output" => 1
			));
		}

		if ($ob->prop("join_sep_pages"))
		{
			classload("vcl/tabpanel");
			$tp = tabpanel::simple_tabpanel(array(
				"var" => "join_tab",
				"default" => $first,
				"opts" => $clss
			));

			return $tp->get_tabpanel(array(
				"content" => $html
			));
		}

		return $html;
	}

	function show($arr)
	{
		$this->read_template("join.tpl");

		if (aw_global_get("uid") != "")
		{
			$add = 0;
		}

		$this->vars(array(
			"form" => $this->get_form_from_obj(array(
				"id" => $arr["id"]
			)),
			"reforb" => $this->mk_reforb("submit_join_form", array("id" => $arr["id"], "add" => $add, "section" => aw_global_get("section")))
		));

		return $this->parse();
	}

	function _update_sess_data($arr, $ret = false)
	{
		if ($ret)
		{
			$sessd = array();
		}
		else
		{
			$sessd = aw_global_get("site_join_status");
		}

		// set the data to the session
		foreach($arr as $k => $v)
		{
			if (substr($k, 0, strlen("typo_")) == "typo_")
			{
				$sessd[$k] = $v;
			}
		}

		if ($ret)
		{
			return $sessd;
		}
		aw_session_set("site_join_status", 	$sessd);
	}

	/** submitting a join form will get you here
	
		@attrib name=submit_join_form

	**/
	function submit_join_form($arr)
	{
		// ok, fuck-o-'s, if $add == 1, we are adding, else changing the user data

		$obj = obj($arr["id"]);

		// update session data in sess[site_join_status]
		$this->_update_sess_data($arr);

		// now, check if we are done
		$join_done = false;

		// check if all required fields are filled
		$req = $obj->meta("required");
		$sessd = aw_global_get("site_join_status");

		$filled = true;
		$nf = array();
		foreach($req as $clid => $rp)
		{
			$rpa = new aw_array($rp);
			foreach($rpa->get() as $propn => $one)
			{
				if ($one == 1)
				{
					if ($sessd["typo_".$clid][$propn] == "")
					{
						$filled = false;
						$nf["prop"][$clid][$propn] = 1;
					}
				}
			}
		}
		

		// if they are , then add the user and go to the after join page
		if ($filled)
		{
			// check if the user can be added
			
			// get the uid and password
			// they are from the user object
			$n_uid = $sessd["typo_".CL_USER]["uid_entry"];
			$n_pass = $sessd["typo_".CL_USER]["passwd"];
			$n_email = $sessd["typo_".CL_USER]["email"];
			$n_pass2 = $sessd["typo_".CL_USER]["passwd_again"];

			$us = get_instance("users");
			if ($us->can_add(array("a_uid" => $n_uid, "pass" => $n_pass, "pass2" => $n_pass2)))
			{
				$join_done = true;

				// add the user
				$us->add(array(
					"uid" => $n_uid, 
					"password" => $n_pass,
					"email" => $n_email, 
					"join_grp" => $obj->id()
				));
	
				// also, create all the objects and do the relations and crap
				$this->_do_create_data_objects($arr, $us->last_user_oid);

				// apply rules on add
				$this->apply_rules_on_data_change($this->get_rules_from_obj($obj), $us->last_user_oid);

				// if the props say so, log the user in
				if ($obj->prop("autologin"))
				{
					$us->login(array(
						"uid" => $n_uid,
						"password" => $n_pass
					));
				}

				// we also gots to clear out all the join data
				aw_session_set("site_join_status", array());
			}
			else
			{
				$nf["gen"] = $GLOBALS["add_state"]["error"];
			}
		}
		// if not, then just return to the fill page. we should give the user some error message as well


		if ($join_done)
		{
			aw_session_set("join_err", array());
			return $obj->prop("after_join_url");
		}

		aw_session_set("join_err", $nf);
		return aw_ini_get("baseurl")."/".$arr["section"];
	}

	function _do_create_data_objects($arr, $u_oid)
	{
		$obj = obj($arr["id"]);

		$u_o = obj($u_oid);

		$change = false;
		$awa = new aw_array($sessd["typo_".CL_USER]);
		foreach($awa->get() as $pn => $pv)
		{
			$change = true;
			$u_o->set_prop($pn, $pv);
		}
		if ($change)
		{
			$u_o->save();
		}


		$sessd = aw_global_get("site_join_status");

		$person = obj();
		$person->set_class_id(CL_CRM_PERSON);
		$person->set_parent($obj->prop("obj_folder"));
		$person->set_name($sessd["typo_".CL_CRM_PERSON]["firstname"]." ".$sessd["typo_".CL_CRM_PERSON]["lastname"]);
		$awa = new aw_array($sessd["typo_".CL_CRM_PERSON]);
		foreach($awa->get() as $pn => $pv)
		{
			$person->set_prop($pn, $pv);
		}
		$p_id = $person->save();
		$this->create_obj_access($p_id, $u_o->prop("uid"));

		$u_o->connect(array(
			"to" => $p_id,
			"reltype" => 2 // RELTYPE_PERSON from core/users/user
		));

		foreach($this->_get_clids($obj) as $clid)
		{
			if ($clid == CL_CRM_PERSON || $clid == CL_USER)
			{
				continue;
			}

			$o = new object();
			$o->set_class_id($clid);
			$o->set_parent($obj->prop("obj_folder"));

			$hase = false;
			$awa = new aw_array($sessd["typo_".$clid]);
			foreach($awa->get() as $pn => $pv)
			{
				$o->set_prop($pn, $pv);
				$hase = true;
			}
			if ($hase)
			{
				$o_id = $o->save();
				$this->create_obj_access($o_id, $u_o->prop("uid"));

				$u_o->connect(array(
					"to" => $o_id,
					"reltype" => 3 // RELTYPE_USER_DATA from core/users/user
				));

				$person->connect(array(
					"to" => $o_id,
					"reltype" => 15 // RELTYPE_USER_DATA from crm/crm_person
				));
			}
		}
	}

	function _get_clids($ob)
	{
		$ret = array(CL_USER,CL_CRM_PERSON);
		foreach($ob->connections_from(array("type" => RELTYPE_JOIN_CLASS)) as $c)
		{
			$cfgf = $c->to();
			$ret[] = $cfgf->prop("type");
		}

		return $ret;
	}

	function _get_page_picker($o)
	{
		$ret = array();

		$page_str = $o->meta("page_str");

		$awa = new aw_array($o->meta("page"));
		foreach($awa->get() as $clid => $clps)
		{
			foreach($clps as $clpid => $pagen)
			{
				if ($pagen != "")
				{
					$ret[$pagen] = $page_str[$pagen];
				}
			}
		}

		return $ret;
	}

	function get_rules_from_obj($o)
	{
		$ret = array();
		foreach($o->connections_from(array("type" => RELTYPE_JOIN_RULE)) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to");
		}
		return $ret;
	}

	function apply_rules_on_data_change($rules, $u_oid)
	{
		$user = obj($u_oid);

		$ri = get_instance("contentmgmt/join/join_site_rule");
		$gi = get_instance("core/users/group");

		foreach($rules as $rule_oid)
		{
			$rule = obj($rule_oid);

			if ($ri->match_rule_to_user($rule, $user))
			{
				$gi->add_user_to_group($user, obj($rule->prop("rule_to_grp")));
			}
		}
	}
}
?>
