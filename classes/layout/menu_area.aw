<?php

/*

@classinfo syslog_type=ST_MENU_AREA 
@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property root_folder type=relpicker reltype=RELTYPE_ROOT_FOLDER field=meta method=serialize
@caption Root kataloog

@property num_levels type=textbox size=3 field=meta method=serialize
@caption Mitu taset

@property mod_levels type=generated generator=gen_mod_levels field=meta method=serialize
@caption Tasemed

@property show_name type=checkbox rel=1 ch_value=1 field=meta method=serialize 
@caption Kas n&auml;idata nime

*/

define("RELTYPE_ROOT_FOLDER",1);

class menu_area extends class_base
{
	function menu_area()
	{
		$this->init(array(
			'tpldir' => 'layout/menu_area',
			'clid' => CL_MENU_AREA
		));
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		$ob = $this->get_object($args["relobj_id"]);
		return $this->show(array('id' => $alias['target'], "relobj" => $ob));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);

		if ($relobj["meta"][CL_MENU_AREA]['show_name'])
		{
			$ob = $this->get_object($id);
			$root_o = $this->get_object($ob['meta']['root_folder']);
			$str = $root_o['name'];
		}
		return $str;
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_ROOT_FOLDER => "root kataloog",
		);
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		
		$od = $ob['meta'];

		// check if the objects for the levels exist and that there are not too meny of them.
		$num = $od["num_levels"];
		for($i = 0; $i < $num; $i++)
		{
			$exists = false;
			if ($od["level_objs"][$i])
			{
				// check if it is not deleted
				$stat = $this->db_fetch_field("SELECT status FROM objects WHERE oid = '".$od["level_objs"][$i]."'", "status");
				if ($stat >= 1)
				{
					$exists = true;
				}
			}
			
			if (!$exists)
			{
				// create object for that level
				$oid = $this->new_object(array(
					"parent" => $ob["parent"],
					"name" => $ob["name"]." tase ".($i+1),
					"class_id" => CL_MENU_AREA_LEVEL,
					"status" => 2,
					"metadata" => array(
						"level" => $i,
						"menu_area" => $ob["oid"]
					)
				));
				$od["level_objs"][$i] = $oid;
			}
		}

		// delete and unset not-needed ones
		$los = new aw_array($od["level_objs"]);
		foreach($los->get() as $level => $oid)
		{
			if ($level > $num)
			{
				$this->delete_object($oid);
				$this->delete_aliases_of($oid);
				unset($od["level_objs"][$level]);
			}
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "level_objs", 
			"value" => $od['level_objs']
		));
	}

	function gen_mod_levels($arr)
	{
		$acts = array();
		$obj = $this->get_object($arr["id"]);
		$ls = new aw_array($obj["meta"]["level_objs"]);
		foreach($ls->get() as $level => $loid)
		{
			$rt = 'mod_lobj_'.$level;

			$acts[$rt] = array(
				'name' => $rt,
				'caption' => "",
				'type' => 'text',
				'table' => 'objects',
				'field' => 'meta',
				'method' => 'serialize',
				'group' => 'general',
				'value' => html::href(array(
					'url' => $this->mk_my_orb("change", array("id" => $loid), "menu_area_level"),
					'caption' => "Muuda taseme ".($level+1)." m&auml;&auml;ranguid"
				))
			);
		}

		dbg::dump($acts);
		return $acts;
	}

	function get_root_menu($oid)
	{
		$ob = $this->get_object($oid);
		return $ob['meta']['root_folder'];
	}

	////
	// !returns the next menu level id for this menu area
	// params:
	//	id - the id of the menu area
	//	cur_level - the current menu level (if 1, this returns id for level 2 , etc)
	function get_next_level_id($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		return $ob['meta']['level_objs'][$cur_level+1];
	}
}
?>
