<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/Attic/crm_errand_condition.aw,v 1.3 2007/12/06 14:33:17 kristo Exp $
// crm_errand_condition.aw - L&auml;hetustingimus 
/*

@classinfo syslog_type=ST_CRM_ERRAND_CONDITION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop


@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property whos_paying type=select
	@caption Transpordi eest tasub (meie/klient)



*/

class crm_errand_condition extends class_base
{
	function crm_errand_condition()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_errand_condition",
			"clid" => CL_CRM_ERRAND_CONDITION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "whos_paying":
				$prop["options"] = array(0 => t("Meie"), 0 => t("Klient"));
				break;
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
