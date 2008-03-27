<?php
// crm_skill_level.aw - Oskuse tase
/*

@classinfo syslog_type=ST_CRM_SKILL_LEVEL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property skill type=relpicker reltype=RELTYPE_SKILL store=connect no_edit=1
@caption Oskus

@property level type=relpicker reltype=RELTYPE_LEVEL store=connect no_edit=1
@caption Tase

@reltype SKILL value=1 clid=CL_CRM_SKILL
@caption Oskus

@reltype LEVEL value=2 clid=CL_META
@caption Oskuse tase

*/

class crm_skill_level extends class_base
{
	function crm_skill_level()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_skill_level",
			"clid" => CL_CRM_SKILL_LEVEL
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "level":
				$prop["options"][0] = t("--vali--");
				if(is_oid($arr["obj_inst"]->prop("skill")))
				{
					$skill_obj = obj($arr["obj_inst"]->prop("skill"));
					if(is_oid($skill_obj->prop("lvl_meta")))
					{
						$ol = new object_list(array(
							"class_id" => CL_META,
							"parent" => $skill_obj->prop("lvl_meta"),
							"lang_id" => array(),
							"status" => object::STAT_ACTIVE,
							"sort_by" => "jrk",
						));
						$prop["options"] += $ol->names();
					}
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
}

?>
