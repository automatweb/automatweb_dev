<?php
global $orb_defs;
$orb_defs["linkslist"] = "xml";
//define("PERPAGE",20); //max count of links per page
define("SHOW_TPL_DIR",aw_ini_get("tpldir")."/linklist/show");
class linklist extends aw_template
{
	////////////////////////////////////
	// the next functions are REQUIRED for all classes that can be added from menu editor interface
	////////////////////////////////////

	function linklist()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("linklist");
	}

	////
	// !called, when adding a new object 
	// parameters:
	//    parent - the folder under which to add the object
	//    return_url - optional, if set, the "back" link should point to it
	//    alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa linklist");
		}
		else
		{
			$this->mk_path($parent,"Lisa linklist");
		}
		$this->read_template("add.tpl");
		$ob = new db_objects;
		//get list of the rootmenus
		$objects = get_instance("objects");
		$root_list= $objects->get_list(); 
		$this->vars(array(
			"name" => "uus lingikogu",
			"tpls" => $this->picker("", $this->get_templates(SHOW_TPL_DIR)),
			"rootitems" => $this->picker("", $root_list),
			"reforb" => 	$this->mk_reforb("submit", 
				array(
					"parent" => $parent, 
					"alias_to" => $alias_to, 
					"return_url" => $return_url,
				)),
		));
		return $this->parse();
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda linklist");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda lingikogu");
		}
		$this->read_template("change.tpl");
		$objects = get_instance("objects");
		$root_list= $objects->get_list(); 

		// list of a link properties by wich we can order links
		$sortim = array ( 
			"name"		=> "lingi nime",
			"jrk"		=> "lingi jrknr",
			"modified"	=> "muutmise aja",
		);
		//list of properties that we can turn into a hyperlink
		$linkis=array(
			"comment" => "comment",
			"url" => "url",
			"caption" => "caption",
		);
		
		// these are the directory properties which we can assign to a form element, for searching
		$propertid=array( 
			"oid" =>     "oid",
			"parent" =>    "parent",
			"name" =>    "name" ,
			"createdby" =>     "createdby"  ,
			"class_id" =>     "class_id"  ,
			"created" =>    "created" ,
			"modified" =>    "modified", 
			"status" =>     "status"  ,
			"hits" =>     "hits"  ,
			"lang_id" =>     "lang_id"  ,
			"comment" =>     "comment"  ,
			"last" =>     "last"  ,
			"modifiedby" =>     "modifiedby"  ,
			"jrk" =>     "jrk"  ,
			"visible" =>     "visible"  ,
			"period" =>     "period"  ,
			"alias" =>     "alias"  ,
			"periodic" =>     "periodic"  ,
			"site_id" =>     "site_id"  ,
			"doc_template" =>     "doc_template"  ,
			"activate_at" =>     "activate_at"  ,
			"deactivate_at" =>     "deactivate_at"  ,
			"autoactivate" =>     "autoactivate"  ,
			"autodeactivate" =>     "autodeactivate"  ,
			"brother_of" =>     "brother_of"  ,
			"cachedirty" =>     "cachedirty"  ,
			"metadata" =>     "metadata"  ,
		);

		// if we use form output instead of regular links
		if($ob["meta"]["is_formentry"]) 
		{
			//get all the form objects we can find
			$forms = $this->list_objects(array(
					"class" => CL_FORM,
					"orderby" => "name",
//					"return" => ARR_ALL,
			));

			if($ob["meta"]["forms"])
			{

				$form = get_instance("form"); 

				$form->load($ob["meta"]["forms"]); 
				$felement = $form->get_form_elements(array(
					"id" => $ob["meta"]["forms"],
					"key" => "id",
					"use_loaded" => true,
					"all_data" => false,
				));

			}		

			$this->vars(array(
				"forms" => $this->picker($ob["meta"]["forms"], $forms),         //all the form objects we can find
				"felement" => $this->picker($ob["meta"]["felement"],$felement), //current active form element
				"vordle" => $this->picker($ob["meta"]["vordle"],$propertid),    //
			));		
			$vormisisestus=$this->parse("vormisisestus");
		}

