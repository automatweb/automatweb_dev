<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/otv_ds_obj.aw,v 1.18 2005/01/12 10:17:30 kristo Exp $
// otv_ds_obj.aw - Objektinimekirja AW datasource 
/*

@classinfo syslog_type=ST_OTV_DS_OBJ relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@property show_notact type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita mitteaktiivseid objekte

@property show_notact_folder type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita mitteaktiivseid katalooge

@property show_notact_noclick type=checkbox ch_value=1 field=meta method=serialize
@caption Mitteaktiivsed pole klikitavad

@property file_show_comment type=checkbox ch_value=1 field=meta method=serialize
@caption Failil nime asemel kommentaar

@property sort_by type=select field=meta method=serialize
@caption Objekte sorteeritakse

@property use_meta_as_folders type=checkbox ch_value=1 field=meta method=serialize
@caption Kasuta kaustade puu joonistamiseks muutujaid

@property show_via_cfgform type=relpicker reltype=RELTYPE_SHOW_CFGFORM field=meta method=serialize
@caption Objekti vaatamine l&auml;bi seadete vormi

@groupinfo folders caption="Kataloogid"
@property folders type=table store=no callback=callback_get_menus editonly=1 group=folders
@caption Kataloogid

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype ADD_TYPE value=2 clid=CL_OBJECT_TYPE
@caption lisatav objektit&uuml;&uuml;p

@reltype SHOW_TYPE value=3 clid=CL_OBJECT_TYPE
@caption n&auml;idatav objektit&uuml;&uuml;p

@reltype SHOW_CFGFORM value=4 clid=CL_CFGFORM
@caption v&auml;ljundi seadete vorm

@reltype META value=6 clid=CL_META
@caption Muutuja
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
			"caption" => "k.a. alammen&uuml;&uuml;d",
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

		$opts = array();
		$use_meta_as_folders = $args['obj_inst']->prop("use_meta_as_folders");
		if(empty($use_meta_as_folders))
		{
			$opts['reltype'] = RELTYPE_FOLDER;
			$opts['class'] = CL_MENU;
		}
		else
		{
			$opts['reltype'] = RELTYPE_META;
			$opts['class'] = CL_META;
		}


		$conns = $args["obj_inst"]->connections_from(array(
			"type" => $opts['reltype'],
		));

		foreach($conns as $conn)
		{
			$c_o = $conn->to();

			$chk = "";
			if ($c_o->class_id() == $opts['class'])
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
/*
	some notes to implement here

	esiteks, ilmselt piisab sellest, kui panna siin alguses mingitesse muutujatesse vastavalt kas
	tegemist on RELTYPE_META või RELTYPE_FOLDER-ga ja samuti ka klassi konstandid
	samuti tuleb ära teha siin see, et kas näidata alammenüüsid ja kas peamenüüd näidata
	või mitte

	siis ilmselt saab tv_sel muutuja abil vastavas grupis olevaid kontakte näitama hakata
	ja kui mingi kontakt kuskil grupis ei ole, siis näidatakse neid siis kui mingit gruppi valitud
	ei ole

	siis teha ära see alamüksuste grupeerimine, see läheb juba otv külge ja pidi nii ehk naa
	needed thing olema, nii et universaalsus is the key

	ilmselt saab seda kuidagi fieldide sorteerimise ja selle abil teha, ei tohiks ülemäära keeruline
	olla kui ma teada saan kuidagi kuidas erinev tabelirida kuskile teatud tingimuse alusel vahele torgata
	eks seda homme hommikul küsib.
	
*/
		// go over all related menus and add subtree id's together if the user has so said.
		$ret = array();

		$sub = $ob->meta("include_submenus");
		$igns = $ob->meta("ignoreself");

		classload("icons", "image");


		$opts = array();
		$use_meta_as_folders = $ob->prop("use_meta_as_folders");
		if(empty($use_meta_as_folders))
		{
			$opts['reltype'] = "RELTYPE_FOLDER";
			$opts['class'] = CL_MENU;
		}
		else
		{
			$opts['reltype'] = RELTYPE_META;
			$opts['class'] = CL_META;
		}

		$conns = $ob->connections_from(array(
//				"type" => RELTYPE_FOLDER
			"type" => $opts['reltype'],
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
//				"class_id" => CL_MENU,
					"class_id" => $opts['class'],
					"parent" => $c_o->id(),
					"status" => $ob->prop("show_notact_folder") ? array(STAT_ACTIVE,STAT_NOTACTIVE) : STAT_ACTIVE,
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
				if ($igns[$c_o->id()] && $t->parent() == $c_o->id())
				{
					$pt = 0;
				}
				else
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
					"jrk" => $t->ord()
				);
			}
		}

		uasort($ret, create_function('$a,$b', 'return ($a["jrk"] == $b["jrk"] ? 0 : ($a["jrk"] > $b["jrk"] ? 1 : -1));'));

		return $ret;
	}

	function get_fields($ob, $full_props = false)
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
				if ($pd["store"] == "no")
				{
					continue;
				}

				if ($full_props)
				{
					$ret[$pn] = $pd;
				}
				else
				{
					$ret[$pn] = $pd["caption"];
				}
			}
		}

		$ret["jrk"] = "J&auml;rjekord";
		return $ret;
	}

	function get_objects($ob)
	{
		$ret = array();

		// if the folder is specified in the url, then show that
		// if use_meta_as_folders option is set, then ignore tv_sel here
		$use_meta_as_folders = $ob->prop("use_meta_as_folders");

		if ($GLOBALS["tv_sel"] && empty($use_meta_as_folders))
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

		$_ft = array(
			"parent" => $parent,
			"status" => $ob->prop("show_notact") ? array(STAT_ACTIVE, STAT_NOTACTIVE) : STAT_ACTIVE,
			"class_id" => $clids,
			"sort_by" => $sby,
			"lang_id" => array()
		);
	
		$ol = new object_list($_ft);
		$ol->sort_by_cb(array(&$this, "_obj_list_sorter"));

		$classlist = aw_ini_get("classes");
		$fields = $this->get_fields($ob, true);

		$ret = array();
		classload("icons", "image");
		for($t = $ol->begin(); !$ol->end(); $t = $ol->next())
		{
			$url = $target = $fileSizeBytes = $fileSizeKBytes = $fileSizeMBytes = "";
			$caption = $t->name();
			if ($t->class_id() == CL_EXTLINK)
			{
				$li = get_instance(CL_EXTLINK);
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
				$url = aw_url_change_var("tv_sel", $t->id());
			}
			else
			{
				if (($_cff = $ob->prop("show_via_cfgform")))
				{
					$url = $this->mk_my_orb("view", array(
						"id" => $t->id(),
						"cfgform" => $_cff,
						"section" => aw_global_get("section")
					), $t->class_id());
				}
				else
				{
					$url = $this->cfg["baseurl"]."/".$t->id();
				}
			}

			if ($ob->prop("show_notact_noclick") && $t->status() == STAT_NOTACTIVE)
			{
				$url = "";
			}

			if ($t->class_id() == CL_FILE && $ob->prop("file_show_comment"))
			{
				$_name = parse_obj_name($t->comment());
			}
			else
			{
				$_name = parse_obj_name($t->name());
			}

			$adr = $t->createdby();
			$mdr = $t->modifiedby();
			$ret[$t->id()] = array(
				"id" => $t->id(),
				"parent" => $t->parent(),
				"name" => $_name,
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
					"caption" => html::img(array(
						"url" => aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif",
						"border" => 0
					))//"Muuda"
				)),
				"jrk" => $t->ord()
			);

			foreach($fields as $ff_n => $ff_d)
			{
				if ($ff_n != "url")
				{
					if ($ff_n != "type")
					{
						$ret[$t->id()][$ff_n] = $t->prop_str($ff_n);
					}
					else
					{
						$ret[$t->id()][$ff_n] = $classlist[$t->class_id()]["name"];
					}
				}
			}

			if ($t->class_id() == CL_FILE && $ob->prop("file_show_comment"))
			{
				$_name = parse_obj_name($t->comment());
			}
			else
			{
				$_name = parse_obj_name($t->name());
			}
			$ret[$t->id()]["name"] = $_name;
			$ret[$t->id()]["jrk"] = $t->ord();
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

		$conns = $o->connections_from(array(
			"type" => "RELTYPE_FOLDER"
		));
		$c = reset($conns);

		if (!$c)
		{
			return array(false, $ret);
		}
		return array($GLOBALS["tv_sel"] ? $GLOBALS["tv_sel"] : $c->prop("to"), $ret);
	}

	function do_delete_objects($o, $arr)
	{
		foreach($arr as $oid)
		{
			$o = obj($oid);
			$o->delete();
		}
	}

	/** saves editable fields (given in $ef) to object $id, data is in $data

		@attrib api=1


	**/
	function update_object($ef, $id, $data)
	{
		if ($data === NULL)
		{
			return;
		}
		$o = obj($id);
		$mod = false;
		foreach($ef as $fn => $tmp)
		{
			if ($fn == "jrk")
			{
				if ($data[$fn] != $o->ord())
				{
					$o->set_ord($data[$fn]);
					$mod = true;
				}
			}
			else
			{
				if ($o->prop($fn) != $data[$fn])
				{
					$o->set_prop($fn, $data[$fn]);
					$mod = true;
				}
			}
		}
		if ($mod)
		{
			$o->save();
		}
	}
}
?>
