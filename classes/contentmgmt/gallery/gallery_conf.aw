<?php

/*

@classinfo syslog_type=ST_GALLERY_CONF relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property conf_folders type=relpicker field=meta method=serialize reltype=RELTYPE_FOLDER multiple=1
@caption Kataloogid, kus konf kehtib

@property conf_ratings type=relpicker field=meta method=serialize reltype=RELTYPE_RATE multiple=1
@caption Hindamisobjektid

@property images_folder type=relpicker field=meta method=serialize reltype=RELTYPE_IMAGES_FOLDER
@caption Piltide asukoht

@property img_vert type=text field=meta method=serialize
@caption Kui pilt on k&otilde;rgem kui laiem

@property v_tn_width type=textbox size=5 field=meta method=serialize
@caption V&auml;ikese pildi laius

@property v_tn_height type=textbox size=5 field=meta method=serialize
@caption V&auml;ikese pildi k&otilde;rgus

@property v_width type=textbox size=5 field=meta method=serialize
@caption Suure pildi laius

@property v_height type=textbox size=5 field=meta method=serialize
@caption Suure pildi k&otilde;rgus

@property img_horiz type=text field=meta method=serialize
@caption Kui pilt on laiem kui k&otilde;rgem 

@property h_tn_width type=textbox size=5 field=meta method=serialize
@caption V&auml;ikese pildi laius

@property h_tn_height type=textbox size=5 field=meta method=serialize
@caption V&auml;ikese pildi k&otilde;rgus

@property h_width type=textbox size=5 field=meta method=serialize
@caption Suure pildi laius

@property h_height type=textbox size=5 field=meta method=serialize
@caption Suure pildi k&otilde;rgus


*/

define("RELTYPE_FOLDER", 1);
define("RELTYPE_RATE", 2);
define("RELTYPE_IMAGES_FOLDER", 3);

class gallery_conf extends class_base
{
	function gallery_conf()
	{
		$this->init(array(
			'clid' => CL_GALLERY_CONF
		));
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_RATE => "hindamisobjektid",
			RELTYPE_FOLDER => "hallatav kataloog",
			RELTYPE_IMAGES_FOLDER => "galerii piltide kataloog",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_FOLDER)
		{
			return array(CL_PSEUDO);
		}
		if ($args["reltype"] == RELTYPE_IMAGES_FOLDER)
		{
			return array(CL_PSEUDO);
		}
		if ($args["reltype"] == RELTYPE_RATE)
		{
			return array(CL_RATE);
		}
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$this->db_query("DELETE FROM gallery_conf2menu WHERE conf_id = '$id'");
		$d = new aw_array($ob['meta']['conf_folders']);
		foreach($d->get() as $fld)
		{
			$this->db_query("INSERT INTO gallery_conf2menu(menu_id, conf_id) VALUES('$fld','$id')");
		}
	}

	function get_image_folder($id)
	{
		$obj = $this->get_object($id);
		return $obj['meta']['images_folder'];
	}

	function get_rate_objects($id)
	{
		$obj = $this->get_object($id);
		return $obj['meta']['conf_ratings'];
	}
}
?>
