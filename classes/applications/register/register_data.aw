<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/register/register_data.aw,v 1.13 2004/11/23 14:45:12 kristo Exp $
// register_data.aw - Registri andmed 
/*

@classinfo syslog_type=ST_REGISTER_DATA relationmgr=yes no_comment=1
@default group=general
@default table=aw_register_data

@tableinfo aw_register_data index=aw_id master_table=objects master_index=brother_of

@property register_id type=hidden field=aw_register_id group=general

@groupinfo data caption="Andmed"
@default group=data

@property user1 type=textbox field=aw_user1 group=data
@caption User-defined 1

@property user2 type=textbox  field=aw_user2 group=data
@caption User-defined 2

@property user3 type=text  field=aw_user3 group=data
@caption User-defined 3

@property user4 type=textbox  field=aw_user4 group=data
@caption User-defined 4

@property user5 type=textbox  field=aw_user5 group=data
@caption User-defined 5

@property user6 type=textbox  field=aw_user6 group=data
@caption User-defined 6

@property user7 type=textbox  field=aw_user7 group=data
@caption User-defined 7

@property user8 type=textbox  field=aw_user8 group=data
@caption User-defined 8

@property user9 type=textbox  field=aw_user9 group=data
@caption User-defined 9

@property user10 type=textbox  field=aw_user10 group=data
@caption User-defined 10

@property user11 type=textbox  field=aw_user11 group=data
@caption User-defined 11

@property user12 type=textbox  field=aw_user12 group=data
@caption User-defined 12

@property user13 type=textbox  field=aw_user13 group=data
@caption User-defined 13

@property user14 type=textbox  field=aw_user14 group=data
@caption User-defined 14

@property user15 type=textbox  field=aw_user15 group=data
@caption User-defined 15

@property user16 type=textbox  field=aw_user16 group=data
@caption User-defined 16

@property user17 type=textbox  field=aw_user17 group=data
@caption User-defined 17

@property user18 type=textbox  field=aw_user18 group=data
@caption User-defined 18

@property user19 type=textbox  field=aw_user19 group=data
@caption User-defined 19

@property user20 type=textbox  field=aw_user20 group=data
@caption User-defined 20


@property userta1 type=textarea  field=aw_tauser1 group=data
@caption User-defined ta 1

@property userta2 type=textarea  field=aw_tauser2 group=data
@caption User-defined ta 2

@property userta3 type=textarea  field=aw_tauser3 group=data
@caption User-defined ta 3

@property userta4 type=textarea  field=aw_tauser4 group=data
@caption User-defined ta 4

@property userta5 type=textarea  field=aw_tauser5 group=data
@caption User-defined ta 5

@property userta6 type=textarea  field=aw_tauser6 group=data
@caption User-defined ta 6

@property userta7 type=textarea  field=aw_tauser7 group=data
@caption User-defined ta 7

@property userta8 type=textarea  field=aw_tauser8 group=data
@caption User-defined ta 8

@property userta9 type=textarea  field=aw_tauser9 group=data
@caption User-defined ta 9

@property userta10 type=textarea  field=aw_tauser10 group=data
@caption User-defined ta 10

@property uservar1 type=classificator  field=aw_varuser1 group=data reltype=RELTYPE_VARUSER1 store=connect
@caption User-defined var 1

@property uservar2 type=classificator  field=aw_varuser2 group=data reltype=RELTYPE_VARUSER2 store=connect
@caption User-defined var 2

@property uservar3 type=classificator  field=aw_varuser3 group=data reltype=RELTYPE_VARUSER3 store=connect
@caption User-defined var 3

@property uservar4 type=classificator  field=aw_varuser4 group=data reltype=RELTYPE_VARUSER4 store=connect
@caption User-defined var 4

@property uservar5 type=classificator  field=aw_varuser5 group=data reltype=RELTYPE_VARUSER5 store=connect
@caption User-defined var 5

@property uservar6 type=classificator  field=aw_varuser6 group=data reltype=RELTYPE_VARUSER6 store=connect
@caption User-defined var 6

@property uservar7 type=classificator  field=aw_varuser7 group=data reltype=RELTYPE_VARUSER7 store=connect
@caption User-defined var 7

@property uservar8 type=classificator  field=aw_varuser8 group=data reltype=RELTYPE_VARUSER8 store=connect
@caption User-defined var 8

@property uservar9 type=classificator  field=aw_varuser9 group=data reltype=RELTYPE_VARUSER9 store=connect
@caption User-defined var 9

@property uservar10 type=classificator  field=aw_varuser10 group=data reltype=RELTYPE_VARUSER10 store=connect
@caption User-defined var 10

@property userdate1 type=date_select  field=aw_userdate1 group=data year_from=1970 year_to=2020
@caption User-defined date select 1

@property userdate2 type=date_select  field=aw_userdate2 group=data year_from=1970 year_to=2020
@caption User-defined date select 2

@property userdate3 type=date_select  field=aw_userdate3 group=data year_from=1970 year_to=2020
@caption User-defined date select 3

@property userdate4 type=date_select  field=aw_userdate4 group=data year_from=1970 year_to=2020
@caption User-defined date select 4

@property userdate5 type=date_select  field=aw_userdate5 group=data year_from=1970 year_to=2020
@caption User-defined date select 5

@property userch1 type=textbox field=aw_userch1 group=data ch_value=1 datatype=int
@caption User-defined checkbox 1

@property userch2 type=textbox  field=aw_userch2 group=data ch_value=1 datatype=int
@caption User-defined checkbox 2

@property userch3 type=text  field=aw_userch3 group=data ch_value=1 datatype=int
@caption User-defined checkbox 3

@property userch4 type=textbox  field=aw_userch4 group=data ch_value=1 datatype=int
@caption User-defined checkbox 4

@property userch5 type=textbox  field=aw_userch5 group=data ch_value=1 datatype=int
@caption User-defined checkbox 5

@property usersubtitle1 type=text store=no group=data subtitle=1
@caption Subtitle1

@property usersubtitle2 type=text store=no group=data subtitle=1
@caption Subtitle2

@property usersubtitle3 type=text store=no group=data subtitle=1
@caption Subtitle3

@property usersubtitle4 type=text store=no group=data subtitle=1
@caption Subtitle4

@property usersubtitle5 type=text store=no group=data subtitle=1
@caption Subtitle5

@property udefhidden1 type=hidden field=meta method=serialize group=general table=objects
@property udefhidden2 type=hidden field=meta method=serialize group=general table=objects
@property udefhidden3 type=hidden field=meta method=serialize group=general table=objects

@property usertext1 type=text store=no group=data
@caption Usertext1

@property usertext2 type=text store=no group=data
@caption Usertext2

@property usertext3 type=text store=no group=data
@caption Usertext3

@property usertext4 type=text store=no group=data
@caption Usertext4

@property usertext5 type=text store=no group=data
@caption Usertext5

@property usersubmit1 type=submit store=no
@caption User-defined submit 1

@reltype VARUSER1 value=1 clid=CL_META
@caption kasutajadefineeritud muutuja 1

@reltype VARUSER2 value=2 clid=CL_META
@caption kasutajadefineeritud muutuja 2

@reltype VARUSER3 value=3 clid=CL_META
@caption kasutajadefineeritud muutuja 3

@reltype VARUSER4 value=4 clid=CL_META
@caption kasutajadefineeritud muutuja 4

@reltype VARUSER5 value=5 clid=CL_META
@caption kasutajadefineeritud muutuja 5

@reltype VARUSER6 value=6 clid=CL_META
@caption kasutajadefineeritud muutuja 6

@reltype VARUSER7 value=7 clid=CL_META
@caption kasutajadefineeritud muutuja 7

@reltype VARUSER8 value=8 clid=CL_META
@caption kasutajadefineeritud muutuja 8

@reltype VARUSER9 value=9 clid=CL_META
@caption kasutajadefineeritud muutuja 9

@reltype VARUSER10 value=1 clid=CL_META
@caption kasutajadefineeritud muutuja 10

@reltype REGISTER value=10 clid=CL_REGISTER
@caption Seostatud register

*/

