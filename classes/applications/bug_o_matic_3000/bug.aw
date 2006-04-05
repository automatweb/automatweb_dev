<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug.aw,v 1.28 2006/04/05 09:19:47 kristo Exp $
//  bug.aw - Bugi 

define("BUG_STATUS_CLOSED", 5);

/*

@classinfo syslog_type=ST_BUG relationmgr=yes no_comment=1 no_status=1 r2=yes

@tableinfo aw_bugs index=aw_id master_index=brother_of master_table=objects

@default group=general
@default table=aw_bugs

@property name type=textbox table=objects
@caption Lühikirjeldus

@layout settings type=hbox
@caption Määrangud

	@layout settings_col1 type=vbox parent=settings
		@property bug_status type=select parent=settings_col1 captionside=top
		@caption Staatus

		@property bug_priority type=select parent=settings_col1 captionside=top
		@caption Prioriteet

		@property who type=crm_participant_search style=relpicker reltype=RELTYPE_MONITOR table=aw_bugs field=who parent=settings_col1 captionside=top
		@caption Kellele


	@layout settings_col2 type=vbox parent=settings


		@property bug_type type=classificator store=connect reltype=RELTYPE_BUGTYPE parent=settings_col2 captionside=top
		@caption T&uuml;&uuml;p

		@property bug_class type=select parent=settings_col2 captionside=top
		@caption Klass

		@property bug_severity type=select parent=settings_col2 captionside=top
		@caption T&ouml;sidus


	
	@layout settings_col3 type=vbox parent=settings

		@property monitors type=relpicker reltype=RELTYPE_MONITOR multiple=1 size=4 store=connect parent=settings_col3 captionside=top
		@caption J&auml;lgijad

		@property deadline type=date_select default=-1 parent=settings_col3 captionside=top
		@caption T&auml;htaeg

@property bug_url type=textbox size=100 
@caption URL

@layout content type=hbox width=20%:80%
@caption Sisu

	@layout bc type=vbox parent=content

		@property bug_content type=textarea rows=23 cols=80 parent=bc
		@caption Sisu

		@property bug_content_comm type=textarea rows=10 cols=80 parent=bc store=no editonly=1
		@caption Lisa kommentaar

	@layout data type=vbox parent=content

		@property num_hrs_guess type=textbox size=5 parent=data captionside=top
		@caption Prognoositav tundide arv 	

		@property num_hrs_real type=textbox size=5 parent=data captionside=top
		@caption Tegelik tundide arv

		@property num_hrs_to_cust type=textbox size=5 parent=data captionside=top
		@caption Tundide arv kliendile

		@property customer type=relpicker reltype=RELTYPE_CUSTOMER parent=data captionside=top
		@caption Klient

		@property project type=relpicker reltype=RELTYPE_PROJECT  parent=data captionside=top
		@caption Projekt

		@property bug_component type=textbox parent=data captionside=top
		@caption Komponent

		@property bug_mail type=textbox parent=data captionside=top
		@caption Bugmail CC
	
		@property fileupload type=releditor reltype=RELTYPE_FILE rel_id=first use_form=emb parent=data captionside=top
		@caption Fail
	
		@property bug_property type=select parent=data captionside=top field=aw_bug_property
		@caption Klassi omadus

@reltype MONITOR value=1 clid=CL_CRM_PERSON
@caption Jälgija

@reltype FILE value=2 clid=CL_FILE
@caption Fail

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt

@reltype BUGTYPE value=5 clid=CL_META
@caption Bugi t&uuml;&uuml;p

@reltype COMMENT value=6 clid=CL_BUG_COMMENT
@caption Kommentaar
*/

define("BUG_OPEN", 1);
define("BUG_INPROGRESS", 2);
define("BUG_DONE", 3);
define("BUG_TESTED", 4);
define("BUG_CLOSED", 5);
define("BUG_INCORRECT", 6);
define("BUG_NOTREPEATABLE", 7);
define("BUG_NOTFIXABLE", 8);
define("BUG_WONTFIX", 9);
define("BUG_FEEDBACK", 10);

