<?php
global $orb_defs;
$orb_defs["linkslist"] = "xml";
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
	// parent - the folder under which to add the object
	// return_url - optional, if set, the "back" link should point to it
	// alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
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
			"default_template" => $this->picker("", $this->get_templates(SHOW_TPL_DIR)),
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
	// id - the id of the object to change
	// return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
		$toolbar->add_button(array(
			"name" => "stat",
			"tooltip" => "statistika",
			"url" => $this->mk_my_orb("stat", array("id" => $id, "return_url" => urlencode($return_url))),
			"imgover" => "lists_over.gif",
			"img" => "lists.gif",
		));
		$toolbar->add_separator();
//		$toolbar->add_cdata($this->parse("aliaslist"));
		$toolbar = $toolbar->get_toolbar();


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
//			"modified" => "modified",
//			"created" => "created",
		);
		//list of properties, that we also can turn into a hyperlink
		$linkis=array(
			"comment" => "comment",
			"url" => "url",
			"caption" => "caption",
			"modifiedby" => "modifiedby",
			"createdby" => "createdby",
//			"name" => "name", //name is actually the same as the caption
			"modified" => "modified",
			"created" => "created",
			"jrk" => "jrk",
			"hits" => "hits",
/*  [oid] => 51395
    [parent] => 51394
    [class_id] => 21
    [created] => 1033467989
    [status] => 2
    [lang_id] => 6
    [last] => 
    [jrk] => 222
    [visible] => 1
    [period] => 
    [alias] => 
    [periodic] => 0
    [site_id] => 9
    [doc_template] => 0
    [activate_at] => 0
    [deactivate_at] => 0
    [autoactivate] => 0
    [autodeactivate] => 0
    [brother_of] => 0
    [cachedirty] => 1
*/
		);

		
		// these are the directory properties which we can assign to a form element for searching
		$propertid=array( 
			"oid"		=> "oid",
			"parent"	=>"parent",
			"name"		=> "name",
			"createdby"	=> "createdby",
			"class_id"	=> "class_id",
			"created"	=> "created",
			"modified"	=> "modified",
			"status"	=> "status",
			"hits"		=> "hits",
			"lang_id"	=> "lang_id",
			"comment"	=> "comment",
			"last"		=> "last",
			"modifiedby"	=> "modifiedby",
			"jrk"		=> "jrk",
			"visible"	=> "visible",
			"period"	=> "period",
			"alias"		=> "alias",
			"periodic"	=> "periodic",
			"site_id"	=> "site_id",
			"doc_template"	=> "doc_template",
			"activate_at"	=> "activate_at",
			"deactivate_at"	=> "deactivate_at",
			"autoactivate"	=> "autoactivate",
			"autodeactivate"	=> "autodeactivate",
			"brother_of"	=> "brother_of",
			"cachedirty"	=> "cachedirty",
			"metadata"	=> "metadata",
		);

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

//see on see tasandite konfinnimise süteem

		$tulpi = $ob["meta"]["tulpi"];

		//delete level(s)
		if ($ob["meta"]["kustuta"])
		{
			foreach($ob["meta"]["kustuta"] as $key => $val)
			{
				unset($tulpi[$key]);
			}
		}

		// add level
		if($adding = (int)$ob["meta"]["add_level"])
		{
			$tulpi[$adding]	= $ob["meta"]["default_tulpi"];  //anname tasandile algus default tulpade arvu
			$ob["meta"]["sortby_links"][$adding] = $ob["meta"]["default_sortby_links"];
			$ob["meta"]["sortby_dirs"][$adding] = $ob["meta"]["default_sortby_dirs"];
			$ob["meta"]["tulpi"][$adding] = $ob["meta"]["default_tulpi"];
			$ob["meta"]["level_template"][$adding] = $ob["meta"]["default_template"];
			$ob["meta"]["jrk_columns"][$adding] = $ob["meta"]["jrk_columns_template"];
		}
	
		$list_templates = $this->get_templates(SHOW_TPL_DIR);
		// parse levels
		if ($tulpi)
		{
			foreach($tulpi as $key =>  $val)
			{
				$this->vars(array("tas" => $key,//"val" => $val,
				"sortby_dirs" => $this->picker($ob["meta"]["sortby_dirs"][$key], $sortim),
				"sortby_links" => $this->picker($ob["meta"]["sortby_links"][$key], $sortim),
				"tulpi" => (int)$ob["meta"]["tulpi"][$key]?(int)$ob["meta"]["tulpi"][$key]:$ob["meta"]["default_tulpi"],
				"jrk_columns" => checked($ob["meta"]["jrk_columns"][$key]),
				"level_template" => $this->picker($ob["meta"]["level_template"][$key], $list_templates),
				"sortby_jknr" => checked($ob["meta"]["sortby_jknr"][$key])));
				$levels[$key] = $this->parse("levels");
			}
			ksort($levels);
			$levels = implode("",$levels);
		}
