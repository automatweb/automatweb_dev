<?php

class aw_spec_layout_obj extends _int_object
{
	/**
		@attrib api=1
		@returns
			array { oid => group_object }
	**/
	public function spec_property_list()
	{
		$ol = new object_list(array(
			"class_id" => CL_AW_SPEC_PROPERTY,
			"lang_id" => array(),
			"site_id" => array(),
			"parent" => $this->id()
		));
		return $ol->arr();
	}

	/**
		@attrib api=1 params=pos
		@param group_data required type=array
			array { oid => array { prop_name => text, prop_type => type, prop_desc => description } }
	**/
	public function set_spec_property_list($group_data)
	{
		$cur_list = $this->spec_property_list();
		
		$group_data = $this->_get_layout_data($group_data);

		foreach($group_data as $idx => $cle)
		{
			// add new
			if (!is_oid($idx))
			{
				$tmp = $this->_add_class_entry($cle);
				$cur_list[$tmp->id()] = $tmp;
				$group_data[$tmp->id()] = $cle;
			}
			else
			// change old
			{
				$this->_upd_class_obj(obj($idx), $cle);
			}
		}
		
		// remove deleted
		foreach($cur_list as $oid => $obj)
		{
			if (!isset($group_data[$oid]))
			{
				$obj->delete();
			}
		}
	}

	/** filter data for empty entries **/
	private function _get_layout_data($class_data)
	{
		$rv = array();
		foreach(safe_array($class_data) as $idx => $cle)
		{
			if (trim($cle["prop_name"]) != "")
			{
				$rv[$idx]  = $cle;
			}
		}
		return $rv;
	}

	/** create new spec class **/
	private function _add_class_entry($cle)
	{
		$o = obj();
		$o->set_class_id(CL_AW_SPEC_PROPERTY);
		$o->set_parent($this->id());
		$this->_upd_class_obj($o, $cle);
		return $o;
	}

	private function _upd_class_obj($o, $cle)
	{
		$o->set_name($cle["prop_name"]);
		$o->set_prop("prop_type", $cle["prop_type"]);
		$o->set_prop("prop_desc", $cle["prop_desc"]);
		$o->save();
	}

	/** 
		@attrib api=1
		@returns
			array { prop type => prop type desc }
	**/
	static public function spec_prop_type_picker()
	{
		return array(
			"textbox" => t("Textbox"),
			"textarea" => t("Textarea"),
			"treeview" => t("Puu"),
			"table" => t("Tabel"),
			"relpicker" => t("Relpicker"),
			"toolbar" => t("Toolbar")
		);
	}
}

?>
