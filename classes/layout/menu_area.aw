<?php

/*

@classinfo syslog_type=ST_MENU_AREA  maintainer=kristo
@classinfo relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property root_folder type=relpicker reltype=RELTYPE_ROOT_FOLDER 
@caption Root kataloog

@property num_levels type=textbox size=3 
@caption Mitu taset

@property mod_levels type=callback callback=gen_mod_levels edit_only=1
@caption Tasemed

@property show_name type=checkbox rel=1 ch_value=1 
@caption Kas n&auml;idata nime

@reltype ROOT_FOLDER value=1 clid=CL_MENU
@caption root kataloog

*/

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
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		$ob = new object($arr["relobj_id"]);
		return $this->show(array('id' => $arr["alias"]["target"], "relobj" => $ob));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$meta = $relobj->meta();

		if ($meta[$this->clid]['show_name'])
		{
			$ob = new object($id);
			$root_o = new object($ob->prop("root_folder"));
			$str = $root_o->name();
		}
		return $str;
	}

	function callback_pre_save($arr)
	{
		extract($arr);
		$ob = $arr["obj_inst"];

		
		$od = $ob->meta();

		$num = $ob->prop("num_levels");
		for($i = 0; $i < $num; $i++)
		{
			$exists = false;
			if ($od["level_objs"][$i] && $this->can("view", $od["level_objs"][$i]))
			{
				$exists = true;
			}
			
			if (!$exists)
			{
				// create object for that level
				$o = obj();
				$o->set_parent($ob->parent());
				$o->set_name($ob->name()." tase ".($i+1));
				$o->set_class_id(CL_MENU_AREA_LEVEL);
				$o->set_status(STAT_ACTIVE);
				$o->set_meta("level", $i);
				$o->set_meta("menu_area", $ob->id());
				$oid = $o->save();

				$od["level_objs"][$i] = $oid;
			}
		}

		// delete and unset not-needed ones
		$los = new aw_array($od["level_objs"]);
		foreach($los->get() as $level => $oid)
		{
			if ($level > $num)
			{
				$tmp = obj($oid);
				$tmp->delete();
				unset($od["level_objs"][$level]);
			}
		}

		$arr["obj_inst"]->set_meta("level_objs",$od["level_objs"]);

	}

	function gen_mod_levels($arr)
	{
		$acts = array();
		$ls = new aw_array($arr["obj_inst"]->meta("level_objs"));
		foreach($ls->get() as $level => $loid)
		{
			$rt = 'mod_lobj_'.$level;

			$acts[$rt] = array(
				'name' => $rt,
				'caption' => "",
				'type' => 'text',
				'store' => 'no',
				'group' => $arr["prop"]["group"],
				'value' => html::href(array(
					'url' => $this->mk_my_orb("change", array("id" => $loid), "menu_area_level"),
					'caption' => "Muuda taseme ".($level+1)." m&auml;&auml;ranguid"
				))
			);
		}

		return $acts;
	}

	function get_root_menu($oid)
	{
		$ob = new object($oid);
		return $ob->prop("root_folder");
	}

	////
	// !returns the next menu level id for this menu area
	// params:
	//	id - the id of the menu area
	//	cur_level - the current menu level (if 1, this returns id for level 2 , etc)
	function get_next_level_id($arr)
	{
		extract($arr);
		$ob = new object($id);
		$meta = $ob->meta();
		return $meta['level_objs'][$cur_level+1];
	}
}
?>
