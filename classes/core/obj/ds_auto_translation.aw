<?php

define("OBJ_TRANS_ORIG", 1);
define("OBJ_TRANS_TRANSLATION", 2);

class _int_obj_ds_auto_translation 
{
	//////////////////////////////////////////////
	// private variables
	var $contained;	// the contained ds

	function _int_obj_ds_auto_translation($contained)
	{
		$this->contained = $contained;
	}

	////
	// !returns the oid that has the specified alias
	// parameters:
	//	alias - required
	//	site_id - optional
	//	parent - optional
	function get_oid_by_alias($arr)
	{
		return $this->contained->get_oid_by_alias($arr);
	}

	////
	// !returns all the object tabel data for the specified object
	// metadata must be unserialized
	function get_objdata($oid)
	{
		$lang_id = aw_global_get("lang_id");

		$req_od = $this->contained->get_objdata($oid);

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
			$this->objdata[$conns[0]["from"]]["type"] = OBJ_TRANS_ORIG;
			

			if ($objdata["lang_id"] == $lang_id)
			{
				// this is the correct one, return it.

				// mark the translated object in the cache and link back to original
				$this->objdata[$oid]["type"] = OBJ_TRANS_TRANSLATED;
				$this->objdata[$oid]["trans_orig"] = $conns[0]["from"];

				return $objdata;
			}
			// this is not the corret language object. get the original and try to find
			// a related translation object that has the correct lang_id
			$conns2 = $this->contained->find_connections(array(
				"from" => $conns[0]["from"],
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
				$this->objdata[$conns[0]["from"]]["trans_rels"][$lang_id] = $conns2[0]["to"];

				// mark the translated object in the cache and link back to original
				$this->objdata[$conns2[0]["to"]]["type"] = OBJ_TRANS_TRANSLATED;
				$this->objdata[$conns2[0]["to"]]["trans_orig"] = $conns[0]["from"];

				// the correct object is in $conns2[0]["to"]
				$ret = $this->contained->get_objdata($conns2[0]["to"]);
				$ret["lang_id"] = $req_od["lang_id"];
				return $ret;
			}
			else
			{
				// no connections, return the untranslated object
				$ret = $this->contained->get_objdata($conns[0]["from"]);
				$ret["lang_id"] = $req_od["lang_id"];
				return $ret;
			}
		}
		else
		{
			// no connections, therefore it must be the correct one

			$this->objdata[$oid]["type"] = OBJ_TRANS_ORIG;

			$ret = $req_od;
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

	////
	// !creates new object, returns object id
	// params:
	//	properties - prop array from propreader
	//	objdata - object data from objtable
	//	tableinfo - tableinfo from prop reader
	// returns:
	//	new oid
	function create_new_object($arr)
	{
		return $this->contained->create_new_object($arr);
	}

	////
	// !saves object properties, including all object table fields, 
	// params:
	//	properties - prop array from propreader
	//	objdata - object data from objtable
	//	tableinfo - tableinfo from prop reader
	//	propvalues - property values
	function save_properties($arr)
	{
		return $this->contained->save_properties($arr);
	}

	////
	// !returns all data for connection $id
	function read_connection($id)
	{
		return $this->contained->read_connection($id);
	}

	////
	// !saves connection 
	function save_connection($data)
	{
		return $this->contained->save_connection($data);
	}

	////
	// !deletes connection $id
	function delete_connection($id)
	{
		return $this->contained->delete_connection($id);
	}

	
	////
	// !returns all connections that match filter
	function find_connections($arr)
	{
		return $this->contained->find_connections($arr);
	}

	////
	// !searches the database
	// params:
	//	array of filter parameters 
	// if class id is present, properties can also be filtered, otherwise only object table fields
	function search($params)
	{
		return $this->contained->search($params);
	}

	function can($a, $b)
	{
		return $this->contained->can($a,$b);
	}

	function delete_object($oid)
	{
		return $this->contained->delete($oid);
	}
}

?>
