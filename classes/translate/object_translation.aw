<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/object_translation.aw,v 1.3 2003/09/17 15:11:42 kristo Exp $
// object_translation.aw - Objekti tõlge 

// create method accepts the following arguments:

// id - id of the object that has to be translated
// srclang - char id of the source language
// dstlang - char id of the target language ..

// and then .. it simply creates the new object under the same parent
// clones all the data from the original object ...
// creates the "translation" relation
// redirects to the change form of the document

// later we can add methods to make dealing with translations less painful

class object_translation extends aw_template
{
	function object_translation()
	{
		$this->init(array(
			"tpldir" => "translate/object_translation",
		));
	}

	////
	// !Creates a new translation of an object and a relation with the old one
	// id(int) - id of the object we should use for cloning
	// srclang(str) - id of the original language
	//	if srclang is not defined, use the language defined in the object id
	// dstlang(str) - id of the target language
	function create($args = array())
	{
		// steps
		// 1 - read the original object
		$orig = new object($args["id"]);
		
		// 2 - resolve the id-s of srcland and dstlang
		$l = get_instance("languages");
		$langinfo = $l->get_list(array(
			"key" => "acceptlang",
			"all_data" => true,
		));

		// if no srclang argument is given, figure it out from the original object
		$srclang = isset($args["srclang"]) ? $args["srclang"] : $orig->lang();
		$srclang_id = $langinfo[$srclang]["id"];

		$dstlang = $args["dstlang"];

		// if no dstlang argument is given, figure it out from the user info
		if (empty($dstlang))
		{
			$udat = $this->get_user();
			$ucfg = new object($udat["oid"]);
			$dstlang_id = $ucfg->meta("target_lang");
		}
		else
		{
			$dstlang_id = $langinfo[$args["dstlang"]]["id"];
		};

		$fl = $this->cfg["classes"][$orig->prop("class_id")]["file"];
		if ($fl == "document")
		{
			$fl = "doc";
		};

		// check if the original object already has a translation relation to an object of the correct lang
		$conns = $orig->connections_from(array(
			"reltype" => RELTYPE_TRANSLATION,
			"to.lang_id" => $dstlang_id
		));
		if (count($conns) > 0)
		{
			// it already has the translation, don't create a new one, just go to changing
			return $this->mk_my_orb("change",array("id" => $conns[0]->prop("to")),$fl);
		}
		// 3 - clone all the data from the original object ...
		$orig_inst = get_instance($fl);

		// get old
		$raw = $orig_inst->serialize(array(
			"oid" => $orig->id(),
			"raw" => true,
		));

		$raw["lang_id"] = $dstlang_id;
		$raw["class_id"] = $orig->class_id();

		// create new .. 
		$clone_id = $orig_inst->unserialize(array(
			"parent" => $orig->parent(),
			"raw" => $raw,
		));

		$clone = obj($clone_id);
		$clone->set_lang($dstlang);
		$clone->save();

		// we also gots to create a relation
		$co = new connection();
		$co->change(array(
			"from" => $orig->id(),
			"to" => $clone_id,
			"reltype" => RELTYPE_TRANSLATION
		));
		return $this->mk_my_orb("change",array("id" => $clone_id),$fl);
	}
};
?>
