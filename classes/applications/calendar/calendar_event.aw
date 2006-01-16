<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/calendar_event.aw,v 1.16 2006/01/16 08:30:02 dragut Exp $
// calendar_event.aw - Kalendri sündmus 
/*

@classinfo syslog_type=ST_CALENDAR_EVENT relationmgr=yes

@default group=general
@default table=planner

@property jrk type=textbox size=4 group=general_sub table=objects
@caption Jrk

@property start1 type=datetime_select field=start 
@caption Algab

@property end type=datetime_select field=end 
@caption Lõpeb


@property project_selector type=project_selector store=no group=projects all_projects=1
@caption Projektid

@property project_selector2 type=project_selector store=no group=projects22 all_projects=1
@caption Projektid 2

@property utextbox1 type=textbox 
@caption

@property utextbox2 type=textbox
@caption 

@property utextbox3 type=textbox
@caption 

@property utextbox4 type=textbox
@caption 

@property utextbox5 type=textbox
@caption 

@property utextbox6 type=textbox
@caption 

@property utextbox7 type=textbox
@caption 

@property utextbox8 type=textbox
@caption 

@property utextbox9 type=textbox
@caption 

@property utextbox10 type=textbox
@caption 

@property utextarea1 type=textarea
@caption 

@property utextarea2 type=textarea
@caption 

@property utextarea3 type=textarea
@caption 

@property utextarea4 type=textarea
@caption 

@property utextarea5 type=textarea
@caption 

@property utextvar1 type=classificator
@caption 

@property utextvar2 type=classificator
@caption 

@property utextvar3 type=classificator
@caption 

@property utextvar4 type=classificator
@caption 

@property utextvar5 type=classificator
@caption 

@property utextvar6 type=classificator
@caption 

@property utextvar7 type=classificator
@caption 

@property utextvar8 type=classificator
@caption 

@property utextvar9 type=classificator
@caption 

@property utextvar10 type=classificator
@caption 

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@default field=meta
@default method=serialize
@default table=objects

@property uimage1 type=releditor reltype=RELTYPE_PICTURE rel_id=first use_form=emb
@caption

@property seealso type=relpicker reltype=RELTYPE_SEEALSO
@caption

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
@caption Kordused

@groupinfo projects caption="Projektid"
@groupinfo recurrence caption=Kordumine


@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype PICTURE value=1 clid=CL_IMAGE
@caption Pilt

@reltype SEEALSO value=2 clid=CL_DOCUMENT
@caption Vaata lisaks

@reltype RECURRENCE value=3 clid=CL_RECURRENCE
@caption Kordus

*/

class calendar_event extends class_base
{
	function calendar_event()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/calendar_event",
			"clid" => CL_CALENDAR_EVENT
		));
	}


	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		$meta = $arr["obj_inst"]->meta();
		if (substr($prop["name"],0,1) == "u")
		{
			if ($meta[$prop["name"]])
			{
				$arr["obj_inst"]->set_meta($prop["name"],"");
			};
		};
		return $retval;
	}	

	function get_property($arr)
	{
		$retval = PROP_OK;
		$prop = &$arr["prop"];
		if ($arr["obj_inst"])
		{
			$meta = $arr["obj_inst"]->meta();
			if (substr($prop["name"],0,1) == "u")
			{
				if (!empty($meta[$prop["name"]]))
				{
					$prop["value"] = $meta[$prop["name"]];
				};
			};
		};
		return $retval;
	}
	
	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function request_execute($o)
	{
		//if ($_GET["exp"] == 1)
		//{
			return $this->show2(array("id" => $o->id()));
		//}
		//else
		//{
		//	return $this->show(array("id" => $o->id()));
		//};
	}

	function show2($arr)
	{
		$ob = new object($arr["id"]);
		$cform = $ob->meta("cfgform_id");
		// feega hea .. nüüd on vaja veel nimed saad
		$cform_obj = new object($cform);
		$output_form = $cform_obj->prop("use_output");
		if (is_oid($output_form))
		{
			$cform = $output_form;
		};
		$t = get_instance(CL_CFGFORM);
		$props = $t->get_props_from_cfgform(array("id" => $cform));
		$htmlc = get_instance("cfg/htmlclient",array("template" => "webform.tpl"));
		$htmlc->start_output();

		foreach($props as $propname => $propdata)
		{
		  	$value = $ob->prop($propname);
			if ($propdata["type"] == "datetime_select")
			{
				if ($value == -1)
				{
					continue;
				};
				$value = date("d-m-Y H:i",$value);	
			};

			if (!empty($value))
			{
			   $htmlc->add_property(array(
			      "name" => $propname,
			      "caption" => $propdata["caption"],
			      "value" => nl2br($value),
			      "type" => "text",
			   ));
			};
		};
		$htmlc->finish_output(array("submit" => "no"));

		$html = $htmlc->get_result(array(
			"form_only" => 1
		));
	
		return $html;
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		// nii .. kuidas ma siin saan ära kasutada classbaset mulle vajaliku vormi genereerimiseks?
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		$vars = $ob->properties();
		$data = array();
		foreach($vars as $k => $v)
		{
			$data[$k] = nl2br($v);
		}
		$this->vars($data);
		return $this->parse();
	}
}
?>