class register_data extends class_base
{
	function register_data()
	{
		$this->init(array(
			"tpldir" => "applications/register/register_data",
			"clid" => CL_REGISTER_DATA
		));
	}
	
	function callback_pre_edit($arr)
	{
		if($register = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_REGISTER"))
		{
			$default = safe_array($register->prop("gfdg"));
			if($register->prop("default_cfgform") == 1 && !empty($default[0]))
			{
				$arr["obj_inst"]->set_meta("cfgform_id", $default[0]);
			}
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		$this->set_register_id = $arr["request"]["set_register_id"];
		if ($this->set_register_id && $arr["request"]["cfgform"])
		{
			$rego = obj($this->set_register_id);
			if ($rego->prop("cfgform_name_in_field") != "" && $prop["name"] == $rego->prop("cfgform_name_in_field"))
			{
				$co = obj($arr["request"]["cfgform"]);
				$prop["value"] = $co->name();
			}
		}
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
		$arr["set_register_id"] = $this->set_register_id;
	}

	function callback_pre_save($arr)
	{
		if ($arr["new"] && $arr["request"]["set_register_id"])
		{
			$arr["obj_inst"]->set_prop("register_id", $arr["request"]["set_register_id"]);
			if ($arr["request"]["cfgform"])
			{
				$rego = obj($arr["request"]["set_register_id"]);
				if ($rego->prop("cfgform_name_in_field") != "")
				{
					$co = obj($arr["request"]["cfgform"]);
					$arr["obj_inst"]->set_prop($rego->prop("cfgform_name_in_field"), $co->name());
				}
			}
		}
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !shows the data
	function show($arr)
	{
		$ot = get_instance(CL_OBJECT_TYPE);
		$cf = get_instance(CL_CFGFORM);
		$obj_inst = obj($arr["id"]);
		$ot_id = $ot->get_obj_for_class(array(
			"clid" => $obj_inst->class_id(),
			"general" => true,
		));
		$return_url = aw_global_get("REQUEST_URI");
		if($register = $obj_inst->get_first_obj_by_reltype("RELTYPE_REGISTER"))
		{
			$url = $register->prop("data_return_url");
			if(!empty($url))
			{
				$return_url = $url;
			}
		}
		return $cf->draw_cfgform_from_ot(array(
			"ot" => $ot_id,
			"reforb" => $this->mk_reforb("save_form_data", array(
				"id" => $arr["id"],
				"return_url" => $return_url,
			)),
		));
	}

	/**

		@attrib name=save_form_data nologin=1 all_args=1
		
		@param id required type=int acl=view
		@param return_url optional
	**/
	function save_form_data($arr)
	{
		$rval = aw_ini_get("baseurl").$arr["return_url"];
		$obj_inst = obj($arr["id"]);
		$ot = get_instance(CL_OBJECT_TYPE);
		if(!$ot_id = $ot->get_obj_for_class(array(
			"clid" => $obj_inst->class_id(),
			"general" => true,
		)))
		{
			return $rval;
		}
		$ot = obj($ot_id);
		
		$is_valid = $this->validate_data(array(
			"cfgform_id" => $ot->prop("use_cfgform"),
			"request" => $arr,
		));
		if(empty($is_valid))
		{
			$o = obj();
			$parent = $obj_inst->parent();
			if($register = $obj_inst->get_first_obj_by_reltype("RELTYPE_REGISTER"))
			{
				$prop = $register->prop("data_rootmenu");
				if(!empty($prop))
				{
					$parent = $prop;
				}
			}
			$o->set_class_id(CL_REGISTER_DATA);
			$o->set_parent($parent);
			foreach($o->get_property_list() as $pn => $pd)
			{
				$o->set_prop($pn, $arr[$pn]);
			}
			$o->set_meta("object_type", $ot->id());
			$o->set_meta("cfgform_id", $ot->prop("use_cfgform"));
			$o->set_name("entry ".date("d-m-Y H:i"));
			$o->save();
		}
		return $rval;
	}
	
	/**

		@attrib name=view nologin=1 all_args=1

	**/
	function view($arr)
	{
		return parent::view($arr);
	}
}
?>
