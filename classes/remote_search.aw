<?php

class remote_search extends aw_template
{
	function remote_search()
	{
		$this->init("remote_search");
	}

	function search($arr)
	{
		extract($arr);
		$this->read_template("search.tpl");

		$clss = $this->get_class_picker(array("addempty" => true));
		$this->vars(array(
			"login_objs" => $this->picker($login_obj, $this->list_objects(array("class" => CL_AW_LOGIN))),
			"classes" => $this->picker($s_class, $clss),
			"s_name" => $s_name,
			"reforb" => $this->mk_reforb("search", array("no_reforb" => true, "search" => 1))
		));

		if ($search && $login_obj)
		{
			$params = array("return" => ARR_ALL);
			if ($s_class)
			{
				$params["class"] = $s_class;
			}
			if ($s_name != "")
			{
				$params["name"] = $s_name;
			}
			$objs = $this->do_orb_method_call(array(
				"class" => "objects",
				"action" => "list_objects",
				"params" => $params,
				"method" => "xmlrpc",
				"login_obj" => $login_obj
			));

			if (is_array($objs))
			{
				foreach($objs as $oid => $odata)
				{
					$this->vars(array(
						"name" => $odata["name"],
						"modifiedby" => $odata["modifiedby"],
						"modified" => $this->time2date($odata["modified"], 2),
						"type" => $clss[$odata["class_id"]],
						"oid" => $oid
					));
					$l.=$this->parse("LINE");
				}
			}
			$this->vars(array(
				"LINE" => $l
			));
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
		return $this->parse();
	}
}
?>