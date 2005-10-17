<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_jupp.aw,v 1.1 2005/10/17 10:32:05 dragut Exp $
// expp_jupp.aw - Expp Jupp 
/*

@classinfo syslog_type=ST_EXPP_JUPP relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

class expp_jupp extends class_base
{
	function expp_jupp()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "expp",
			"clid" => CL_EXPP_JUPP
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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

	/**  
		
		@attrib name=show is_public="1" caption="Expp" nologin="1" default="1" all_args="1"
		
		@param id type=int
		
		@returns
		
		
		@comment

	**/
	function show($arr){
		// kasutajagrupid
		$ugParent = aw_ini_get("groups.tree_root");
		$username = "vobla11";
		$grupp = "kala5";
		$kompanii = "meri5";
aw_disable_acl();
		$i = 0;
		while( $i++ < 5 ) {

			// Kas grupp on olemas
			$co_ol = new object_list(array(
				"class_id" => CL_GROUP,
				"name" => $grupp,
				"lang_id" => array(),
				"site_id" => array(),
			));
			if ($co_ol->count() == 0) {
				arr( "UserGroup > $grupp > new" );
				$ug1 = obj();
				$ug1->set_class_id( CL_GROUP );
				$ug1->set_parent( $ugParent );
				$ug1->set_name( $grupp );
				$ug1->set_prop( "name", $grupp );
				$ug1->save();
			} else {
				arr( "UserGroup > $grupp > old" );
				$ug1 = $co_ol->begin();
				$ug1->set_parent( $ugParent );
				$ug1->set_name( $grupp );
				$ug1->set_prop( "name", $grupp );
				$ug1->save();
			}

	// --> toimetus -> Company
				arr( "Company > $kompanii" );
				$co_ol = new object_list(array(
					"class_id" => CL_CRM_COMPANY,
					"reg_nr" => $komapnii,
					"lang_id" => array(),
					"site_id" => array(),
				));
			if ($co_ol->count() == 0) {
				arr( "Company > $kompanii > new" );
				$co = obj();
				$co->set_class_id(CL_CRM_COMPANY);
				$co->set_parent( 1 );
				$co->set_name( $kompanii );
				$co->set_prop( 'reg_nr', $kompanii );
				$co->save();
			} else {
				arr( "Company > $kompanii > old" );
				$co = $co_ol->begin();
				$co->set_parent( 1 );
				$co->set_name( $kompanii );
				$co->set_prop( 'reg_nr', $kompanii );
				$co->save();
			}

	// --> toode -> User
		arr( "User > $username" );
		$co_ol = new object_list(array(
			"class_id" => CL_USER,
			"name" => $username,
			"lang_id" => array(),
			"site_id" => array(),
		));
		if ($co_ol->count() == 0) {
			$usi = get_instance(CL_USER);
			$us = $usi->add_user(array(
				"uid" => $username,
				"use_md5_passwords" => true,
				"join_grp" => array($ug1->id()),
			));
			arr( "User > $username > new" );
			$us->set_name( $username );
			$us->set_parent( $ugParent );
			$us->save();
		} else {
			arr( "User > $username > old" );
			$us = $co_ol->begin();
			$us->set_parent( $ugParent );
			$us->set_name( $username );
			$us->save();
		}
	}
}

  function submit($arr)
  {
    die("the name is ".$arr["name"]);
  }

	//-- methods --//

}
?>
