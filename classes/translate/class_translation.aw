<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/class_translation.aw,v 1.1 2003/09/23 17:10:37 duke Exp $
// translation.aw - Tõlge 
/*

@classinfo syslog_type=ST_TRANSLATION

@default table=objects
@default group=general

@property lang_code type=select field=meta method=serialize
@caption Keel

@property catalog type=select field=subclass 
@caption Kataloog

@property info type=text editonly=1 store=no group=general,workbench
@caption Info 

@property preview type=callback callback=callback_get_preview_links editonly=1 store=no group=workbench
@caption Eelvaade

@property workbench type=callback callback=callback_gen_workbench store=no group=workbench no_caption=1
@caption Tõlkimine


@groupinfo workbench caption="Tõlgi"
@classinfo relationmgr=yes


*/

// tõlke testimiseks
define("RELTYPE_TEST",1);

class class_translation extends class_base
{
	function class_translation()
	{
		$this->init(array(
			"tpldir" => "translate",
			"clid" => CL_TRANSLATION
		));
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_TEST => "tõlke test",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
		switch($args["reltype"])
		{
                        case RELTYPE_TEST:
				// I need to fill this array dynamically. augh!
				// and I do not think I can do it right now --duke
				//$retval = array(CL_FILE);
				break;
		};
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "catalog":
				if (empty($args["obj"]["oid"]))
				{
					$data["options"] = aw_ini_get("translate.ids");
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "lang_code":
				$tmp = aw_ini_get("languages.list");
				$lang_codes = array();
			
				foreach($tmp as $langdata)
				{
					$lang_codes[$langdata["acceptlang"]] = $langdata["acceptlang"] . " (" . $langdata["name"] . ")";
				};
				$data["options"] = $lang_codes;
				break;

			case "info":
				$langs = aw_ini_get("languages.list");
				$ids = aw_ini_get("translate.ids");
				$data["value"] = "Keel: " . $args["obj_inst"]->prop("lang_code"). "<br>" . "Kataloog: " . $ids[$args["obj_inst"]->subclass()];
				break;

			case "preview":
				// oh, oh, oh. But I really really do need to know which catalogs contain
				// translations for which classes

				// yah, well. I just need to figure out which asd aädsölas d
				if ($args["obj"]["subclass"] == TR_FORUM)
				{
					// hrm I should replace this with some call to return a random
					// object id
					$data["value"] = html::href(array(
						"url" => $this->mk_my_orb("change",array("id" => 96227,"trid" => $args["obj"]["oid"]),"forum_v2"),
						"caption" => "Foorumi eelvaade",
					));
				}
				break;
		}
		return $retval;
	}

	function callback_get_preview_links($arr)
	{
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_TEST,
		));

		$retval = array();

		foreach($conns as $id => $item)
		{
			$target_obj = $item->to();
			$classinf = $this->cfg["classes"][$target_obj->class_id()];
			$retval["prev" . $id] = array(
				"type" => "text",
				"caption" => $classinf["name"] . " eelvaade",
				"value" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
							"id" => $target_obj->id(),
							"trid" => $arr["obj_inst"]->id(),
						),basename($classinf["file"])),
					"caption" => $target_obj->name(),
				)),

			);	
		}

		return $retval;
	}

	function callback_pre_edit($arr)
	{
		$scr = get_instance("translate/scanner");
		$ids = aw_ini_get("translate.ids");
		$trans_id = $ids[$arr["obj_inst"]->subclass()];
		$fname = $outname = $this->cfg["basedir"] . "/xml/trtemplate/" . $trans_id . ".xml";
		$this->dt = $scr->_unser($fname);

		// now . would be nice to reorder those things
		/*
		print "<pre>";
		print_r($this->dt);
		print "</pre>";
		*/

		$files = array();
		foreach($this->dt as $item)
		{
			if (!in_array($item["file"],$files))
			{
				$files[] = $item["file"];
			};
		}

		// those things should be ordered by files first, then tabs .. and under
		// each tab we should have the properties belonging to that tab.


		// without that, I'm afraid this thing is being pretty useless

		$clist = aw_ini_get("classes");

		$clids = array();

		$by_file = array();

		foreach($clist as $clid => $cldata)
		{
			if (!empty($cldata["file"]))
			{
				$by_file[basename($cldata["file"])] = $clid;
			};
		};

		// now that I have the class names, I need to figure out whether
		// any test objects have been created. If not, creat them
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_TEST,
		));

		if (sizeof($conns) == 0)
		{
			$parent = $arr["obj_inst"]->parent();
			foreach($files as $item)
			{
				// create new objects and connect them to the current one
				$clid = $by_file[$item];
				$o = new object(array(
					"name" => "test $item", 
					"parent" => $parent,
					"class_id" => $by_file[$item],
				));
				$o->save();

				$arr["obj_inst"]->connect(array(
					"from" => $arr["obj_inst"]->id(),
					"to" => $o->id(),
					"reltype" => RELTYPE_TEST,
				));
			}
		}

	}

	function callback_gen_workbench($args)
	{
		// first, load the correct translation file
		$this->read_template("table.tpl");
		$prop = $args["prop"];
		$c = "";
		$cntr = 0;
		foreach($this->dt as $key => $val)
		{
			$this->vars(array(
				"color" => $cntr % 2 ? "#F0FDFF" : "#FDFFEA",
				"id" => $key,
				"context" => $val["ctx"],
				"cap_orig" => $val["caption"],
				"comm_orig" => $val["comment"],
				"file" => $val["file"],
				"cap_trans" => $args["obj"]["meta"]["trans"][$key]["caption"],
				"comm_trans" => $args["obj"]["meta"]["trans"][$key]["comment"],
			));
			$cntr++;
			$c .= $this->parse("ITEM");
		};
		$this->vars(array(
			"ITEM" => $c,
		));
		$prop["value"] = $this->parse();
		return array($prop);
	}

	function callback_pre_save($args)
	{
		$dc =& $args["coredata"];
		$dc["metadata"]["trans"] = $args["form_data"]["trans"];
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}
}
?>
