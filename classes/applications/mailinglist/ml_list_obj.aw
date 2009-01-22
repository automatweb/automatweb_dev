<?php

class ml_list_obj extends _int_object
{
	function set_name($v)
	{
		return parent::set_name($v);
	}

	/** Returns mailinglist member sources
		@attrib api=1
		@returns object list
	**/
	public function get_sources()
	{
		$ol = new object_list();
		foreach($this->connections_from(array("type" => "RELTYPE_MEMBER_PARENT")) as $c)
		{
			$ol->add($c->prop("to"));
		}
		return $ol;
	}

	/** Returns data about mailinglist sources
		@attrib api=1
		@returns array
	**/
	public function get_sources_data()
	{
		return $this->meta("sources_data");
	}

	public function add_minions($src = array())
	{
		$sub = array();
		$minion_classes = array(CL_MENU , CL_CRM_SECTOR);
		$sources_data = $this->get_sources_data();
		foreach($src as $menu)
		{
			if(!$this->can("view" , $menu))
			{
				continue;
			}
			$o = obj($menu);
			if(in_array($o->class_id() , $minion_classes) && $sources_data[$o->id()]["use_minions"])
			{
				$ol = new object_list(array(
					"class_id" => $minion_classes,
					"lang_id" => array(),
					"parent" => $menu,
				));

				foreach($this->add_minions($ol->ids()) as $submenu)
				{
					$sub[] = $submenu;
				}
			}
		}
		foreach($sub as $submenu)
		{
			$src[] = $submenu;
		}
		return $src;
	}

	/** Saves data about mailinglist sources
		@attrib api=1
		@param data optional type=array
	**/
	public function set_sources_data($data)
	{
		$this->set_meta("sources_data" , $data);
	}

	/**
	@attrib api=1 params=name
	@param all optional type=int
		if 1, then return all member dublicates also
	@param sources optional type=array
		List member source, object id's
	@param name optional type=string
		Mailinglist member name for search
	@param mail optional type=string
		Mailinglist member e-mail address for search
	@param from optional type=int
	@param to optional type=int
	
	@returns array
	@comment
		if the source is file, then parent_name is set
		else oid is set
	@examples
		$members = $ml_list_object->get_members();
		//members = Array(
			[0] => Array(
				[parent] => 7375
				[name] => keegi
				[mail] => keegi@normaalne.ee
				[parent_name] => mailinglist.txt
			)
			[1] => Array(
				[oid] => 7500
				[parent] => 580
				[name] => inimene
				[mail] => inimene@mail.ee
		))
	**/
	public function get_members($arr = array())
	{
		$list = get_instance(CL_ML_LIST);
		$ml_list_members = $list->get_members(array(
			"src"	=> $arr["sources"],
			"all"	=> $arr["all"],
			"id" => $this->id(),
			"from"	=> $arr["from"],
			"to"	=> $arr["to"],
		));

		if(strlen($arr["name"]) > 1 || strlen($arr["mail"]) > 1)
		{
			foreach($ml_list_members as $key => $val)
			{
				if((strlen($arr["name"]) > 1) && (substr_count(strtolower($val["name"]), strtolower($arr["name"])) < 1))
				{
					unset($ml_list_members[$key]);
					continue;
				}
					
				if((strlen($arr["mail"]) > 1) && (substr_count($val["mail"], $arr["mail"]) < 1)) 
				{
					unset($ml_list_members[$key]);
				}
			}
		}
		return $ml_list_members;
	}

}

?>