class bug extends class_base
{
	function bug()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug",
			"clid" => CL_BUG
		));

		$this->bug_statuses = array(
			BUG_OPEN => t("Lahtine"),
			BUG_INPROGRESS => t("Tegemisel"),
			BUG_DONE => t("Valmis"),
			BUG_TESTED => t("Testitud"),
			BUG_CLOSED => t("Suletud"),
			BUG_INCORRECT => t("Vale teade"),
			BUG_NOTREPEATABLE => t("Kordamatu"),
			BUG_NOTFIXABLE => t("Parandamatu"),
			BUG_WONTFIX => t("Ei paranda"),
			BUG_FEEDBACK => t("Vajab tagasisidet")
		);
	}

	function callback_on_load($arr)
	{
		$this->cx = get_instance("cfg/cfgutils");
		if($this->can("add", $arr["request"]["parent"]) && $arr["request"]["action"] == "new")
		{
			$parent = new object($arr["request"]["parent"]);
			$props = $parent->properties();
			$cx_props = $this->cx->load_properties(array(
				"clid" => $parent->class_id(),
				"filter" => array(
					"group" => "general",
				),
			));
			$this->parent_options = array();
			$els = array("who", "monitors", "project", "customer");
			foreach($els as $el)
			{
				$this->parent_options[$el] = array();
				$objs = $parent->connections_from(array(
					"type" => $cx_props[$el]["reltype"],
				));
				foreach($objs as $obj)
				{
					$this->parent_options[$el][$obj->prop("to")] = $obj->prop("to.name");
				}
			}
			$this->parent_data = array(
				"who" => $props["who"],
				"bug_class" => $props["bug_class"],
				"monitors" => $props["monitors"],
				"project" => $props["project"],
				"customer" => $props["customer"],
//				"deadline" => $props["deadline"],
			);
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		if($arr["new"] && !empty($this->parent_data[$prop["name"]]))
		{ 
			$prop["value"] = $this->parent_data[$prop["name"]];
		}
		switch($prop["name"])
		{
			case "name":
				$prop["post_append_text"] = " #".$arr["obj_inst"]->id()." ".sprintf(t("Vaade avatud: %s"), date("d.m.Y H:i"));
				break;

			case "bug_content":
				if (!$arr["new"])
				{
					$prop["value"] = "<br>".$this->_get_comment_list($arr["obj_inst"])."<br>";
					$prop["type"] = "text";
				}
				break;

			case "bug_status":	
				$prop["options"] = $this->bug_statuses;
				break;

			case "bug_priority":
			case "bug_severity":
				$prop["options"] = $this->get_priority_list();
				break;
				
			case "who":
			case "monitors":
				if($arr["new"])
				{
					foreach($this->parent_options[$prop["name"]] as $key => $val)
					{
						$tmp[$key] = $val;
					}
					// also, the current person
					$u = get_instance(CL_USER);
					$p = obj($u->get_current_person());
					$tmp[$p->id()] = $p->name();
					if ($prop["multiple"] == 1)
					{
						$prop["value"] = $this->make_keys(array_keys($tmp));
					}
					$prop["options"] = array("" => t("--vali--")) + $tmp;
				}
				if ($this->can("view", $prop["value"]) && !isset($prop["options"][$prop["value"]]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				break;

			case "bug_class":
				$prop["options"] = array("" => "") + $this->get_class_list();
				break;

			case "project":
				if (is_object($arr["obj_inst"]) && $this->can("view", $arr["obj_inst"]->prop("customer")))
				{
					$filt = array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_ORDERER" => $arr["obj_inst"]->prop("customer"),
					);
					$ol = new object_list($filt);
				}
				else
				{
					$i = get_instance(CL_CRM_COMPANY);
					$prj = $i->get_my_projects();
					if (!count($prj))
					{
						$ol = new object_list();
					}
					else
					{
						$ol = new object_list(array("oid" => $prj));
					}
				}

				$prop["options"] = array("" => t("--vali--")) + $ol->names();

				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}

				if($arr["new"])
				{
					foreach($this->parent_options[$prop["name"]] as $key => $val)
					{
						$prop["options"][$key] = $val;
					}
				}
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$cst = $i->get_my_customers();
				if (!count($cst))
				{
					$prop["options"] = array("" => t("--vali--"));
				}
				else
				{
					$ol = new object_list(array("oid" => $cst));
					$prop["options"] = array("" => t("--vali--")) + $ol->names();
				}

				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}

				if($arr["new"])
				{
					foreach($this->parent_options[$prop["name"]] as $key => $val)
					{
						$prop["options"][$key] = $val;
					}
				}
				break;

			case "num_hrs_real":
				$url = $this->mk_my_orb("stopper_pop", array(
					"id" => $arr["obj_inst"]->id(),
					"s_action" => "start",
					"type" => t("Bug"),
					"name" => $arr["obj_inst"]->name()
				), CL_TASK);
				$prop["post_append_text"] = " <a href='javascript:void(0)' onClick='aw_popup_scroll(\"$url\",\"aw_timers\",320,400)'>".t("Stopper")."</a>";
				break;

			case "bug_url":
				$prop["post_append_text"] = "<a href='javascript:void(0)' onClick='window.location=document.changeform.bug_url.value'>Ava</a>";
				break;

			case "bug_property":
				if ($arr["obj_inst"]->prop("bug_class"))
				{
					$prop["options"] = $this->_get_property_picker($arr["obj_inst"]->prop("bug_class"));
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
			case "bug_content":
				if (!$arr["new"])
				{
					$prop["value"] = $arr["obj_inst"]->prop("bug_content");
				}
				break;

			case "bug_content_comm":
				if (trim($prop["value"]) != "")
				{
					// save comment
					//$this->_add_comment($arr["obj_inst"], $prop["value"]);
					$this->add_comments[] = $prop["value"];
				}
				break;
				
			case "bug_status":
				if($prop["value"] == BUG_STATUS_CLOSED && !$arr["new"])
				{
					if(aw_global_get("uid") != $arr["obj_inst"]->createdby())
					{
						$retval = PROP_FATAL_ERROR;
						$prop["error"] = t("Puuduvad õigused bugi sulgeda!");
					}
				}

				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"])
				{
					$com = sprintf(t("Staatus muudeti %s => %s"), $this->bug_statuses[$old], $this->bug_statuses[$prop["value"]]);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;

			case "bug_priority":
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"])
				{
					$com = sprintf(t("Prioriteet muudeti %s => %s"), $old, $prop["value"]);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;

			case "num_hrs_real":
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"])
				{
					$com = sprintf(t("Tegelik tundide arv muudeti %s => %s"), $old, $prop["value"]);
					$this->add_comments[] = $com;
				}
				break;

			case "who":
				$nv = "";
				if ($this->can("view", $prop["value"]))
				{
					$nvo = obj($prop["value"]);
					$nv = $nvo->name();
				}

				if (($old = $arr["obj_inst"]->prop_str($prop["name"])) != $nv)
				{
					$com = sprintf(t("Kellele muudeti %s => %s"), $old, $nv);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;

			case "bug_class":
				$clss = aw_ini_get("classes");
				$old = $clss[(int)$arr["obj_inst"]->prop($prop["name"])]["name"];
				$nv = $clss[(int)$prop["value"]]["name"];
				if ($old != $nv)
				{
					$com = sprintf(t("Klass muudeti %s => %s"), $old, $nv);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;
		}
		return $retval;
	}	

	function notify_monitors($bug, $comment)
	{
		$monitors = $bug->prop("monitors");

		// if the status is right, then add the creator of the bug to the list
		$states = array(
			BUG_TESTED,
			BUG_INCORRECT,
			BUG_NOTREPEATABLE,
			BUG_NOTFIXABLE,
			BUG_WONTFIX,
			BUG_FEEDBACK
		);
		$u = get_instance(CL_USER);
		$us = get_instance("users");
		if (in_array($bug->prop("bug_status"), $states))
		{
			$crea = $bug->createdby();
			$monitors[] = $u->get_person_for_user(obj($us->get_oid_for_uid($crea)));
		}

		// I should add a way to send CC-s to arbitraty e-mail addresses as well
		foreach(array_unique($monitors) as $person)
		{
			if(!$this->can("view", $person))
			{
				continue;
			}
			$person_obj = obj($person); 
			// don't send to the current user, cause, well, he knows he's just done it. 
			if ($person == $u->get_current_person())
			{
				continue;
			}
			$email = $person_obj->prop("email");
			$notify_addresses = array();
			if (is_oid($email))
			{
				$email_obj = new object($email);
				$addr = $email_obj->prop("mail");
				if (is_email($addr))
				{
					$notify_addresses[] = $addr;
				};
			};
		};

		$addrs = explode(",",$bug->prop("bug_mail"));
		foreach($addrs as $addr)
		{
			if (is_email($addr))
			{
				$notify_addresses[] = $addr;
			}; 
		};
		if (sizeof($notify_addresses) == 0)
		{
			return false;
		};

		$notify_list = join(",",$notify_addresses);

		$oid = $bug->id();
		$name = $bug->name();
		$uid = aw_global_get("uid");

		$msgtxt = t("Bug") . ": " . $this->mk_my_orb("change",array("id" => $oid)) . "\n";
		$msgtxt .= t("Summary") . ": " . $name . "\n";
		$msgtxt .= t("URL") . ": " . $bug->prop("bug_url") . "\n";
		$msgtxt .= "-------------\n\nNew comment from " . $uid . " at " . date("Y-m-d H:i") . "\n";
		$msgtxt .= strip_tags($comment)."\n";
		$msgtxt .= strip_tags($this->_get_comment_list($bug, "desc", false));

		send_mail($notify_list,"Bug #" . $oid . ": " . $name . " : " . $uid . " lisas kommentaari",$msgtxt,"From: automatweb@automatweb.com");
	}

	function get_sort_priority($bug)
	{
		$sp_lut = array(
			BUG_OPEN => 100,
			BUG_INPROGRESS => 110,
			BUG_DONE => 70,
			BUG_TESTED => 60,
			BUG_CLOSED => 50,
			BUG_INCORRECT => 40,
			BUG_NOTREPEATABLE => 40,
			BUG_NOTFIXABLE => 40
		);
		$rv = $sp_lut[$bug->prop("bug_status")] + $bug->prop("bug_priority");
		// also, if the bug has a deadline, then we need to up the priority as the deadline comes closer
		if (($dl = $bug->prop("deadline")) > 200)
		{
			// deadline in the next 24 hrs = +3
			if ($dl < (time() - 24*3600))
			{
				$rv++;
			}
			// deadline in the next 48 hrs +2
			if ($dl < (time() - 48*3600))
			{
				$rv++;
			}
			// has deadline = +1
			$rv++;
		}


		$rv += (double)1.0/((double)10000.0 - (double)$bug->id());		

		return $rv;
	}

	function handle_stopper_stop($bug, $inf)
	{
		$inf["desc"] .= sprintf(t("\nTegelik tundide arv muudeti %s => %s"), $bug->prop("num_hrs_real"), $bug->prop("num_hrs_real")+$inf["hours"]);

		$bug->set_prop("num_hrs_real", $bug->prop("num_hrs_real") + $inf["hours"]);
		$bug->save();

		if (trim($inf["desc"]) != "")
		{
			$this->_add_comment($bug, $inf["desc"]);
		}
	}

	function _get_comment_list($o, $so = "asc", $nl2br = true)
	{
		$this->read_template("comment_list.tpl");

		$ol = new object_list(array(
			"class_id" => CL_BUG_COMMENT,
			"parent" => $o->id(),
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "objects.created $so"
		));
		$com_str = "";
		foreach($ol->arr() as $com)
		{
			$comt = $com->comment();
			$comt = preg_replace("/(http:\/\/dev.struktuur.ee\/cgi-bin\/viewcvs\.cgi\/[^<\n]*)/ims", "<a href='\\1'>Diff</a>", $comt);

			if ($nl2br)
			{
				$comt = nl2br($comt);
			}
			$this->vars(array(
				"com_adder" => $com->createdby(),
				"com_date" => date("d.m.Y H:i", $com->created()),
				"com_text" => $comt
			));
			$com_str .= $this->parse("COMMENT");
		}

		$main_c = nl2br($o->prop("bug_content"));
		$this->vars(array(
			"main_text" => $so == "asc" ? $main_c : "",
			"main_text_after" => $so == "asc" ? "" : $main_c,
			"COMMENT" => $com_str
		));
		return $this->parse();
	}

	function _add_comment($bug, $comment)
	{
		if (!is_oid($bug->id()))
		{
			return;
		}
		// email any persons interested in status changes of that bug
		$this->notify_monitors($bug, $comment);

		$o = obj();
		$o->set_parent($bug->id());
		$o->set_class_id(CL_BUG_COMMENT);
		$o->set_comment(trim($comment));
		$o->save();

		$bug->connect(array(
			"to" => $o->id(),
			"type" => "RELTYPE_COMMENT"
		));
	}

	function get_priority_list()
	{
		$res = array();
		$res[1] = "1 (Madalaim)";
		$res[2] = "2";
		$res[3] = "3";
		$res[4] = "4";
		$res[5] = "5 (K&otilde;rgeim)";
		return $res;
	}

	function get_status_list()
	{
		return $this->bug_statuses;
	}

	function get_class_list()
	{
		$ret = array();
		$this->cx = get_instance("cfg/cfgutils");
		$class_list = new aw_array($this->cx->get_classes_with_properties());
		$cp = get_class_picker(array("field" => "def"));
				
		$prop["options"][0] = "";
		foreach($class_list->get() as $key => $val)
		{
			$ret[$key] = $val;
		};
		return $ret;
	}

	function callback_pre_save($arr)
	{
		if (is_array($this->add_comments) && count($this->add_comments))
		{
			$this->_add_comment($arr["obj_inst"], join("\n", $this->add_comments));
		}
	}
	
	function callback_post_save($arr)
	{
		if ($arr["new"])
		{
			$this->notify_monitors($arr["obj_inst"], $arr["obj_inst"]->prop("bug_content"));
		}
	}

	/**
		@attrib name=handle_commit nologin=1
		@param bugno required type=int 
		@param msg optional
		@param set_fixed optional
		@param time_add optional
	**/
	function handle_commit($arr)
	{
		aw_disable_acl();
		$bug = obj($arr["bugno"]);
		$msg = trim($this->hexbin($arr["msg"]));
		if ($arr["set_fixed"] == 1)
		{
			$msg .= "\nStaatus muudeti ".$this->bug_statuses[$bug->prop("bug_status")]." => ".$this->bug_statuses[BUG_DONE]."\n";
			$bug->set_prop("bug_status", BUG_DONE);
			$save = true;
		}

		if ($arr["time_add"])
		{
			$ta = $arr["time_add"];
			// parse time
			$hrs = 0;
			if ($ta[strlen($ta)-1] == "m")
			{
				$hrs = ((double)$ta) / 60.0;
			}
			else
			{
				$hrs = (double)$ta;
			}
			// round to 0.25
			$hrs = floor($hrs * 4.0+0.5)/4.0;
			$msg .= sprintf(t("\nTegelik tundide arv muudeti %s => %s"), $bug->prop("num_hrs_real"), $bug->prop("num_hrs_real")+$hrs);
			$bug->set_prop("num_hrs_real", $bug->prop("num_hrs_real") + $hrs);
			$save = true;
		}
		if ($save)
		{
			$bug->save();
		}

		$this->_add_comment($bug, $msg);
		aw_restore_acl();
		die(sprintf(t("Added comment to bug %s"), $arr[bugno]));
	}

	function do_db_upgrade($tbl, $f)
	{
		switch($f)
		{
			case "aw_bug_property":
				$this->db_add_col($tbl, array(
					"name" => $f,
					"type" => "varchar",
					"length" => 255
				));
				break;
		}
	}

	function _get_property_picker($clid)
	{
		$o = obj();
		$o->set_class_id($clid);
		$ret = array("" => "");
		$props = $o->get_property_list();
		foreach($o->get_group_list() as $gn => $gc)
		{
			$ret["grp_".$gn] = $gc["caption"];
			foreach($props as $pn => $pd)
			{
				if ($pd["group"] == $gn)
				{
					$ret["prop_".$pn] = "&nbsp;&nbsp;&nbsp;".$pd["caption"];
				}
			}
		}
		return $ret;
	}

	function request_execute($o)
	{
		header("Location: ".$this->mk_my_orb("change", array("id" => $o->id()), "bug", true));
		die();
	}
}
?>
