<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/translation.aw,v 1.1 2003/08/29 11:51:34 duke Exp $
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

@property preview type=text editonly=1 store=no group=workbench
@caption Eelvaade

@property workbench type=callback callback=callback_gen_workbench store=no group=workbench no_caption=1
@caption Tõlkimine


@groupinfo workbench caption="Tõlgi"


*/

class translation extends class_base
{
	function translation()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
	    // if they exist at all. the default folder does not actually exist, 
	    // it just points to where it should be, if it existed
		$this->init(array(
			'tpldir' => 'translate',
			'clid' => CL_TRANSLATION
		));
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
				$langs = aw_ini_get("translate.languages");
				$ids = aw_ini_get("translate.ids");
				$data["value"] = "Keel: " . $langs[$args["obj"]["meta"]["lang_code"]] . "<br>" . "Kataloog: " . $ids[$args["obj"]["subclass"]];
				break;

			case "preview":
				if ($args["obj"]["subclass"] == TR_FORUM)
				{
					// hrm I should replace this with some call to return a random
					// object id
					$data["value"] = html::href(Array(
						"url" => $this->mk_my_orb("change",array("id" => 96227),"forum_v2"),
						"caption" => "Foorumi eelvaade",
					));
				}
				break;
		}
		return $retval;
	}

	function callback_gen_workbench($args)
	{
		// first, load the correct translation file
		$scr = get_instance("translate/scanner");
		$ids = aw_ini_get("translate.ids");
		$trans_id = $ids[$args["obj"]["subclass"]];
		$fname = $outname = $this->cfg["basedir"] . "/xml/trtemplate/" . $trans_id . ".xml";
		$dt = $scr->_unser($fname);
		$this->read_template("table.tpl");
		$prop = $args["prop"];
		$c = "";
		// XX: can we do this with VCL table?
		$colors = array(
			"CTX_CAPTION" => "#C0FFEE",
			"CTX_COMMENT" => "#EEFFEE",
		);
		foreach($dt as $key => $val)
		{
			$this->vars(array(
				"id" => $key,
				"color" => $colors[$val["ctx"]],
				"context" => $val["ctx"],
				"orig" => $val["text"],
				"trans" => $args["obj"]["meta"]["trans"][$key],
			));
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
