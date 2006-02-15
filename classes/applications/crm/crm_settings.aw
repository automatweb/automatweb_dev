<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_settings.aw,v 1.8 2006/02/15 13:03:40 kristo Exp $
// crm_settings.aw - Kliendibaasi seaded 
/*

@classinfo syslog_type=ST_CRM_SETTINGS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects


@default group=general

	@property s_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption Kliendi seadete vorm 

	@property s_p_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption Eraisikust kliendi seadete vorm 

	@property work_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption Minu t&ouml;&ouml;koha seadete vorm

	@property coworker_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption T&ouml;&ouml;kaaslaste seadete vorm 

	@property bill_def_prod type=relpicker reltype=RELTYPE_PROD table=objects field=meta method=serialize
	@caption Vaikimisi toode arve ridadel

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


@groupinfo whom caption="Kellele kehtib"

@reltype USER value=1 clid=CL_USER
@caption Kasutaja

@reltype CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

@reltype PERSON value=3 clid=CL_CRM_PERSON
@caption Isik

@reltype COMPANY value=4 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype SECTION value=5 clid=CL_CRM_SECTION
@caption Osakond

@reltype PROFESSION value=6 clid=CL_CRM_PROFESSION
@caption Ametinimetus

@reltype PROD value=7 clid=CL_SHOP_PRODUCT
@caption Toode

*/

class crm_settings extends class_base
{
	function crm_settings()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_settings",
			"clid" => CL_CRM_SETTINGS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function get_current_settings()
	{
		$u = get_instance(CL_USER);
		$curp = $u->get_current_person();
		$curco = $u->get_current_company();
		
		$cd = get_instance("applications/crm/crm_data");
		$cursec = $cd->get_current_section();
		$curprof = $cd->get_current_profession();

		$ol = new object_list(array(
			"class_id" => CL_CRM_SETTINGS,
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
}
?>
