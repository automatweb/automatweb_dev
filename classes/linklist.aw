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


	function lingikogu_toolbar($arr)
	{
		extract($arr); //id
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
		$toolbar->add_separator();
		$toolbar->add_button(array(
			"name" => "change",
			"tooltip" => "konfigureeri",
			"url" => $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)),$ob),
			"imgover" => "conf.gif",
			"img" => "conf.gif",
		));

		$toolbar->add_separator();
		$toolbar->add_button(array(
			"name" => "stats",
			"tooltip" => $this->mk_my_orb("change", array("id" => $id, "sid"=>$sid, "return_url" => urlencode($return_url)), "linklist_stat"),
			"url" => $this->mk_my_orb("change", array("id" => $id, "sid"=>$sid, "return_url" => urlencode($return_url)), "linklist_stat"),
			"imgover" => "lists_over.gif",
			"img" => "lists.gif",
		));
		$toolbar->add_cdata("kes ikoone meisterdab?");
		return $toolbar->get_toolbar();
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

		// list of a link object properties by wich we can order links
		$sortim = array ( 
			"name"		=> "lingi nime",
			"jrk"		=> "lingi jrknr",
			"modified"	=> "muutmise aja",
			"modified" => "modified",
			"created" => "created",
			"oid" => "oid",

		);
		//list of object properties, that we also can turn into a hyperlink

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
//			"oma_tekst_1"=>"oma_tekst_1",
//			"oma_tekst_2"=>"oma_tekst_2",
/*  
			"oid" => 
			"parent" => 51394
			"class_id" => 21
			"created" => 1033467989
			"status" => 2
			"lang_id" => 6
			"last" => 
			"jrk" => 222
			"visible" => 1
			"period" => 
			"alias" => 
			"periodic" => 0
			"site_id" => 9
			"doc_template" => 0
			"activate_at" => 0
			"deactivate_at" => 0
			"autoactivate" => 0
			"autodeactivate" => 0
			"brother_of" => 0
			"cachedirty" => 1
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
					"return" => ARR_ALL,
			));

			//leiame CSS stiilid
			$stiilid= $this->list_objects(array(
					"class" => CL_CSS,
					"orderby" => "name",
			));

/*			if($ob["meta"]["forms"])
			{
				$form = get_instance("form"); 
				$form->load($ob["meta"]["forms"]); 
//siin tekim mingi error kui fomi elemente ei leita
				$felement = $form->get_form_elements(array(
					"id" => $ob["meta"]["forms"],
					"key" => "id",
					"use_loaded" => true,
					"all_data" => false,
				));
			}
*/

//see on see tasandite konfinnimise süteem

		$dir = $ob["meta"]["dir"];
		$link = $ob["meta"]["link"];

		//delete level(s)
		if ($dir)
			foreach($dir as $key => $val)
			{
				if ($val["kustuta"])
				{
					unset($dir[$key]);
					unset($link[$key]);
				}
			}

		// default levelit ei saa kustutada, vaja on ju kindlasti templatet jne
		$dir[0]=$dir[0]?$dir[0]:array();
		// aga stiiliinfo võib nullida küll
