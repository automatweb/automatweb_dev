<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/search_v2.aw,v 1.4 2005/03/14 17:27:28 kristo Exp $

/*
@default group=search
@default form=search

@property stoolbar type=toolbar group=search,advsearch no_caption=1
@caption Toolbar

@property server type=select group=advsearch
@caption Server

@property name type=textbox group=search,advsearch
@caption Nimi

@property comment type=textbox group=advsearch
@caption Kommentaar

@property class_id type=select multiple=1 size=10 group=search,advsearch
@caption Tüüp

@property oid type=textbox group=search,advsearch
@caption OID

@property createdby type=textbox group=search,advsearch
@caption Looja

@property modifiedby type=textbox group=search,advsearch
@caption Muutja

@property status type=chooser  group=advsearch
@caption Staatus

@property alias type=textbox group=advsearch
@caption Alias

@property lang_id type=chooser group=advsearch
@caption Keel

@property site_id type=select group=advsearch
@caption Saidi ID

@property search_bros type=checkbox ch_value=1 group=advsearch
@caption Otsi vendi

@property sbt type=submit group=search,advsearch
@caption Otsi

@property result_table type=table no_caption=1 group=search,advsearch
@caption Otsingutulemused

@groupinfo search caption="Lihtne otsing"
@groupinfo advsearch caption="Põhjalik otsing"


@forminfo search onload=init_search onsubmit=search method=get

*/

class search_v2 extends class_base
{
	function search_v2()
	{
		$this->init();
	}

	function init_search($arr)
	{
		$this->do_search = false;
		$parts = array();
		$string_fields = array("name","createdby","modifiedby","comment","alias");
		$numeric_fields = array("oid","status","lang_id");
		foreach($string_fields as $string_field)
		{
			if (!empty($arr[$string_field]))
			{
				$parts[$string_field] = "%" . $arr[$string_field] . "%";

			};
		};

		foreach($numeric_fields as $numeric_field)
		{
			if (!empty($arr[$numeric_field]))
			{
				$parts[$numeric_field] = $arr[$numeric_field];
			};
		};

		if (!empty($arr["class_id"]))
		{
			if (!empty($arr["class_id"][0]))
			{
				$parts["class_id"] = $arr["class_id"];
			};
		};

		$this->server_id = false;
		if (!empty($arr["server"]))
		{
			$this->server_id = $arr["server"];
			$this->do_search = true;
		};

		$this->qparts = $parts;
		if (sizeof($parts) > 0)
		{
			$this->do_search = true;
		};
	}

	/** generates search form
		
		@attrib name=search default="1" all_args="1"

	**/
	function search($arr)
	{
		$arr["form"] = "search";
		$arr["group"] = !empty($arr["group"]) ? $arr["group"] : "search";
		if (!isset($arr["action"]))
		{
			$arr["action"] = "";
		};
		return $this->change($arr);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$prop["value"] = $arr["request"][$prop["name"]];
		switch($prop["name"])
		{
			case "stoolbar":
				$toolbar = &$prop["vcl_inst"];
				$toolbar->add_button(array(
					"name" => "search",
					"tooltip" => t("Otsi"),
					"url" => "javascript:document.changeform.submit()",
					"img" => "search.gif",
				));
				$toolbar->add_separator();
				$toolbar->add_button(array(
					"name" => "cut",
					"tooltip" => t("Lõika"),
					"action" => "cut",
					"img" => "cut.gif",
				));
				$toolbar->add_button(array(
					"name" => "copy",
					"tooltip" => t("Kopeeri"),
					"action" => "copy",
					"img" => "copy.gif",
				));
				$toolbar->add_button(array(
					"name" => "delete",
					"tooltip" => t("Kustuta"),
					"action" => "delete",
					"img" => "delete.gif",
				));
	
				break;

			case "server":
				$ol = new object_list(array(
					"class_id" => CL_AW_LOGIN,
					"site_id" => array(),
					"lang_id" => array()
				));
                        	$prop["options"] =  array("" => "") + $ol->names();
				break;

			case "class_id":
				$prop["options"] = $this->_get_s_class_id();


				break;

			case "status":
				$prop["options"] = array(
					"3" => t("Kõik"),
					"2" => t("Aktiivsed"),
					"1" => t("Deaktiivsed"),
				);
				break;

			case "lang_id":
				$lg = get_instance("languages");
                        	$prop["options"] = $lg->get_list(array("ignore_status" => true));
				break;
	

			case "result_table":
				if ($this->do_search)
				{
					$this->do_search($arr);
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
		};
		return PROP_OK;
	}

	function do_search($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "icon",
			"caption" => "",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "lang_id",
			"caption" => t("Keel"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("Tüüp"),
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"type" => "time",
			"format" => "d.m.y / H:i",
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"type" => "time",
			"format" => "d.m.y / H:i",
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
			"caption" => t("Vali"),
		));

		classload("core/icons");

		if ($this->do_search)
		{
			$s_args = array(
				"lang_id" => array(),
				"site_id" => array(),
			);
			foreach($this->qparts as $qkey => $qvalue)
			{
				$s_args[$qkey] = $qvalue;
			};

			// 3 - ignore status
			if ($s_args["status"] == 3)
			{
				unset($s_args["status"]);
			};

			$s_args["limit"] = 500;

			$_tmp = $this->_search_mk_call("objects", "storage_query", $s_args);

			//arr($_tmp);

			$clinf = aw_ini_get("classes");

			foreach($_tmp as $id => $item)
			{
				$type = $clinf[$item["class_id"]]["name"];
				$icon = sprintf("<img src='%s' alt='$type' title='$type'>",icons::get_icon_url($item["class_id"]));
				$t->define_data(array(
					"name" => html::href(array(
						"caption" => $item["name"],
						"url" => $this->mk_my_orb("change",array("id" => $id),$item["class_id"]),
					)),
					"lang_id" => $item["lang_id"],
					"oid" => $id,
					"icon" => $icon,
					"created" => $item["created"],
					"createdby" => $item["createdby"],
					"modifiedby" => $item["modifiedby"],
					"modified" => $item["modified"],
					"class_id" => $clinf[$item["class_id"]]["name"],
					"location" => $item["path_str"],
				));
				//$o_data = $this->_search_mk_call("objects","get_object",array("id" => $item));
				//arr($o_data);
			};
		};

	}
	
	// generates contents for the class picker drop-down menu
	function _get_s_class_id()
	{
		$tar = array(0 => LC_OBJECTS_ALL) + get_class_picker(array(
			"only_addable" => 1
		));

		$atc_inst = get_instance("admin/add_tree_conf");
		$atc_id = $atc_inst->get_current_conf();
		if (is_oid($atc_id) && $this->can("view", $atc_id))
		{
			$atc = obj($atc_id);

			$tmp = array();
			foreach($tar as $clid => $cln)
			{
				if ($atc_inst->can_access_class($atc, $clid))
				{
					$tmp[$clid] = $cln;
				}
			}
			$tar = $tmp;
		}

		return $tar;
	}

	function _search_mk_call($class, $action, $params)
        {
                $_parms = array(
                        "class" => $class,
                        "action" => $action,
                        "params" => $params
                );
		if ($this->server_id)
                {
                        $_parms["method"] = "xmlrpc";
                        $_parms["login_obj"] = $this->server_id;
                }
                $ret =  $this->do_orb_method_call($_parms);
                return $ret;
        }



};
?>
