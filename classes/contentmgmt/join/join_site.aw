<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/join/join_site.aw,v 1.14 2004/11/23 16:06:38 ahti Exp $
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

@property send_join_mail type=checkbox ch_value=1 field=meta method=serialize 
@caption Kas liitumisel saadetakse meil

@groupinfo props caption="Vormid"

@groupinfo sel_props parent=props caption="Vali elemendid"
@groupinfo mk_pages parent=props caption="Koosta lehed"
@groupinfo page_titles parent=props caption="Lehtede Pealkirjad"
@groupinfo seps parent=props caption="Vahepealkirjad"

@property join_properties type=table store=no group=sel_props
@caption Liitumisel k&uuml;sitavad v&auml;ljad

@property join_sep_pages type=checkbox field=meta method=serialize ch_value=1 group=sel_props
@caption Eraldi lehtedel

@property join_but_text type=textbox field=meta method=serialize group=sel_props
@caption Liitumise nupu tekst

@property join_properties_pages type=table store=no group=mk_pages
@caption Liitumisel k&uuml;sitavad v&auml;ljad

@property join_properties_page_titles type=table store=no group=page_titles
@caption Liitumise Lehtede Pealkirjad

@property join_seps type=table store=no group=seps
@caption Vahepealkirjad


@groupinfo rules caption="Reeglid"

@groupinfo rules_show parent=rules caption="Nimekiri reeglitest"
@groupinfo rules_add parent=rules caption="Lisa Reegel"

@property rules_show type=table store=no group=rules_show
@caption Reeglid

@property rule_name type=textbox group=rules_add store=no
@caption Reegli nimi

@property rules_add type=text store=no group=rules_add
@caption Lisa Reegel

@property rule_to_grp type=relpicker reltype=RELTYPE_RULE_GRP group=rules_add store=no
@caption Vali grupp, kuhu reegel kasutaja paneb

@groupinfo preview caption="Eelvaade"

@property preview type=text store=no no_caption=1 group=preview

@groupinfo joinmail caption="Meil"

@property joinmail_legend type=text store=no group=joinmail
@caption Meili legend

@property jm_texts type=callback callback=callback_get_jm_texts group=joinmail store=no

@reltype JOIN_CLASS value=1 clid=CL_OBJECT_TYPE
@caption liitumise vorm

@reltype JOIN_RULE value=2 clid=CL_JOIN_SITE_RULE
@caption gruppi kuuluvuse reegel

@reltype OBJ_FOLDER value=3 clid=CL_MENU
@caption objektide kataloog

@reltype RULE_GRP value=4 clid=CL_GROUP
@caption reegli grupp

