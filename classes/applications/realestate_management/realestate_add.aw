<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_add.aw,v 1.2 2006/03/07 13:34:11 markop Exp $
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

@property help type=text
@caption Abi:

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
			case "help":
				$template_dir = $this->site_template_dir;
				$prop["value"] = nl2br(htmlentities("peab olema määratud nii template faili nimi, kui ka taseme nimi.
				Template fail peab asuma kataloogis :".$template_dir.".
				Kui tahad templates näha valmis tehtud property't koos õigete valikutega jne, siis kasuta templates muutujuat {VAR:property}, kui vaja läheb vaid property väärtust , kasuta muutujat {VAR:property_value} (property asemele siis vastava property nimi, mille saab Kohustuslike väljade alt...sulgudes olev tekst).
				Kui miski property kohta märkida, et see on kohustluslik, siis töötab asi nii, et juhul , kui mingisse teplate'i kirjutatakse vastava property nimi, siis sealt edasi ei lasta , enne kui ta miski väärtuse kaasa saab.
				
				Et saaks erinevatele tasemetele tagasi minna, siis tuleks kasutada template'is miskit taolist asja:
				<!-- SUB: LINKS --><!-- SUB: URL -->
				<a href=\"{VAR:level_url}\"><!-- END SUB: URL -->
				{VAR:level_name}</a><!-- END SUB: LINKS -->
				kus siis {VAR:level_name} asemele tekkivad kõik tasemete nimed ja {VAR:level_url} asemele tasemete urlid... vaid juhul kui vastavale tasemele pääsemiseks on vajalikud väljad juba täidetud. {VAR:reforb} oleks ka kasulik kuskile formi sisse panna
				
				Kasutuses veel (vajalikud xmlrewquest jaoks): 
				{VAR:url}
				{VAR:admin_structure_id} ,Riigi haldusjaotuse ID
				{VAR:div0}-{VAR:div4}, vastavalt siis maakonna, linna, linnaosa, valla ja asula/küla haldusüksuse IDd"));
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
			case "realestate_type":
				if($arr["obj_inst"]->prop("realestate_type"))
				{
					$prop["value"] = $arr["obj_inst"]->prop("realestate_type");
				}
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
			$props = array_merge(
				$o->get_property_list(),
				$cfgu->load_class_properties(array(
					"clid" => CL_REALESTATE_PROPERTY,
				))
			);
			
			$groups = $cfgu->get_groupinfo();
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

	//kontroll, kas mõni vajalik väli on jäänud täitmata
	function not_filled($arr)
	{
		$ret = FALSE;
		extract($arr);
		if(sizeof($data) > 0)
		{
			foreach($data as $key => $val)
			{
				if(!(strlen($val) > 0) && $fields[$key])
				{
					error::raise(array(
						"msg" => t("väli '".$key."' peab olema täidetud"),
						"fatal" => false,
						"show" => true,
					));					
					$ret = true;
				}
			}
		}
		return $ret;
	}

	function parse_alias($arr)
	{
		$targ = obj($arr["alias"]["target"]);
		enter_function("realestate_add::parse_alias");
		$clid = $targ->prop("realestate_type");
		$levels = $targ->meta("levels");
		$fields = $targ->meta("required_fields");
		$parent = $targ->prop("realestate_environment");
		global $level;
		if(!$level)
		{
			$level = 1;
			$_SESSION["realestate_input_data"] = NULL;
		}
		$data = $_SESSION["realestate_input_data"];
		$data["level"] = NULL;
		
		if($this->not_filled(array("data" => $data , "fields" => $fields,)))
		{
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
		$tpl2 = $levels[$level]["template"];
		$_SESSION["realestate_input_data"]["level"] = $level+1;	
		$this->read_template($tpl);
		lc_site_load("realestate_add", &$this);

		//tekitab muutujad erinevate tasemete nimede ja linkidega		
		$this->vars(array("url" => $this->mk_my_orb("get_divisions", array())));
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
		//juhul , kui template faile rohkem ei ole, siis läheb edasi objekti salvestama
		if(!$tpl2)
		{
			$do = "submit";
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("subscribe",array(
				"section"	=> aw_global_get("section"),
				"level"		=> $level,
				"return_to"	=> post_ru(),
				"id"		=> $arr["alias"]["target"],
				"do"		=> $do,
				"parent"	=> $parent,
				"type"		=> $this->type_name($clid),
				"clid"		=> $clid,
			)),
		));
		//property tervenisti saatmine... valmisjoonistatud kujul
		$props_html = $this->get_props_for_site(array(
			"alias_id"	=> $arr["alias"]["target"],
			"clid"		=> $clid,
			"parent"	=> $parent,
		));
		$this->vars($props_html);	
		//property väärtuse saatmine kujul "property_nimi"_value
		$data_value = array();
		foreach($_SESSION["realestate_input_data"] as $key => $value)
		{
			$data_value[$key.'_value'] = $value;
		}
		$this->vars($data_value);	

		$subs = array("county", "city" ,"citypart", "vald" , "settlement");
		$add_realestate_obj = obj($arr["alias"]["target"]);
		$realestate_environment_obj = obj($add_realestate_obj->prop("realestate_environment"));
		$admin_structure_id = $realestate_environment_obj->prop("administrative_structure");
		$division = array(
			$realestate_environment_obj->prop("address_equivalent_1") ,
			$realestate_environment_obj->prop("address_equivalent_2") ,
			$realestate_environment_obj->prop("address_equivalent_3") ,
			$realestate_environment_obj->prop("address_equivalent_4") ,
			$realestate_environment_obj->prop("address_equivalent_5")
		);
		//muutujad div0 - div4 vastavalt haldusüksuste IDd
		foreach ($division as $key => $div)
		{
			$this->vars(array("div".$key => $div));
		}
		//saidil läheb vast vaja ka Riigi haldusjaotuse IDd
		$this->vars(array("admin_structure_id" => $admin_structure_id));
		$parent_division; // siia peaks miski väärtuse panema, kui tahaks get_divisions funktsioonist ühe kindla halduspiirkonna alampiirkondade nimekirja
		foreach($subs as $key => $sub)//erinevate maakondade, linnade , linnaosade , valdade jne valikud, mis loodetavasti on SUBides
		{
			if ($this->is_template($sub))
			{
				$this->vars(array($sub => $this->get_divisions(array(
					"admin_structure_id"	=> $admin_structure_id, 
					"division"		=> $division[$key],
					"parent"		=> $parent_division,
					"sub"			=> $sub,
				))));
			}
		}
		exit_function("realestate_add::parse_alias");
		return $this->parse();
	}

	/** get_divisions
		@attrib name=get_divisions nologin="1" 
	**/
	
	function get_divisions($arr)
	{
		global $site , $admin_structure_id , $parent , $division;
		if($site)
		{
			$site = true;
			$arr["parent"] = $parent;
			$arr["admin_structure_id"] = $admin_structure_id;
			$arr["division"] = $division;
		}
		$admin_structure = obj($arr["admin_structure_id"]);
		$param = array(
			"prop" => "units_by_division",
			"division" => $arr["division"], // required. aw object or oid
			"parent" => $arr["parent"], // optional. int. aw oid
		);
		$unit_objlist = $admin_structure->prop($param);
		//juhul kui saidilt tuleb xmlhttprequest
		if($site)
		{
			header("Content-type: text/xml");
			$xml = "<?xml version=\"1.0\" encoding=\"".aw_global_get("charset")."\" standalone=\"yes\"?>\n<response>\n";
			if(is_array($unit_objlist->arr()) && is_oid($arr["parent"]))
			{				
				foreach($unit_objlist->arr() as $key => $obj)
				{
					$xml .= "<item><value>".$obj->id()."</value><text>".$obj->name()."</text></item>";
				}
			}
			else
			{
				$xml .= "<item><value>0</value><text>".$arr["parent"]." </text></item>";
				$xml .= "<item><value>1</value><text>".$arr["division"]."</text></item>";
			}
			$xml .= "</response>";
			die($xml);
		}
		$c = "";
		foreach($unit_objlist->arr() as $key => $obj)
		{	$selected = "";
			if($_SESSION["realestate_input_data"][$arr["sub"]] == $obj->id() || ($_SESSION["realestate_input_data"][$arr["sub"]] == $obj->name() && $arr["sub"] == "settlement"))
			{
				$selected = "selected";
			}
			$this->vars(array(
				"division"	=> $obj->name(),
				"division_id"	=> $obj->id(),
				"selected"	=> $selected,
			));
			$c .= $this->parse($arr["sub"]);
		}
		return $c;
	}

	function get_props_for_site($arr)
	{	
		extract($arr);
		if($_SESSION["realestate_input_data"]["realestate_id"])
		{
			$dummy = obj($_SESSION["realestate_input_data"]["realestate_id"]);
		}
		else
		{
			$dummy = new object();
			$dummy->set_class_id($clid);
			$dummy->set_parent($parent);
			$dummy->set_prop("realestate_manager" , $parent);
		}
		$rd = get_instance($clid);
		$rd2 = get_instance(CL_REALESTATE_PROPERTY);
		$rd->load_defaults();
		$rd2->load_defaults();
		$o = obj();
		$o->set_class_id($clid);
		$o_props = $o->get_property_list();

		//valitud propertytele leiab get_property funktsioonist väärtusi
		$props_to_get = array("year_built","transaction_broker_fee","transaction_broker_fee_type" , "transaction_rent_total" , "estate_price_total" , "legal_status" , "transaction_selling_price");
		foreach($o_props as $key => $val)
		{
			if(in_array($key , $props_to_get))
			{
			$rd->get_property(array("prop" => &$o_props[$key] , $prop, "request" => $request , "obj_inst" => $dummy));
			}
		}
	
		$cfgu = get_instance("cfg/cfgutils");
		$els = array_merge(
			$o_props,
			$cfgu->load_class_properties(array(
				"clid" => CL_REALESTATE_PROPERTY,
			))
		);

		$rd->load_defaults();
		$els = $rd->parse_properties(array(
			"properties" => $els,
			"obj_inst" => $dummy,
		));

		foreach($els as $key => $val)
		{
			unset($els[$key]["autocomplete_source"]);
			unset($els[$key]["autocomplete_params"]);
		}
		
		classload("cfg/htmlclient");
		$html = array();
		foreach($els as $key => $val)
		{
			$val["value"] = $_SESSION["realestate_input_data"][$key];
			$htmlc = new htmlclient(array(
				"template" => "real_webform.tpl",
			));
			$htmlc->set_layout($layout);
			$htmlc->start_output();
			$val["capt_ord"] = $val["wf_capt_ord"];
			$htmlc->add_property($val);
			$htmlc->finish_output();
			$html[$key] = $htmlc->get_result(array(
				"raw_output" => 1,
			));
		}
	//	classload("vcl/table");
	//	$t = new vcl_table();
	//	$prop = array("name" => "address_connection", "type" => "table", "vcl_inst" =>&$t);
	//	$i = get_instance(CL_REALESTATE_PROPERTY);
	//	$i->get_property(array("prop" => &$prop, "request" => $request));
	//	$t->sort_by();
	//	$html["address_connection"] = $t->draw();
		return $html;
	}

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
		$level = $_SESSION["realestate_input_data"]["level"];		
		if(!$_SESSION["realestate_input_data"]["realestate_id"])
		{
			$clss = aw_ini_get("classes");
			$class_entry = $clss[$args["clid"]];
			$parent = $class_entry["parents"];
			$manager = get_instance(CL_REALESTATE_MANAGER);		
			$realestate_obj_id = $manager->add_property(array(
				"manager"	=> $args["parent"],
				"type"		=> $args["type"],
				"section" 	=> aw_global_get("section"),
			));	
			$realestate_obj = obj($realestate_obj_id);
			$realestate_obj->set_name($_SESSION["realestate_input_data"]["name"]);
			$_SESSION["realestate_input_data"]["realestate_id"] = $realestate_obj_id;
		}
		else
		{
			$realestate_obj = obj($_SESSION["realestate_input_data"]["realestate_id"]);
		}
		foreach($args as $key => $val)
		{
			$_SESSION["realestate_input_data"][$key] = $val;
		}
		$props = $realestate_obj->get_property_list();
		$realestate_environment_obj = obj($args["parent"]);
		$address_props = array(
			"county"	=> $realestate_environment_obj->prop("address_equivalent_1"),
			"city"		=> $realestate_environment_obj->prop("address_equivalent_2"),
			"citypart"	=> $realestate_environment_obj->prop("address_equivalent_3"),
			"vald"		=> $realestate_environment_obj->prop("address_equivalent_4"),
			"settlement"	=> $realestate_environment_obj->prop("address_equivalent_5"),
			"place_name"	=> $realestate_environment_obj->prop("address_equivalent_5"),
			"street"	=> "street",
			"street_address"=> 0,
			"apartment"	=> 0,
		);
		$address = $realestate_obj->get_first_obj_by_reltype("RELTYPE_REALESTATE_ADDRESS");
		foreach($_SESSION["realestate_input_data"] as $key => $val)
		{
			if(array_key_exists($key , $props))
			{
				$realestate_obj->set_prop($key, $val);
			}
			//aadressi salvestamine - tõsine porno
			if(array_key_exists($key , $address_props))
			{
				if(($key == "street_address") || ($key == "apartment"))
				{
					$address->set_prop ($key, $val);
				}
				else
				{
					if($key == "place_name")
					{
						//kohanimi lisatakse asulate hulka
						if(strlen($val)>1)
						{
							$address->set_prop ("unit_name", array (
								"division" => $address_props[$key],
								"name" => $val,
							));
							$_SESSION["realestate_input_data"]["settlement"] = $val;
							$_SESSION["realestate_input_data"]["place_name"] = null;
						}
					}
					else
					{
						if(is_oid($val))
						{
							$adr_obj = obj($val);
							$val = $adr_obj->name();
						}
						$address->set_prop ("unit_name", array (
							"division" => $address_props[$key],
							"name" => $val,
						));
					}
				}
			}
			$address->save();
		}
		$realestate_obj->save();
		$main_obj = obj($args["id"]);		
		if($args["do"] == "submit" )
		{
			return aw_ini_get("baseurl")."/".$main_obj->prop("redir_object");		
		}
		else
		{
			return aw_url_change_var("level", $level , $args["return_to"]);	
		}
	}
}
?>