//see on see tasandite konfinnimise süteem

		$tulpi=$ob["meta"]["tulpi"];

		//delete level(s)
		if ($ob["meta"]["kustuta"])
		{
			foreach($ob["meta"]["kustuta"] as $key => $val)
			{
				unset($tulpi[$key]);
			}
		}

		// add level
		if($adding=(int)$ob["meta"]["add_level"])
		{
			$tulpi[$adding]=$ob["meta"]["default_tulpi"];  //anname tasandile algus default tulpade arvu
			$ob["meta"]["sortby_links"][$adding]=$ob["meta"]["default_sortby_links"];
			$ob["meta"]["sortby_dirs"][$adding]=$ob["meta"]["default_sortby_dirs"];
			$ob["meta"]["tulpi"][$adding]=$ob["meta"]["default_tulpi"];
		}
		
		// parse levels
		if ($tulpi)
		{
			foreach($tulpi as $key =>  $val)
			{
				$this->vars(array("tas" => $key,//"val" => $val,
				"sortby_dirs" => $this->picker($ob["meta"]["sortby_dirs"][$key], $sortim),
				"sortby_links" => $this->picker($ob["meta"]["sortby_links"][$key], $sortim),
				"tulpi" => (int)$ob["meta"]["tulpi"][$key]?(int)$ob["meta"]["tulpi"][$key]:$ob["meta"]["default_tulpi"],
				"act" => checked($ob["meta"]["act_tasand"][$key]),
				"sortby_jknr" => checked($ob["meta"]["sortby_jknr"][$key])));
				$levels[$key]=$this->parse("levels");
			}
			ksort($levels);
			$levels=implode("",$levels);
		}
