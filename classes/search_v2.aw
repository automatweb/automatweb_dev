<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/search_v2.aw,v 1.1 2005/01/21 12:50:31 duke Exp $

/*
@default group=search
@default form=search

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
		if (!empty($arr["name"]))
		{
			$parts["name"] = "%" . $arr["name"] . "%";

		};

		if (!empty($arr["class_id"]))
		{
			if (!empty($arr["class_id"][0]))
			{
				$parts["class_id"] = $arr["class_id"];
			};
		};


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
		$arr["group"] = "search";
		return $this->change($arr);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$prop["value"] = $arr["request"][$prop["name"]];
		switch($prop["name"])
		{
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
					"3" => "Kõik",
					"2" => "Aktiivsed",
					"1" => "Deaktiivsed",
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
		));
		$t->define_field(array(
			"name" => "icon",
			"caption" => "",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		$t->define_field(array(
			"name" => "lang_id",
			"caption" => "Keel",
		));
		$t->define_field(array(
			"name" => "class_id",
			"caption" => "Tüüp",
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => "Asukoht",
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => "Loodud",
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => "Looja",
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
		));

		if ($this->do_search)
		{
			$s_args = array(
				"lang_id" => array(),
				"site_id" => array(),
			);
			if ($this->qparts["name"])
			{
				$s_args["name"] = $this->qparts["name"];
			};
			if ($this->qparts["class_id"])
			{
				$s_args["class_id"] = $this->qparts["class_id"];
			};
			$s_args["limit"] = 500;

			$_tmp = $this->_search_mk_call("objects", "storage_query", $s_args);

			//arr($_tmp);

			foreach($_tmp as $id => $item)
			{
				$t->define_data(array(
					"name" => $item["name"],
					"oid" => $id,
					"created" => $item["created"],
					"createdby" => $item["createdby"],
					"modifiedby" => $item["modifiedby"],
					"modified" => $item["modified"],
					"class_id" => $item["class_id"],
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



}
?>