///end/tasandid

		//selecting wich link property we wanna see as hyperlink	
		foreach($linkis as $key =>  $val)
		{
			$this->vars(array(
					"mis" => $key,
					"is_hyper" => checked($ob["meta"]["klikitav"][$key]),
			));
			$klikitav.= $this->parse("klikitav");
		}

		$ref = $this->mk_reforb("submit", array("id" =>  $id, "return_url" => urlencode($return_url)));

		//gets a list of templates (for linkslist "show")
		$list_templates = $this->get_templates(SHOW_TPL_DIR);

		$this->vars(array(
		
				"forms" => $this->picker($ob["meta"]["forms"], $forms),		//all the form objects we can find
				"felement" => $this->picker($ob["meta"]["felement"],$felement),	//current active form element
				"vordle" => $this->picker($ob["meta"]["vordle"],$propertid),	//

			"jrk_columns_default" => checked($ob["meta"]["jrk_columns_default"]),
			"dir_is_form_result" => checked($ob["meta"]["dir_is_form_result"]),
			"toolbar" => $toolbar,
			"default_tulpi" => $ob["meta"]["default_tulpi"],			// default column count
			"abix" => "", 
			"is_formentry" => $ob["meta"]["is_formentry"]?checked($ob["meta"]["is_formentry"]):"",		// kas on vormisisestus (radio)
			"is_not_formentry" => $ob["meta"]["is_formentry"]?"":checked(1),	// kas on vormisisestus (radio)
			"vormisisestus" => $vormisisestus,				// formentry data (sub)
			"levels" => $levels,
			"tasand" => $ob["meta"]["tasand"]?$ob["meta"]["tasand"]:1,
			"klikitav" => $klikitav,
			"name" => $ob["name"],
			"comment" => $ob["comment"],
			"active_dirs" => checked($ob["meta"]["active_dirs"]),
			"active_links" => checked($ob["meta"]["active_links"]),
			"newwindow" => checked($ob["meta"]["newwindow"]),
			"path" => checked($ob["meta"]["path"]),				//show path
			"default_sortby_dirs" => $this->picker($ob["meta"]["default_sortby_dirs"], $sortim),
			"default_sortby_links" => $this->picker($ob["meta"]["default_sortby_links"], $sortim),
			"default_template" => $this->picker($ob["meta"]["default_template"], $list_templates),
			"rootitems" => $this->picker($ob["meta"]["lingiroot"], $root_list),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));
		return $this->parse();
	}

	////
	// !gets list of the files in given path (eg templates)
	// parameters:
	//  $path - directory where to search the files
	//  returns key and value as "filename.ext", because numeric key may differ after file add/delete
	function get_templates($path,$ext="")
	{
		if ($dir = @opendir($path))
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file != "." && $file != ".." && is_file("$path/$file"))
				{ 
					$list_templates[$file] = $file;
				}  
			}
			closedir($dir);
		}
		return $list_templates;
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	// id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"lingiroot" => $lingiroot,
					"path" => $path,

					"tulpi" => $tulpi,
					"jrk_columns" => $jrk_columns,
					"level_template" => $level_template,
					"sortby_dirs" => $sortby_dirs,
					"sortby_links" => $sortby_links,
					"kustuta" => $kustuta,

					"is_formentry" => $is_formentry,
					"forms" => $forms,
					"felement" => $felement,
					"dir_is_form_result" => $dir_is_form_result,
					"vordle" => $vordle,
					"jrk_columns_default" => $jrk_columns_default,
					"default_tulpi" => (int)$default_tulpi?(int)$default_tulpi:1,
					"klikitav" => $klikitav,

