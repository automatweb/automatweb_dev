<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/party.aw,v 1.3 2005/04/21 09:41:32 ahti Exp $
// party.aw - Pidu 
/*

@classinfo syslog_type=ST_PARTY relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@tableinfo planner index=id master_table=objects master_index=brother_of
@default table=planner

@property start1 type=datetime_select field=start 
@caption Algus

@property end type=datetime_select
@caption Lõpp

@property image type=relpicker reltype=RELTYPE_PICTURE table=objects field=meta method=serialize
@caption Flaier

@property content type=textarea cols=60 rows=20 field=description
@caption Sisu

@property from_artist type=select multiple=1 table=objects field=meta method=serialize
@caption Võta esineja objektist ürituse

@property relman type=aliasmgr no_caption=1 store=no
@caption Seostehaldur

@groupinfo artists caption="Esinejad"

@property artists type=releditor reltype=RELTYPE_ARTIST props=name,firstname,lastname,notes mode=manager field=meta method=serialize table=objects table_fields=name,firstname,lastname,notes table_edit_fields=name,firstname,lastname,notes group=artists
@caption Esinejad

@groupinfo projects caption="Projektid"

@property project_selector type=project_selector store=no group=projects all_projects=1
@caption Projektid

@reltype PICTURE value=1 clid=CL_IMAGE
@caption Flaier

@reltype ARTIST value=2 clid=CL_CRM_PERSON
@caption Esineja

*/

class party extends class_base
{
	function party()
	{
		$this->init(array(
			"clid" => CL_PARTY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "from_artist":
				$prop["options"] = array(
					0 => t("--vali--"),
					"image" => t("Pilt"),
					"content" => t("Sisu"),
				);
				break;
		};
		return $retval;
	}
}
?>
