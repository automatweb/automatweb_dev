<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room_settings.aw,v 1.2 2006/11/24 15:59:49 markop Exp $
// room_settings.aw - Ruumi seaded 
/*

@classinfo syslog_type=ST_ROOM_SETTINGS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property max_times_per_day type=textbox field=meta method=serialize
@caption Maksimaalne aegade arv samal p&auml;eval


@groupinfo whom caption="Kellele kehtib"
@default group=whom
	@property users type=relpicker multiple=1 store=connect reltype=RELTYPE_USER field=meta method=serialize
	@caption Kasutajad

	@property persons type=relpicker multiple=1 store=connect reltype=RELTYPE_PERSON field=meta method=serialize
	@caption Isikud

	@property cos type=relpicker multiple=1 store=connect reltype=RELTYPE_COMPANY field=meta method=serialize
	@caption Organisatsioonid

	@property sects type=relpicker multiple=1 store=connect reltype=RELTYPE_SECTION field=meta method=serialize
	@caption Osakonnad

	@property profs type=relpicker multiple=1 store=connect reltype=RELTYPE_PROFESSION field=meta method=serialize
	@caption Ametinimetused

	@property everyone type=checkbox ch_value=1 table=objects field=flags
	@caption K&otilde;ik




@reltype USER value=1 clid=CL_USER
@caption Kasutaja

@reltype PERSON value=3 clid=CL_CRM_PERSON
@caption Isik

@reltype COMPANY value=4 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype SECTION value=5 clid=CL_CRM_SECTION
@caption Osakond

@reltype PROFESSION value=6 clid=CL_CRM_PROFESSION
@caption Ametinimetus

*/

class room_settings extends class_base
{
	function room_settings()
	{
		$this->init(array(
			"tpldir" => "common/room_settings",
			"clid" => CL_ROOM_SETTINGS
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

	/**
		@param $room optional
		room id
	**/
	function get_current_settings($room)
	{
		if(is_oid($room) && $this->can("view" , $room))
		{
			$room = obj($room);
			$oids = $room->prop("settings");
		}
		$u = get_instance(CL_USER);
		$curp = $u->get_current_person();
		$curco = $u->get_current_company();
		$cd = get_instance("applications/crm/crm_data");
		$cursec = $cd->get_current_section();
		$curprof = $cd->get_current_profession();

		$ol = new object_list(array(
			"class_id" => CL_CRM_SETTINGS,
			"lang_id" => array(),
			"oid" => $oids,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_SETTINGS.RELTYPE_USER" => aw_global_get("uid_oid"),
					"CL_CRM_SETTINGS.RELTYPE_PERSON" => $curp,
					"CL_CRM_SETTINGS.RELTYPE_COMPANY" => $curco,
					"CL_CRM_SETTINGS.RELTYPE_SECTION" => $cursec,
					"CL_CRM_SETTINGS.RELTYPE_PROFESSION" => $curprof,
					"CL_CRM_SETTINGS.everyone" => 1
				)
			))
		));

		if ($ol->count() > 1)
		{
			// the most accurate setting SHALL Prevail!
			$has_co = $has_p = $has_u = $has_all = $has_sec = $has_prof = false;
			foreach($ol->arr() as $o)
			{
				if ($cursec && $o->is_connected_to(array("to" => $cursec)))
				{
					$has_sec = $o;
				}
				if ($curprof && $o->is_connected_to(array("to" => $curprof)))
				{
					$has_prof = $o;
				}

				if ($o->is_connected_to(array("to" => $curco)))
				{
					$has_co = $o;
				}
				if ($o->is_connected_to(array("to" => $curp)))
				{
					$has_p = $o;
				}
				if ($o->is_connected_to(array("to" => aw_global_get("uid_oid"))))
				{
					$has_u = $o;
				}
				if ($o->prop("everyone"))
				{
					$has_all = $o;
				}
			}

			if ($has_u)
			{
				return $has_u;
			}
			if ($has_p)
			{
				return $has_p;
			}
			if ($has_prof)
			{
				return $has_prof;
			}
			if ($has_sec)
			{
				return $has_sec;
			}
			if ($has_co)
			{
				return $has_co;
			}
			if ($has_all)
			{
				return $has_all;
			}
		}

		if ($ol->count())
		{
			return $ol->begin();
		}
	}


//-- methods --//
}
?>
