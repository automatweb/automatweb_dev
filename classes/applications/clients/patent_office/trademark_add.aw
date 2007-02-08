<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/trademark_add.aw,v 1.7 2007/02/08 16:16:38 markop Exp $
// trademark_add.aw - Kaubam&auml;rgi veebist lisamine 
/*

@classinfo syslog_type=ST_TRADEMARK_ADD relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property procurator_menu type=relpicker reltype=RELTYPE_PROCURATOR_MENU
	@caption Volinike kaust
	
	@property bank_payment type=relpicker reltype=RELTYPE_BANK_PAYMENT
	@caption Pangamakse objekt
	
	@property trademarks_menu type=relpicker reltype=RELTYPE_TRADEMARK_MENU
	@caption Kaubam&auml;rgitaotluste kaust
	
	@property series type=relpicker reltype=RELTYPE_SERIES
	@caption Numbriseeria
	
@reltype BANK_PAYMENT value=11 clid=CL_BANK_PAYMENT
@caption Pangalingi objekt

@reltype PROCURATOR_MENU value=8 clid=CL_MENU
@caption Volinike kaust

@reltype TRADEMARK_MENU value=9 clid=CL_MENU
@caption Kaubam&auml;rgitaotluste kaust

@reltype SERIES clid=CL_CRM_NUMBER_SERIES value=3
@caption Numbriseeria


*/

class trademark_add extends class_base
{
	function trademark_add()
	{
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_TRADEMARK_ADD
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
	
		//l�petab ja salvestab
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

	function get_folders_as_object_list($o, $level, $parent)
	{
		$ol = new object_list();
		$links = $o->meta("meaningless_sh__");
		//arr($links);
		$_SESSION["patent"]["jrk"] = 0;
		if(is_array($links) && sizeof($links) == 6)
		{
			foreach($links as $link)
			{
				if($this->can("view" , $link))
				{
					$ol->add($link);
				}
				else break;
			}
		}
		if(!(is_array($links) && sizeof($links) == 6))
		{
			$ol = new object_list();
			$o1 = new object();
			$o1->set_name("Taotleja andmed");
			$o1->set_class_id(CL_TRADEMARK_ADD);
			$o1->set_parent($o->id());
			$o1->save();
			$ol->add($o1);
			
			$o2 = new object();
			$o2->set_name("Kaubam�rk");
			$o2->set_class_id(CL_TRADEMARK_ADD);
			$o2->set_parent($o->id());
			$o2->save();
			$ol->add($o2);
						
			$o3 = new object();
			$o3->set_name("Kaupade ja teenuste loetelu");
			$o3->set_class_id(CL_TRADEMARK_ADD);
			$o3->set_parent($o->id());
			$o3->save();
			$ol->add($o3);
			
			$o4 = new object();
			$o4->set_name("Prioriteet");
			$o4->set_class_id(CL_TRADEMARK_ADD);
			$o4->set_parent($o->id());
			$o4->save();
			$ol->add($o4);
			
			$o5 = new object();
			$o5->set_name("Riigil�iv");
			$o5->set_class_id(CL_TRADEMARK_ADD);
			$o5->set_parent($o->id());
			$o5->save();
			$ol->add($o5);
			
			$o6 = new object();
			$o6->set_name("Andmete kontroll/edastamine");
			$o6->set_class_id(CL_TRADEMARK_ADD);
			$o6->set_parent($o->id());
			$o6->save();
			$ol->add($o6);
			
			$o->set_meta("meaningless_sh__" , $ol->ids());
			$o->save();
		}
		return $ol;
	}

	function make_menu_link($o, $ref = NULL)
	{
		//r($_SESSION);
		if($_SESSION["patent"]["jrk"] == 0)
		{
			$url = $_SERVER["SCRIPT_URI"]."?section=".$_GET["section"]."&data_type=0";
			//aw_url_change_var("data_type", "0");
		//arr($_GET["section"]);
		//arr();
		}
		elseif(!($_GET["data_type"] <  ($_SESSION["patent"]["jrk"])) || is_oid($_SESSION["patent"]["id"]))
		{
			$url = aw_url_change_var("data_type", $_SESSION["patent"]["jrk"]);
		}
		else
		$url = aw_url_change_var()."#";//aw_url_change_var("", "#");	
		// $url =$_SERVER["SCRIPT_URI"]."?section=".aw_ini_get("section")."&data_type=".$_SESSION["patent"]["jrk"];
		
		
//		$url = aw_url_change_var("data_type", $_SESSION["patent"]["jrk"]);
		$_SESSION["patent"]["jrk"]++;
	//	arr($_SESSION["patent"]["data_type"]);
	//arr($_SESSION["patent"]["data_type"]); arr(($_SESSION["patent"]["jrk"]-2));
		return $url;
	//	else return "";
		$this->mk_my_orb("parse_alias",
			array(
				"id" => $_SESSION["persons_webview"],
				"section" => $o->id(),
				"view" => 1,
				"level" => $this->jrks[$o->id()],
				"company_id" => $_SESSION["company"],
		),
		CL_PERSONS_WEBVIEW);
	}

//-- methods --//
}
?>
