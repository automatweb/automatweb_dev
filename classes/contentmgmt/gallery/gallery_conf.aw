<?php

/*

@classinfo syslog_type=ST_GALLERY_CONF relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo imgsize caption=Piltide&nbsp;suurused
@groupinfo logo caption=Logo

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

@property img_vert field=meta method=serialize group=imgsize
@caption Kui pilt on k&otilde;rgem kui laiem

@property v_tn_subimage type=checkbox ch_value=1 field=meta method=serialize group=imgsize
@caption Kas v&auml;ike pilt on kadreeritud

@property v_tn_subimage_left type=textbox size=5 field=meta method=serialize group=imgsize
@caption Mitu pikslit vasakult kaader algab

@property v_tn_subimage_top type=textbox size=5 field=meta method=serialize group=imgsize
@caption Mitu pikslit &uuml;levalt kaader algab

@property v_tn_subimage_width type=textbox size=5 field=meta method=serialize group=imgsize
@caption Kaadri laius

@property v_tn_subimage_height type=textbox size=5 field=meta method=serialize group=imgsize
@caption Kaadri k&otilde;rgus

@property v_tn_width type=textbox size=5 field=meta method=serialize group=imgsize
@caption V&auml;ikese pildi laius

@property v_tn_height type=textbox size=5 field=meta method=serialize group=imgsize
@caption V&auml;ikese pildi k&otilde;rgus

@property v_width type=textbox size=5 field=meta method=serialize group=imgsize
@caption Suure pildi laius

@property v_height type=textbox size=5 field=meta method=serialize group=imgsize
@caption Suure pildi k&otilde;rgus

@property img_horiz field=meta method=serialize group=imgsize
@caption Kui pilt on laiem kui k&otilde;rgem 

@property h_tn_subimage type=checkbox ch_value=1 field=meta method=serialize group=imgsize
@caption Kas v&auml;ike pilt on kadreeritud

@property h_tn_subimage_left type=textbox size=5 field=meta method=serialize group=imgsize
@caption Mitu pikslit vasakult kaader algab

@property h_tn_subimage_top type=textbox size=5 field=meta method=serialize group=imgsize
@caption Mitu pikslit &uuml;levalt kaader algab

@property h_tn_subimage_width type=textbox size=5 field=meta method=serialize group=imgsize
@caption Kaadri laius

@property h_tn_subimage_height type=textbox size=5 field=meta method=serialize group=imgsize
@caption Kaadri k&otilde;rgus

@property h_tn_width type=textbox size=5 field=meta method=serialize group=imgsize
@caption V&auml;ikese pildi laius

@property h_tn_height type=textbox size=5 field=meta method=serialize group=imgsize
@caption V&auml;ikese pildi k&otilde;rgus

@property h_width type=textbox size=5 field=meta method=serialize group=imgsize
@caption Suure pildi laius

@property h_height type=textbox size=5 field=meta method=serialize group=imgsize
@caption Suure pildi k&otilde;rgus

@property insert_logo type=checkbox ch_value=1 field=meta method=serialize group=logo
@caption Kas suurele pildile kleepida logo

@property logo_img type=relpicker field=meta method=serialize group=logo reltype=RELTYPE_LOGO
@caption Logo pilt

@property logo_corner type=select field=meta method=serialize group=logo
@caption Mis nurgas

@property logo_dist_x type=textbox size=5 field=meta method=serialize group=logo
@caption Mitu pikslit vertikaalsest servast

@property logo_dist_y type=textbox size=5 field=meta method=serialize group=logo
@caption Mitu pikslit horisontaalsest servast

@property logo_transparency type=textbox size=5 field=meta method=serialize group=logo
@caption Logo l&auml;bipaistvus, 0-100 (0 -0 t&auml;iesti l&auml;bipaistev)

@property logo_text type=textbox field=meta method=serialize group=logo
@caption Logo tekst (%nimi% asendatakse galerii nimega)

@property tn_insert_logo type=checkbox ch_value=1 field=meta method=serialize group=logo
@caption Kas v&auml;ikesele pildile kleepida logo

@property tn_logo_img type=relpicker field=meta method=serialize group=logo reltype=RELTYPE_LOGO
@caption Logo pilt

@property tn_logo_corner type=select field=meta method=serialize group=logo
@caption Mis nurgas

@property tn_logo_dist_x type=textbox size=5 field=meta method=serialize group=logo
@caption Mitu pikslit vertikaalsest servast

@property tn_logo_dist_y type=textbox size=5 field=meta method=serialize group=logo
@caption Mitu pikslit horisontaalsest servast

@property tn_logo_transparency type=textbox size=5 field=meta method=serialize group=logo
@caption Logo l&auml;bipaistvus, 1-100 (1-t&auml;iesti l&auml;bipaistev)

@property tn_logo_text type=textbox field=meta method=serialize group=logo
@caption Logo tekst (%nimi% asendatakse galerii nimega)


*/

define("RELTYPE_FOLDER", 1);
define("RELTYPE_RATE", 2);
define("RELTYPE_IMAGES_FOLDER", 3);
define("RELTYPE_LOGO", 4);

define("CORNER_LEFT_TOP", 1);
define("CORNER_LEFT_BOTTOM", 2);
define("CORNER_RIGHT_TOP", 3);
define("CORNER_RIGHT_BOTTOM", 4);

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
			RELTYPE_LOGO => "logo pilt"
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
		if ($args["reltype"] == RELTYPE_LOGO)
		{
			return array(CL_IMAGE);
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

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		switch($prop['name'])
		{
			case "v_tn_subimage_top":
			case "v_tn_subimage_left":
			case "v_tn_subimage_width":
			case "v_tn_subimage_height":
				if ($arr["obj"]["meta"]["v_tn_subimage"] != 1)
				{
					return PROP_IGNORE;
				}
				break;

			case "h_tn_subimage_top":
			case "h_tn_subimage_left":
			case "h_tn_subimage_width":
			case "h_tn_subimage_height":
				if ($arr["obj"]["meta"]["h_tn_subimage"] != 1)
				{
					return PROP_IGNORE;
				}
				break;

			case "logo_img":
			case "logo_corner":
				$prop["options"] = array(
					CORNER_LEFT_TOP => "&Uuml;lemine vasak",
					CORNER_LEFT_BOTTOM => "Alumine vasak",
					CORNER_RIGHT_TOP => "&Uuml;lemine parem",
					CORNER_RIGHT_BOTTOM => "Alumine parem"
				);
			case "logo_dist_x":
			case "logo_transparency":
			case "logo_dist_y":
				if ($arr["obj"]["meta"]["insert_logo"] != 1)
				{
					return PROP_IGNORE;
				}
				break;

			case "tn_logo_img":
			case "tn_logo_corner":
				$prop["options"] = array(
					CORNER_LEFT_TOP => "&Uuml;lemine vasak",
					CORNER_LEFT_BOTTOM => "Alumine vasak",
					CORNER_RIGHT_TOP => "&Uuml;lemine parem",
					CORNER_RIGHT_BOTTOM => "Alumine parem"
				);
			case "tn_logo_dist_x":
			case "tn_logo_transparency":
			case "tn_logo_dist_y":
				if ($arr["obj"]["meta"]["tn_insert_logo"] != 1)
				{
					return PROP_IGNORE;
				}
				break;
		}
		return PROP_OK;
	}
}
?>
