<?php
// $Header: /home/cvs/automatweb_dev/classes/pank/transaction.aw,v 1.1 2004/07/15 06:49:26 rtoomas Exp $
// transaction.aw - Ülekanne 
/*

@classinfo syslog_type=ST_TRANSACTION relationmgr=yes
@tableinfo pank_transaction index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@default table pank_transaction

@property trans_from type=text
@caption Kandja

@property trans_to type=text
@caption Saaja

@property sum type=text
@caption Summa

@property time type=text
@caption Kellaaeg

*/

class transaction extends class_base
{
	function transaction()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "pank/transaction",
			"clid" => CL_TRANSACTION
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
			//most probably have to find out the owner of the account
			case 'trans_from':
				break;
			//most probably have to find out the owner of the account
			case 'trans_to':
				break;
			//format the date
			case 'time':
				//echo $this->time2date($arr['obj_inst']->prop('time'));
				break;
		};
		return $retval;
	}

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
