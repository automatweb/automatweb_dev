<?php

/* 

	@default table=objects
	@default method=serialize
	@default field=meta

	@property status type=status field=status
	@caption Staatus

	@property pealkiri type=textbox 
	@caption Tootekataloogi pealkiri

	@property rootauto type=checkbox ch_value=yes 
	@caption Automaatne rootmenüü nimi

	@property rootname type=textbox 
	@caption Rootmenüü pealkiri

	@property newwindow type=checkbox ch_value=yes 
	@caption Ava tooted uues aknas

	@property headline type=checkbox ch_value=yes 
	@caption Näita pealkirja ja joont

	@property root type=select 
	@caption Rootmenyy

*/
class esitlus extends class_base
{
	function esitlus()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init(array(
			'tpldir' => "esitlus",
			'clid' => CL_ESITLUS
		));
	}

	function get_property(&$arr)
	{
		if ($arr['prop']['name'] == 'root')
		{
			$arr['prop']['options'] = $this->get_menu_list();
		}

		return PROP_OK;
	}

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
		$ob = $this->get_object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob["name"]
		));

		return $this->parse();
	}
		
	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row["parent"] = $parent;
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}
}
?>