<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/otv_ds_obj.aw,v 1.5 2004/06/17 13:54:46 kristo Exp $
// otv_ds_obj.aw - Objektinimekirja AW datasource 
/*

@classinfo syslog_type=ST_OTV_DS_OBJ relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@property show_notact type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita mitteaktiivseid objekte

@property sort_by type=select field=meta method=serialize
@caption Objekte sorteeritakse

@groupinfo folders caption="Kataloogid"
@property folders type=table store=no callback=callback_get_menus editonly=1 group=folders
@caption Kataloogid

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype ADD_TYPE value=2 clid=CL_OBJECT_TYPE
@caption lisatav objektit&uuml;&uuml;p

@reltype SHOW_TYPE value=3 clid=CL_OBJECT_TYPE
@caption n&auml;idatav objektit&uuml;&uuml;p

*/

class otv_ds_obj extends class_base
{
	function otv_ds_obj() 
	{
		$this->init(array(
			"tpldir" => "contentmgmt/object_treeview/otv_ds_obj",
			"clid" => CL_OTV_DS_OBJ
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "sort_by":
				$prop["options"] = array(
					"objects.modified DESC" => "Objekti muutmise kuup&auml;eva j&auml;rgi",
					"objects.jrk" => "Objektide j&auml;rjekorra j&auml;rgi"
				);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "folders":
				$arr['obj_inst']->set_meta("include_submenus",$arr["request"]["include_submenus"]);
				$arr['obj_inst']->set_meta("ignoreself",$arr["request"]["ignoreself"]);
				break;

			case "clids":
				$_clids = array();
				classload("aliasmgr");
				$a = aliasmgr::get_clid_picker();
				foreach($a as $clid => $clname)
				{
					$rt = "clid_".$clid;
					if (isset($arr["request"][$rt]) && $arr["request"][$rt] == 1)
					{
						$_clids[$clid] = $clid;
					}
				}
				$arr["obj_inst"]->set_meta("clids", $_clids);
				break;
		}
		return $retval;
	}	

	function callback_get_menus($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();

		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "ot_menus",
			"layout" => "generic"
		));
		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
		$this->t->define_field(array(
			"name" => "check",
			"caption" => "k.a. alammen��d",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));
		$this->t->define_field(array(
			"name" => "ignoreself",
			"caption" => "&auml;ra n&auml;ita peamen&uuml;&uuml;d",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));

		$include_submenus = $args["obj_inst"]->meta("include_submenus");
		$ignoreself = $args["obj_inst"]->meta("ignoreself");


		$conns = $args["obj_inst"]->connections_from(array(
			"type" => RELTYPE_FOLDER
		));

		foreach($conns as $conn)
		{
			$c_o = $conn->to();

			$chk = "";
			if ($c_o->class_id() == CL_MENU)
			{
				$chk = html::checkbox(array(
					"name" => "include_submenus[".$c_o->id()."]",
					"value" => $c_o->id(),
					"checked" => $include_submenus[$c_o->id()],
				));
			}

			$this->t->define_data(array(
				"oid" => $c_o->id(),
				"name" => $c_o->path_str(array(
					"max_len" => 3
				)),
				"check" => $chk,
				"ignoreself" => html::checkbox(array(
					"name" => "ignoreself[".$c_o->id()."]",
					"value" => $c_o->id(),
					"checked" => $ignoreself[$c_o->id()],
				)),
			));
		};
		
