<?php
// $Header: /home/cvs/automatweb_dev/classes/core/objects.aw,v 1.1 2005/03/20 16:48:36 kristo Exp $
// objects.aw - objektide haldamisega seotud funktsioonid
class objects extends core
{
	function objects()
	{
		$this->init();
	}

	/** Displays an object. Any object. 
		
		@attrib name=show params=name nologin="1" default="0"
		
		@param id required type=int
	**/
	function show($args = array())
	{
		extract($args);
		$ret = "";

		$o =&obj($id);
		$i = $o->instance();
		if (method_exists($i, "parse_alias"))
		{
			$ret = $i->parse_alias(array(
				"oid" => $id,
				"alias" => array("target" => $id)
			));
			if (is_array($ret))
			{
				$ret = $ret["replacement"];
			}
		}

		return $ret;
	}

	/**  
		@attrib name=db_query params=name default="0"
		
		@param sql required
	**/
	function orb_db_query($arr)
	{
		extract($arr);
		$ret = array();
		// only SELECT queries
		if (strtoupper(substr(trim($sql), 0, 7)) != "SELECT")
		{
			return NULL;
		}
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			if (isset($row["oid"]))
			{
				if (!$this->can("view", $row["oid"]))
				{
					continue;
				}
			}
			$ret[] = $row;
		}
		return $ret;
	}

	/**
		@attrib name=storage_query params=name all_args="1"
		@param name optional
		@param class_id optional type=int
		@param comment optional
		@param site_id optional
		@param createdby optional 
		@param modifiedby optional
		@param status optional type=int
		@param lang_id optional type=int
	**/
	function storage_query($arr)
	{
		$arr["site_id"] = array();
		$arr["lang_id"] = array();
		$ol = new object_list($arr);
		$rv = array();
		foreach($ol->arr() as $o)
		{
			$m_o = $o->modifiedby();
			$c_o = $o->createdby();
			$rv[$o->id()] = array(
				"name" => $o->name(),
				"class_id" => $o->class_id(),
				"created" => $o->created(),
				"modified" => $o->modified(),
				"createdby" => $c_o->name(),
				"modifiedby" => $m_o->name(),
				"lang_id" => $o->lang(),
				"path_str" => htmlspecialchars($o->path_str()),
			);
		};
		return $rv;
	}

	/**  
		@attrib name=delete_object params=name default="0"
		
		@param oid required
	**/
	function orb_delete_object($arr)
	{
		extract($arr);
		$tmp = obj($oid);
		$tmp->delete();
	}

	/**  
		@attrib name=aw_ini_get_mult params=name nologin="1" default="0"
		
		@param vals required
	**/
	function aw_ini_get_mult($arr)
	{
		extract($arr);
		$ret = array();
		foreach($vals as $vn)
		{
			$ret[$vn] = aw_ini_get($vn);
		}
		return $ret;
	}

	/** Object list
		
		@attrib name=get_list params=name default="0" nologin="1" all_args="1"
		
		@param ignore_langmenus optional
		@param empty optional
		@param rootobj optional type=int

		@comment
			returns list of id => name pairs for all menus
	**/
	function orb_get_list($arr)
	{
		if (is_array($arr))
		{
			extract($arr);
		}
		if (!isset($rootobj))
		{
			$rootobj = -1;
		}
		$ret = $this->get_menu_list($ignore_langmenus,$empty,$rootobj);
		return $ret;
	}

	/** serialize
		
		@attrib name=serialize params=name default="0" nologin="1" 
		
		@param oid required
		
		@comment
			serializes an object
	**/
	function orb_serialize($arr)
	{
		return parent::serialize($arr);
	}
}

?>