//					"tas" => $tas,
					"add_level" => $add_level,

					"active_dirs" => $active_dirs,
					"active_links" => $active_links,

					"newwindow" => $newwindow,
					"default_template" => $default_template,
					"default_sortby_dirs" => $default_sortby_dirs,
					"default_sortby_links" => $default_sortby_links,
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_LINK_LIST,
				"comment" => $comment,
				"metadata" => array(
					"lingiroot" => $lingiroot,
					"default_template" => $default_template,
				)
			));
		}


		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}
		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}


	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	// alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
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
		extract($arr); // cd = current directory

		$this->write_stat(array("oid"=>$id,"uid"=>$uid,"action"=>1));

		$ob = $this->get_object($id);
		$cd = $cd?$cd:$ob["meta"]["lingiroot"];
		$this->add_hit($cd);
		$ak = $cd;
		//start YAH

		if ($ob["meta"]["is_formentry"] && $ob["meta"]["dir_is_form_result"])
		{	// kuidas ma saan yah menüü kui menüü lingid on kõik otsinguga leitud?
		}
		else
		{
			while((($ak == $ob["meta"]["lingiroot"]))==false)
			{
				$ph = $this->get_object(array("oid" => $ak,"return" => ARR_ALL),false,false);
				$ak = $ph["parent"];
				//$tase++;
				$YAH[++$tase] = array("name" => $ph["name"],
					"link" => $this->mk_my_orb("show",array("cd" => $ak,"id" => $id))
				);
			};
		}

		$YAH[++$tase]= array( //taseme number on igaljuhul vajalik siit kätte saada
			"name" => $ob["name"],
			"link" => $this->mk_my_orb("show",array("cd" => $ob["meta"]["lingiroot"],"id" => $id))
		);

		$templiit = $ob["meta"]["level_template"][$tase]?$ob["meta"]["level_template"][$tase]:$ob["meta"]["default_template"];
		$this->read_template("show/".$templiit);
		$order_dirs = $ob["meta"]["sortby_dirs"][$tase]?$ob["meta"]["sortby_dirs"][$tase]:$ob["meta"]["default_sortby_dirs"];
		$order_links = $ob["meta"]["sortby_links"][$tase]?$ob["meta"]["sortby_links"][$tase]:$ob["meta"]["default_sortby_links"];

		if ($ob["meta"]["path"]) 		//if YAH then parse it
		$nms=$this->parse_YAH($YAH,"show/".$templiit);



		// kui  kasutame vormisisestust
		if ($ob["meta"]["is_formentry"])
		{
			//kataloogi lingi väärtus võetakse vormisissestusest

			$form = get_instance("form"); 
			$form->load($ob["meta"]["forms"]); 
			$form->set_element_value($ob["meta"]["felement"], urldecode($search)); 
			if ($ob["meta"]["dir_is_form_result"]) 
			{
				// leitud id järgi, kõik objektid

				$arr = new aw_array($form->search());
				foreach($arr->get() as $val)
				{
					$menus[$val] = $this->get_object($val);
				}
				//linke ei pane		
			}
			else
			{
				$menus = $this->list_objects(array("class" => CL_PSEUDO, 
					"parent" => $cd,
					"active" => $ob["meta"]["active_dirs"],
					"orderby" => $order_dirs,
					"return" => ARR_ALL
				));
				//linkide asemel on vormi väljastus
				$links = $form->new_do_search(array()); 
	
			}

		}

		//tavaline lingikogu
		if (!$ob["meta"]["is_formentry"]) 
		{
		// menüüd on "füüsilised" kataloogid
			$menus = $this->list_objects(array("class" => CL_PSEUDO, 
				"parent" => $cd,
				"active" => $ob["meta"]["active_dirs"],
				"orderby" => $order_dirs,
				"return" => ARR_ALL
			));
		//lingid on aktiivses kataloogis olevad lingiobjektid
			$objects = $this->list_objects(array(
				"class" =>  CL_EXTLINK,
				"parent" => $cd,
				"active" => $ob["meta"]["active_links"],
				"orderby" => $order_links,
				"return" => ARR_ALL,
			));
		}

		if ($menus)
		{
		// pagan neid tulpi, mul läks kapitaalselt juhe kokku
		// jääb nii praegult
			if (!$ob["meta"]["tulpi"][$tase])
			{
				$jrku=$ob["meta"]["jrk_columns_default"];
				$tlpi=$ob["meta"]["default_tulpi"];
			}
			else
			{
				if ($ob["meta"]["jrk_columns"][$tase])
				{
					$jrku=1;
				}
				$tlpi=$ob["meta"]["tulpi"][$tase];
			}
		///////////
			
			foreach($menus as $key => $value) 
			{
				extract($value);
				$sub_count = $this->count_objects(array(
					"class" => CL_PSEUDO, 
					"parent" => $oid,
				));
				if ($sub_count)
				{
					$this->vars(array("count"=>$sub_count));
					$sub_count=$this->parse("sub_count");
				}
				$this->vars(array(
					"hits" => $this->get_hit($oid),
					"sub_count" => $sub_count?$sub_count:"",
					"name" => $name,
					"link" => $this->mk_my_orb(
						"show",
						array(
							"cd" => $oid,
							"id"  => $id,
							"search" => $ob["meta"]["felement"]?urlencode($value[$ob["meta"]["vordle"]]):"",
						)
					)
				));


				if($jrku)  //see on see jagamine tulpadesse jrk järgi
				{
					$tulp = $value["jrk"][0]; //first character of jrk
					if($tlpi<$tulp) $tulp = 1; //kui jrk algab suurema numbriga kui tulpade arv, siis lheb esimesse
				} 
				else
				{
					$tulp = ($total % $tlpi)+1; //siin peaks kuidagi võrdselt ära jagama
				}


				$tulp = $tulp?$tulp:1;
				$tasand[$tulp][$value["jrk"]].= $this->parse("dir");
				$total++;
			}//foreach


			ksort($tasand);				// level 1, level2 ...
			foreach ($tasand as $key=> $val)	//parsime tulbad
			{
			//	ksort($val);			// sordime ühe tulba lingid?
				$this->vars(array("dirs" => implode("",$val)));
				$tulbad.= $this->parse("tulp");
			}
		}//if menus





		//kui tahame linke


			if ($objects)
			{
				classload("extlinks");
				$total2=0;
				$ll = new extlinks();
				foreach($objects as $o_key => $o_value)
				{
					extract($o_value); //link properties
					$total2++;
					list($url,$target,$caption) = $ll->draw_link($o_key);

					$target=$ob["meta"]["newwindow"]?"target=_blank":"";
					$this->vars(array("plain_url" => $url));
				//teeme lingiks kui vaja
					$arr  = new aw_array($ob["meta"]["klikitav"]);
					foreach($arr->get() as $val)
					{
						$link = $this->mk_my_orb("goto",array("id"  => $oid),"");
						$u[$val]=$o="<A HREF=\"$link\" onMouseover=\"window.status='$url'; return true\" $target>".$$val."</A>";
					};

					@extract($u);
					$this->vars(array(
						"hits" => $this->get_hit($o_key),
						"url" => $url,
						"name" => $caption,
						"comment" => $comment,
						"target" => $target,
						"modified" => $modified,
						"modifiedby" => $modifiedby,
						"createdby" => $createdby,
						"modified" => $modified,
						"created" => $created,
						"jrk" => $jrk,
						));
					$links.= $this->parse("links"); //parse links
				}
			}

		

		$this->vars(array(
			"abix"=>$tase,
			"nms" => $nms,
			"total" => (int)$total,
			"total2" => (int)$total2,
			"lingiroot" => $ob["meta"]["lingiroot"],
			"active_links" => $ob["meta"]["active_links"],
			"active_dirs" => $ob["meta"]["active_dirs"],
			"newwindow" => 	$ob["meta"]["newwindow"],
			"default_sortby_dirs" => $ob["meta"]["default_sortby_dirs"],
			"default_sortby_links" => $ob["meta"]["default_sortby_links"],
			"default_template" => $ob["meta"]["default_template"],
			"name" => $ob["name"],
			"comment" => $ob["comment"],
			"cd" => $cd,
			"tulbad" => $tulbad,
//			"links" => $links?$links:"<tr><td>linke pole?</td></tr>"
			"links" => $links
		));

		return $this->parse();
	}





	function parse_YAH($YAH,$templiit)
	{
		$this->read_template($templiit);
			foreach($YAH as $val)
			{
				$this->vars(array(
					"name" => $val["name"],
					"link" => $val["link"],
				));
				$yah_bar = $this->parse("tee").$yah_bar;
			}
		return $yah_bar;
	}	
	
	
	////
	// !this adds a hit to the external link (and possibli some information about user) and redirects user to to this link, 
	// browser's back button does not return to this page :)
	// 
	function link_redirect($arr)
	{
		extract($arr); //id = link id
		$this->write_stat(array("oid"=>$id,"uid"=>$uid,"action"=>2));
		$ob = $this->get_object($id);
		$this->add_hit($id);
		classload("extlinks");
		$ll = new extlinks();
		list($url,$target,$caption) = $ll->draw_link($id);
//		echo $uid." ";
		header("Location: $url");
		die();
	}



	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	// oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		return aw_serialize($row);
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	// str - the string
	// parent - the folder where the new object should be created
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

