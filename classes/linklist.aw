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
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, 
								"alias_to" => $alias_to, 
								"return_url" => $return_url
								)),
//			"parent" => $this->picker($parent,$ob->get_list()),
//			"search_doc" => $this->mk_orb("search_doc", array())
//			,"extlink" => "checked"
			));
		return $this->parse();
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
/*
function teeseda() 
{ 
$form = get_instance("form"); 
$html = $form->gen_preview(array( 
"id" => $form_id, 
"entry_id" => $entry_id, 
"reforb" => $this->mk_reforb("my_submit", array("id" => $id)) 
)); 
} 
*/	
	
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
$form = get_instance("form"); 
		
		$this->read_template("change.tpl");
		$objects = get_instance("objects");
		$root_list= $objects->get_list(); 
		$sortim = array ( 
			"name"		=> "lingi nime",
			"jrk"		=> "lingi jrknr",
			"modified"	=> "muutmise aja"
		);
//print_r($ob["meta"]);

//see on see tasandite konfinnimise süteem
$tmp=array("0"=>"vali", "1"=>"1" ,"2"=>"2" ,"3"=>"3" ,"4"=>"4" ,"5"=>"5" ,"6"=>"6" ,"7"=>"7"); //tasandid
$tmp2=array("1"=>"1" ,"2"=>"2" ,"3"=>"3" ,"4"=>"4" ,"5"=>"5" ,"6"=>"6" ,"7"=>"7");//tulbad
$linkis=array("comment"=>"comment","url"=>"url","caption"=>"caption");
		$tulpi=$ob["meta"]["tulpi"];
		if ($ob["meta"]["tasand"])
		{
			if($ob["meta"]["tegevus"]=="lisa")
			{
				$tulpi[$ob["meta"]["tasand"]]=$ob["meta"]["default_tulpi"];  //anname tasandile algus default tulpade arvu
				$ob["meta"]["sortby_links"][$ob["meta"]["tasand"]]=$ob["meta"]["default_sortby_links"];
				$ob["meta"]["sortby_dirs"][$ob["meta"]["tasand"]]=$ob["meta"]["default_sortby_dirs"];
				$ob["meta"]["tulpi"][$ob["meta"]["tasand"]]=$ob["meta"]["default_tulpi"];
			}
			elseif($ob["meta"]["tegevus"]=="kustuta")
			{
				unset($tulpi[$ob["meta"]["tasand"]]); //kui tulpade arv 0 siis kustutame tasandi info üldse ära
			}
		}
		if ($tulpi) 
		{
			foreach($tulpi as $key =>  $val)
			{
				$this->vars(array("tas"=>$key,//"val"=>$val,
				"sortby_dirs" => $this->picker($ob["meta"]["sortby_dirs"][$key], $sortim),
				"sortby_links" => $this->picker($ob["meta"]["sortby_links"][$key], $sortim),
				"tulpi" => $this->picker($ob["meta"]["tulpi"][$key], $tmp2),
				"act"=>checked($ob["meta"]["act_tasand"][$key]),
				"sortby_jknr"=>checked($ob["meta"]["sortby_jknr"][$key])));
				$t10.=$this->parse("tasandids");
			}
		}
//////////

		
		foreach($linkis as $key =>  $val)
		{
			$this->vars(array("mis"=>$key,
					"kas_kliki"=>checked($ob["meta"]["klikitav"][$key]),
					"kas_naita"=>checked($ob["meta"]["naidata"][$key])
			));
			$klikitav.=$this->parse("klikitav");
		}

$ref = $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)));


		$list_templates=$this->get_templates(SHOW_TPL_DIR);
		$this->vars(array(
			"default_tulpi"=>$this->picker($ob["meta"]["default_tulpi"],array("vali",1,2,3,4,5,6)),
			"abix"=>$abix,
			"tasandids" => $t10,
			"t10" => $t10,
			"act_default"=>checked($ob["meta"]["act_tasand"][0]),
			"tasand" => $ob["meta"]["tasand"]?$ob["meta"]["tasand"]:1,
			"tasandid" => $this->picker($ob["meta"]["tasand"],$tmp ),
			"tulbad" => $this->picker($ob["meta"]["tulpi"], $tmp2),
			"klikitav" => $klikitav,
			"name" => $ob["name"],
			"active_dirs" => checked($ob["meta"]["active_dirs"]),
			"active_links" => checked($ob["meta"]["active_links"]),
			"newwindow" => checked($ob["meta"]["newwindow"]),
			"path" => checked($ob["meta"]["path"]),
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
	//  "filename.ext"=>filename.ext, because numeric key may differ after file add/delete
	function get_templates($path,$ext="")
	{
		if ($dir = @opendir($path))
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file != "." && $file != "..")
				{ 
					$list_templates[$file]=$file;
				}  
			}
			closedir($dir);
		}
		return $list_templates;
	}




