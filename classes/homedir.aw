<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/homedir.aw,v 2.8 2002/12/02 18:54:09 kristo Exp $
// homedir.aw - Class for managing users home directory

classload("users");
class homedir extends users 
{
	function homedir($args = array())
	{
		$this->init("homedir");
		lc_load("definition");
	}

	////
	// !Draws a custom homedir
	// folder(int) - milline folder hetkel aktiivne on?
	function generate($args = array())
	{
		extract($args);
		// first we need to check whether the object we are requesting is actually under the homedir
		// of the user. Actually this should be done by checking ACL, but for now we will settle
		// for this.
	}

	////
	// !deletes the objects from the users home folder that are selected
	function del_objects($arr)
	{
		extract($arr);
		if (is_array($delete))
		{
			reset($delete);
			while (list(,$id) = each($delete))
			{
				$this->delete_object($id);
			}
		}
		return $this->mk_site_orb(array("action" => "gen_home_dir", "id" => $parent));
	}

	////
	//! Genereerib sisselogitud kasutaja kodukataloogi
	function gen_home_dir($args = array())
	{
		classload('icons');
		$udata = $this->get_user();
		$baseurl = $this->cfg["baseurl"];
		$parent = $args["id"];

		$tpl = ($args["tpl"]) ? $args["tpl"] : "homefolder.tpl";
		$this->read_template($tpl);

		$result = array();
		$startfrom = (!$parent) ? $udata["home_folder"] : $parent;

		$grps_by_parent = array();
		$grps = array();
	
		// we always start from the home folder
		$fldr = $udata["home_folder"];

		do
		{
			$groups = $this->get_objects_below(array(
				"parent" => $fldr,
				"class" => CL_PSEUDO,
			));

			foreach($groups as $key => $val)
			{
				$grps_by_parent[$val["parent"]][$key] = $val;
				$grps[$key] = $val["parent"];
			};

			$fldr = array_keys($groups);

		} while(sizeof($groups) > 0);

		$current = $startfrom;
		
		while ($udata["home_folder"] != $current)
		{
			$path[$current] = 1;
			$current = $grps[$current];
		}
		
		$path[$udata["home_folder"]] = 1;

		// security check. if the requested is outside or above the
		// users home folder, we will show him the home folder.

		// this should be done differently of course, by checking the
		// ACL of the respective document, but for now, we will settle
		// to this.
		if (!$grps[$startfrom])
		{
			$startfrom = $udata["home_folder"];
		};

		// we need a small cycle that passes all the oids-s and 
		// parents until we do find 

		$this->path = $path;
		$this->folders = "";
		$this->active = $startfrom;
		$this->_show_hf_folder($grps_by_parent,$udata["home_folder"]);

		
		// print sizeof($grps_by_parent[$udata["home_folder"]]);
		
		// we will always have to display the first level,
		// and then find out the parents of the currently
		// opened folder, and then we have to track the parents
		// back up to the home folder
	
		$thisone = $this->get_object($startfrom);
		$prnt = $this->get_object($thisone["parent"]);

		$q = "SELECT name,oid,class_id,name FROM objects
			WHERE objects.parent = '$startfrom' and objects.status != 0 and objects.class_id != 1";
		$this->db_query($q);

		$folders = "";
		$cnt = 0;
		while($row = $this->db_next())
		{
			$class = $this->cfg["classes"][$row["class_id"]]["file"];
			$preview = $this->mk_my_orb("preview", array("id" => $row["oid"]),$class);
			$cnt++;
			switch ($row["class_id"])
			{
				case CL_PSEUDO:
					$tpl = "folder";
					break;
				
				case CL_FILE:
					$preview = file::get_url($row["oid"],$row["name"]);
					break;

				default:
					$tpl = "doc";
					break;
			};
			$this->vars(array(
				"name" => ($row["name"]) ? $row["name"] : "(nimetu)",
				"id" => $row["oid"],
				"iconurl" => icons::get_icon_url($row["class_id"],0),
				"color" => ($cnt % 2) ? "#FFFFFF" : "#EEEEEE",
				"f_click" => $this->mk_my_orb("gen_home_dir", array("id" => $row["oid"])),
				"preview" => $preview,
				"change" => $this->mk_my_orb("change", array("id" => $row["oid"]),$class)
			));

			$folders .= $this->parse($tpl);
		};
		$delete = "";
		if ($cnt > 0)
		{
			$delete = $this->parse("delete");
		};

		$this->vars(array(
			"folder" => $folders,
			"doc" => "",
			"name" => $thisone["name"],
			"parent" => $startfrom,
			"delete" => $delete,
			"total" => $cnt,
			"folders" => $this->folders,
			"reforb1" => $this->mk_reforb("del_objects", array("parent" => $startfrom)),	// delete form
			"home_f" => $this->mk_my_orb("gen_home_dir"),
		));
		return $this->parse();
	}
	
	////
	// !for internal use
	function _show_hf_folder($items,$section)
	{
		static $indent = 0;
		$indent++;
		static $cnt = 0;
		if (is_array($items[$section]))
		{
			while(list($key,$val) = each($items[$section]))
			{
				$cnt++;
				$this->vars(array(
					"id" => $val["oid"],
					"indent" => str_repeat("&nbsp;",$indent * 3),
					"name" => $val["name"],
					"color" => ($cnt % 2) ? "#EEEEEE" : "#FFFFFF",
				));
				$tpl = ($this->active == $key) ? "activefolder" : "folders";
				$this->folders .= $this->parse($tpl);
				if ( (is_array($items[$key])) && ($this->path[$key]))
				{
					$this->_show_hf_folder($items,$key);
				};
			}
		}
		$indent--;
	}

	////
	// !creates a new folder int the users home folder
	function submit_add_folder($arr)
	{
		extract($arr);
		$id = $this->new_object(array("parent" => $parent, "class_id" => CL_PSEUDO, "status" => 2, "name" => $name));
		$this->db_query("INSERT INTO menu(id,type) values($id,".MN_HOME_FOLDER_SUB.")");
		global $status_msg;
		$status_msg = LC_HOMEDIR_ADDED;
		session_register("status_msg");

		return $this->mk_my_orb("gen_home_dir", array("id" => $parent));
	}
};
?>