function write_stat($arr)
{
		extract($arr);
		$in = "insert into lingikogu_stat (oid, uid, action) values ('$oid','$uid','$action')";
		$this->db_query($in);
}

/////////////////////////////////

/*
			"stat"=>$this->get_stat(array()),



function stat($arr)
{
extract($arr);
if ($)





}




function get_stat($arr)
{
		extract($arr);
		$this->read_template("stat.tpl");

	$sekund=date("s");
	$minut=date("i");
	$tund=date("H");
	$paev=date("d");
	$kuu=date("m");
	$aasta=date("Y");




$now=$aasta.$kuu.$paev.$tund.$minut.$sekund;

$t1="20020115110011";
$t2="20021015110011";


$q = "select oid from lingikogu_stat where tm between $t1 and $t2"; //vahemik
//$q = "select oid from lingikogu_stat where tm >= $t1"; //alates
//$q = "select oid from lingikogu_stat where tm <= $t1"; //kuni
//$q = "select oid from lingikogu_stat where tm = '????????10????'"; //tundide lõikes ntx kl 10
//$q = "select oid from lingikogu_stat where tm = '????????10????'"; //tundide lõikes ntx kl 10

	
	$this->db_query($q);

while ($row=$this->db_fetch_row())
{

			print_r($row);

}
		




		$q = "select count(id) as caunt from lingikogu_stat";
		$caunt=$this->db_fetch_field($q,"caunt");
		$q = "select count(id) as caunt from lingikogu_stat where action=1";
		$caunt_dirs=$this->db_fetch_field($q,"caunt");
		$q = "select count(id) as caunt from lingikogu_stat where action=2";
		$caunt_links=$this->db_fetch_field($q,"caunt");

		$this->vars(array(
			"caunt"=>$caunt,
			"caunt_links"=>$caunt_links,
			"caunt_dirs"=>$caunt_dirs,
//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,//""=>$,
			));

		return $this->parse();


//		$this->db_query($q);

//return $html;
}



*/
}

?>