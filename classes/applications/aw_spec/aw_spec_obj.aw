<?php

class aw_spec_obj extends _int_object
{
	/**
		@attrib api=1
		@returns
			array { oid => class_object }
	**/
	public function spec_class_list()
	{
		$ol = new object_list(array(
			"class_id" => CL_AW_SPEC_CLASS,
			"lang_id" => array(),
			"site_id" => array(),
			"parent" => $this->id()
		));
		return $ol->arr();
	}

	/**
		@attrib api=1 params=pos
		@param class_data required type=array
			array { oid => array { class_name => text, class_desc => text } }
	**/
	public function set_spec_class_list($class_data)
	{
		$cur_list = $this->spec_class_list();
		
		$class_data = $this->_get_array_data($class_data, "class_name");

		foreach($class_data as $idx => $cle)
		{
			// add new
			if (!is_oid($idx))
			{
				$tmp = $this->_add_class_entry($cle);
				$cur_list[$tmp->id()] = $tmp;
				$class_data[$tmp->id()] = $cle;
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
			if (!isset($class_data[$oid]))
			{
				$obj->delete();
			}
		}
	}

	/** Returns a list of relation objects for the current spec
		@attrib api=1 

		@returns
			array { relation_oid => relation_object }
	**/
	function spec_relation_list()
	{
		$ol = new object_list(array(
			"class_id" => CL_AW_SPEC_RELATION,
			"lang_id" => array(),
			"site_id" => array(),
			"parent" => $this->id()
		));
		return $ol->arr();
	}

	/** Sets the relation list for theis spec
		@attrib api=1 params=pos
		@param relation_data required type=array
			array { oid => array { rel_from => class_id, rel_name => text, rel_to => class_id } }
	**/
	public function set_spec_relation_list($relation_data)
	{
		$cur_list = $this->spec_relation_list();
		
		$relation_data = $this->_get_array_data($relation_data, "rel_name");

		foreach($relation_data as $idx => $cle)
		{
			// add new
			if (!is_oid($idx))
			{
				$tmp = $this->_add_relation_entry($cle);
				$cur_list[$tmp->id()] = $tmp;
				$relation_data[$tmp->id()] = $cle;
			}
			else
			// change old
			{
				$this->_upd_relation_obj(obj($idx), $cle);
			}
		}
		
		// remove deleted
		foreach($cur_list as $oid => $obj)
		{
			if (!isset($relation_data[$oid]))
			{
				$obj->delete();
			}
		}
	}


	/** filter data for empty entries **/
	private function _get_array_data($class_data, $key)
	{
		$rv = array();
		foreach(safe_array($class_data) as $idx => $cle)
		{
			if (trim($cle[$key]) != "")
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
		$o->set_class_id(CL_AW_SPEC_CLASS);
		$o->set_parent($this->id());
		$this->_upd_class_obj($o, $cle);
		return $o;
	}

	private function _upd_class_obj($o, $cle)
	{
		$o->set_name($cle["class_name"]);
		$o->set_prop("desc", $cle["class_desc"]);
		$o->save();
	}

	private function _add_relation_entry($cle)
	{
		$o = obj();
		$o->set_class_id(CL_AW_SPEC_RELATION);
		$o->set_parent($this->id());
		$this->_upd_relation_obj($o, $cle);
		return $o;
	}

	private function _upd_relation_obj($o, $cle)
	{
		$o->set_name($cle["rel_name"]);
		$o->set_prop("rel_from", $cle["rel_from"]);
		$o->set_prop("rel_to", $cle["rel_to"]);
		$o->save();
	}
}

?>
