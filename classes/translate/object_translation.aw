<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/object_translation.aw,v 1.10 2002/12/24 15:14:22 kristo Exp $
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

	/** Creates a new translation of an object and a relation with the old one 
		
		@attrib name=create params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment
		id(int) - id of the object we should use for cloning
		srclang(str) - id of the original language
		if srclang is not defined, use the language defined in the object id
		dstlang(str) - id of the target language

	**/
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
			reset($conns);
			list(,$f_c) = each($conns);
			return $this->mk_my_orb("change",array("id" => $f_c->prop("to"), "set_lang_id" => $f_c->prop("to.lang_id")),$fl);
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
//		echo "unserializing for $fl <br>";
//		echo dbg::dump($raw);
		$clone_id = $orig_inst->unserialize(array(
			"parent" => $orig->parent(),
			"raw" => $raw,
		));
		if (!$clone_id)
		{
			error::throw(array(
				"id" => ERR_CLONE,
				"msg" => "object_translation::create(): could not clone object ".$orig->id()
			));
		}

		$clone = obj($clone_id);
//		echo "clone name = ".$clone->name()." <br>";
		if (!$clone->parent())
		{
			$clone->set_parent($orig->parent());
		}
		if (!$clone->class_id())
		{
			$clone->set_class_id($orig_inst->class_id());
		}
		$clone->set_lang($dstlang);
		$clone->save();

		// we also gots to create a relation
		$co = new connection();
		$co->change(array(
			"from" => $orig->id(),
			"to" => $clone_id,
			"reltype" => RELTYPE_TRANSLATION
		));

		// and also a reverse relation back to the original object
		$co = new connection();
		$co->change(array(
			"to" => $orig->id(),
			"from" => $clone_id,
			"reltype" => RELTYPE_ORIGINAL
		));

		return $this->mk_my_orb("change",array("id" => $clone_id, "set_lang_id" => $dstlang_id),$fl);
	}

	function get_original($oid)
	{
		return $GLOBALS["object_loader"]->ds->_get_root_obj($oid);
	}

	function translation_list($oid, $no_orig = false)
	{
		$obj = obj($oid);

		// see if the object has translations
		$conn = $obj->connections_from(array(
			"type" => RELTYPE_TRANSLATION
		));
		if (count($conn) < 1)
		{
			// if it has none, then try to figure out if it is a translated object
			$conn = $obj->connections_to(array(
				"type" => RELTYPE_TRANSLATION
			));
			if (count($conn) > 0)
			{
				// if it has connections pointing to it, then it is, so get the translations from the original
				// we need to do this, because the previous query must ever only return 0 or 1 connections
				$obj = obj($conn[0]->prop("from"));
				$conn = $obj->connections_from(array(
					"type" => RELTYPE_TRANSLATION
				));
			}
		}

		$l = get_instance("languages");

		if ($no_orig)
		{
			$ret = array();
		}
		else
		{
			$ret = array(
				$l->get_langid_for_code($obj->lang()) => $obj->id()
			);
		}
		foreach($conn as $c)
		{
			if ($no_orig)
			{
				if ($c->prop("to") != $obj->id())
				{
					$ret[$c->prop("to.lang_id")] = $c->prop("to");
				}
			}
			else
			{
				$ret[$c->prop("to.lang_id")] = $c->prop("to");
			}
		}
		return $ret;
	}

	/** shows a message that the object has not been translated yet 
		
		@attrib name=show_trans params=name nologin="1" default="0"
		
		@param section required
		@param set_lang_id required
		
		@returns
		
		
		@comment

	**/
	function show_trans($arr)
	{
		extract($arr);
		$this->read_template("show_trans.tpl");

		$l = get_instance("languages");
		$ld = $l->fetch($set_lang_id);
		
		$this->vars(array(
			"trans_msg" => nl2br($ld["meta"]["trans_msg"])
		));

		$llist = $l->get_list();

		$lt = "";

		$tr = $this->translation_list($section);
		foreach($tr as $lid => $oid)
		{
			$this->vars(array(
				"url" => aw_ini_get("baseurl")."/".$oid,
				"name" => $llist[$lid]
			));
			$lt .= $this->parse("LANGUAGE");
		}

		$this->vars(array(
			"LANGUAGE" => $lt
		));

		return $this->parse();
	}
};
?>