		$nodes[$prop["name"]] = array(
			"type" => "text",
			"caption" => $prop["caption"],
			"value" => $this->t->draw(),
		);
		return $nodes;
	}

	/** returns data about folders that the datasource object $o contains

		@comment

			returnes an array, key is entry id, value is array(
				id => id,
				parent => parent
				name => name,
				url => url,
				target => target,
				comment => comment
				type => type,
				add_date => add_date,
				mod_date => mod_date,
				adder => adder
				modder => modder,
				icon => icon,
				fileSizeBytes,
				fileSizeKBytes,
				fileSizeMBytes
			)

			bot id and parent are opaque strings
	**/
	function get_folders($ob)
	{
		if (!is_oid($ob->id()))
		{
			return;
		}

		// go over all related menus and add subtree id's together if the user has so said. 
		$ret = array();
		
		$sub = $ob->meta("include_submenus");
   		$igns = $ob->meta("ignoreself");

		classload("icons", "image");
																		
		$conns = $ob->connections_from(array(
			"type" => RELTYPE_FOLDER
		));
		foreach($conns as $conn)
		{
			$c_o = $conn->to();
			if (!isset($this->first_folder))
			{
				$this->first_folder = $c_o->id();
			}
			
			$cur_ids = array();

			if ($sub[$c_o->id()])
			{
				$_ot = new object_tree(array(
					"class_id" => CL_MENU,
					"parent" => $c_o->id(),
					"status" => STAT_ACTIVE,
					"lang_id" => array(),
					"sort_by" => "objects.jrk"
				));
				$cur_ids = $_ot->ids();
			}

			if (!$igns[$c_o->id()])
			{
				$cur_ids[] = $c_o->id();
			}

			foreach($cur_ids as $t_id)
			{
				$t = obj($t_id);
				if ($t_id == $c_o->id())
				{
					$pt = 0;
				}
				else
				{
					$pt = $t->parent();
				}
				$adr = $t->createdby();
				$mdr = $t->modifiedby();
				$ret[$t->id()] = array(
					"id" => $t->id(),
					"parent" => $pt,
					"name" => parse_obj_name($t->name()),
					"comment" => $t->comment(),
					"add_date" => $t->created(),
					"mod_date" => $t->modified(),
					"adder" => $adr->name(),
					"modder" => $mdr->name(),
					"icon" => image::make_img_tag(icons::get_icon_url($t->class_id(), $t->name())),
				);
			}
		}

		return $ret;
	}

	function get_fields($ob)
	{
		$ret = array();

		$ot = get_instance("admin/object_type");

		$clids = array();
		$cttt = $ob->connections_from(array("type" => "RELTYPE_SHOW_TYPE"));
		foreach($cttt as $c)
		{
			$ps = $ot->get_properties($c->to());
			foreach($ps as $pn => $pd)
			{
				$ret[$pn] = $pd["caption"];
			}
		}
		
		return $ret;
	}

	function get_objects($ob)
	{
		$ret = array();

		// if the folder is specified in the url, then show that
		if ($GLOBALS["tv_sel"])
		{
			$parent = $GLOBALS["tv_sel"];
		}
		else
		// right. if the user has said, that no tree should be shown
		// then get files in all selected folders
		if (!$ob->meta('show_folders'))
		{
			$con = $ob->connections_from(array(
				"type" => RELTYPE_FOLDER
			));

			$parent = array();
			foreach($con as $c)
			{
				$parent[$c->prop("to")] = $c->prop("to");
			}
		}

		if (!is_oid($ob->id()))
		{
			return;
		}

		if (!$parent)
		{
			// if parent can't be found. then get the objects from all the root folders
			$con = $ob->connections_from(array(
				"type" => "RELTYPE_FOLDER"
			));

			$ignoreself = $ob->meta("ignoreself");

			$parent = array();
			foreach($con as $c)
			{
				// but only those that are to be ignored!
				if ($ignoreself[$c->prop("to")])
				{
					$parent[$c->prop("to")] = $c->prop("to");
				}
			}
		}

		$clids = array();
		$cttt = $ob->connections_from(array("type" => "RELTYPE_SHOW_TYPE"));
		foreach($cttt as $c)
		{
			$c_o = $c->to();
			$clids[] = $c_o->subclass();
		}

		$awa = new aw_array($parent);
		if (count($awa->get()) < 1)
		{
			$parent = $this->first_folder;
		}

		$sby = "objects.modified DESC";
		if ($ob->prop("sort_by") != "")
		{
			$sby = $ob->prop("sort_by");
		}

		$ol = new object_list(array(
			"parent" => $parent,
			"status" => $ob->prop("show_notact") ? array(STAT_ACTIVE, STAT_NOTACTIVE) : STAT_ACTIVE,
			"class_id" => $clids,
			"sort_by" => $sby,
			"lang_id" => array()
		));
		$ol->sort_by_cb(array(&$this, "_obj_list_sorter"));

		$classlist = aw_ini_get("classes");
		$fields = $this->get_fields($ob);

		$ret = array();
		classload("icons", "image");
		for($t = $ol->begin(); !$ol->end(); $t = $ol->next())
		{
			$url = $target = $fileSizeBytes = $fileSizeKBytes = $fileSizeMBytes = "";
			$caption = $t->name();
			if ($t->class_id() == CL_EXTLINK)
			{
				$li = get_instance("links");
				list($url,$target,$caption) = $li->draw_link($t->id());
			}
			else
			if ($t->class_id() == CL_FILE)
			{
				$fi = get_instance("file");
				$url = $fi->get_url($t->id(),$t->name());
				
				if ($fd["newwindow"])
				{
					$target = "target=\"_blank\"";
				}
				$fileSizeBytes = number_format(file::get_file_size($t->prop('file')),2);
				$fileSizeKBytes = number_format(file::get_file_size($t->prop('file'))/(1024),2);
				$fileSizeMBytes = number_format(file::get_file_size($t->prop('file'))/(1024*1024),2);
			}
			else
			if ($t->class_id() == CL_MENU)
			{
				$url = aw_url_change_var("tv_sel", $t->id()); /*$this->mk_my_orb("show", array(
					"section" => $t->id(),
					"id" => $t->id(),
					"tv_sel" => $t->id()
				));*/
			}
			else
			{
				$url = $this->cfg["baseurl"]."/".$t->id();
			}

			$adr = $t->createdby();
			$mdr = $t->modifiedby();
			$ret[$t->id()] = array(
				"id" => $t->id(),
				"parent" => $t->parent(),
				"name" => parse_obj_name($t->name()),
				"url" => $url,
				"target" => $target,
				"comment" => $t->comment(),
				"type" => $classlist[$t->class_id()]["name"],
				"add_date" => $t->created(),
				"mod_date" => $t->modified(),
				"adder" => $adr->name(),
				"modder" => $mdr->name(),
				"icon" => image::make_img_tag(icons::get_icon_url($t->class_id(), $t->name())),
				"fileSizeBytes" => $fileSizeBytes,
				"fileSizeKBytes" => $fileSizeKBytes,
				"fileSizeMBytes" => $fileSizeMBytes,
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $t->id(), "section" => aw_global_get("section")), $t->class_id()),
					"caption" => "Muuda"
				)),
			);

			foreach($fields as $ff_n => $ff_c)
			{
				$ret[$t->id()][$ff_n] = $t->prop($ff_n);
			}
		}
		return $ret;
	}

	function _obj_list_sorter($a, $b)
	{
		if ($a->class_id() == CL_MENU && $b->class_id() != CL_MENU)
		{
			return -1;
		}
		else
		if ($a->class_id() != CL_MENU && $b->class_id() == CL_MENU)
		{
			return 1;
		}
		else
		if ($a->class_id() != CL_MENU && $b->class_id() != CL_MENU)
		{
			return $a->modified() < $b->modified();
		}
		else
		if ($a->class_id() == CL_MENU && $b->class_id() == CL_MENU)
		{
			return $a->modified() < $b->modified();
		}
	}

	function check_acl($acl, $o, $id)
	{
		return $this->can($acl, $id);
	}

	function get_add_types($o)
	{
		$ret = array();
		foreach($o->connections_from(array("type" => "RELTYPE_ADD_TYPE")) as $c)
		{
			$ret[] = $c->to();
		}
		return $ret;
	}

	function do_delete_objects($o, $arr)
	{
		foreach($arr as $oid)
		{
			$o = obj($oid);
			$o->delete();
		}
	}
}
?>