/*
function my_submit($arr) 
{ 
		extract($arr);
$form = get_instance("form"); 
$form->process_entry(array( 
"id" => $form_id, 
"entry_id" => $entry_id, 
)); 

$new_entry_id = $form->entry_id; 
} 


*/


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
//					"confi_tasandid"=>$confi_tasandid,
					"tegevus"=>$tegevus,
					"default_tulpi"=>$default_tulpi,
					"sortby_jknr"=>$sortby_jknr,
					"klikitav"=>$klikitav,
					"naidata"=>$naidata,
					"act_tasand"=>$act_tasand,
					"tas"=>$tas,
					"t10"=>$t10,
					"tasandid" => $tasandid,
					"tasand" => $tasand,
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
	// !currently shows a predefined catalogs at the top and below links of the active catalog
	//

	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$this->read_template("show/".$ob["meta"]["tpls"]);
		$aktiivne=$aktiivne?$aktiivne:$ob["meta"]["lingiroot"];

		//parses subdirectories

//kui näitame pathi
//start kataloogitee
//	if ($ob["meta"]["path"]) 
	{
		// see peaks olema kataloogi tee
		// teen ümber kui tuleb parem idee // get_object_chain
		$ak=$aktiivne;
		while((($ak==$ob["meta"]["lingiroot"]))==false)
		{
			$ph = $this->get_object(array("oid"=>$ak,"return" => ARR_ALL),false,false);
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
						),""
						,true //lingikogu eraldi uues aknas
					) 
				));
			$nms=$this->parse("tee").$nms;
			$tase++;
	}
	if (!$ob["meta"]["path"]) $nms="";

//	echo $tase;
///end kataloogitee/**/

		$order=$ob["meta"]["sortby_dirs"][$tase]?$ob["meta"]["sortby_dirs"][$tase]:$ob["meta"]["default_sortby_dirs"];

		$menus = $this->list_objects(array("class" => CL_PSEUDO, 
					"parent" => $aktiivne,
					"active" => $ob["meta"]["active_dirs"],
					"orderby"=> $order,
					"return" => ARR_ALL
					));
		if ($menus)
		{
			foreach($menus as $key => $value) 
			{
				extract($value);
				//if ($limit>=PERPAGE) break;
				$this->vars(array(
				"name" => $value["jrk"]."_".$name,
				"link" => $this->mk_my_orb("show",array(
							"aktiivne" => $oid,
							"id" => $id
							),
//							"",true) //lingikogu eraldi uues aknas
							"")
					));
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
			}


ksort($tasand);
foreach ($tasand as $key=>$val) //parsime tulbad
{
//		ksort($val); // sordime ühe tulba lingid

	$this->vars(array("dirs"=>$key.implode("",$val)));
	$tulbad.=$this->parse("tulp");
}


		}

		//parses links under active directory
		$order=$ob["meta"]["sortby_links"][$tase]?$ob["meta"]["sortby_links"][$tase]:$ob["meta"]["default_sortby_links"];
		$objects = $this->list_objects(array("class" =>  CL_EXTLINK,
					"parent" => $aktiivne,
					"active" => $ob["meta"]["active_links"],
					"orderby"=> $ob["meta"]["sortby_links"][$tase],
					"return" => ARR_ALL
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

				if($ob["meta"]["newwindow"]) {
					$target="target=_blank";
					}
				//teeme lingiks kui vaja
				if ($ob["meta"]["klikitav"])
				{
					foreach($ob["meta"]["klikitav"] as $val) 
					{
//						$abi=sprintf("<a href=%s %s>%s</a>",$url,$target,$$val);//uimane
						$u[$val]="<a href=$url $target>".$$val."</a>";
					}
				extract($u);
				}

				$this->vars(array(
					"l_url" => $url,
					"l_name" => $value["jrk"]."_".$caption,
					"l_comment" => $comment,
				));

					$links.=$this->parse("links"); //parse links
			}
		}

//$abix=$this->get_obj_chain( array("oid"=>$aktiivne, "stop"=>$ob["meta"]["lingiroot"]));
//print_r($abix);

		$this->vars(array(
			"abix" => "",
			"nms"=>$nms,
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
//			"m_root" => $rts?$rts:"siia vist ei tule migdagi kui alamakatlooge pole?",
			"aktiivne" => $aktiivne,
			"tulbad" => $tulbad,
			"links" => $links?$links:"<tr><td>linke pole?</td></tr>"
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