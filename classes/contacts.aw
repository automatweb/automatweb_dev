<?php
// $Header
// contacts.aw - kontaktihaldus

class contacts extends aw_template 
{
	function contacts($args = array())
	{
		// right now contact templates are stored next to the messenger ones
		$this->init("messenger");
		lc_load("definition");
	}

	////
	// !Imports a contact
	// see kutsutakse välja messengerist, kui klikkida avatud kirjal
	// "Kellelt" peale (ehk siis Add to Addressbook)
	function import($args = array())
	{
		extract($args);
		$udata = $this->get_user();
 
		$folder = ($folder) ? $folder : $udata["home_folder"];
		$this->_gen_contact_group_list();
 
		classload("form");
		$f = new form();
		$f->load($this->cfg["form"]);
		$el = $f->get_element_by_name("grupp");
		$ef = &$f->get_element_by_id($el->id);
 
		$addr = rawurldecode($addr);
		$this->dequote($addr);
		//preg_match("/(.+?)[<|\(|\[](.+?)[>|\)|\]]/",$addr,$matches);
		//$name = str_replace("\"","",$matches[1]);
		//list($name,$surname) = explode(" ",$name);
		$elvalues = array(
			"name" => $name,
			"surname" => $surname,
			"email" => $addr,
			"grupp" => $this->flatlist,
		);
		$form = $f->gen_preview(array(
			"id" => $this->cfg["form"],
			"reforb" => $this->mk_reforb("submitimport",array("folder" => $folder)),
			"elvalues" => $elvalues,
			"form_action" => "/index.".$this->cfg["ext"],
		));
		print $form;
    exit;
  }

	////
	// !importcontact handler
	function submitimport($args = array())
	{
		extract($args);
		classload("form");
		$f = new form($this->cfg["form"]);
		// save the form entry, and now .. should we show it?
		$args["id"] = $this->cfg["form"];
		$args["parent"] = $args[0];
		$f->process_entry($args);
		// I realize that this a little ugly
		print "<script language='javascript'>window.close()</script>";
		exit;
	}

	////
	// !Genereerib nimekirja kontaktigruppidest
	// startfrom(int) - millisest objektist alustada
	function _gen_contact_group_list($args = array())
	{
		$udata = $this->get_user();
		$fldr = ($args["startfrom"]) ? $args["startfrom"] : $udata["home_folder"];
		do
		{
			// kysime koik sellel levelil asuvad objektid
			$groups = $this->_get_groups_by_level($fldr);
 
			// sorteerime nad parentite jargi ära
			// ja paigutame ka flat massiivi
			foreach($groups as $key => $val)
			{
				$grps_by_parent[$val["parent"]][$key] = $val["name"];
				$grps[$key] = $val["name"];
			};
 
			// koostame parentite nimekirja jargmise tsykli jaoks
			$fldr = array_keys($groups);
 
		// kordame nii kaua, kuni yhtegi objekti enam ei leitud
		} while(sizeof($groups) > 0);
 
		$this->flatlist = array($udata["home_folder"] => LC_CONTACT_NOT_SORTED);
		$this->_indent_array($grps_by_parent,$udata["home_folder"]);
	}

	function _get_groups_by_level($parent)
	{
		$groups = array();
		$this->get_objects_by_class(array(
				"parent" => $parent,
				"class" => CL_CONTACT_GROUP
		));
 
		while($row = $this->db_next())
		{
			$groups[$row["oid"]] = array("name" => $row["name"],"parent" => $row["parent"]);
		};
		return $groups;
	}


	function _indent_array($arr,$level)
	{
		if (!is_array($arr) ||!is_array($arr[$level]))
		{
			return;
		};
		static $indent = 0;
		$indent++;
		while(list($key,$val) = each($arr[$level]))
		{
			$this->flatlist[$key] = str_repeat("&nbsp;",$indent*3) . $val;
			if (is_array($arr[$key]))
			{
				$this->_indent_array($arr,$key);
			};
		};
		$indent--;
	}