@reltype REL_OBJ value=5 clid=CL_CRM_USER,CL_USER,CL_CRM_COMPANY
@caption default seoste objektid

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
			"password" => 1,
			"relmanager" => 1,
			"relpicker" => 1,
			"date_select" => 1,
			"chooser" => 1
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

			case "join_seps":
				$this->_do_join_seps($arr);
				break;

			case "preview":
				$data["value"] = $this->show(array(
					"id" => $arr["obj_inst"]->id()
				));
				break;

			case "rules_show":
				$this->_do_rules_tbl($arr);
				break;

			case "rules_add":
				$data["value"] = $this->_do_add_rule($arr);
				break;

			case "joinmail_legend":
				$data["value"] = "E-maili sisu, mis saadetakse kasutajale liitumisel (kasutajanime alias #kasutaja#, parooli alias #parool# ja parooli muutmise lingi alias #pwd_hash#).";
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

			case "join_seps":
				$this->_save_join_seps($arr);
				break;

			case "rule_to_grp":
				$this->_save_rule($arr);
				break;

			case "jm_texts":
				$arr["obj_inst"]->set_meta("jm_texts", $arr["request"]["lm_l_tx"]);
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

	function _init_seps_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "sep_name",
			"caption" => "Tekst",
			"align" => "center"
		));
	}

	function _do_join_seps($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_seps_tbl($t);
		$t->set_sortable(false);

		$awa = new aw_array($arr["obj_inst"]->meta("join_seps"));
		foreach($awa->get() as $sepid => $sep)
		{
			$t->define_data(array(
				"sep_name" => html::textbox(array(
					"name" => "join_seps[$sepid]",
					"value" => $sep
				))
			));
		}

		$t->define_data(array(
			"sep_name" => html::textbox(array(
				"name" => "join_seps[new]",
				"value" => ""
			))
		));
	}

	function _do_join_props($arr)
	{
		$prop =& $arr["prop"];
		
		$this->_init_jp_table($prop["vcl_inst"]);

		$required = $arr["obj_inst"]->meta("required");
		$visible = $arr["obj_inst"]->meta("visible");

		$clss = aw_ini_get("classes");

		foreach($this->_get_clids($arr["obj_inst"]) as $clid)
		{
			$cln = basename($clss[$clid]["file"]);

			// get properties for clid
			$cfgu = get_instance("cfg/cfgutils");
			$props = $cfgu->load_properties(array(
				"file" => $cln,
				"clid" => $clid
			));

			$prop["vcl_inst"]->define_data(array(
				"prop" => "<b>".$clss[$clid]["name"]."</b>",
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
					"checked" => ($required[$clid][$nprop["name"]] == 1)
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
						"checked" => ($visible[$clid][$nprop["name"]] == 1)
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

		// insert all separators 
		$prop["vcl_inst"]->define_data(array(
			"prop" => "<b>Vahepealkirjad</b>",
			"visible" => "",
			"required" => ""
		));
		$seps = new aw_array($arr["obj_inst"]->meta("join_seps"));
		foreach($seps->get() as $sepid => $sepn)
		{
			$prop["vcl_inst"]->define_data(array(
				"prop" => $sepn,
				"page" => html::textbox(array(
					"name" => "page[sep][$sepid]",
					"value" => $page["sep"][$sepid],
					"size" => 5
				)),
				"ord" => html::textbox(array(
					"name" => "ord[sep][$sepid]",
					"value" => $ord["sep"][$sepid],
					"size" => 5
				)),
			));
		}
		

		foreach($this->_get_clids($arr["obj_inst"]) as $clid)
		{
			$cln = basename($clss[$clid]["file"]);

			// get properties for clid
			$cfgu = get_instance("cfg/cfgutils");
			$props = $cfgu->load_properties(array(
				"file" => $cln,
				"clid" => $clid
			));

			$prop["vcl_inst"]->define_data(array(
				"prop" => "<b>".$clss[$clid]["name"]."</b>",
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
							"value" => ($propn[$clid][$nprop["name"]] == "" ? $nprop["caption"] : $propn[$clid][$nprop["name"]])
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
		$ord["sep"] = is_array($arr["request"]["ord"]["sep"]) ? $arr["request"]["ord"]["sep"] : array();
		$page["sep"] = is_array($arr["request"]["page"]["sep"]) ? $arr["request"]["page"]["sep"] : array();

		$arr["obj_inst"]->set_meta("ord", $ord);
		$arr["obj_inst"]->set_meta("propn", $propn);
		$arr["obj_inst"]->set_meta("page", $page);
	}

	function _save_join_properties_page_titles($arr)
	{
		$arr["obj_inst"]->set_meta("page_str", $arr["request"]["page_str"]);
	}

	function _save_join_seps($arr)
	{
		$awa = new aw_array($arr["request"]["join_seps"]);
		$dat = array();
		$maxid = 0;
		foreach($awa->get() as $sepid => $sept)
		{
			if ($sept != "" && $sepid != "new")
			{
				$dat[$sepid] = $sept;
				$maxid = $sepid;
			}
		}

		if ($arr["request"]["join_seps"]["new"] != "")
		{
			$dat[$maxid+1] = $arr["request"]["join_seps"]["new"];
		}
		$arr["obj_inst"]->set_meta("join_seps", $dat);
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

		$clss = aw_ini_get("classes");

		// for each cfgform related
		foreach($this->_get_clids($ob) as $clid)
		{
			$cln = basename($clss[$clid]["file"]);

			$clss[$clid] = $clss[$clid]["name"];

			if (!$first)
			{
				$first = $clid;
			}

			if ($ob->prop("join_sep_pages") && !($_GET["join_tab"] == $clid || (!$_GET["join_tab"] && $first == $clid)))
			{
				continue;
			}

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
		
		aw_global_set("no_cache", 1);

		$o = obj($arr["id"]);
		$tx = "Liitun";
		if ($o->prop("join_but_text"))
		{
			$tx = $o->prop("join_but_text");
		}
		$this->vars(array(
			"form" => $this->get_form_from_obj(array(
				"id" => $arr["id"]
			)),
			"join_but_text" => $tx,
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
	
		@attrib name=submit_join_form nologin="1"

	**/
	function submit_join_form($arr)
	{
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
			// disable the fucking acl. 
			aw_disable_acl();
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
				$cu = get_instance("core/users/user");
				$u_oid = $cu->add_user(array(
					"uid" => $n_uid,
					"email" => $n_email,
					"password" => $n_pass,
					"join_grp" => $obj->id()
				));
	
				// also, create all the objects and do the relations and crap
				$this->_do_create_data_objects($arr, $u_oid->id());

				// apply rules on add
				$this->apply_rules_on_data_change($this->get_rules_from_obj($obj), $u_oid->id());

				aw_restore_acl();

				// if the props say so, log the user in
				if (true || $obj->prop("autologin"))
				{
					$us->login(array(
						"uid" => $n_uid,
						"password" => $n_pass
					));
				}

				// if needed, send join mail
				if ($obj->prop("send_join_mail"))
				{
					$this->_do_send_join_mail(array(
						"obj" => $obj,
						"uid" => $n_uid,
						"pass" => $n_pass,
						"email" => $n_email,
						"data" => $sessd
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
			$this->join_done = true;
			aw_session_set("join_err", array());
			return $obj->prop("after_join_url");
		}

		aw_session_set("join_err", $nf);
		if ($arr["err_return_url"])
		{
			return $arr["err_return_url"];
		}

		return aw_ini_get("baseurl")."/".$arr["section"];
	}

	function _do_create_data_objects($arr, $u_oid)
	{
		// in here we gots to assume the identity of the to-be-created user
		// so that the user will get all access to the correct objects
		// we can't do this with create_obj_access, because we can't access relation objects
		$u_o = obj($u_oid);
		aw_switch_user(array(
			"uid" => $u_o->prop("uid")
		));

		$obj = obj($arr["id"]);

		$sessd = aw_global_get("site_join_status");

		$person = obj();
		$person->set_class_id(CL_CRM_PERSON);
		$person->set_parent($obj->prop("obj_folder"));
		$person->set_name($sessd["typo_".CL_CRM_PERSON]["firstname"]." ".$sessd["typo_".CL_CRM_PERSON]["lastname"]);
		$p_id = $person->save();

		$u_o->connect(array(
			"to" => $p_id,
			"reltype" => 2 // RELTYPE_PERSON from core/users/user
		));


		$com = obj();
		$com->set_class_id(CL_CRM_COMPANY);
		$com->set_parent($obj->prop("obj_folder"));
		$com->set_name($sessd["typo_".CL_CRM_COMPANY]["name"]);
		$c_id = $com->save();

		$person->connect(array(
			"to" => $c_id,
			"reltype" => 6 // RELTYPE_WORK from crm/crm_person
		));
		
		$a_objs = array();
		foreach($this->_get_clids($obj) as $clid)
		{
			if ($clid == CL_CRM_PERSON || $clid == CL_USER || $clid == CL_CRM_COMPANY)
			{
				continue;
			}

			$o = new object();
			$o->set_class_id($clid);
			$o->set_parent($obj->prop("obj_folder"));
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
			$a_objs[$o->class_id()] = $o_id;
			
		}

		// also, do update, all complex element crap is in there
		$this->_do_update_data_objects($obj, $u_o, $arr, $a_objs);
		aw_restore_user();
	}

	function _get_clids($ob)
	{
		$ret = array(CL_USER,CL_CRM_PERSON, CL_CRM_COMPANY);
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
		aw_disable_acl();
		$ret = array();
		foreach($o->connections_from(array("to.class_id" => CL_JOIN_SITE_RULE /* RELTYPE_JOIN_RULE */)) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to");
		}
		aw_restore_acl();
		return $ret;
	}

	function apply_rules_on_data_change($rules, $u_oid)
	{
		aw_disable_acl();
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
		aw_restore_acl();
	}

	/**
		$params can contain:
			- err_return_url - if set, errored inputs go to that
	**/
	function get_elements_from_obj($ob, $params)
	{
		$visible = $ob->meta("visible");
		$required = $ob->meta("required");
		$propn = $ob->meta("propn");

		$je = aw_global_get("join_err");

		$cfgu = get_instance("cfg/cfgutils");

		if (aw_global_get("uid") != "")
		{
			$us = get_instance("users");
			$u_o = obj($us->get_oid_for_uid(aw_global_get("uid")));
			$visible[CL_USER]["uid_entry"] = false;
		}		

		$sessd = aw_global_get("site_join_status");

		$clss = aw_ini_get("classes");

		$tp = array();
		// for each cfgform related
		foreach($this->_get_clids($ob) as $clid)
		{
			// get properties for clid
			$props = $cfgu->load_properties(array(
				"file" => basename($clss[$clid]["file"]),
				"clid" => $clid
			));
			$relinfo = $cfgu->relinfo;

			$data_o = obj();
			$data_o->set_class_id($clid);
			// get data object if user is logged
			if ($u_o)
			{
				if ($clid == CL_USER)
				{
					$data_o = $u_o;
				}
				else
				if ($clid == CL_CRM_PERSON)
				{
					$c = reset($u_o->connections_from(array("type" => 2 /* RELTYPE_PERSON */)));
					if ($c)
					{
						$data_o = $c->to();
					}
				}
				else
				if ($clid == CL_CRM_COMPANY)
				{
					$c = reset($u_o->connections_from(array("type" => 2 /* RELTYPE_PERSON */)));
					if ($c)
					{
						$tmp = $c->to();
						$c = reset($tmp->connections_from(array("type" => 6 /* RELTYPE_WORK from crm_person */)));
						if ($c)
						{
							$data_o = $c->to();
						}
					}
				}
			}

			$ttp = array();
			foreach($props as $pid => $prop)
			{	
				if ($visible[$clid][$pid])
				{
					$ttp[$pid] = $prop;
				}
			}

			$class_inst = get_instance($clid);
			$class_inst->init_class_base();
			$ttp = $class_inst->parse_properties(array("properties" => $ttp, "obj_inst" => $data_o));

			foreach($ttp as $pid => $prop)
			{	
				if ($visible[$clid][$prop["name"]])
				{
					$oldn = str_replace($wn."[", "", str_replace("]", "", $prop["name"]));

					if ($clid == CL_USER && $oldn == "uid_entry")
					{
						if ($je["gen"] != "")
						{
							$tp["err_".$clid."_".$oldn] = array(
								"name" => "err_".$clid."_".$oldn,
								"type" => "text",
								"no_caption" => 1,
								"value" => "<font color='#FF0000'>".$je["gen"]."</font>"
							);
						}
					}
					if ($je["prop"][$clid][$pid])
					{
						$tp["err_".$clid."_".$oldn] = array(
							"name" => "err_".$clid."_".$oldn,
							"type" => "text",
							"no_caption" => 1,
							"value" => "<font color='#FF0000'>J&auml;rgnev v&auml;li peab olema t&auml;idetud!</font>"
						);
					}

					// if it's a relpicker, get the rels from the default rel object
					// and insert them in there
					if ($props[$pid]["type"] == "relpicker")
					{
						$tmp = reset($ob->connections_from(array(
							"type" => 5, // RELTYPE_REL_OBJ 
							"to.class_id" => $clid
						)));
						if ($tmp)
						{
							$relv = $relinfo[$prop["reltype"]]["value"];
							$relto = $tmp->to();
							$data = array();
							foreach($relto->connections_from(array("type" => $relv)) as $c)
							{
								$data[$c->prop("to")] = $c->prop("to.name");
							}
							$prop["options"] = $data;
						}
					}
					/*else
					if ($prop["type"] == "relmanager" && $data_o)
					{
						$prop["real_obj"] = $data_o;
					}
					else
					if ($prop["type"] == "chooser")
					{
						$tmp_do = $data_o;
						if (!is_object($tmp_do))
						{
							$tmp_do = obj();
							$tmp_do->set_class_id($clid);
						}
						$tmp_param = array(
							"obj_inst" => &$tmp_do,
							"prop" => &$prop,
							"request" => $params
						);
						$class_inst->get_property($tmp_param);
					}*/

					// set value in property
					if ($data_o)
					{
						$prop["value"] = $data_o->prop($pid);
					}
					else
					{
						// try to read from sess data
						$wn = "typo_".$clid;
						$cf_sd = $sessd[$wn];
						$prop["value"] = $cf_sd[$oldn];
					}

					$pid = "typo_".$clid."[".$oldn."]";
					$prop["name"] = $pid;
					$tp[$pid] = $prop;
				}
			}
		}

		// add seprator props
		$seps = new aw_array($ob->meta("join_seps"));
		foreach($seps->get() as $sepid => $sepn)
		{
			$pid = "typo_sep[jsep_".$sepid."]";
			$tp[$pid] = array(
				"type" => "text",
				"name" => $pid,
				//"no_caption" => 1,
				"subtitle" => 1,
				"value" => $sepn
			);
		}
		
		// now that we got all props, re-order them based on the order on the pages page
		$this->_do_final_sort_props($ob, $tp);

		if ($params["err_return_url"] != "")
		{
			$tp["err_return_url"] = array(
				"name" => "err_return_url",
				"type" => "hidden",
				"store" => "no",
				"value" => $params["err_return_url"]
			);
		}

		aw_session_set("join_err", false);
		return $tp;
	}

	function submit_update_form($arr)
	{
		$obj = obj($arr["id"]);

		$us = get_instance("users");
		$u_o = obj($us->get_oid_for_uid(aw_global_get("uid")));

		// update data objects
		$this->_do_update_data_objects($obj, $u_o, $arr);

		// apply rules on add
		$this->apply_rules_on_data_change($this->get_rules_from_obj($obj), $u_o->id());
	}

	function _do_update_data_objects($ob, $u_o, $data, $a_objs = array())
	{
		$visible = $ob->meta("visible");
		$cfgu = get_instance("cfg/cfgutils");

		$clss = aw_ini_get("classes");

		// for each cfgform related
		foreach($this->_get_clids($ob) as $clid)
		{
			$cln = basename($clss[$clid]["file"]);

			$clss[$clid] = $clss[$clid]["name"];

			// get properties for clid
			$props = $cfgu->load_properties(array(
				"file" => $cln,
				"clid" => $clid
			));

			// find the correct data object
			$data_o = false;

			// if it's user, then we gots it
			if ($clid == CL_USER)
			{
				$data_o = $u_o;
			}
			elseif ($clid == CL_CRM_PERSON)
			{
				$c = reset($u_o->connections_from(array("type" => 2 /* RELTYPE_PERSON */)));
				if ($c)
				{
					$data_o = $c->to();
				}
			}
			elseif ($clid == CL_CRM_COMPANY)
			{
				$c = reset($u_o->connections_from(array("type" => 2 /* RELTYPE_PERSON */)));
				if ($c)
				{
					$tmp = $c->to();
					$c = reset($tmp->connections_from(array("type" => 6 /* RELTYPE_WORK from crm_person */)));
					if ($c)
					{
						$data_o = $c->to();
					}
				}
			}
			elseif($this->can("edit", $a_objs[$clid]) && is_oid($a_objs[$clid]))
			{
				$data_o = obj($a_objs[$clid]);
			}
			
			if ($data_o)
			{
				$this->_do_fake_form_submit(array(
					"data_o" => $data_o,
					"props" => $props,
					"visible" => $visible,
					"clid" => $clid,
					"data" => $data
				));
			}
		}
	}

	function _do_fake_form_submit($arr)
	{
		extract($arr);
		$submit_data = array(
			"return" => "id",
			"id" => $data_o->id(),
			"cb_no_groups" => 1
		);
		foreach($props as $pid => $prop)
		{	
			if ($visible[$clid][$prop["name"]])
			{
				$oldn = str_replace($wn."[", "", str_replace("]", "", $prop["name"]));
				$wn = "typo_".$clid;
				$cf_sd = $data[$wn];
				if ($clid == CL_USER)
				{
					$data_o->set_prop($pid, $cf_sd[$oldn]);
				}

				if ($prop["type"] == "relmanager")
				{
					$submit_data["cb_emb"] = $data["cb_emb"][$wn];
				}
				else
				{
					$submit_data[$pid] = $cf_sd[$oldn];
				}
			}
		}

		if ($clid == CL_USER)
		{
			$data_o->save();
		}
		else
		{
			$data_o_inst = $data_o->instance();
			$data_o_inst->submit($submit_data);
		}
	}

	function __prop_sorter($a, $b)
	{
		// get order from prop name
		preg_match("/typo_(.*)\[(.*)\]/U", $a["name"], $a_mt);
		preg_match("/typo_(.*)\[(.*)\]/U", $b["name"], $b_mt);	
		$a_clid = $a_mt[1];
		$a_prop = $a_mt[2];

		$b_clid = $b_mt[1];
		$b_prop = $b_mt[2];

		if ($a_clid == "sep")
		{
			list(, $a_prop) = explode("_", $a_prop);
			$a_ord = $this->__sort_ord[$a_clid][$a_prop];
		}
		else
		{
			$a_ord = $this->__sort_ord[$a_clid][$a_prop];
		}

		if ($b_clid == "sep")
		{
			list(, $b_prop) = explode("_", $b_prop);
			$b_ord = $this->__sort_ord[$b_clid][$b_prop];
		}
		else
		{
			$b_ord = $this->__sort_ord[$b_clid][$b_prop];
		}

		if ($a_ord == $b_ord)
		{
			return 0;
		}
		return ($a_ord < $b_ord) ? -1 : 1;
	}

	function _do_final_sort_props(&$ob, &$tp)
	{
		$this->__sort_ord = $ob->meta("ord");
		uasort($tp, array(&$this, "__prop_sorter"));
	}

	function callback_get_jm_texts($arr)
	{
		$ret = array();

		$la = get_instance("languages");
		$ll = $la->listall();

		$jmt = $arr["obj_inst"]->meta("jm_texts");

		foreach($ll as $lid => $ldata)
		{
			$name3 = "lm_l_tx[".$lid."][from]";
			$tmp3 = array(
				"name" => $name3,
				"type" => "textbox",
				"table" => "objects",
				"field" => "meta",
				"method" => "serialize",
				"caption" => "Liitumise meili from aadress (".$ldata["name"].")",
				"value" => $jmt[$lid]["from"]
			);

			$name = "lm_l_tx[".$lid."][subj]";
			$tmp = array(
				"name" => $name,
				"type" => "textbox",
				"table" => "objects",
				"field" => "meta",
				"method" => "serialize",
				"caption" => "Liitumise meili subjekt (".$ldata["name"].")",
				"value" => $jmt[$lid]["subj"]
			);

			$name2 = "lm_l_tx[".$lid."][text]";
			$tmp2 = array(
				"name" => $name2,
				"type" => "textarea",
				"rows" => 10,
				"cols" => 50,
				"table" => "objects",
				"field" => "meta",
				"method" => "serialize",
				"caption" => "Liitumise meil (".$ldata["name"].")",
				"value" => $jmt[$lid]["text"]
			);

			$name4 = "lm_l_tx_".$lid."_sep";
			$tmp4 = array(
				"name" => $name4,
				"type" => "text",
				"store" => "no",
				"no_caption" => 1,
				"value" => "<hr>"
			);

			$ret[$name3] = $tmp3;
			$ret[$name] = $tmp;
			$ret[$name2] = $tmp2;
			$ret[$name4] = $tmp4;
		}

		return $ret;
	}

	function _do_send_join_mail($arr)
	{
		$jms = $arr["obj"]->meta("jm_texts");

		$from = $jms[aw_global_get("lang_id")]["from"];
		$subj = $jms[aw_global_get("lang_id")]["subj"];
		$text = $jms[aw_global_get("lang_id")]["text"];

		$us = get_instance("users");
		$cp = $us->get_change_pwd_hash_link($arr["uid"]);

		$text = str_replace("#parool#", $arr["pass"], $text);
		$text = str_replace("#kasutaja#", $arr["uid"], $text);
		$text = str_replace("#pwd_hash#", $cp, $text);

		send_mail($arr["email"],$subj,$text,"From: ".$from);
	}
}
?>
