<?php

define("OBJ_TRANS_ORIG", 1);
define("OBJ_TRANS_TRANSLATION", 2);

class _int_obj_ds_auto_translation extends _int_obj_ds_decorator
{
	function _int_obj_ds_auto_translation($contained)
	{
		parent::_int_obj_ds_decorator($contained);
	}

	////
	// !returns all the object tabel data for the specified object
	// metadata must be unserialized
	function get_objdata($oid, $param = array())
	{
		if ($GLOBALS["__obj_sys_opts"]["no_auto_translation"] == 1)
		{
			return $this->contained->get_objdata($oid, $param);
		}

		$lang_id = aw_global_get("lang_id");
		$req_od = $this->contained->get_objdata($oid, $param);

		// check whether there are any relations of type RELTYPE_TRANSLATION pointing
		// to this object .. 
		$conns = $this->contained->find_connections(array(
			"to" => $oid,
			"type" => RELTYPE_TRANSLATION
		));
		if (count($conns) > 1)
		{
			error::throw(array(
				"id" => ERR_TRANS,
				"msg" => "ds_auto_translation::get_objdata($oid): found more than one translation relation to object $oid!"
			));
		}
		else
		if (count($conns) > 0)
		{
			// we found that there are some connections to this object with translation reltype
			// that means that this is a translation object. so load it and see if we hit the correct one in regards to lang_id
			$objdata = $req_od;

			// mark in cache that this is the original object for the translation
			reset($conns);
			list(,$f_c) = each($conns);
			$this->objdata[$f_c["from"]]["type"] = OBJ_TRANS_ORIG;
			

			if ($objdata["lang_id"] == $lang_id)
			{
				// this is the correct one, return it.

				// mark the translated object in the cache and link back to original
				$this->objdata[$oid]["type"] = OBJ_TRANS_TRANSLATED;
				$this->objdata[$oid]["trans_orig"] = $f_c["from"];

				// but we still gots to read the original and get the untranslated props from that!
				return $this->_merge_obj_dat($this->contained->get_objdata($f_c["from"], $param), $objdata);
			}
			// this is not the corret language object. get the original and try to find
			// a related translation object that has the correct lang_id
			$conns2 = $this->contained->find_connections(array(
				"from" => $f_c["from"],
				"type" => RELTYPE_TRANSLATION,
				"to.lang_id" => $lang_id
			));

			if (count($conns2) > 1)
			{
				error::throw(array(
					"id" => ERR_TRANS,
					"ds_auto_translation::get_objdata($oid): found more than one translation relation from object $conns2[0][from] pointing to an object with lang id $lang_id!"
				));
			}
			else
			if (count($conns2) == 1)
			{
				// mark the found translation connection in the cache

				reset($conns2);
				list(, $f_c2) = each($conns2);

				$this->objdata[$f_c["from"]]["trans_rels"][$lang_id] = $f_c2["to"];

				// mark the translated object in the cache and link back to original
				$this->objdata[$f_c2["to"]]["type"] = OBJ_TRANS_TRANSLATED;
				$this->objdata[$f_c2["to"]]["trans_orig"] = $f_c["from"];

				// the correct object is in $f_c2["to"]
				return $this->_merge_obj_dat($req_od, $this->contained->get_objdata($f_c2["to"], $param));
			}
			else
			{
				return $this->_merge_obj_dat($req_od, $this->contained->get_objdata($f_c["from"], $param));
			}
		}
		else
		{
			// there are no translation connections to this, therefore it is the original. BUT
			// if it is of the incorrect language, we must find if it has a translation to the current lanugage
			// and if it does, return THAT
			$this->objdata[$oid]["type"] = OBJ_TRANS_ORIG;

			if ($req_od["lang_id"] != $lang_id)
			{
				$conns = $this->contained->find_connections(array(
					"from" => $oid,
					"type" => RELTYPE_TRANSLATION,
					"to.lang_id" => $lang_id
				));
				if (count($conns) > 1)
				{
					error::throw(array(
						"id" => ERR_TRANS,
						"msg" => "ds_auto_translation::get_objdata($oid): found more than one translation relation from object $oid pointing to an object with lang id $lang_id!"
					));
				}
				else
				if (count($conns) > 0)
				{
					reset($conns);	
					list(, $dat) = each($conns);

					$this->objdata[$oid]["trans_rels"][$lang_id] = $dat["to"];

					// mark the translated object in the cache and link back to original
					$this->objdata[$dat["to"]]["type"] = OBJ_TRANS_TRANSLATED;
					$this->objdata[$dat["to"]]["trans_orig"] = $oid;

					// the correct object is in $dat["to"]
					return $this->_merge_obj_dat($req_od, $this->contained->get_objdata($dat["to"], $param));
				}
				else
				{
					$ret = $req_od;
				}
			}
			else
			{
				$ret = $req_od;
			}
			return $ret;
		}
	}

