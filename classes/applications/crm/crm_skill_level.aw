<?php
// crm_skill_level.aw - Oskuse tase
/*

@classinfo syslog_type=ST_CRM_SKILL_LEVEL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property skill type=relpicker reltype=RELTYPE_SKILL store=connect no_edit=1 automatic=1
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
			case "skill":
				$by_parent = array();
				foreach($prop["options"] as $opt_id => $opt_capt)
				{
					if (!is_oid($opt_id))
					{
						continue;
					}
					$val = $this->_get_level_in_opts($opt_id, $prop["options"]);
					if ($val == 0)
					{
						$by_parent[0][] = $opt_id;
					}
					else
					{
						$tmp = obj($opt_id);
						$by_parent[$tmp->parent()][] = $opt_id;
					}
				}

				$prop["options"] = array();
				$prop["options"][""] = t("--vali--");
				$prop["disabled_options"] = array();
				$this->_format_opts($prop["options"], 0, $by_parent, $prop["disabled_options"]);

				if (preg_match("/skills_releditor(\d)/imsU", $arr["name_prefix"], $mt))
				{
					// list only items under top-level items with jrk no 1
					echo "filter by no ".$mt[1]." <br>";
					$this->_filter_opts_by_level_jrk($prop["options"], $mt[1]);
				}
				break;

			case "level":
				$prop["options"][0] = t("--vali--");
				if(is_oid($arr["obj_inst"]->prop("skill")))
				{
					$skill_id = $arr["obj_inst"]->prop("skill");
				}
				else
				{
					$ol = new object_list(array("class_id" => CL_CRM_SKILL, "lang_id" => array(),"site_id" => array()));
					if ($ol->count())
					{
						foreach($ol->arr() as $tmp)
						{
							if ($tmp->prop("lvl_meta") > 0)
							{
								$skill_id = $tmp->id();
							}
						}
					}
				}
				
				if ($this->can("view", $skill_id))
				{
					$skill_obj = obj($skill_id);
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

	function _filter_opts_by_level_jrk(&$opts, $jrk)
	{
		foreach($opts as $k => $v)
		{
			if (!is_oid($k))
			{
				continue;
			}
			$tmp = obj($k);
			if ($this->_get_level_in_opts($k, $opts) == 1 && $tmp->ord() == $jrk)
			{
				$filter_opt = $k;
			}
		}

		if ($filter_opt)
		{
			foreach($opts as $k => $v)
			{
				if (!is_oid($k))
				{
					continue;
				}
				if (!$this->_opt_is_below($k, $filter_opt, $opts))
				{
					unset($opts[$k]);
				}
			}
		}
		else
		{
			$opts = array("" => t("--vali--"));
		}
	}

	private function _opt_is_below($opt, $filter_opt, $opts)
	{
		$o = obj($opt);
		foreach($o->path() as $path_item)
		{
			if ($path_item->id() == $filter_opt)
			{
				return true;
			}
		}
		return false;
	}

	function _format_opts(&$opts, $parent, $by_parent, &$disabled_opts)
	{
		$this->level++;
		$cnt = 0;
		foreach($by_parent[$parent] as $opt_id)
		{
			$tmp = obj($opt_id);
			$opts[$opt_id] = str_repeat("&nbsp;&nbsp;", $this->level-1).$tmp->trans_get_val("name");
			if ($this->_format_opts($opts, $opt_id, $by_parent, $disabled_opts) != 0)
			{
				$disabled_opts[$opt_id] = $opt_id;
			}
			$cnt++;
		}
		$this->level--;
		return $cnt;
	}

	private function _get_level_in_opts($opt_id, $opts)
	{
		if ($this->can("view", $opt_id))
		{
			$o = obj($opt_id);
			if (isset($opts[$o->parent()]))
			{
				return $this->_get_level_in_opts($o->parent(), $opts)+1;
			}
		}
		return 0;
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
