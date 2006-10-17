<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/conference.aw,v 1.2 2006/10/17 16:26:29 tarvo Exp $
// conference.aw - Konverents 
/*

@classinfo syslog_type=ST_CONFERENCE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property topic_comment type=textarea cols=50 rows=6
	@caption L&uuml;hikirjeldus/teema

	@property organizers type=relpicker reltype=RELTYPE_ORGANIZER multiple=1
	@caption Korraldajad

	@property catering_warehouse type=relpicker reltype=RELTYPE_CATERING_WAREHOUSE
	@caption Toitlustuse ladu

	@property start_time type=text value=ehitatakse_mujalt
	@caption Algusaeg

	@property end_time type=text value=ehitatakase_mujalt
	@caption L&otilde;puaeg

	@property place type=relpicker reltype=RELTYPE_LOCATION
	@caption Toimumiskoht

	@property conference_plan type=textarea cols=50 rows=6
	@caption Konverentsi kava

	@property conference_plan_file type=file
	@caption Konverenrtsikava failina

	@property extra_info type=textarea cols=50 rows=6
	@caption Lisainfo

# TAB ESINEJAD
@groupinfo presenters caption="Esinejad"
@default group=presenters

	@property presenters type=releditor mode=manager reltype=RELTYPE_PRESENTER props=firstname,lastname table_fields=firstname,lastname

# TAB OSALEJAD
@groupinfo participants caption="Osalejad"
@default group=participants

	@property participants type=releditor mode=manager reltype=RELTYPE_PARTICIPANT props=firstname,lastname table_fields=firstname,lastname

# TAB RESSURSID
@groupinfo resources caption="Ressursid"

	@groupinfo room_resources caption="Ruumi ressursid" parent=resources
	@default group=room_resources

		@property room_resources type=table no_caption=1
		@caption Ruumide ressursid

	@groupinfo catering_resources caption="Toitlustuse ressursid" parent=resources
	@default group=catering_resources

		@property catering_resources type=text no_caption=1
		@caption Toitlustuse ressursid

	@groupinfo other_resources caption="Teised ressursid" parent=resources
	@default group=other_resources

		@property accommondation type=textarea cols=50 rows=6
		@caption Majutus

		@property transport type=textarea cols=50 rows=6
		@caption Transport

# TAB SPONSORID
@groupinfo sponsors caption="Sponsorid"
@default group=sponsors

	@property sponsors type=releditor mode=manager reltype=RELTYPE_SPONSOR props=name table_fields=name


@reltype ORGANIZER value=1 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Korraldaja

@reltype LOCATION value=2 clid=CL_LOCATION
@caption Toimumiskoht

@reltype PRESENTER value=3 clid=CL_CRM_PERSON
@caption Esineja

@reltype PARTICIPANT value=4 clid=CL_CRM_PERSON
@caption Osaleja

@reltype SPONSOR value=5 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption Sponsor

@reltype CATERING_WAREHOUSE value=6 clid=CL_SHOP_WAREHOUSE
@caption Toitlustuse ladu

@reltype RESERVATION value=7 clid=CL_RESERVATION
@caption Ruumi reservatsioon
*/

class conference extends class_base
{
	function conference()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/conference",
			"clid" => CL_CONFERENCE
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
	function get_reservations($oid)
	{
		if(!is_oid($oid))
		{
			return array();
		}
		$c = new connection();
		$conns = $c->find(array(
			"to.class_id" => CL_RESERVATION,
			"reltype" => "RELTYPE_RESERVATION",
			"from" => $oid,
		));
		$ret = array();
		foreach($conns as $data)
		{
			$ret[$data["to"]] = obj($data["to"]);
		}
		return $ret;
	}

	function _get_room_resources($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "free_resources",
			"caption" => t("vabu ressursse"),
		));
		$t->define_field(array(
			"name" => "room",
			"caption" => t("Ruum"),
		));
		$res = $this->get_reservations($arr["obj_inst"]->id());
		$room_inst = get_instance(CL_ROOM);
		foreach($res as $oid => $obj)
		{
			$room = $obj->prop("resource");
			$room_obj = obj($room);
			$resources = $room_inst->get_room_resources($room);
			foreach($resources as $res_id => $res_obj)
			{
				$t->define_data(array(
					"name" => $res_obj->name(),
					"room" => $room_obj->name(),
				));
			}

		}
		$t->set_rgroupby(array(
			"group" => "room",
		));
}

	function _get_catering_resources($arr)
	{
		$catering_wh = obj($arr["obj_inst"]->prop("catering_warehouse"));
		$tmp = array(
			"obj_inst" => $catering_wh,
		);
		$warehouse = get_instance(CL_SHOP_WAREHOUSE);
		$warehouse->_init_view($tmp);
		$arr["prop"]["value"] = $warehouse->do_prod_list(&$tmp);
		
	}
}
?>
