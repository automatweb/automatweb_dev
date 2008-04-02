<?php
// crm_recommendation.aw - Soovitus
/*

@classinfo syslog_type=ST_CRM_RECOMMENDATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property person type=relpicker reltype=RELTYPE_PERSON store=connect field=meta method=serialize
@caption Soovitav isik

@property relation type=classificator reltype=RELTYPE_RELATION store=connect
@caption Suhe soovitajaga

@property jobwish type=relpicker reltype=RELTYPE_JOBWISH store=connect field=meta method=serialize
@caption Soovitatav t&ouml;&ouml;

@reltype JOBWISH value=1 clid=CL_PERSONNEL_MANAGEMENT_JOB_WANTED
@caption Soovitatav t&ouml;&ouml;

@reltype PERSON value=2 clid=CL_CRM_PERSON
@caption Soovitav isik

@reltype RELATION value=3 clid=CL_META
@caption Suhe soovitajaga

*/

class crm_recommendation extends class_base
{
	function crm_recommendation()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_recommendation",
			"clid" => CL_CRM_RECOMMENDATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "person":
				if(!$prop["value"])
				{
					$prop["post_append_text"] = "";
					$prop["type"] = "textbox";
					$prop["autocomplete_source"] = $this->mk_my_orb("person_ac");
					$prop["autocomplete_params"] = array();
				}
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "person":
				if(!is_oid($prop["value"]) && strlen($prop["value"]) > 0)
				{
					$ol = new object_list(array(
						"class_id" => CL_CRM_PERSON,
						"lang_id" => array(),
						"site_id" => array(),
					));
					$rev_nms = array_flip($ol->names());
					if(array_key_exists($prop["value"], $rev_nms))
					{
						$arr["obj_inst"]->set_prop("person", $rev_nms[$prop["value"]]);
					}
					else
					{
						$new_p = new object;
						$new_p->set_class_id(CL_CRM_PERSON);
						$new_p->set_parent($arr["obj_inst"]->parent());
						$new_p->set_name($prop["value"]);
						$new_p->save();
						$arr["obj_inst"]->set_prop("person", $new_p->id());
					}
					return PROP_IGNORE;
				}
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
	
	/**
		@attrib name=person_ac all_args=1
	**/
	function person_ac($arr)
	{
		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$cl_json = get_instance("protocols/data/json");

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);
		
		$ol = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			"site_id" => array(),
			"limit" => 500,
		));
		$autocomplete_options = $ol->names();
		foreach($autocomplete_options as $k => $v)
		{
			$autocomplete_options[$k] = iconv(aw_global_get("charset"), "UTF-8", parse_obj_name($v));
		}

		$autocomplete_options = array_unique($autocomplete_options);
		header("Content-type: text/html; charset=utf-8");
		exit ($cl_json->encode($option_data));
	}
}

?>
