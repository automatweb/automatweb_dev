<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_add.aw,v 1.1 2006/02/21 16:24:05 markop Exp $
// realestate_add.aw - Kinnisvaraobjekti lisamine 
/*

@classinfo syslog_type=ST_REALESTATE_ADD relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property realestate_type type=select
@caption Kinnisvaraobjekti tüüp 

@property realestate_environment type=relpicker reltype=RELTYPE_MANEGER
@caption Kinnisvarahalduse keskkond

@property redir_object type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
@caption Dokument millele suunata

@groupinfo required_fields caption="Kohustuslikud väljad"
@default group=required_fields

@property required_fields type=callback callback=callback_get_fields store=no no_caption=1
@caption väljad

@groupinfo levels caption=Tasemed
@default group=levels

@property levels type=table store=no no_caption=1
@caption Tasemed

@reltype MANEGER value=1 clid=CL_REALESTATE_MANAGER
@caption Saatja

@reltype REDIR_OBJECT value=2 clid=CL_DOCUMENT
@caption ümbersuunamine

*/

class realestate_add extends class_base
{
	function realestate_add()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "realestate_add",
			"clid" => CL_REALESTATE_ADD
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
			case "realestate_type":
				$options = array(
						"" => t(""),
						CL_REALESTATE_HOUSE => t("Maja"),
						CL_REALESTATE_ROWHOUSE => t("Ridaelamu"),
						CL_REALESTATE_COTTAGE => t("Suvila"),
						CL_REALESTATE_HOUSEPART => t("Majaosa"),
						CL_REALESTATE_APARTMENT => t("Korter"),
						CL_REALESTATE_COMMERCIAL => t("Äripind"),
						CL_REALESTATE_GARAGE => t("Garaaz"),
						CL_REALESTATE_LAND => t("Maa"),
					);
				//kui kinnisvaraobjekti tüüp valitud, siis teda enam muuta ei saa
				if(($arr["obj_inst"]->prop("realestate_type")))
				{
					$prop["type"] = "text";
					$prop["value"] =  $options[$prop["value"]];
				}
				else
				{
					$prop["options"] = $options;
				}
				break;
			case "levels":
				$this->do_table($arr);
				break;		
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "required_fields":
				$this->submit_meta($arr);
				break;
			case "levels":
				$this->submit_meta($arr);
				break;
		}
		return $retval;
	}	

	function submit_meta($arr = array())
	{
		$meta = $arr["request"]["meta"];
		//praagib välja tasemed, kus ei ole kas adekvaatset template faili või nime
		if(($arr["prop"]["name"] == "levels") && is_array($meta))
		{
			$temp_arr = array();
			foreach($meta as $metadata)
			{
				if((strlen($metadata["name"]) > 0) && strlen($metadata["template"]) > 4)
				{
					$temp_arr[] = $metadata;
				}
			}
			$meta = $temp_arr;
		}
		if (is_array($meta))
		{
			$so = new object($arr["obj_inst"]->id());
			$so->set_name($arr["obj_inst"]->name());
			$so->set_meta($arr["prop"]["name"], $meta);
			$so->save();
		};
	}
	
	//tekitab vastava kinnisvara objektide propertite nimekirja,
	//kust siis saab valida, mida on kohustuslik täita jne
	function callback_get_fields($arr)
	{
		if(($arr["obj_inst"]->prop("realestate_type")))
		{
			$clid = $arr["obj_inst"]->prop("realestate_type");
			$ret = array();
			$cfgu = get_instance("cfg/cfgutils");
//			$clss = aw_ini_get("classes");
//			$class_entry = $clss[$clid];
//			$file = $class_entry["file"];
			$o = obj();
			$o->set_class_id($clid);
			$props = $o->get_property_list();			
//			$props = $cfgu->load_class_properties(array(
//				"clid" => $clid,
//				"file" => $file,
				//"file" => "realestate_house",
	//			"filter" => array("form" => array("","add","edit")),
	//			"filter" => array("group" => array("grp_detailed")),
//			));
			$groups = $cfgu->get_groupinfo();
		//	$realestate_type_obj = get_instance(CL_REALESTATE_HOUSE);
			$meta = $arr["obj_inst"]->meta("required_fields");
			
			foreach($props as $name => $prop)
			{
				if($prop["caption"])
				{
					$value = 0;
					if($meta[$prop["name"]])
					{
						$value = 1;
					}
					$ret[] = array(
						"name" => "meta[".$name."]",
						"caption" => //"{VAR:".$prop["name"]."}",
						$prop["caption"].' ('.$prop["name"].')',
						"type" => "checkbox" ,
						"ch_value" => 1 ,
						"value" => $value,
					);
				}
			}
		}
		return $ret;
	}
	
	function do_table($arr)
	{
		$levels = $arr["obj_inst"]->meta("levels");
		$t = &$arr["prop"]["vcl_inst"];
		
		$t->define_field(array(
			"name" => "id",
			"caption" => t("Tase"),
//			"sortable" => 1,
		));		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Etapi nimi"),
		));
		$t->define_field(array(
			"name" => "template",
			"caption" => t("Template"),
		));
		$transyes = $arr["obj_inst"]->prop("transyes");
		$langdata = array();
		$count = 1;
		foreach($levels as $level)
		{
			$data = array(
				"id" => $count,
			);
		
			$data["name"] = html::textbox(array(
				"name" => "meta[".$count."][name]",
				"size" => 30,
				"value" => $level["name"],
			));
			
			$data["template"] = html::textbox(array(
				"name" => "meta[".$count."][template]",
				"size" => 30,
				"value" => $level["template"],
			));
			$t->define_data($data);
			$count++;
		}
		$new_data = array(
			"id" => $count,
		);
		
		 $new_data["name"] = html::textbox(array(
			"name" => "meta[".$count."][name]",
			"size" => 30,
			"value" => "",
		));
		
		$new_data["template"] = html::textbox(array(
			"name" => "meta[".$count."][template]",
			"size" => 30,
			"value" => "",
		));
		$t->define_data($new_data);
		$t->set_sortable(false);
	}
	
	//kui kinnisvaraobjekti tüüpi pole määratud, siis pole tasemete ja kohustuslike väljade grupid eriti vajalikud
	function callback_mod_tab($arr)
	{
		if((!$arr["obj_inst"]->prop("realestate_type")) 
		&& (($arr["id"] == "required_fields") 
		|| ($arr["id"] == "levels")))
		{
			return false;
		}
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

	function type_name($clid)
	{
		switch ($clid)
		{
			case CL_REALESTATE_HOUSE:
				return "house";
				break;
			case CL_REALESTATE_ROWHOUSE:
				return "rowhouse";
				break;
			case CL_REALESTATE_COTTAGE:
				return "cottage";
				break;
			case CL_REALESTATE_HOUSEPART:
				return "housepart";
				break;
			case CL_REALESTATE_APARTMENT:
				return "apartment";
				break;
			case CL_REALESTATE_COMMERCIAL:
				return "commercial";
				break;
			case CL_REALESTATE_GARAGE:
				return "garage";
				break;
			case CL_REALESTATE_LAND:
				return "land";
				break;
			default:
				return FALSE;
		}
	}

	function parse_alias($arr)
	{
		$targ = obj($arr["alias"]["target"]);
		enter_function("realestate_add::parse_alias");
		
		$clid = $targ->prop("realestate_type");
		$ret = array();
		$cfgu = get_instance("cfg/cfgutils");
		$o = obj();
		$o->set_class_id($clid);
		$props = $o->get_property_list();
		
		$levels = $targ->meta("levels");
		$fields = $targ->meta("required_fields");
		$parent = $targ->prop("realestate_environment");
		$clid = $targ->prop("realestate_type");		
		global $level;
		if(!$level)
		{
			$level = 1;
			$_SESSION["realestate_input_data"] = NULL;
		}
		$data = $_SESSION["realestate_input_data"];
		$data["level"] = NULL;
		$targ = obj($args["alias"]["target"]);
		$cb_errmsg = aw_global_get("cb_errmsg");
		$cb_reqdata = aw_global_get("cb_reqdata");
		aw_session_del("cb_errmsg", "");
		aw_session_del("cb_reqdata", "");
		$tobj = new object($args["alias"]["target"]);
		$relobj = new object($args["alias"]["relobj_id"]);		

		//kontroll, kas mõni vajalik väli on jäänud täitmata
		$not_filled = array();
		if(sizeof($data) > 0)
		{	
			foreach($data as $key => $val)
			{
				if(!(strlen($val) > 0) && $fields[$key])
				{
					$not_filled[] = $key;
				}
			}
		}
		if(sizeof($not_filled) > 0)
		{
			foreach($not_filled as $required)
			{
				print 'väli "'.$required.'" peab olema täidetud';
			}
			$level = $level-1;
		}
		else 
		{
			if($level > $_SESSION["realestate_input_data"]["filled_level"])
			{
				$_SESSION["realestate_input_data"]["filled_level"] = $level-1;
			}

		}
		$tpl = $levels[($level-1)]["template"];

		if(!$tpl)
		{
			$type = $this->type_name($clid);
			$ret_doc = $targ->prop("redir_object");
			$return_url = aw_ini_get("baseurl")."/".$ret_doc;			
			$this->subscribe(array(
				"level" 	=> $level,
				"return_to" 	=> $return_url,
				"do"		=> "submit",
				"parent"	=> $parent,
				"id"		=> $arr["alias"]["target"],	
				"clid"		=> $clid,
				"type"		=> $type,
			));
		}
		$_SESSION["realestate_input_data"]["level"] = $level+1;	
		$dir = $this->site_template_dir;
		if(file_exists($dir.'/'.$tpl))
		{
			$this->read_template($tpl);
		}
		else
		{
			echo $dir.'/'.$tpl.' nimelist template faili suht kindlalt olemas ei ole...vähemalt seal , kus peaks...';
		}
		lc_site_load("realestate_add", &$this);
		$this->vars($data);
		$properties = $data;
		
		/*tekitab muutujad erinevate tasemete nimede ja linkidega - linkidega vastavalt siis
		 kui vastavale tasemele pääsemiseks on vastavad väljad juba täidetud
		asi peaks olema templates umbes nii:
		<!-- SUB: LINKS --><!-- SUB: URL -->
		<a href="{VAR:level_url}"><!-- END SUB: URL -->
		{VAR:level_name}</a><!-- END SUB: LINKS --> */
		
		if ($this->is_template("LINKS"))
		{
			$c = "";
			foreach($levels as $key => $data)
			{
				$key++;
				$u .= "";
				if(($_SESSION["realestate_input_data"]["filled_level"]+2) > $key)
				{
					if($this->is_template("URL"))
					{
						$level_url = aw_url_change_var("level", ($key) , post_ru());
						$this->vars(array(
							"level_url" => $level_url,
						));
						$u .= $this->parse("URL");
						$this->vars(array(
							"URL" => $u,
						));
					}
				}
				else {
					$this->vars(array(
						"URL" => NULL,
					));
				}
				$this->vars(array(
					"level_name" 	=> $data["name"],
				));
				$c .= $this->parse("LINKS");				
				
			}
			$this->vars(array(
				"LINKS" => $c,
			));
		}
		
		$this->vars(array(
			"listname" => $tobj->name(),
			"cb_errmsg" => $cb_errmsg,
			"reforb" => $this->mk_reforb("subscribe",array(
				"section"	=> aw_global_get("section"),
				"level"		=> $level,
				"return_to"	=> post_ru(),
				"id"		=> $arr["alias"]["target"],
			)),
		));
//-----------------------------terve property saatmine , mitte ainult väärtuse-------------------
		//$cfgform = $tobj->get_first_obj_by_reltype("RELTYPE_CFGFORM");
		$ftype = $tobj->prop("form_type");
		$inst = empty($ftype) ? CL_REGISTER_DATA : $ftype;
		$rd = get_instance($inst);

		$dummy = new object();
		$dummy->set_class_id($clid);
		$dummy->set_parent($parent);
		$dummy->set_prop("realestate_manager" , $args["parent"]);		
		
		//$rd->cfgform_id = $cfgform->id();
		$rd->load_defaults();
		$els = $props;
		$els = $rd->parse_properties(array(
			"properties" => $els,
			"obj_inst" => $dummy,
		));
		$els = (array)$els + (array)$tmpx;
		classload("cfg/htmlclient");
		$htmlc = new htmlclient(array(
			"template" => "real_webform.tpl",
		));
		$htmlc->set_layout($layout);
		$htmlc->start_output();

		foreach($els as $pn => $pd)
		{
			$pd["capt_ord"] = $pd["wf_capt_ord"];
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		//$html = $htmlc->get_result(array(
		//	"raw_output" => 1,
		//));
		
		$html = array();
		
		foreach($els as $key => $val)
		{//	$val["capt_ord"] = $_SESSION["realestate_input_data"][$key];
			$val["value"] = $_SESSION["realestate_input_data"][$key];
			$htmlc = new htmlclient(array(
				"template" => "real_webform.tpl",
			));
			$htmlc->set_layout($layout);
			$htmlc->start_output();
		//	$val["capt_ord"] = $val["wf_capt_ord"];
			$htmlc->add_property($val);
			$htmlc->finish_output();
			$html[$key] = $htmlc->get_result(array(
				"raw_output" => 1,
			));
		}
		$this->vars($html);	

//		$htmlc->draw_element($targ->prop("realestate_environment"));
//------------------------------ property väärtuse saatmine kujul "property_nimi"_value --------------		
		$data_value = array();
		foreach($_SESSION["realestate_input_data"] as $key => $value)
		{
			$data_value[$key.'_value'] = $value;
		}
		$this->vars($data_value);	
		
		$this->vars(array("redirect_object" => $tobj->prop("redirect_object"),));
		exit_function("realestate_add::parse_alias");
		return $this->parse();
//		return $this->show(array("id" => $arr["alias"]["target"]));
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

	/** subscribe
		
		@attrib name=subscribe nologin="1" 
		@param id required type=int 
		@param rel_id required type=int 
		
	**/
	function subscribe($args = array())
	{
		if($args["do"] == "submit")
		{
			$main_obj = obj($args["id"]);
			$clss = aw_ini_get("classes");
			$class_entry = $clss[$args["clid"]];
			$parent = $class_entry["parents"];
			
/*			$realestate_obj = obj();
			$realestate_obj->set_parent($args["parent"]);
			$realestate_obj->set_class_id($args["clid"]);
			$realestate_obj->set_name($_SESSION["realestate_input_data"]["name"]);
			$realestate_obj->set_prop("realestate_manager" , $args["parent"]);
			$realestate_obj->save();
*/			
			$manager = get_instance(CL_REALESTATE_MANAGER);
			$realestate_obj_id = $manager->add_property(array(
				"manager"	=> $args["parent"],
				"type"		=> $args["type"],
				"section" 	=> aw_global_get("section"),
			));		
			
			$realestate_obj = obj($realestate_obj_id);
			$realestate_obj->set_name($_SESSION["realestate_input_data"]["name"]);
			$props = $realestate_obj->get_property_list();
			
			foreach($_SESSION["realestate_input_data"] as $key => $val)
			{
				if(array_key_exists($key , $props))
				{
					$realestate_obj->set_prop($key, $val);
				}
			}
			$ret_doc = $main_obj->prop("redir_object");
			//return $ret_doc;
			return aw_ini_get("baseurl")."/".$ret_doc;
		}
		
		$level = $_SESSION["realestate_input_data"]["level"];
		foreach($args as $key => $val)
		{
			$_SESSION["realestate_input_data"][$key] = $val;
		}
//		if($ret_doc)
//		{
//			return aw_ini_get("baseurl")."/".$ret_doc;
//		}
//		else
//		{
	//		return aw_ini_get("baseurl")."/".$ret_doc;
			return aw_url_change_var("level", $level , $_POST["return_to"]);		
//		}
	}

//-- methods --//
}
?>