	////
	// !reads property data from the database
	// parameters:
	//	properties - property array
	//	tableinfo - tableinfo from propreader
	//	objdata - result of this::get_objdata
	function read_properties($arr)
	{
		if ($GLOBALS["__obj_sys_opts"]["no_auto_translation"] == 1)
		{
			return $this->contained->read_properties($arr);
		}
		extract($arr);
		
		$oid = $objdata["oid"];
		if ($this->objdata[$oid]["type"] == OBJ_TRANS_TRANSLATED)
		{
			// if the object is a translated object, then also read the original and merge the 
			// properties together, based on the settings for each property
			$orig_oid = $this->objdata[$oid]["trans_orig"];
			if (!$orig_oid)
			{
				error::throw(array(
					"id" => ERR_TRANS,
					"msg" => "ds_auto_translation::read_properties(): no original object id for object $oid in get_objdata cache!"
				));
			}

			$orig_data = $this->contained->read_properties(array(
				"properties" => $properties,
				"tableinfo" => $tableinfo,
				"objdata" => $this->contained->get_objdata($orig_oid),
			));

			$trans_data = $this->contained->read_properties($arr);

			// copy untranslatable property values from the original to the translation
			foreach($properties as $prop)
			{
				if ($prop["trans"] != 1)
				{
					$trans_data[$prop["name"]] = $orig_data[$prop["name"]];
				}
			}
			return $trans_data;
		}
		else
		if ($this->objdata[$oid]["type"] == OBJ_TRANS_ORIG)
		{
			// if it is an origin
			return $this->contained->read_properties($arr);
		}
		else
		{
			error::throw(array(
				"id" => ERR_TRANS,
				"msg" => "ds_auto_translation::read_properties(): no info about object $oid in get_objdata cache!"
			));
		}
	}

	function _get_root_obj($oid)
	{
		// now figure out if this is the root obj

		// check whether there are any relations of type RELTYPE_TRANSLATION pointing
		// to this object .. 
		$conns = $this->contained->find_connections(array(
			"to" => $oid,
			"type" => RELTYPE_TRANSLATION
		));
		if (count($conns) > 0)
		{
			// return the from object, cause that's the one
			reset($conns);
			list(, $dat) = each($conns);
			return $dat["from"];
		}
		return $oid;
	}

	////
	// !searches the database
	// params:
	//	array of filter parameters 
	// if class id is present, properties can also be filtered, otherwise only object table fields
	function search($params)
	{
		if ($GLOBALS["__obj_sys_opts"]["no_auto_translation"] == 1)
		{
			return $this->contained->search($params);
		}
		// rewrite parent parameter to point to the real object
		if (isset($params["parent"]))
		{
			if (is_array($params["parent"]))
			{
				$npr = array();
				foreach($params["parent"] as $pr)
				{
					$npr[] = $this->_get_root_obj($pr);
				}
				$params["parent"] = $npr;
			}
			else
			{
				$params["parent"] = $this->_get_root_obj($params["parent"]);
			}
		}
		if (!isset($params["lang_id"]))
		{
			$params["lang_id"] = aw_global_get("lang_id");
		}
		return $this->contained->search($params);
	}

	function _merge_obj_dat($original, $translated)
	{
		// check props
		if (!isset($GLOBALS["properties"][$original["class_id"]]))
		{
			$lp = array(
				"clid" => $original["class_id"]
			);
			if ($original["class_id"] == CL_DOCUMENT)
			{
				$lp["file"] = "doc";
			}

			list($GLOBALS["properties"][$original["class_id"]], $GLOBALS["tableinfo"][$original["class_id"]], $GLOBALS["relinfo"][$original["class_id"]]) = $GLOBALS["object_loader"]->load_properties($lp);
		}

		$tm = $translated["meta"];
		$translated["meta"] = $original["meta"];

		foreach($GLOBALS["properties"][$original["class_id"]] as $pn => $pd)
		{
			if ($pd["trans"] == 1 && $pd["table"] == "objects" && $pd["field"] == "meta" && $pd["method"] == "serialize")
			{
				$translated["meta"][$pn] = $tm[$pn];
			}
		}
		

		// for the object flags, all flags, except for the OBJ_IS_TRANSLATED flag must be copied from the original
		$tmpf = $translated["flags"];

		$translated["flags"] = $original["flags"] & (~OBJ_IS_TRANSLATED);	// first unset it
		$translated["flags"] |= $tmpf & OBJ_IS_TRANSLATED; // now or the one from the translated object back

		return $translated;
	}

	function save_properties($arr)
	{
		if ($GLOBALS["__obj_sys_opts"]["no_auto_translation"] == 1)
		{
			return $this->contained->save_properties($arr);
		}

		$oid = $arr["objdata"]["oid"];

		// check if the object we are saving is the original
		// if so, then we must also write all the untranslated properties to the translated objects. 
		// currently, just do the objects.jrk thing.
		if ($this->objdata[$oid]["type"] == OBJ_TRANS_ORIG)
		{
			// get all translations
			$conns = $this->contained->find_connections(array(
				"from" => $oid,
				"type" => RELTYPE_TRANSLATION
			));
			foreach($conns as $c)
			{
				$tmp_od = $this->contained->get_objdata($c["to"]);
				$tmp_pv = $this->contained->read_properties(array(
					"properties" => $arr["properties"],
					"tableinfo" => $arr["tableinfo"],
					"objdata" => $tmp_od
				));

				$tmp_od["jrk"] = $arr["objdata"]["jrk"];
				$tmp_pv["jrk"] = $arr["propvalues"]["jrk"];

				$this->save_properties(array(
					"properties" => $arr["properties"],
					"objdata" => $tmp_od,
					"tableinfo" => $arr["tableinfo"],
					"propvalues" => $tmp_pv
				));
			}
		}

		return $this->contained->save_properties($arr);
	}
}

?>