//////////

		
		
		//selecting wich link property we wanna see as hyperlink	
		foreach($linkis as $key =>  $val)
		{
			$this->vars(array(
					"mis" => $key,
					"is_hyper" => checked($ob["meta"]["klikitav"][$key]),
			));
			$klikitav.=$this->parse("klikitav");
		}

		$ref = $this->mk_reforb("submit", array("id" =>  $id, "return_url" => urlencode($return_url)));

		//gets a list of templates (for linkslist "show")
		$list_templates=$this->get_templates(SHOW_TPL_DIR);

		$this->vars(array(
			"default_tulpi" => $ob["meta"]["default_tulpi"],			// default column count
			"abix" => "", 
			"is_formentry" => $ob["meta"]["is_formentry"]?checked($ob["meta"]["is_formentry"]):"",		// kas on vormisisestus (radio)
			"is_not_formentry" => $ob["meta"]["is_formentry"]?"":checked(1),	// kas on vormisisestus (radio)
			"vormisisestus" => $vormisisestus,				// formentry data (sub)
			"levels" => $levels,
			"tasand" => $ob["meta"]["tasand"]?$ob["meta"]["tasand"]:1,
			"klikitav" => $klikitav,
			"name" => $ob["name"],
			"active_dirs" => checked($ob["meta"]["active_dirs"]),
			"active_links" => checked($ob["meta"]["active_links"]),
			"newwindow" => checked($ob["meta"]["newwindow"]),
			"path" => checked($ob["meta"]["path"]),				//show path
			"default_sortby_dirs" => $this->picker($ob["meta"]["default_sortby_dirs"], $sortim),
			"default_sortby_links" => $this->picker($ob["meta"]["default_sortby_links"], $sortim),
			"tpls" => $this->picker($ob["meta"]["tpls"], $list_templates),
			"rootitems" => $this->picker($ob["meta"]["lingiroot"], $root_list),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));
		return $this->parse();
	}

	////
	// !gets list of the files in given path (eg templates)
	//  "filename.ext" => filename.ext, because numeric key may differ after file add/delete
	function get_templates($path,$ext="")
	{
		if ($dir = @opendir($path))
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file != "." && $file != ".." && is_file("$path/$file"))
				{ 
					$list_templates[$file]=$file;
				}  
			}
			closedir($dir);
		}
		return $list_templates;
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"metadata" => array(
					"kustuta" => $kustuta,
					"vordle" => $vordle,
					"is_formentry" => $is_formentry,
					"forms" => $forms,
					"felement" => $felement,
					"default_tulpi" => (int)$default_tulpi?(int)$default_tulpi:1,
					"klikitav" => $klikitav,
//					"naidata" => $naidata,
					"tas" => $tas,
//					"tasandid" => $tasandid,
//					"tasand" => $tasand,
					"add_level" => $add_level,
					"tulpi" => $tulpi,
					"lingiroot" => $lingiroot,
					"active_dirs" => $active_dirs,
					"active_links" => $active_links,
					"tpls" => $tpls,
					"path" => $path,
					"newwindow" => $newwindow,
					"sortby_dirs" => $sortby_dirs,
					"sortby_links" => $sortby_links,
					"default_sortby_dirs" => $default_sortby_dirs,
					"default_sortby_links" => $default_sortby_links
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"metadata" => array(
					"lingiroot" => $lingiroot,
					"tpls" => $tpls),
				"name" => $name,
				"class_id" => CL_LINK_LIST,
				"comment" => $comment
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}
		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}


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
	// !linklist, currently shows a predefined catalogs at the top and below links of the active catalog or 
	// form output maching the serach criteria
	//
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$this->read_template("show/".$ob["meta"]["tpls"]);
		$aktiivne=$aktiivne?$aktiivne:$ob["meta"]["lingiroot"];

		//	if ($ob["meta"]["path"]) 
		{
		// see peaks olema kataloogi tee
		// teen ümber kui tuleb parem idee // get_object_chain vihjeks
		$ak=$aktiivne;
		while((($ak==$ob["meta"]["lingiroot"]))==false)
		{
			$ph = $this->get_object(array("oid" => $ak,"return" => ARR_ALL),false,false);
			$this->vars(array(
				"name" => $ph["name"],
				"link" => $this->mk_my_orb("show",array(
							"aktiivne" => $ak
							,"id" => $id
						),""
//						,true //lingikogu eraldi uues aknas
						) 
					));
			$nms=$this->parse("tee").$nms;
			$ak=$ph["parent"];
			$tase++;
		};

			$this->vars(array(           //kirvemeetodil läheb praegu /juur
				"name" => "juur",
				"link" => $this->mk_my_orb("show",array(
								"aktiivne" => $ob["meta"]["lingiroot"],
								"id" => $id
								),
								""
//								true //lingikogu eraldi uues aknas
								) 
			));
			$nms=$this->parse("tee").$nms;
			$tase++; //taseme number vajalik 
	}
	// if path is not needed then delete, its stupid to generate path and then delete, but we need level (tase)
	if (!$ob["meta"]["path"]) 
	{
		$nms="";
	}

