<?php
// $Header: /home/cvs/automatweb_dev/classes/pank/account.aw,v 1.1 2004/07/15 06:49:26 rtoomas Exp $
// account.aw - Konto 
/*
@tableinfo pank_konto index=oid master_table=objects master_index=oid
@classinfo syslog_type=ST_ACCOUNT relationmgr=yes

@default table=objects
@default group=general

@default table=pank_konto

@groupinfo account_overview caption="Konto ülevaade"
@default group=account_overview

@property account_balance type=textbox
@caption Konto saldo

*/

class account extends class_base
{
	function account()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "pank/account",
			"clid" => CL_ACCOUNT
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

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

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