//		$link[0]=$link[0]?$link[0]:array();


		// add level
		if($ob["meta"]["add_level"])
		{
			$dir[(int)$ob["meta"]["add_level"]]= $dir[0];
			$link[(int)$ob["meta"]["add_level"]]= $link[0];
		}

		$list_templates = $this->get_templates(SHOW_TPL_DIR);

		foreach($dir as $key =>  $val)
		{
			$this->vars(array(
				"level" => $key,
				"sortby_dirs" => $this->picker($dir[$key]["sortby_dirs"], $sortim),
				"show_links" => checked($dir[$key]["show_links"]),
				"newwindow" => checked($dir[$key]["newwindow"]),
				"sortby_links" => $this->picker($dir[$key]["sortby_links"], $sortim),
				"tulpi" => (int)$dir[$key]["tulpi"], //0==1
				"jrk_columns" => checked($dir[$key]["jrk_columns"]),
				"level_template" => $this->picker($dir[$key]["level_template"], $list_templates),
				"sortby_jknr" => checked($dir[$key]["sortby_jknr"]),
			));
			$levels[$key] = $this->parse("levels");
		}

			ksort($levels);
			$levels = implode("",$levels);


	foreach($dir as $key=>$val)
	{
		$level=$key;
		$level_style="";


		$oma=array(); //plah :| ... oma teksti jaoks key-d, + 1 uue tekstivälja jaoks on see jama siin
		$i=1;
		while(($link[$level]["oma_tekst_".$i]["text"]!=""))		
		{
			$oma["oma_tekst_".$i]="oma_tekst_".$i;
			$i++;
		}
		$oma["oma_tekst_".$i]="oma_tekst_".$i;

		$properti=$linkis + $oma; //textiväljade keyd siia otsa, igal levelil siis kujuneb eri arv tekstivälju, vastavalt vajadusele


		foreach($properti as $key => $val)
		{

			$text="";
			/// see on lame aga praegu vaadatakse kas key nimi algab "oma_" st siis on see enda sissestatav tekst
			if ((strpos($key,"oma_")==0) and (strpos($key,"oma_")!==false))
			{
				$this->vars(array(
					"mis" => $key,
					"level" => $level,
					"text"=>$link[$level][$key]["text"],
				));
				$text = $this->parse("add_text");
			}

			$this->vars(array(
				"mis" => $key,
				"level" => $level,
				"jrk" => (int)$link[$level][$key]["jrk"],
				"hyper" => checked($link[$level][$key]["hyper"]),
				"show" => checked($link[$level][$key]["show"]),
				"stiilid" => $this->picker($link[$level][$key]["style"],array("vali") + $stiilid),
				"br" => checked($link[$level][$key]["br"]),
				"add_text" => $text,
			));
			$level_style.=$this->parse("level_style");
		}


			$this->vars(array(
				"level"=>$level,
				"level_style"=>$level_style,
				));
			$level_styles.=$this->parse("level_styles");
	}



		$this->vars(array(
//			"level" => $level,
//			"current_level"=>$ob["meta"]["current_level"],
			"forms" => $this->picker($ob["meta"]["forms"], $forms),		//all the form objects we can find
			"felement" => $this->picker($ob["meta"]["felement"],$felement),	//current active form element
			"vordle" => $this->picker($ob["meta"]["vordle"],$propertid),	//

			"dir_is_form_result" => checked($ob["meta"]["dir_is_form_result"]),
			"toolbar" => $this->lingikogu_toolbar(array("id"=>$id,"sid"=>$ob["meta"]["sid"])),
//			"default_tulpi" => $ob["meta"]["default_tulpi"],			// default column count
//			"abix" => $ob["meta"]["link"]["default"][jrk]."test", 
			"is_formentry" => $ob["meta"]["is_formentry"]?checked($ob["meta"]["is_formentry"]):"",		// kas on vormisisestus (radio)
			"is_not_formentry" => $ob["meta"]["is_formentry"]?"":checked(1),	// kas on vormisisestus (radio)
			"vormisisestus" => $vormisisestus,				// formentry data (sub)
			"levels" => $levels,
//			"tasand" => $ob["meta"]["tasand"]?$ob["meta"]["tasand"]:1,
			"level_styles" => $level_styles,
			"name" => $ob["name"],
			"comment" => $ob["comment"],
			"active_dirs" => checked($ob["meta"]["active_dirs"]),
			"active_links" => checked($ob["meta"]["active_links"]),
			"YAH" => checked($ob["meta"]["YAH"]),				//show path
//			"default_sortby_dirs" => $this->picker($ob["meta"]["default_sortby_dirs"], $sortim),
//			"default_sortby_links" => $this->picker($ob["meta"]["default_sortby_links"], $sortim),
//			"default_template" => $this->picker($ob["meta"]["default_template"], $this->get_templates(SHOW_TPL_DIR)),
			"rootitems" => $this->picker($ob["meta"]["lingiroot"], $root_list),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "sid"=> $ob["meta"]["sid"],"return_url" => urlencode($return_url)))
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
					"YAH" => $YAH,
					"dir" => $dir,
					"link"=> $link,

					"is_formentry" => $is_formentry,
					"forms" => $forms,
					"felement" => $felement,
					"dir_is_form_result" => $dir_is_form_result,
					"vordle" => $vordle,
					"form_output_is" => $form_output_is,
					"add_level" => $add_level,

					"active_dirs" => $active_dirs,
					"active_links" => $active_links,
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
		$uid= aw_global_get("uid");
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
				//$tase++;
				$YAH[++$tase] = array("name" => $ph["name"],
					"link" => $this->mk_my_orb("show",array("cd" => $ak,"id" => $id))
				);
				$ak = $ph["parent"];
			};
		}

		$YAH[++$tase]= array( //taseme number on igaljuhul vajalik siit kätte saada
			"name" => $ob["name"],
			"link" => $this->mk_my_orb("show",array("cd" => $ob["meta"]["lingiroot"],"id" => $id))
		);

		
		if (!is_array($ob["meta"]["dir"][$tase]))
			{
			$tase=0; //käiku lähevad default taseme määrangud
			}
		$this_dir=$ob["meta"]["dir"][$tase];
		$this_link=$ob["meta"]["link"][$tase];
		
		$templiit = $this_dir["level_template"];
		$this->read_template("show/$templiit");
		$order_dirs = $this_dir["sortby_dirs"];
		$order_links = $this_dir["sortby_links"];

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
				$links = $form->new_do_search(array("output_id"=>2));//$ob["meta"]["form_output_is"]));
	
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
			if($this_dir["show_links"])
			{
	
				$objects = $this->list_objects(array(
					"class" =>  CL_EXTLINK,
					"parent" => $cd,
					"active" => $ob["meta"]["active_links"],
					"orderby" => $order_links,
					"return" => ARR_ALL,
				));
			}
		}

		//kui menüüsid on siis parsime tulpadesse
		if ($menus)
		{
			$tulbad=$this->menus($menus,$this_dir,$id,$ob["meta"]["felement"]?$ob["meta"]["vordle"]:"");
		}

		//kui tahame linke

			if ($objects)
			{
				classload("extlinks");
				$total2=0;
				$ll = new extlinks();

				//makes css for link objects
				$css=$this->mk_link_css($this_link);

				//makes template for link objects
				$link_tpl=$this->mk_link_obj_template($this_link);

				// localparse 
				foreach($objects as $key => $val)
				{
					extract($val); //link properties
					$total2++;
					list($url,$target,$caption) = $ll->draw_link($key);

					$target=$this_dir["newwindow"]?"target=_blank":"";
					$link=array(
						"link" => $this->mk_my_orb("goto",array("id"  => $oid),""),
						"hits" => $this->get_hit($key),
						"url" => $url,
						"plain_url" => $url,
						"caption" => $caption,
						"comment" => $comment,
						"target" => $target,
						"modified" => $modified,
						"modifiedby" => $modifiedby,
						"createdby" => $createdby,
						"modified" => $modified,
						"created" => $created,
						"jrk" => $jrk,
					);
					$links.= $this->localparse($link_tpl,$link); //parse links
				}
			}


		if ($ob["meta"]["YAH"]) 		//if YAH then parse it
		{
			$YE=$this->parse_YAH($YAH,"show/".$templiit);
			$this->vars(array(
				"total"=>$total,
				"YAH"=>$YE,
			));
			$YEP=$this->parse("YAHBAR");

		}







		$this->vars(array(
			"css" => $css,
			"abix" => $tase,
			"YAHBAR" => $YEP,
			"total" => (int)$total,
			"total2" => (int)$total2,
			"name" => $ob["name"],
			"comment" => $ob["comment"],
			"cd" => $cd,
			"tulp" => $tulbad,
//			"links" => $links?$links:"<tr><td>linke pole?</td></tr>"
			"links" => $links
		));

		return $this->parse();
	}


	////
	// !make css for link object
	// conf - level conf
	//
	function mk_link_css($conf)
	{
		if(!is_array($conf))
		{
			return false;
		}
		$s = get_instance("css");
		foreach($conf as $key => $val)
		{
			if ($val["style"] && !$css[$val["style"]])
			{//echo $val["style"];
				$style = $this->get_object($val["style"]);
				$css[$val["style"]] = $s->_gen_css_style("style".$style["oid"],$style["meta"]["css"]);
			}
		}
		return $css?("<style>\n".implode("",$css)."</style>"):"";
	}




	////
	// !make template for link object
	// conf - level conf
	//
	function mk_link_obj_template($conf)
	{
		if(is_array($conf))				
		{
			foreach($conf as $key => $val)
			{
				if (is_array($val) && $val["show"]){
					$val["br"]=$val["br"]?"<br />":"";
					$class=$val["style"]?"class=\"style".$val["style"]."\"":"";

					if ($val["hyper"]){
						$linktpl[(int)$val["jrk"]].="<A $class HREF=\"{VAR:link}\" onMouseover=\"window.status='{VAR:url}'; return true\" {VAR:target}>{VAR:".$key."}".$val["text"]."</A>".$val["br"]."\n";
					}
					elseif ($val["style"])
					{
						$linktpl[(int)$val["jrk"]].="<span $class>{VAR:".$key."}".$val["text"]."</span>".$val["br"]."\n";
					}
					else
					{
						$linktpl[(int)$val["jrk"]].="{VAR:".$key."}".$val["text"].$val["br"]."\n";
					}
				}
			};
		}
		@ksort($linktpl);
		return @implode("",$linktpl);
	}


	////
	// !menüü andmete tulpadesse jagamine
	//  menus - menüü objectid
	//  ob - tulpade confi andmed
	//  optional:
	//	$search - millise parameetri järgi otsime //name, oid, ...
	function menus($menus,$conf,$id,$search="")
		{
		extract($conf);

		$jrku=!$tulpi?$jrk_columns:($jrk_columns?1:""); //ühesõnaga, kui tulpi confis üldse kirjas pole siis paneme ühte tulpa
			
			foreach($menus as $key => $value) 
			{
				extract($value);
				//leiame alammenüüs olevate objektidearvu // praegu leitakse alamenüüde arv, aga vaest oleks mõttekas leida (ka) linkide arv
				$sub_count = $this->count_objects(array(
					"class" => CL_PSEUDO, 
					"parent" => $oid,
				));
				if ($sub_count)
				{
					$this->vars(array("count"=>$sub_count));
					$subs=$this->parse("sub_count");
				}
				$this->vars(array(
					"hits" => $this->get_hit($oid),
					"sub_count" => $subs,
					"name" => $name,
					"link" => $this->mk_my_orb(
						"show",
						array(
							"cd" => $oid,
							"id"  => $id,
							"search" => urlencode($value[$search]),
						)
					)
				));

				if($jrku)  //see on see jagamine tulpadesse jrk järgi
				{
					$tulp = $value["jrk"][0]; //first character of jrk
					if($tulpi<$tulp) $tulp = 1; //kui jrk algab suurema numbriga kui tulpade arv, siis lheb esimesse
				}
				else
				{
					$tulp = ($total % $tulpi)+1; //siin peaks kuidagi võrdselt ära jagama
				}

				$tulp = $tulp?$tulp:1;
				$tasand[$tulp][$value["jrk"]].= $this->parse("dir");
				$total++;
			}//foreach

			//sordime ikka nii ära et esimene tulp on esimene
			ksort($tasand);

			foreach ($tasand as $val)	//parsime tulbad
			{
			//	ksort($val);			// sordime ühe tulba lingid?
				$this->vars(array(
					"dir" => implode("",$val)
				));
				$tulbad.= $this->parse("tulp");
			}
			return $tulbad;
		}//if menus




	function parse_YAH($YAH,$templiit)
	{
		$this->read_template($templiit);
			foreach($YAH as $val)
			{
				$this->vars(array(
					"name" => $val["name"],
					"link" => $val["link"],
				));
				$yah_bar = $this->parse("YAH").$yah_bar;
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
		$now=time();
		$in = "insert into lingikogu_stat (oid, uid, action,tm) values ('$oid','$uid','$action',$now)";
		$this->db_query($in);
}


}
?>