///end kataloogitee/**/

		$order=$ob["meta"]["sortby_dirs"][$tase]?$ob["meta"]["sortby_dirs"][$tase]:$ob["meta"]["default_sortby_dirs"];

		$menus = $this->list_objects(array("class" => CL_PSEUDO, 
					"parent" => $aktiivne,
					"active" => $ob["meta"]["active_dirs"],
					"orderby" => $order,
					"return" => ARR_ALL
					));
		if ($menus)
		{
			foreach($menus as $key => $value) 
			{
				extract($value);
				$sub_count = $this->count_objects(array(
					"class" => CL_PSEUDO, 
					"parent" => $oid,
				));

				$this->vars(array(
					"sub_count" => $sub_count?"<small>[$sub_count]</small>":"",
//					"name" => $value["jrk"]." * ".$name,
					"name" => $name,
				));
				
				if ($ob["meta"]["forms"] && $ob["meta"]["felement"]) // kui on formiinfo
				{
					$this->vars(array(
					"link" => $this->mk_my_orb("show",array(
								"aktiivne" => $oid
								,"id"  => $id
								,"otsi" => urlencode($value[$ob["meta"]["vordle"]])//$$ob["meta"]["vordle"]
								),
								"")
						));
				} else {
					$this->vars(array(
					"link" => $this->mk_my_orb("show",array(
							"aktiivne" => $oid,
							"id" => $id
							),
							"")
						));
				
				}	

				if($ob["meta"]["tulpi"][$tase])  //see on see jagamine tulpadesse
				{
					$tulp=$value["jrk"][0];
					if(($ob["meta"]["tulpi"][$tase]<$tulp)) $tulp=1; //kui jrk algab suurema numbriga kui tulpade arv, siis lheb esimesse
				} 
				else
				{
					$tulp=($limit % $ob["meta"]["default_tulpi"])+1; //siin peaks kuidagi võrdselt ära jagama
				}
				if (!$tulp)
				{
					$tulp=1;
				}
				$tasand[$tulp][$value["jrk"]].=$this->parse("dir");
				$limit++;
			}//foreach


			ksort($tasand);
			foreach ($tasand as $key=> $val) //parsime tulbad
			{
			//	ksort($val); // sordime ühe tulba lingid
				$this->vars(array("dirs" => implode("",$val)));
				$tulbad.=$this->parse("tulp");
			}
		}//if menus



		$order=$ob["meta"]["sortby_links"][$tase]?$ob["meta"]["sortby_links"][$tase]:$ob["meta"]["default_sortby_links"];

		//kui tahame linke
		if (!$ob["meta"]["is_formentry"]) 
		{
			$objects = $this->list_objects(array("class" =>  CL_EXTLINK
					,"parent" => $aktiivne
					,"active" => $ob["meta"]["active_links"]
					,"orderby" => $order
					,"return" => ARR_ALL
			));
			if ($objects)
			{
				classload("extlinks");
				$limit2=0;
				$ll = new extlinks();
				foreach($objects as $key => $value)
				{
					extract($value);
					$limit2++;
					//if ($limit2>=PERPAGE) break;
					list($url,$target,$caption) = $ll->draw_link($key);
	
					if($ob["meta"]["newwindow"]) 
					{
						$target="target=_blank";
					}
					$this->vars(array("plain_url" => $url));
				//teeme lingiks kui vaja
					if ($ob["meta"]["klikitav"])
					{
						foreach($ob["meta"]["klikitav"] as $val) 
						{
//							$abi=sprintf("<a href=%s %s>%s</a>",$url,$target,$$val);//uimane
							$u[$val]="<a href=$url $target>".$$val."</a>";
						}
						extract($u);
					}

					$this->vars(array(
						"l_url" => $url,
//						"l_name" => $value["jrk"]."_".$caption,
						"l_name" => $caption,
						"l_comment" => $comment,
						"target" => $target
						));

					$links.=$this->parse("links"); //parse links
				}
			}

		} 
		else //kui tahame vormisissestusi
		{
			$form = get_instance("form"); 
			$form->load($ob["meta"]["forms"]); 
			$form->set_element_value($ob["meta"]["felement"],urldecode($otsi)); 
			$links = $form->new_do_search(array()); 
		}

		$this->vars(array(
			"abix" => "",
			"nms" => $nms,
			"total" => (int)$limit,
			"total2" => (int)$limit2,
			"lingiroot" => $ob["meta"]["lingiroot"],
			"active_links" => $ob["meta"]["active_links"],
			"active_dirs" => $ob["meta"]["active_dirs"],
			"newwindow" => 	$ob["meta"]["newwindow"],
			"default_sortby_dirs" => $ob["meta"]["default_sortby_dirs"],
			"default_sortby_links" => $ob["meta"]["default_sortby_links"],
			"tpls" => $ob["meta"]["tpls"],
			"name" => $ob["name"],
			"aktiivne" => $aktiivne,
			"tulbad" => $tulbad,
//			"links" => $links?$links:"<tr><td>linke pole?</td></tr>"
			"links" => $links
		));

		return $this->parse();
	}

	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		return aw_serialize($row);
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = unserialize($str);
		$row["parent"] = $parent;
		$id = $this->new_object($row);
		return true;
	}

	////
	// !this is not required 99% of the time, but you can override adding aliases to documents - when the user clicks
	// on "pick this" from the aliasmanager "add existing object" list and this function exists in the class, then it will be called
	// parameters
	//   id - the object to which the alias is added
	//   alias - id of the object to add as alias
/*	function addalias($arr)
	{
		extract($arr);
		// this is the default implementation, don't include this function if you're not gonna change it
		$this->add_alias($id,$alias);
		header("Location: ".$this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"));
	}*/
}
?>