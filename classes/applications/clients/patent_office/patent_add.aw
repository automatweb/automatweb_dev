<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/patent_add.aw,v 1.1 2006/12/12 16:48:45 markop Exp $
// patent_add.aw - Kaubam&auml;rgi veebist lisamine 
/*

@classinfo syslog_type=ST_PATENT_ADD relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property procurator_menu type=relpicker reltype=RELTYPE_PROCURATOR_MENU
	@caption Volinike kaust
	
	@property bank_payment type=relpicker reltype=RELTYPE_BANK_PAYMENT
	@caption Pangamakse objekt
	
	@property patents_menu type=relpicker reltype=RELTYPE_PATENT_MENU
	@caption Patentide kaust
	
@reltype BANK_PAYMENT value=11 clid=CL_BANK_PAYMENT
@caption Pangalingi objekt

@reltype PROCURATOR_MENU value=8 clid=CL_MENU
@caption Volinike kaust

@reltype PATENT_MENU value=9 clid=CL_MENU
@caption Patentide kaust



*/

class patent_add extends class_base
{
	function patent_add()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/patent_add",
			"clid" => CL_PATENT_ADD
		));
	}

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

	/** 
		@attrib name=parse_alias is_public="1" caption="Change"
	**/
	function parse_alias($arr)
	{
		enter_function("patent::parse_alias");
		
		$patent_inst = get_instance(CL_PATENT);
		
		return $patent_inst->parse_alias($arr);
		
		if($_GET["patent_id"])
		{
			$_SESSION["patent"] = null;
			$_SESSION["patent"]["id"] = $_GET["patent_id"];
			$this->fill_session($_GET["patent_id"]);
		}
		
		if(!$_SESSION["patent"]["data_type"])
		{
			$_SESSION["patent"]["data_type"] = 0;
		}
		if(isset($_GET["data_type"]))
		{
			$arr["data_type"] = $_GET["data_type"];
		}
		else
		{
			$arr["data_type"] = $_SESSION["patent"]["data_type"];
		}
		
		if($arr["data_type"] == 6)
		{
			return $patent_inst->my_patent_list();//$this->mk_my_orb("my_patent_list", array());
		}
		
		$tpl = $patent_inst->info_levels[$arr["data_type"]].".tpl";
		$patent_inst->read_template($tpl);
		lc_site_load("patent", &$this);
		$patent_inst->vars($patent_inst->web_data($arr));
		
		$this->vars(array("reforb" => $this->mk_reforb("submit_data",array(
				"data_type"	=> $arr["data_type"],
				"return_url" 	=> get_ru(),
			)),
		));
	
		//lõpetab ja salvestab
		if($arr["data_type"] == 5)
		{
			$this->vars(array("reforb" => $patent_inst->mk_reforb("submit_data",array(
					"save" => 1,
					"return_url" 	=> get_ru(),
				)),
			));
		}
		
		exit_function("realestate_add::parse_alias");
		return $this->parse();
	}


//-- methods --//
}
?>
