<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/object_translation.aw,v 1.20 2005/03/08 13:25:24 kristo Exp $
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
		obj_set_opt("no_auto_translation", 1);
		// steps
		// 1 - read the original object
		$orig = new object($args["id"]);

		// if the original does not have the has_translation flag set, let's set it!
		if (!$orig->flag(OBJ_HAS_TRANSLATION))
		{
			$orig->set_flag(OBJ_HAS_TRANSLATION, true);
			$orig->save();
		}		

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
			$us = get_instance(CL_USER);
			$ucfg = new object($us->get_current_user());
			$dstlang_id = $ucfg->meta("target_lang");
		}
		else
		{
			$dstlang_id = $langinfo[$args["dstlang"]]["id"];
		};

		$clss = aw_ini_get("classes");
		$fl = $clss[$orig->prop("class_id")]["file"];
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
			error::raise(array(
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
		$clone->set_flag(OBJ_HAS_TRANSLATION, OBJ_HAS_TRANSLATION);
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

	/** returns a list of translated objects to other lanugages, from the object $oid
		
		@comment 
			opts array gives parameters, currently:
			- ret_name - if true, array value is translated object name, not lang code
	**/
	function translation_list($oid, $no_orig = false, $opts = array())
	{
		// make language id => acceptlang lut
		$l_inst = get_instance("languages");
		$lref = $l_inst->get_list(array(
			"key" => "id",
			"all_data" => true
		));

		obj_set_opt("no_auto_translation", 1);
		$orig_id = $oid;
		$orig_obj = obj($oid);
		$orig_name = $orig_obj->name();
		$orig_lang = $l_inst->get_langid_for_code($orig_obj->lang());
		obj_set_opt("no_auto_translation", 0);

		// check if there is an ORIGINAL trans - if so, then it is translated, if not, then original
		$c = new connection();
		$conn = $c->find(array(
			"from" => $oid,
			"type" => RELTYPE_ORIGINAL
		));
		if (count($conn) > 0)
		{
			reset($conn);
			list(,$f_conn) = each($conn);
			$conn = $c->find(array(
				"from" => $f_conn["to"],
				"type" => RELTYPE_TRANSLATION
			));

			$orig_id = $f_conn["to"];
			$orig_name = $f_conn["to.name"];
			$orig_lang = $f_conn["to.lang_id"];
		}
		else
		{
			$conn = $c->find(array(
				"from" => $oid,
				"type" => RELTYPE_TRANSLATION
			));
		}

		if ($no_orig)
		{
			$ret = array();
		}
		else
		{
			if ($opts["ret_name"])
			{
				$ret = array(
					$orig_lang => array(
						"oid" => $orig_id,
						"name" => $orig_name
					)
				);
			}
			else
			{
				$ret = array(
					$orig_lang => $orig_id
				);
			}
		}

		// now $conn contains all the translation relations from the original obj to the translated objs
		foreach($conn as $c)
		{
			if ($no_orig)
			{
				if ($c["to"] != $orig_id)
				{
					if ($opts["ret_name"])
					{
						$ret[$c["to.lang_id"]] = array(
							"name" => $c["to.name"],
							"oid" => $c["to"]
						);
					}
					else
					{
						$ret[$c["to.lang_id"]] = $c["to"];
					}
				}
			}
			else
			{
				if ($opts["ret_name"])
				{
					$ret[$c["to.lang_id"]] = array(
						"name" => $c["to.name"],
						"oid" => $c["to"]
					);
				}
				else
				{
					$ret[$c["to.lang_id"]] = $c["to"];
				}
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
	
		$llist = $l->get_list();
		$lt = "";

		$tr = $this->translation_list($section, false, array("ret_name" => true));
		$od = $tr[$set_lang_id];

		$this->vars(array(
			"lang_baseurl" => aw_ini_get("baseurl")."/?set_lang_id=".$set_lang_id,
			"lang_name" => $ld["name"],
			"doc_url" => aw_ini_get("baseurl")."/".$od["oid"]."?set_lang_id=".$set_lang_id,
			"doc_name" => $od["name"],
			"trans_msg" => nl2br($ld["meta"]["lang_trans_msg"] != "" ? $ld["meta"]["lang_trans_msg"] : $ld["meta"]["trans_msg"])
		));

		$hd = $nd = "";
		if ($tr[$set_lang_id])
		{
			$hd = $this->parse("HAS_DOC");
		}
		else
		{
			$nd = $this->parse("NO_DOC");
		}

		$this->vars(array(
			"HAS_DOC" => $hd,
			"NO_DOC" => $nd,
		));

		$lt .= $this->parse("LANGUAGE_SEL");

		foreach($llist as $lid => $lname)
		{
			if ($lid == $set_lang_id)
			{
				continue;
			}

			$ld = $l->fetch($lid);
			$od = $tr[$lid];

			$this->vars(array(
				"lang_baseurl" => aw_ini_get("baseurl")."/?set_lang_id=".$lid,
				"lang_name" => $ld["name"],
				"doc_url" => aw_ini_get("baseurl")."/".$od["oid"]."?set_lang_id=".$lid,
				"doc_name" => $od["name"],
				"trans_msg" => nl2br($ld["meta"]["lang_trans_msg"] != "" ? $ld["meta"]["lang_trans_msg"] : $ld["meta"]["trans_msg"])
			));

			$hd = $nd = "";
			if ($tr[$lid])
			{
				$hd = $this->parse("HAS_DOC");
			}
			else
			{
				$nd = $this->parse("NO_DOC");
			}

			$this->vars(array(
				"HAS_DOC" => $hd,
				"NO_DOC" => $nd,
			));

			$lt .= $this->parse("LANGUAGE");
		}

		$this->vars(array(
			"LANGUAGE" => $lt,
			"LANGUAGE_SEL" => "",
		));

		return $this->parse();
	}
};
?>