	function gen_msg_menu($args = array())
	{
		extract($args);
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$retval = $xm->build_menu(array(
			"vars"  => $vars,
			"xml"   => $this->cfg["basedir"] . "/xml/contacts/menucode.xml",
			"tpl"   => $this->template_dir . "/menus.tpl",
			"activelist" => $activelist,
		));
		return $retval;
	}
	
	////
	// !Contact manager
	function list_contacts($args = array())
	{
		extract($args);
		$folder = ($folder) ? "folder=$folder" : "";
		$menu = $this->gen_msg_menu(array(
			"activelist" => array("contact","list"),
			"vars" => array("folder" => $folder),
		));
		
		$this->read_template("contacts.tpl");
		$udata = $this->get_user();
		$folder = ($args["folder"]) ? $args["folder"] : $udata["home_folder"];
		$fdata = $this->get_object($folder);
		$path = array();

		if ($fdata["class_id"] == CL_CONTACT_GROUP)
		{
			//print "check passed<br>";
			$path[$fdata["oid"]] = $fdata["name"];
		}
		else
		{
			// we will just default to the user homedir
			// this is inexpensive, since get_object caches the results
			$path[$udata["home_folder"]] = "root";
			$fdata = $this->get_object($udata["home_folder"]);
		};
		
		// now we will have to try and find the names of all objects up until the home_folder
		// of course only if we already aren't IN the home folder
		$found = ($udata["home_folder"] != $fdata["oid"]);
		$parent = $fdata["parent"];
		while($found)
		{
			$obj = $this->get_object($parent);
			if ($obj["class_id"] != CL_CONTACT_GROUP)
			{
				if ($udata["home_folder"] == $obj["oid"])
				{
					$path[$obj["oid"]] = "root";
				};
				$found = false;
			}
			else
			{
				$path[$obj["oid"]] = $obj["name"];
			};
			$parent = $obj["parent"];
		};
		
		$fullpath = map2("<a href='?class=contacts&id=%s'>%s</a>",$path);
		$fullpath = join(" &gt; " ,array_reverse($fullpath));
		
		$this->get_objects_by_class(array(
			"parent" => $folder,
			"class" => CL_CONTACT_GROUP,
		));
		$glist = "";
		$cnt = 0;
		while($row = $this->db_next())
		{
			$cnt++;
			$this->vars(array(
				"jrk" => $cnt,
				"name" => $row["name"],
				"id" => $row["oid"],
				"members" => "n/a",
			));
			$glist .= $this->parse("gline");
		};
		classload("form");
		$f = new form($this->cfg["form"]);
		$f->load($this->cfg["form"]);
		$ids = $f->get_ids_by_name(array("names" => array("name","surname","email","phone")));
		$ear = $f->get_entries(array("parent" => $folder,"all_data" => true));
		$c = "";
		$cnt = 0;
		foreach($ear as $row)
		{
			$this->vars(array(
				"name" => $row[$ids["name"]] . " " . $row[$ids["surname"]],
				"email" => $row[$ids["email"]],
				"phone" => $row[$ids["phone"]],
				"id" => $row["id"],
				"color" => ($cnt % 2) ? "#EEEEEE" : "#FFFFFF",
			));
			$cnt++;
			$c .= $this->parse("line");
		};

		$this->_gen_contact_group_list();
		$this->vars(array(
			"menu" => $menu,
			"line" => $c,
			"gline" => $glist,
			"mlist" => $this->picker($folder,$this->flatlist),
			"grouplist" => $this->picker($folder,$this->flatlist),
			"reforb" => $this->mk_reforb("submit_contacts",array("folder" => $folder)),
			"fullpath" => $fullpath,
		));
		return $this->parse();
	}

