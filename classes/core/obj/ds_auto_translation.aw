<?php

define("OBJ_TRANS_ORIG", 1);
define("OBJ_TRANS_TRANSLATION", 2);

class _int_obj_ds_auto_translation extends _int_obj_ds_decorator
{
	function _int_obj_ds_auto_translation($contained)
	{
		parent::_int_obj_ds_decorator($contained);
		$this->conn_by_to = array();
		$this->conn_by_from = array();

		enter_function("ds_auto_translation::conn_init");
		// cache conns 
		$this->contained->db_query("
			SELECT 
				source as `from`,
				target as `to`,
				reltype as `type`,
				a.id as `id`,
				o_t.lang_id as `to.lang_id`,
				o_s.lang_id as `from.lang_id`,
				o_t.class_id as `to.class_id`,
				o_s.class_id as `from.class_id`
			FROM 
				aliases a
				LEFT JOIN objects o_s ON o_s.oid = a.source
				LEFT JOIN objects o_t ON o_t.oid = a.target
			WHERE 
				reltype IN (".RELTYPE_TRANSLATION.",".RELTYPE_ORIGINAL.")
				AND o_s.status != 0 AND o_t.status != 0
		");
		while ($row = $this->contained->db_next())
		{
			$this->conn_by_to[$row["to"]][$row["type"]][$row["id"]] = $row;
			$this->conn_by_from[$row["from"]][$row["type"]][$row["id"]] = $row;
		}
		exit_function("ds_auto_translation::conn_init");
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

		if ($GLOBALS["AUTO_TRANS_D"] == 1)
		{
			echo __LINE__."::enter , oid =$oid <br>";
		}
		$lang_id = aw_global_get("lang_id");
		$req_od = $this->contained->get_objdata($oid, $param);

		// idea - don't translate brothers, but if the requested object is a brother, read the data from the
		// brother object and translate the parent, also set lang_id to the correct value. might work.  

		// check if this object is part of the whole translation hooplaa or not. if not, just return the objdata
		if (($req_od["flags"] & (OBJ_HAS_TRANSLATION|OBJ_IS_TRANSLATED|OBJ_NEEDS_TRANSLATION)) == 0)
		{
			if ($GLOBALS["AUTO_TRANS_D"] == 1)
			{
				echo __LINE__."::return regular od, for object is not part of translation <br>";
			}
			return $req_od;
		}

		$sets = array();

		$conn_oid = $oid;
		if ($req_od["brother_of"] != $oid && $req_od["brother_of"])
		{
			$conn_oid = $req_od["brother_of"];
			$sets["oid"] = $req_od["oid"];
			//$tmp = $this->get_objdata($req_od["parent"]);
			$sets["parent"] = $req_od["parent"];//$tmp["oid"];
			if ($GLOBALS["AUTO_TRANS_D"] == 1)
			{
				echo __LINE__."::is brother, read conns from $conn_oid <br>";
			}
		}

		// check whether there are any relations of type RELTYPE_TRANSLATION pointing
		// to this object .. 
		if ($GLOBALS["AUTO_TRANS_D"] == 1)
		{
			echo __LINE__."::read conns TO $conn_oid of type RELTYPE_TRANSLATION <br>";
		}

		$conns = safe_array($this->conn_by_to[$conn_oid][RELTYPE_TRANSLATION]);

		/*if (count($conns) > 1)
		{
			error::raise(array(
				"id" => ERR_TRANS,
				"msg" => sprintf(t("ds_auto_translation::get_objdata(%s): found more than one translation relation to object %s!"), $oid, $oid)
			));
		}
		else*/
		if (count($conns) > 0)
		{
			// we found that there are some connections to this object with translation reltype
			// that means that this is a translation object. so load it and see if we hit the correct one in regards to lang_id
			$objdata = $req_od;
			if ($GLOBALS["AUTO_TRANS_D"] == 1)
			{
				echo __LINE__."::found some connections to this object of type RELTYPE_TRANSLATION, meaning that this is a translation object <br>";
			}

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
				if ($GLOBALS["AUTO_TRANS_D"] == 1)
				{
					echo __LINE__."::found correct translation object oid = $f_c[from], merge and return <br>------------------------------<br>";
				}
				return $this->_merge_obj_dat($this->contained->get_objdata($f_c["from"], $param), $objdata);
			}
			// this is not the corret language object. get the original and try to find
			// a related translation object that has the correct lang_id
			
			$dat = safe_array($this->conn_by_from[$f_c["from"]][RELTYPE_TRANSLATION]);
			$conns2 = array();	
			foreach($dat as $idx => $inf)
			{
				if ($inf["to.lang_id"] == $lang_id)
				{
					$conns2[$idx] = $inf;
				}
			}

			if ($GLOBALS["AUTO_TRANS_D"] == 1)
			{
				echo __LINE__."::incorrect language, get original and trace from that original = $f_c[from], read connections from that <br>";
			}

			/*if (count($conns2) > 1)
			{
				error::raise(array(
					"id" => ERR_TRANS,
					"ds_auto_translation::get_objdata($oid): found more than one translation relation from object $conns2[0][from] pointing to an object with lang id $lang_id!"
				));
			}
			else*/
			if (count($conns2) > 0)
			{
				// mark the found translation connection in the cache

				reset($conns2);
				list(, $f_c2) = each($conns2);
				if ($GLOBALS["AUTO_TRANS_D"] == 1)
				{
					echo __LINE__."::echo found coeect connection $f_c2[id] , from = $f_c[from] to $f_c[to], merge and return <br>-----------------------<br>";
				}

				$this->objdata[$f_c["from"]]["trans_rels"][$lang_id] = $f_c2["to"];

				// mark the translated object in the cache and link back to original
				$this->objdata[$f_c2["to"]]["type"] = OBJ_TRANS_TRANSLATED;
				$this->objdata[$f_c2["to"]]["trans_orig"] = $f_c["from"];

				// the correct object is in $f_c2["to"]
				$tmp = $this->_merge_obj_dat($req_od, $this->contained->get_objdata($f_c2["to"], $param));
				return $tmp;
			}
			else
			{
				$tmp =  $this->_merge_obj_dat($req_od, $this->contained->get_objdata($f_c["from"], $param));
				if ($GLOBALS["AUTO_TRANS_D"] == 1)
				{
					echo __LINE__."::did not find connection to required language, return merge of original ($f_c[from] ) and requested oid <br>---------------------------<br>";
				}
				return $tmp;
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
				if ($GLOBALS["AUTO_TRANS_D"] == 1)
				{
					echo __LINE__."::this is the original object, find correct translation from it (from = $conn_oid , type = RELTYPE_TRANSLATION , to.lang_id = $lang_id)  <br>";
				}
				
				$dat = safe_array($this->conn_by_from[$conn_oid][RELTYPE_TRANSLATION]);
				$conns = array();	
				foreach($dat as $idx => $inf)
				{
					if ($inf["to.lang_id"] == $lang_id)
					{
						$conns[$idx] = $inf;
					}
				}

				/*if (count($conns) > 1)
				{
					error::raise(array(
						"id" => ERR_TRANS,
						"msg" => sprintf(t("ds_auto_translation::get_objdata(%s): found more than one translation relation from object %s pointing to an object with lang id %s!"), $oid, $oid, $lang_id)
					));
				}
				else*/
				if (count($conns) > 0)
				{
					reset($conns);	
					list(, $dat) = each($conns);

					$this->objdata[$oid]["trans_rels"][$lang_id] = $dat["to"];

					// mark the translated object in the cache and link back to original
					$this->objdata[$dat["to"]]["type"] = OBJ_TRANS_TRANSLATED;
					$this->objdata[$dat["to"]]["trans_orig"] = $oid;

					// the correct object is in $dat["to"]
					$tmp =  $this->_merge_obj_dat($req_od, $this->contained->get_objdata($dat["to"], $param));
					if ($GLOBALS["AUTO_TRANS_D"] == 1)
					{
						echo __LINE__."::got correct translation rel, return merge $req_od[oid] with $dat[to]<br>--------------------------------<br>";
					}
					foreach($sets as $k => $v)
					{
						$tmp[$k] = $v;
					}
					return $tmp;
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
			if ($GLOBALS["AUTO_TRANS_D"] == 1)
			{
				echo __LINE__."::return requested oid cause did not find anything. damn. oid = $oid , retoid = $ret[oid] <br>------------------------<br>";
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

		// check if this object is part of the whole translation hooplaa or not. if not, just return the objdata
		if (($objdata["flags"] & (OBJ_HAS_TRANSLATION|OBJ_IS_TRANSLATED|OBJ_NEEDS_TRANSLATION)) == 0)
		{
			return $this->contained->read_properties($arr);
		}
		
		$oid = $objdata["oid"];

		if (!isset($this->objdata[$oid]))
		{
			$this->get_objdata($oid);
		}

		if ($this->objdata[$oid]["type"] == OBJ_TRANS_TRANSLATED)
		{
			// if the object is a translated object, then also read the original and merge the 
			// properties together, based on the settings for each property
			$orig_oid = $this->objdata[$oid]["trans_orig"];
			if (!$orig_oid)
			{
				error::raise(array(
					"id" => ERR_TRANS,
					"msg" => sprintf(t("ds_auto_translation::read_properties(): no original object id for object %s in get_objdata cache!"), $oid)
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
			error::raise(array(
				"id" => ERR_TRANS,
				"msg" => sprintf(t("ds_auto_translation::read_properties(): no info about object %s in get_objdata cache!"), $oid)
			));
		}
	}

	function _get_root_obj($oid)
	{
		// now figure out if this is the root obj

		// check whether there are any relations of type RELTYPE_TRANSLATION pointing
		// to this object .. 
		/*$conns = $this->contained->find_connections(array(
			"to" => $oid,
			"type" => RELTYPE_TRANSLATION
		));*/

		$conns = safe_array($this->conn_by_to[$oid][RELTYPE_TRANSLATION]);

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
			if (is_object($params["parent"]) && get_class($params["parent"]) == "aw_array")
			{
				$npr = array();
				foreach($params["parent"]->get() as $pr)
				{
					$npr[] = $this->_get_root_obj($pr);
				}
				$params["parent"] = $npr;
			}
			else
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

	function find_connections($arr)
	{
		if ($arr["from"] && $arr["from.class_id"] && $arr["type"])
		{
			// beisikli if were doing connections_from
			$cldef = $GLOBALS["cfg"]["__default"]["classes"][$arr["from.class_id"]];
			if ($cldef["rels"][$arr["type"]]["trans_always_original"] == 1)
			{
				if (is_array($arr["from"]))
				{
					$tmp = array();
					foreach($arr["from"] as $t_from)
					{
						$tmp[] = $this->_get_original_obj($t_from);
					}
					$arr["from"] = $tmp;
				}
				else
				{
					$arr["from"] = $this->_get_original_obj($arr["from"]);
				}
			}
		}

		if ($arr["to"] && $arr["from.class_id"] && $arr["type"])
		{
			// beisikli if were doing connections_to

			$cldef = $GLOBALS["cfg"]["__default"]["classes"][$arr["from.class_id"]];
			if ($cldef["rels"][$arr["type"]]["trans_always_original"] == 1)
			{
				if (is_array($arr["to"]))
				{
					$tmp = array();
					foreach($arr["to"] as $t_to)
					{
						$tmp[] = $this->_get_original_obj($t_to);
					}
					$arr["to"] = $tmp;
				}
				else
				{
					$arr["to"] = $this->_get_original_obj($arr["to"]);
				}
			}
		}

		return $this->contained->find_connections($arr);
	}

	function _get_original_obj($oid)
	{
		// if this is the original
		$conns = safe_array($this->conn_by_from[$oid][RELTYPE_TRANSLATION]);
		if (count($conns) > 0)
		{
			return $oid;
		}
		$conn = reset(safe_array($this->conn_by_from[$oid][RELTYPE_ORIGINAL]));
		if (!$conn)
		{
			return $oid;
		}
		return $conn["to"];
	}
}

?>
