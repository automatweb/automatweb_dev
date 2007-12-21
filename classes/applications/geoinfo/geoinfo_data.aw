<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/geoinfo/geoinfo_data.aw,v 1.2 2007/12/21 09:26:09 robert Exp $
// geoinfo_data.aw - Geoinfo andmed 
/*

@classinfo syslog_type=ST_GEOINFO_DATA relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=data

	@property name type=textbox table=objects
	@caption Nimi
	
	@property obj_oid type=textbox field=meta method=serialize
	@caption Objekti ID

	@property coord_y type=textbox field=meta method=serialize
	@caption Pikkuskoordinaat

	@property coord_x type=textbox field=meta method=serialize
	@caption Laiuskoordinaat

	@property address type=textbox field=meta method=serialize
	@caption Aadress

	@property open_show type=chooser field=meta method=serialize
	@caption N&auml;idatakse avamisel

@default group=usert

	@property usertf1 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 1

	@property userta1 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 2

	@property usertf2 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 3

	@property userta2 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 4

	@property usertf3 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 5

	@property userta3 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 6

	@property usertf4 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 7

	@property userta4 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 8

	@property usertf5 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 9

	@property userta5 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 10

	@property usertf6 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 11

	@property userta6 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 12

	@property usertf7 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 13

	@property userta7 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 14

	@property usertf8 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 15

	@property userta8 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 16

	@property usertf9 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 17

	@property userta9 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 18

	@property usertf10 type=textbox field=meta method=serialize
	@caption Kasutaja defineeritud 19

	@property userta10 type=textarea cols=46 rows=3 field=meta method=serialize
	@caption Kasutaja defineeritud 20

@default group=pm_styles
	
	@property icon_t type=text store=no

	@property noicon type=checkbox ch_value=1 field=meta method=serialize
	@caption Ikooni ei kuvata

	@property icon_style type=relpicker reltype=RELTYPE_ICON store=connect field=meta method=serialize
	@caption Ikooni stiil
	
	@property icon_color type=colorpicker field=meta method=serialize
	@caption Ikooni värv

	@property icon_size type=textbox field=meta method=serialize size=3
	@caption Ikooni suurus (0-100%)
	
	@property icon_transp type=textbox field=meta method=serialize size=3
	@caption Ikooni l&auml;bipaistvus (0-100%)

	@property label_t type=text store=no

	@property label_color type=colorpicker field=meta method=serialize
	@caption Sildi värv

	@property label_transp type=textbox field=meta method=serialize size=3
	@caption Sildi l&auml;bipaistvus (0-100%)

@default group=pm_desc

	@property desc1 type=textarea cols=50 rows=20 field=meta method=serialize
	@caption Kirjeldus 1

	@property desc2 type=textarea cols=50 rows=20 field=meta method=serialize
	@caption Kirjeldus 2

@default group=pm_view

	@property view_t type=text store=no

	@property view_range type=textbox field=meta method=serialize size=3
	@caption Vaataja kaugus maapinnast (m)

	@property view_heading type=textbox field=meta method=serialize size=3
	@caption Suund (0-360 kraadi)

	@property view_tilt type=textbox field=meta method=serialize size=3
	@caption Vaatenurk maapinna suhtes (0-360 kraadi)

	@property height_t type=text store=no
	
	@property icon_height type=textbox field=meta method=serialize size=3
	@caption Ikooni k&otilde;rgus maapinnast (m)

@groupinfo data caption=Andmed parent=general
@groupinfo usert caption="Kasutaja defineeritud" parent=general

@groupinfo placemark caption=Kohapunkt
	@groupinfo pm_styles caption=Stiilid parent=placemark
	@groupinfo pm_desc caption=Kirjeldus parent=placemark
	@groupinfo pm_view caption=Vaade parent=placemark

@reltype ICON value=1 clid=CL_IMAGE
@caption Ikooni stiil

*/

class geoinfo_data extends class_base
{
	function geoinfo_data()
	{
		$this->init(array(
			"tpldir" => "applications/geoinfo/geoinfo_data",
			"clid" => CL_GEOINFO_DATA
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "userta1":
			case "usertf1":
			case "userta2":
			case "usertf2":
			case "userta3":
			case "usertf3":
			case "userta4":
			case "usertf4":
			case "userta5":
			case "usertf5":
			case "userta6":
			case "usertf6":
			case "userta7":
			case "usertf7":
			case "userta8":
			case "usertf8":
			case "userta9":
			case "usertf9":
			case "userta10":
			case "usertf10":
				$p = obj($arr["obj_inst"]->parent());
				if($p->class_id() == CL_GEOINFO_MANAGER && is_oid($arr["obj_inst"]->prop("obj_oid")))
					$prop["type"] = "text";
				break;
			case "icon_t":
				$prop["value"] = t("Ikoon");
				break;
			case "label_t":
				$prop["value"] = t("Silt");
				break;
			case "height_t":
				$prop["value"] = t("K&otilde;rgus");
				break;
			case "view_t":
				$prop["value"] = t("Vaade");
				break;
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
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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

//-- methods --//
}
?>
