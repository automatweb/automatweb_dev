<?php
// $Header: /home/cvs/automatweb_dev/classes/kliendibaas/Attic/phone.aw,v 1.1 2003/08/21 10:21:07 axel Exp $
// phone.aw - Telefon 
/*

@classinfo syslog_type=ST_PHONE relationmgr=yes

@default table=objects
@default group=general

@property name type=textbox
@caption Number

//@property type type=select field=meta method=serialize table=objects
//@caption Telefoni tüüp

@property comment type=textbox
@caption Kommentaar


*/

define ('BELONGTO',1);

class phone extends class_base
{
	function phone()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "kliendibaas/phone",
			"clid" => CL_PHONE
		));
	}
	
	function callback_get_rel_types()
	{
		return array(
			BELONGTO => 'Numbriga seotud objekt',
		);
	}
	
	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args["reltype"])
                {
			case BELONGTO:
				$retval = array(CL_ADDRESS, CL_FIRMA, CL_ISIK);
			break;
		};
		return $retval;
	}
		
	//////
	// class_base classes usually need those, uncomment them if you want to use them

	
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
			case 'status':
				$retval=PROP_IGNORE;
			break;
			/*case 'type':
				$data['options']=array('0' => 'vali telefoni tüüp', 'general' => 'Tavatelefon','mobile' => 'Mobiil', 'fax' => 'Faks','pipar' => 'Piipar');
			break;*/
			
		};
		return $retval;
	}
	

	/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
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
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