	////
	// !Kuvab kontaktigruppide puu
	function groups($args = array())
	{
		extract($args);
		$udata = $this->get_user();
		$folder = ($parent) ? "folder=$parent" : "";
		$menu = $this->gen_msg_menu(array(
			"activelist" => array("groups","list"),
			"vars" => array("folder" => $folder),
		));
		$this->read_template("contactgroups.tpl");
		$folder = ($args["parent"]) ? $args["parent"] : $udata["home_folder"];
		$fdata = $this->get_object($folder);
		$path = array();

		if ($fdata["class_id"] == CL_CONTACT_GROUP)
		{
			//print "check passed<br>";
			$path[$fdata["oid"]] = $fdata["name"];
		}
		else
		{
			// we will just default to the user homedir
			// this is inexpensive, since get_object caches the results
			$path[$udata["home_folder"]] = "root";
			$fdata = $this->get_object($udata["home_folder"]);
		};
		
		// now we will have to try and find the names of all objects up until the home_folder
		// of course only if we already aren't IN the home folder
		$found = ($udata["home_folder"] != $fdata["oid"]);
		$parent = $fdata["parent"];
		while($found)
		{
			$obj = $this->get_object($parent);
			if ($obj["class_id"] != CL_CONTACT_GROUP)
			{
				if ($udata["home_folder"] == $obj["oid"])
				{
					$path[$obj["oid"]] = "root";
				};
				$found = false;
			}
			else
			{
				$path[$obj["oid"]] = $obj["name"];
			};
			$parent = $obj["parent"];
		};
		
		$fullpath = map2("<a href='?class=groups&parent=%s'>%s</a>",$path);
		$fullpath = join(" &gt; " ,array_reverse($fullpath));
		$this->get_objects_by_class(array(
			"parent" => $folder,
			"class" => CL_CONTACT_GROUP,
		));
		$glist = "";
		$cnt = 0;
		while($row = $this->db_next())
		{
			$cnt++;
			$this->vars(array(
				"jrk" => $cnt,
				"name" => $row["name"],
				"id" => $row["oid"],
				"members" => "n/a",
			));
			$glist .= $this->parse("gline");
		};
		$this->vars(array(
			"menu" => $menu,
			"gline" => $glist,
			"fullpath" => $fullpath,
		));
		return $this->parse();
	}
	
