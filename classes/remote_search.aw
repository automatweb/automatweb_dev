<?php

class remote_search extends aw_template
{
	function remote_search()
	{
		$this->init("remote_search");
	}


	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa remote otsing");
		}
		else
		{
			$this->mk_path($parent,"Lisa remote otsing");
		}
		$this->read_template("search.tpl");

		$search = get_instance("search");
		$arr["clid"] = &$this; 
		$this->vars(array(
			"search" => $search->show($arr),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"login_objs" => $this->picker($login_obj, $this->list_objects(array("class" => CL_AW_LOGIN))),
		));
		return $this->parse();
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_REMOTE_SEARCH
			));
		}

		$s["login_obj"] = $login_obj;
		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => $s
		));

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		if (is_array($sel))
		{
			$copied_objects = aw_global_get("copied_objects");
			foreach($sel as $oid)
			{
				$copied_objects[$oid] = serialize($this->do_orb_method_call(array(
					"class" => "objects",
					"action" => "serialize",
					"params" => array("oid" => $oid),
					"method" => "xmlrpc",
					"login_obj" => $login_obj
				)));
			}
			aw_session_set("copied_objects",$copied_objects);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url), "search" => 1));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda remote search");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda remote search");
		}
		$this->read_template("search.tpl");

		$search = get_instance("search");
		$arr = array("s" => $ob["meta"]);
		$arr["clid"] = &$this; 
		$arr["search"] = 1;
		$arr["login_obj"] = $ob["meta"]["login_obj"];
		$this->vars(array(
			"name" => $ob["name"],
			"search" => $search->show($arr),
			"res" => $search->get_results(),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"login_objs" => $this->picker($ob["meta"]["login_obj"], $this->list_objects(array("class" => CL_AW_LOGIN))),
		));

		if (is_array($sel))
		{
			$copied_objects = aw_global_get("copied_objects");
			foreach($sel as $oid)
			{
				$copied_objects[$oid] = serialize($this->do_orb_method_call(array(
					"class" => "objects",
					"action" => "serialize",
					"params" => array("oid" => $oid),
					"method" => "xmlrpc",
					"login_obj" => $login_obj
				)));
			}
			aw_session_set("copied_objects",$copied_objects);
		}
		return $this->parse();
	}

	function search_callback_do_search($arr)
	{
		$params = array("return" => ARR_ALL);
		if (is_array($arr["s"]["class_id"]))
		{
			list(,$tmp) = each($arr["s"]["class_id"]);
			if ($tmp)
			{
				$params["class"] = $tmp;
			}
		}
		if ($arr["s"]["name"] != "")
		{
			$params["name"] = $arr["s"]["name"];
		}
		if ($arr["s"]["location"] != "")
		{
			$params["parent"] = $arr["s"]["location"];
		}
//		echo "params = <pre>", var_dump($params),"</pre> <br>";
		$this->objs = $this->do_orb_method_call(array(
			"class" => "objects",
			"action" => "list_objects",
			"params" => $params,
			"method" => "xmlrpc",
			"login_obj" => $arr["login_obj"]
		));
		if (!is_array($this->objs))
		{
			$this->objs = array();
		}
		reset($this->objs);
	}

	function search_callback_get_next()
	{
		list(,$ret) = each($this->objs);
		return $ret;
	}

	function search($arr)
	{
		extract($arr);
		$this->read_template("search.tpl");

		$search = get_instance("search");
		$arr["clid"] = &$this; 
		$this->vars(array(
			"search" => $search->show($arr),
			"res" => $search->get_results(),
			"reforb" => $this->mk_reforb("search",array("no_reforb" => 1,"search" => 1)),
			"login_objs" => $this->picker($login_obj, $this->list_objects(array("class" => CL_AW_LOGIN))),
		));

		if (is_array($sel))
		{
			$copied_objects = aw_global_get("copied_objects");
			foreach($sel as $oid)
			{
				$copied_objects[$oid] = serialize($this->do_orb_method_call(array(
					"class" => "objects",
					"action" => "serialize",
					"params" => array("oid" => $oid),
					"method" => "xmlrpc",
					"login_obj" => $login_obj
				)));
			}
			aw_session_set("copied_objects",$copied_objects);
		}
		return $this->parse();
	}

	function search_callback_table_header()
	{
		return " ";
	}

	function search_callback_table_footer()
	{
		return " ";
	}

	function search_callback_get_fields(&$fields, $args)
	{
		$fields = array();
		if ($args["login_obj"])
		{
			$fields["location"] = array(
				"type" => "select",
				"caption" => "Asukoht",
				"options" => $this->do_orb_method_call(array(
						"class" => "objects",
						"action" => "get_list",
						"method" => "xmlrpc",
						"params" => array("empty" => true),
						"login_obj" => $args["login_obj"]
					)),
				"selected" => $args["s"]["location"]
			);
		}
		else
		{
			$fields["location"] = array(
				"type" => "select",
				"caption" => "Asukoht",
				"options" => array("0" => "Igaltpoolt"),
				"selected" => $args["s"]["location"]
			);
		}
	}

	function search_callback_modify_data($row,$args)
	{
		$row["change"] = "<input type='checkbox' name='sel[]' value='$row[oid]'>";
	}
}
?>