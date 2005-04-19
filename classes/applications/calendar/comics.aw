<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/comics.aw,v 1.1 2005/04/19 17:51:48 ahti Exp $
// comics.aw - Koomiks 
/*

@classinfo syslog_type=ST_COMICS relationmgr=yes no_comment=1 r2=yes

@default table=objects
@default group=general

@tableinfo planner index=id master_table=objects master_index=brother_of
@default table=planner

@property start1 type=datetime_select field=start 
@caption Avaldatakse

@property image type=releditor reltype=RELTYPE_PICTURE rel_id=first use_form=emb
@caption Pilt

@property num type=textbox size=10 table=objects field=jrk datatype=int
@caption Koomiksi number

@property content type=textarea cols=60 rows=20 field=description
@caption Sisu

@property relman type=aliasnmgr no_caption=1 store=no
@caption Seostehaldur

@groupinfo projects caption="Projektid"

@property project_selector type=project_selector store=no group=projects all_projects=1
@caption Projektid

@groupinfo scripts caption="Skriptid"

@property scripts type=releditor reltype=RELTYPE_SCRIPT props=name,comment,content mode=manager field=meta method=serialize table=objects table_fields=name,comment,content group=scripts

@reltype PICTURE value=1 clid=CL_IMAGE
@caption Pilt

@reltype SCRIPT value=2 clid=CL_COMICS_SCRIPT
@caption Skript

*/

class comics extends class_base
{
	function comics()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/calendar",
			"clid" => CL_COMICS
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
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

	function request_execute($obj)
	{
		$obj_i = $obj->instance();
		//$t = get_instance(CL_CFGFORM);
		//$props = $t->get_props_from_cfgform(array("id" => $cform));
		$this->read_template("comics_show.tpl");
		$props = $obj_i->load_defaults();
		$vars = array();
		foreach($props as $propname => $propdata)
		{
			$value = $obj->prop($propname);
			if ($propdata["type"] == "datetime_select")
			{
				if ($value == -1)
				{
					continue;
				};
				$value = date("d-m-Y", $value);
			}
			if($propdata["type"] == "releditor")
			{
				if($ob = $obj->get_first_conn_by_reltype($propdata["reltype"]))
				{
					$img = get_instance(CL_IMAGE);
					$imgdata = $img->get_image_by_id($ob->prop("to"));
					$value = $imgdata["url"];
				}
			}
			$vars[$propname] = $value;
		};
		$this->vars($vars);
		return $this->parse();
	}
}
?>