	////
	// !Handleb contacts funktsioonist tulnud datat.
	function submit_contacts($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			$inlist = join(",",array_keys($check));
			$q = "UPDATE objects SET parent = '$group' WHERE oid IN ($inlist)";
			$this->db_query($q);
		};
		global $status_msg;
		$status_msg = LC_CONTACT_CONTACT_MOVED_FOLDER;
		session_register("status_msg");
		return $this->mk_site_orb(array(
					"action" => "contacts",
					"folder" => $folder));
	}
	
	////
	// !Displays a form for editing/adding a contact
	function edit($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("contact",($args["id"]) ? "list" : "add"),
			));
		classload("form");
		$f = new form($this->cfg["form"]);
		$udata = $this->get_user();
		$folder = ($folder) ? $folder : $udata["home_folder"];
		$form = $f->gen_preview(array(
			"id" => $this->cfg["form"],
			"entry_id" => ($args["id"]) ? $args["id"] : "",
			"reforb" => $this->mk_reforb("submit",array("folder" => $folder)),
			"form_action" => $this->cfg["baseurl"]."/index.".$this->cfg["ext"],
		));
		$this->read_template("edit_contact.tpl");
		$this->vars(array(
			"menu" => $menu,
			"form" => $form,
		));
		return $this->parse();
	}
	
	////
	// !Submits a contact
	function submit($args = array())
	{
		extract($args);
		classload("form");
		$f = new form($this->cfg["form"]);
		// save the form entry, and now .. should we show it?
		$args["id"] = $this->cfg["form"];
		$args["parent"] = $folder;
		$f->process_entry($args);
		global $status_msg;
		$status_msg = ($entry_id) ? LC_CONTACT_CONTACT_SAVED : LC_CONTACT_ADDED;
		session_register("status_msg");
		if (!$entry_id)
		{
			$entry_id = $f->entry_id;
		};
		$ref = $this->mk_site_orb(array(
			"action" => "edit",
			"id" => $entry_id,
		));
		return $ref;
	}
	
	////
	// !Kuvab kontaktigrupi muutmis/lisamisvormi
	function edit_group($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
			"activelist" => ($args["id"]) ? array("contacts") : array("addgroup"),
		));
		$this->read_template("contact_group.tpl");
		$name = "";
		if ($args["id"])
		{
			$obj = $this->get_object($args["id"]);
			$name = $obj["name"];
		};
		$this->vars(array(
			"name" => $name,
			"menu" => $menu,
			"reforb" => $this->mk_reforb("submit_group",array("id" => $id,"folder" => $folder)),
		));
		return $this->parse();
	}
	
	////
	// !Submitib kontaktigrupi
	function submit_group($args = array())
	{
		extract($args);
		// kui folder on defineeritud, siis lisame grupi selle alla
		// kui mitte, siis otse kodukataloogi alla
		$udata = $this->get_user();
		$folder = ($folder) ? $folder: $udata["home_folder"];
		if ($args["id"])
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"class_id" => CL_CONTACT_GROUP,
				"name" => $name,
				"parent" => $folder,
			));
		};
		global $status_msg;
		$status_msg = ($args["id"]) ? LC_CONTACT_GROUP_SAVED : LC_CONTACT_GROUP_ADDED;
		session_register("status_msg");
		return $this->mk_site_orb(array(
			"action" => "edit_group",
			"id" => $id,
		));
	}
	
	////
	// !Kuvab kontakti otsimise vormi
	function search($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
			"activelist" => array("search"),
		));
		$this->read_template("search_contact.tpl");
		classload("form");
		$f = new form(2024);
		$form = $f->gen_preview(array(
			"id" => 2024,
			"reforb" => $this->mk_reforb("submit_search",array()),
			"form_action" => $this->cfg["baseurl"]."/index.".$this->cfg["ext"],
		));
		$this->vars(array(
			"menu" => $menu,
			"form" => $form,
		));
		return $this->parse();
	}

	////
	// !Performs the actual search
	function submit_search($args = array())
	{
		$menu = $this->gen_msg_menu(array(
			"activelist" => array("contacts","search"),
		));
		
		$this->_gen_contact_group_list();
		
		$this->read_template("search_contact_res.tpl");
		// FIXME:
		classload("form");
		$f = new form(2024);
		$f->load($this->cfg["form"]);
		$ids = $f->get_ids_by_name(array("names" => array("name","surname","email","phone")));
		// vaja kuvada otsitulemused. kuidas?
		$f->process_entry(array("id" => 2024));
		$res = $f->search($f->entry_id,array_keys($this->flatlist));
		$c = "";
		if (is_array($f->cached_results))
		{
			list($entry_id,$results) = each($f->cached_results);
			if (is_array($results))
			{
				foreach($results as $idx => $row)
				{
					$this->vars(array(
						"name" => $row[$ids["name"]] . " " . $row[$ids["surname"]],
						"phone" => $row[$ids["phone"]],
						"id" => $idx,
						"email" => $row[$ids["email"]],
					));
					$c .= $this->parse("line");
				};
			};
		};
		
		$this->vars(array(
			"menu" => $menu,
			"line" => $c,
		));
		return $this->parse();
	}
	
	function pick($args = array())
	{
		extract($args);
		$this->read_template("pick_contacts.tpl");

		// siia paneme koikide gruppide flat listi
		$grps = array();

		// ja siia parenti jargi grupeerituna
		$grps_by_parent = array();
		
		
			// Koostame nimekirja koigist selle kasutaja kontaktigruppidest
			$udata = $this->get_user();
			$fldr = $udata["home_folder"];

			do
			{
				// kysime koik sellel levelil asuvad objektid
				$groups = $this->_get_groups_by_level($fldr);

				// sorteerime nad parentite jargi ära
				// ja paigutame ka flat massiivi
				foreach($groups as $key => $val)
				{
					$grps_by_parent[$val["parent"]][$key] = $val["name"];
					$grps[$key] = $val["name"];
				};
			
				// koostame parentite nimekirja jargmise tsykli jaoks
				$fldr = array_keys($groups);
		
			// kordame nii kaua, kuni yhtegi objekti enam ei leitud
			} while(sizeof($groups) > 0);
			
			// nyyd on dropdowni jaoks vaja koostada idenditud nimekiri koigist objektidest
			$this->flatlist = array($udata["home_folder"] => LC_CONTACT_NOT_SORTED);
			$this->indentlevel = 0;
			$this->_indent_array($grps_by_parent,$udata["home_folder"]);
			
			// koostame nimekirja koigist selle formi entritest
			classload("form");
			$f = new form($this->cfg["form"]);
			$f->load($this->cfg["form"]);
			$ids = $f->get_ids_by_name(array("names" => array("name","surname","email","phone")));
			
			// see on selleks, et get_entries arvestaks ka neid kontakte, mis kodukataloogi
			// on salvestatud
			$grps[$udata["home_folder"]] = 1;
		
			$dat = $f->get_entries(array("parent" => array_keys($grps), "all_data" => true));
			
			// siia salvestame koik entryd parentite kaupa grupeerituna
			$entries_by_parent = array();

			foreach($dat as $row)
			{
				$name = sprintf("%s %s <%s>",$row[$ids["name"]],$row[$ids["surname"]],$row[$ids["email"]]);
				$entries[$row["oid"]] = $name;
				$entries_by_parent[$row["parent"]][] = $name;
			};
			
			$cnt = 1;
			$g = "";
			$gl = "";
			$garr = "";
			$gd = 0;

		
			foreach($grps as $oid => $name)
			{
				$this->vars(array(
						"oid" => $oid,
						"gid" => $oid,
					));

				if (is_array($entries_by_parent[$oid]))
				{
					foreach($entries_by_parent[$oid] as $key => $gname)
					{
						$gname = str_replace("\"","\\\"",$gname);
						$this->vars(array(
								"id" => $key,
								"name" => $gname,
								"gd" => $gd,
							));
						$gl .= $this->parse("gline");
						$garr .= $this->parse("garr");
						$gd++;
					};
				};

				$this->vars(array("gline" => $gl));
				$gl = "";
				$g .= $this->parse("group");
			};
			
			// listid
			$this->get_objects_by_class(array(
				"class" => CL_MAILINGLIST,
			));
			$gd = 0;
			$larr = "";
			while($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"],
					"gd" => $gd,
				));
				$gd++;
				$larr .= $this->parse("larr");
			};
			$this->vars(array("larr" => $larr));
			$g .= $this->parse("group");


			$dummy = array("0" => LC_CONTACT_ALL,"1" => LC_CONTACT_LISTS, "2" => "Uued listid");
			
			classload("ml_list");
			$mlist = new ml_list();
			$arr = $mlist->get_lists_and_groups(array("check_acl" => 1, "fullnames" => 1, "prefix" => ":", "spacer"=> " "));//check_acl =1,

			$gd = 0;
			$l2arr = "";
			foreach($arr as $lid_gid => $name)
			{
				$this->vars(array(
					"name" => $name,
					"gd" => $gd,
				));
				$gd++;
				$l2arr .= $this->parse("l2arr");
			};
			$this->vars(array("l2arr" => $l2arr,"oid" => "0"));
			//$g = $this->parse("group");
			$listcontacts = $this->parse("listcontacts");
	

		$this->vars(array(
			"groups" => $this->picker(-1,$dummy + $this->flatlist),
			"group" => $g,
			"is_list_msg" => $listmsg,
			"garr" => $garr,
			"hf" => $udata["home_folder"],
		));
		
		print $this->parse();
	}
};
?>
