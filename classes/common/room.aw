<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room.aw,v 1.1 2006/10/12 12:42:16 tarvo Exp $
// room.aw - Ruum 
/*

@classinfo syslog_type=ST_ROOM relationmgr=yes no_comment=1 no_status=1 prop_cb=1


@default table=objects
@default field=meta
@default method=serialize

# TAB GENERAL

@groupinfo general caption="&Uuml;ldine"
@default group=general

	@layout general_split type=hbox width=50%:50%

	@layout general_up type=vbox closeable=1 area_caption=&Uuml;ldinfo parent=general_split
	@default parent=general_up

		@property name type=textbox
		@caption Nimi

		@property location type=relpicker reltype=RELTYPE_LOCATION
		@caption Asukoht

		@property owner type=relpicker reltype=RELTYPE_OWNER
		@caption Omanik

		@property inventory type=relpicker reltype=RELTYPE_INVENTORY
		@caption Varustuse kataloog

		@property area type=relpicker reltype=RELTYPE_AREA
		@caption Valdkond

	@layout general_down type=vbox closeable=1 area_caption=Mahutavus&#44;&nbsp;kasutustingimused parent=general_split
	@default parent=general_down

		@property square_meters type=textbox
		@caption Suurus(ruutmeetrites)

		@property normal_capacity type=textbox
		@caption Normaalne mahutavus

		@property max_capacity type=textbox
		@caption Maksimaalne mahutavus

		@property conditions type=relpicker reltype=RELTYPE_CONDITIONS
		@caption Kasutustingimused

# TAB CALENDAR

@groupinfo calendar caption="Kalender"
@default group=calendar,parent=
	@property dummy__ type=hidden

# TAB IMAGES

@groupinfo images caption="Pildid"
@default group=images,parent=
	@property dummy_ type=hidden

# TAB PRICES

@groupinfo prices caption="Hinnad"
@default group=prices,parent=
	@property dummy type=hidden

# RELTYPES

@reltype LOCATION value=1 clid=CL_LOCATION
@caption Asukoht

@reltype OWNER value=2 clid=CL_CRM_COMPANY
@caption Omanik

@reltype INVENTORY value=3 clid=CL_SHOP_WAREHOUSE
@caption Varustuse kataloog

@reltype AREA value=4 clid=CL_CRM_FIELD_CONFERENCE_ROOM
@caption Valdkond

@reltype CONDITIONS value=5 clid=CL_DOCUMENT
@caption Kasutustingimused

*/

class room extends class_base
{
	function room()
	{
		$this->init(array(
			"tpldir" => "common/room",
			"clid" => CL_ROOM
		));
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
