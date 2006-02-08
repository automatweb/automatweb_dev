<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug.aw,v 1.10 2006/02/08 13:49:38 tarvo Exp $
// bug.aw - Bugi 
/*

@classinfo syslog_type=ST_BUG relationmgr=yes no_comment=1 no_status=1 r2=yes

@tableinfo aw_bugs index=aw_id master_index=brother_of master_table=objects

@default group=general

@property name type=textbox table=objects
@caption Lühikirjeldus

@default table=aw_bugs
@property bug_content type=textarea rows=10 cols=80
@caption Sisu

@property bug_type type=classificator table=objects field=meta method=serialize
@caption T&uuml;&uuml;p

@property bug_status type=select
@caption Staatus

@property customer type=relpicker reltype=RELTYPE_CUSTOMER
@caption Klient

@property project type=relpicker reltype=RELTYPE_PROJECT
@caption Projekt

@property deadline type=date_select default=-1
@caption T&auml;htaeg

@property who type=relpicker reltype=RELTYPE_MONITOR
@caption Kellele

@property bug_priority type=select
@caption Prioriteet

@property bug_severity type=select
@caption T&ouml;sidus

//////// inf 
property reporter_browser type=classificator
caption Brauser

property reporter_os type=classificator
caption OS

@property bug_class type=select
@caption Klass

@property fileupload type=releditor reltype=RELTYPE_FILE rel_id=first use_form=emb
@caption Fail

@property bug_component type=textbox 
@caption Komponent

@property bug_url type=textbox size=100
@caption URL

@property bug_mail type=textbox size=60
@caption Bugmail CC

@property monitors type=relpicker reltype=RELTYPE_MONITOR multiple=1 size=5 method=serialize
@caption J&auml;lgijad

@property comms type=comments group=comments store=no
@caption Kommentaarid

@groupinfo comments caption="Kommentaarid"

@reltype MONITOR value=1 clid=CL_CRM_PERSON
@caption Jälgija

@reltype FILE value=2 clid=CL_FILE
@caption Fail

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt
*/

class bug extends class_base
{
	function bug()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug",
			"clid" => CL_BUG
		));
	}

	function callback_on_load($arr)
	{
		if($this->can("add", $arr["request"]["parent"]) && $arr["request"]["action"] == "new")
		{
			$parent = new object($arr["request"]["parent"]);
			$props = $parent->properties();
			$this->parent_data = array(
				"who" => $props["who"],
				"bug_class" => $props["bug_class"],
				"monitors" => $props["monitors"],
				"project" => $props["project"],
				"customer" => $props["customer"],
				"deadline" => $props["deadline"],
			);
		}
	}

	function get_property($arr)
	{
		//arr($arr["new"]);
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "bug_status":	
				$prop["options"] = array(
					1 => t("Uus"),
					2 => t("Tegemisel"),
					3 => t("Valmis"),
					4 => t("Suletud"),
					5 => t("Vale teade"),
					6 => t("Kordamatu"),
					7 => t("Parandamatu"),
				);
				break;

			case "bug_priority":
			case "bug_severity":
				foreach(range(5, 1) as $r)
				{
					$prop["options"][$r] = $r;
				}
				break;

			case "bug_class":
				$cx = get_instance("cfg/cfgutils");
				$class_list = new aw_array($cx->get_classes_with_properties());
				$cp = get_class_picker(array("field" => "def"));
				
				$prop["options"][0] = "";
				foreach($class_list->get() as $key => $val)
				{
					$prop["options"][$key] = $val;
				};	
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

				$data["options"] = array("" => "") + $ol->names();

				if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
				{
					foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PROJECT")) as $c)
					{
						$data["options"][$c->prop("to")] = $c->prop("to.name");
					}
				}

				if (!isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$tmp->id()] = $tmp->name();
				}

				asort($data["options"]);
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$cst = $i->get_my_customers();
				if (!count($cst))
				{
					$data["options"] = array("" => "");
				}
				else
				{
					$ol = new object_list(array("oid" => $cst));
					$data["options"] = array("" => "") + $ol->names();
				}

				if (is_object($arr["obj_inst"]) && !$arr["new"])
				{
					foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
					{
						$data["options"][$c->prop("to")] = $c->prop("to.name");
					}
				}

				if (!isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$tmp->id()] = $tmp->name();
				}

				asort($data["options"]);
				if (is_object($arr["obj_inst"]) && $arr["obj_inst"]->class_id() == CL_TASK)
				{
					$arr["obj_inst"]->set_prop("customer", $data["value"]);
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
			case "comms":
				// email any persons interested in status changes of that bug
				$this->notify_monitors($arr);
				break;
			case "bug_status":
				if($prop["value"] == 4 && !$arr["new"])
				{
					if(aw_global_get("uid") != $arr["obj_inst"]->createdby())
					{
						$retval = PROP_FATAL_ERROR;
						$prop["error"] = t("Puuduvad õigused bugi sulgeda!");
					}
				}
				break;
		}
		return $retval;
	}	

	function notify_monitors($arr)
	{
		$monitors = $arr["obj_inst"]->prop("monitors");
		/*
		$monitors = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_MONITOR",
		));
		*/
		// I should add a way to send CC-s to arbitraty e-mail addresses as well
		foreach($monitors as $person)
		{
			if(!$this->can("view", $person))
			{
				continue;
			}
			//$person_obj = $person->to();
			$person_obj = obj($person); 
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

		$addrs = explode(",",$arr["obj_inst"]->prop("bug_mail"));
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

		$oid = $arr["obj_inst"]->id();
		$name = $arr["obj_inst"]->name();
		$uid = aw_global_get("uid");

		$msgtxt = t("Bug") . ": " . $oid . "\n";
		$msgtxt .= t("Summary") . ": " . $name . "\n";
		$msgtxt .= t("URL") . ": " . $this->mk_my_orb("change",array("id" => $oid)) . "\n";
		$msgtxt .= "-------------\n\nNew comment from " . $uid . " at " . date("Y-m-d H:i") . "\n";
		$msgtxt .= $arr["request"]["comms"]["comment"];

		send_mail($notify_list,"Bug #" . $oid . ": " . $name . " : " . $uid . " lisas kommentaari",$msgtxt,"From: automatweb@automatweb.com");
	}
/*
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
	*/
}
?>
