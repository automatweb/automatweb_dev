<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_location.aw,v 1.6 2007/12/06 01:47:25 dragut Exp $
// scm_location.aw - Toimumiskoht 
/*

@classinfo syslog_type=ST_SCM_LOCATION relationmgr=yes no_status=1 prop_cb=1
@tableinfo scm_location index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property comment type=textarea
@caption Kirjeldus

@property loc_path type=textarea table=scm_location field=location_path
@caption Kohale saab

@property address type=relpicker table=scm_location field=address reltype=RELTYPE_ADDRESS
@caption Aadress

@property map_url type=textbox table=scm_location field=map_url
@caption Kaart

//seda peab veel palju muutma
@property map type=relpicker table=scm_location field=map reltype=RELTYPE_MAP
@caption Kaardi pilt

@property photo type=relpicker table=scm_location field=photo reltype=RELTYPE_PHOTO
@caption Foto kohast

@property make_copy type=choose multiple=1 field=meta method=serialize
@caption Tee koopia

@groupinfo transl caption=T&otilde;lgi
@default group=transl
	
	@property transl type=callback callback=callback_get_transl store=no
	@caption T&otilde;lgi



@reltype MAP value=1 clid=CL_IMAGE
@caption Kaart

@reltype PHOTO value=2 clid=CL_IMAGE
@caption Foto

@reltype ADDRESS value=3 clid=CL_CRM_ADDRESS
@caption Aadress

*/

class scm_location extends class_base
{
	function scm_location()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_location",
			"clid" => CL_SCM_LOCATION
		));
		$this->trans_props = array(
			"name", "loc_path", "description"
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;
		}
		return $retval;
	}	
	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function get_locations()
	{
		$list = new object_list(array(
			"class_id" => CL_LOCATION,
		));
		return $list->arr();
	}

	function add_location($arr = array())
	{
		$obj = obj();
		$obj->set_parent($arr["parent"]);
		$obj->set_class_id(CL_LOCATION);
		$obj->set_name($arr["name"]);
		$obj->set_prop("address", $arr["address"]);
		$obj->set_prop("map", $arr["map"]);
		$obj->set_prop("photo", $arr["photo"]);
		$oid = $obj->save_new();
		return $oid;
	}
	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($table, $field, $query, $error)
	{
		if (empty($field))
		{
			$this->db_query('CREATE TABLE '.$table.' (
				oid INT PRIMARY KEY NOT NULL, 
				address int
			)');
			return true;
		}

		
		switch ($field)
		{
			case 'address':
			case 'map':
			case 'photo':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
			case 'map_url':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'text'
				));
                                return true;
			case 'location_path':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'text'
				));
                                return true;
		}
		return false;
	}
}
?>
