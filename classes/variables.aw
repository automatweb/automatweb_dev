<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/variables.aw,v 2.4 2002/12/02 12:19:55 kristo Exp $

class variables extends aw_template
{
	function variables()
	{
		$this->init("mailinglist");
		lc_load("definition");
		$this->lc_load("mailinglist","lc_mailinglist");
	}

	function add_var($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_my_orb("gen_list", array("parent" => $parent))."'>Muutujad</a> / Lisa");
		$this->read_template("add_var.tpl");
		$this->vars(array(
			"var_name" => "", 
			"var_id" => "",
			"parent" =>$parent,
			"reforb" =>$this->mk_reforb("submit_var",array("parent" => $parent,"id" => 0))
		));
		return $this->parse();
	}
		
	function add_var_submit($arr)
	{
		extract($arr);
		
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name));
			$this->_log("ml_var",sprintf(LC_VARS_CHANGED_VAR,$name));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent,"name" => $name, "class_id" => CL_MAILINGLIST_VARIABLE,"status" => 2));
			$this->_log("ml_var",sprintf(LC_VARS_ADD_VAR,$name));
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}
		
	function delete_var($arr)
	{
		extract($arr);
		$this->delete_object($id);
		header("Location: ".$this->mk_my_orb("gen_list",array("parent" => $parent)));
	}
		
	function change_var($arr)
	{
		extract($arr);
		$this->read_template("add_var.tpl");
		if (!($row = $this->get_object($id)))
		{
			$this->raise_error(ERR_ML_VAR_NO_VAR,"variables->change_var(): no such variable!", true);
		}
		$this->mk_path(0,"<a href='".$this->mk_my_orb("gen_list", array("parent" => $row["parent"]))."'>Muutujad</a> / Muuda");
		$this->parent = $row["parent"];
		$this->vars(array(
			"var_name" => $row["name"], 
			"var_id" => $row["oid"],
			"parent" => $row["parent"],
			"reforb" => $this->mk_reforb("submit_var",array("parent" => $row["parent"],"id" => $id))
		));
		return $this->parse();
	}

		
	function make_tree($selected)
	{
		$this->selected = $selected;

		global $op,$list_folders;
		if ($op == "close")
		{
			$list_folders[$selected] = 1;
		}
		else
		if ($op == "open")
		{
			$list_folders[$selected] = 0;
		}

		$this->menucache = array();
		$this->db_query("SELECT objects.oid as oid, 
														objects.parent as parent,
														objects.comment as comment,
														objects.name as name,
														objects.created as created,
														objects.createdby as createdby,
														objects.modified as modified,
														objects.modifiedby as modifiedby,
														objects.last as last,
														objects.jrk as jrk
											FROM objects 
											WHERE objects.class_id = 24 AND objects.status != 0
											GROUP BY objects.oid
											ORDER BY objects.parent");
		while ($row = $this->db_next())
		{
			$this->menucache[$row["parent"]][] = array("data" => $row);
		}

		$this->vars(array(
			"space_images" => "",
			"image" => "<img src='/images/puu_site.gif'>",
			"cat_id" => "1",
			"op" => "",
			"cat_name" => "Site",
			"NFIRST" => "", 
			"cat_comment" => "", 
			"modifiedby" => "", 
			"modified" => "", 
			"open_link"		=> $this->mk_my_orb("gen_list",array("parent" => 1,"op" => "")),
			"CAN_CHANGE" => "", 
			"CAN_DELETE" => "", 
			"CAN_ACL" => "",
		));
		$ret = $this->parse("C_LINE");
		// now recursively show the menu
		$this->sel_level = 0;
		$this->level =0;
		return $ret.$this->rec_menu(1,"");
	}

	function rec_menu($parent,$space_images)
	{
		global $list_folders;
		$ext = $this->cfg["ext"];

		if (!is_array($this->menucache[$parent]))	// if no items on this level return immediately
		{
			return;
		}

		$this->level++;
		$ret = "";
		reset($this->menucache[$parent]);
		$num_els = count($this->menucache[$parent]);
		$cnt = 1;
		while (list(,$v) = each($this->menucache[$parent]))
		{
			$spim = $space_images;

			if ($list_folders[$v["data"]["oid"]] == 1)	// if it's closed
			{
				$op = "open";
			}
			else
			{
				$op = "close";
			}

			if (is_array($this->menucache[$v["data"]["oid"]]))	// has subitems
			{
				$image = "<a href='orb.$ext?class=variables&action=gen_list&parent=".$v["data"]["oid"]."&op=$op'><img src='";

				if ($list_folders[$v["data"]["oid"]] == 1)	// if closed
				{
					$image.="/images/puu_plus";
				}
				else
				{
					$image.="/images/puu_miinus";
				}

				if ($cnt == $num_els)
				{
					$image.="l.gif";
				}
				else
				{
					$image.=".gif";
				}

				$image.="' border=0>";
			}
			else	// does not have subitems
			{
				$image = "<img src='";
				if ($cnt == $num_els)
				{
					$image.="/images/puu_lopp.gif";
				}
				else
				{
					$image.="/images/puu_rist.gif";
				}
				$image.="' border=0><a href='orb.$ext?class=variables&action=gen_list&parent=".$v["data"]["oid"]."&op=$op'>";
			}

			$image.="<img src='/images/";
			if ($this->selected == $v["data"]["oid"])
			{
				$image.="puu_folderl.gif";
			}
			else
			{
					$image.="puu_folder.gif";
			}
			$image.="' border=0></a>";

			$this->vars(array(
				"space_images"	=> $spim, 
				"image"					=> $image,
				"cat_name"			=> $v["data"]["name"],
				"cat_comment"		=> $v["data"]["comment"],
				"modifiedby"		=> $v["data"]["modifiedby"],
				"modified"			=> $this->time2date($v["data"]["modified"],2),
				"change_link" => $this->mk_my_orb("change_cat",array("id" => $v["data"]["oid"])),
				"delete_link" => $this->mk_my_orb("delete_cat",array("id" => $v["data"]["oid"])),
				"open_link"		=> $this->mk_my_orb("gen_list",array("parent" => $v["data"]["oid"],"op" => "open")),
				"cat_id"				=> $v["data"]["oid"],
				"op"						=> "&op=open",
				"parent"				=> $this->selected
			));

			$cc = $this->parse("CAN_CHANGE");

			if ($v["data"]["mtype"] != 1)
			{
				$cd = $this->parse("CAN_DELETE");
			}

			$ca = $this->parse("CAN_ACL");

			$this->vars(array(
				"CAN_CHANGE"		=> $cc,
				"CAN_DELETE"		=> $cd,
				"CAN_ACL"				=> $ca
			));

			$ret.=$this->parse("C_LINE");

			if ($cnt == $num_els)			// if we are not at the end of this level we need to show a line, otherwise empty space.
			{
				$spim.="<img src='/images/puu_tyhi.gif' border=0>";
			}
			else
			{
				$spim.="<img src='/images/puu_joon.gif' border=0>";
			}

			if ($list_folders[$v["data"]["oid"]] == 0)	// if the folder is open
			{
				$ret.=$this->rec_menu($v["data"]["oid"],$spim);
			}

			$cnt++;
		}
		$this->level--;
		return $ret;
	}

	////
	// generates a list of variables
	function gen_list($args = array())
	{
		extract($args);
		$this->mk_path(0,"Muutujate nimekiri");
		$this->read_template("list_vars.tpl");
		if ($parent < 1)
		{
			$parent = 1;
		}

		$l = $this->make_tree($parent);
		$this->vars(array(
			"C_LINE" => $l,
			"parent" => $parent,
			"add_link" => $this->mk_my_orb("add_cat",array("parent" => $parent)),
			"add_var" => $this->mk_my_orb("new", array("parent" => $parent))
		));
		$this->vars(array("parent" => $parent));
		$this->vars(array("ADD_CAT" => $this->parse("ADD_CAT")));


		$c="";
		$this->db_query("SELECT objects.* FROM objects
										 WHERE objects.class_id = 18 AND objects.status != 0 AND objects.parent = $parent");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"var_id" => $row["oid"], 
				"var_name" => $row["name"],
				"change_var" => $this->mk_my_orb("change", array("id" => $row["oid"])),
				"delete_var" => $this->mk_my_orb("delete_var", array("id" => $row["oid"], "parent" => $parent))
			));

			$cc = $this->parse("V_CHANGE");
			$cd = $this->parse("V_DELETE");
			$ca = $this->parse("V_ACL");

			$this->vars(array("V_CHANGE" => $cc, "V_DELETE" => $cd, "V_ACL" => $ca));

			$c.=$this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $c,
		));
		return $this->parse();
	}
	

	////
	// !Displays list of stamps
	function list_stamps($arr)
	{
		extract($arr);
		$this->mk_path(0,"Stampide nimekiri");
		$this->read_template("list_stamps.tpl");
		$c="";
		$this->db_query("SELECT objects.* FROM objects 
										 WHERE objects.class_id = 19 AND objects.status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"stamp_id" => $row["oid"],
				"stamp_name" => $row["name"],
				"change_link" => $this->mk_my_orb("change_stamp",array("id" => $row["oid"])),
				"delete_link" => $this->mk_my_orb("delete_stamp",array("id" => $row["oid"])),
			));

			$vc = $this->parse("V_CHANGE");
			$vd = $this->parse("V_DELETE");
			$va = $this->parse("V_ACL");

			$this->vars(array("V_CHANGE" => $vc, "V_DELETE" => $vd, "V_ACL" => $va));

			$c.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $c,
			"add_link" => $this->mk_orb("add_stamp",array()),
		));
		return $this->parse();
	}

	////
	// !displays the form for adding a new stamp
	function add_stamp($args = array())
	{
		extract($args);
		$this->read_template("add_stamp.tpl");
		$this->mk_path("0","<a href='orb.".$this->cfg["ext"]."?class=variables&action=list_stamps'>Stampide nimekiri</a> / Lisa stamp");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_stamp",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !displays the form for changing a stamp
	function change_stamp($args = array())
	{
		extract($args);
		$this->read_template("add_stamp.tpl");
		$this->mk_path("0","<a href='orb.".$this->cfg["ext"]."?class=variables&action=list_stamps'>Stampide nimekiri</a> / Lisa stamp");
		
		if (!($row = $this->get_object($id)))
		{
			$this->raise_error(ERR_ML_VAR_NO_STAMP,"variables->change_stamp($id): no such stamp!", true);
		}
		
		$this->vars(array(
			"stamp_name" => $row["name"],
			"stamp_value" => $row["comment"],
			"reforb" => $this->mk_reforb("submit_stamp",array("id" => $id)),
		));

		return $this->parse();
	}

	////
	// !Submits a stamp
	function submit_stamp($args = array())
	{
		extract($args);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $value));
			$this->_log("ml_var",sprintf(LC_VARS_CHANGED_STAMP,$name));
		}
		else
		{
			$id = $this->new_object(array("parent" => 1,"name" => $name, "class_id" => CL_MAILINGLIST_STAMP, "comment" => $value));
			$this->_log("ml_var",sprintf(LC_VARS_ADD_STAMP,$name));
		};
		return $this->mk_my_orb("change_stamp",array("id" => $id));
	}
	
	////
	// !deletes a stamp
	function delete_stamp($args = array())
	{
		extract($args);
		$this->delete_object($id,CL_MAILINGLIST_STAMP);
		return $this->mk_my_orb("list_stamps",array());
	}
	
	function db_list()
	{
		$this->get_objects_by_class(array(
			"class" => CL_MAILINGLIST_VARIABLE,
		));
	}

	function db_list_stamps()
	{
		$this->get_objects_by_class(array(
			"class" => CL_MAILINGLIST_STAMP,
		));
	}

	////
	// !displays the form for adding a new category
	function add_cat($args = array())
	{
		extract($args);
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=variables&action=gen_list&parent=$parent'>Muutujad</a> / Lisa kategooria");
		$this->read_template("add_var_cat.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_cat",array("parent" => $parent)),
		));
		return $this->parse();
	}
	
	////
	// !Submits a category
	function submit_cat(&$arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"status" => 2,
				"comment" => $comment,
			));
			$this->_log("ml_var",sprintf(LC_VARS_CHANGED_CAT,$name));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_ML_VAR_CAT,
				"comment" => $comment,
			));
			
			$this->_log("ml_var",sprintf(LC_VARS_ADD_CAT,$name));
		}
		return $this->mk_my_orb("change_cat",array("id" => $id));
	}

	//// 
	// !Displays the form for changing a category
	function change_cat($args = array())
	{
		extract($args);
		if (!($row = $this->get_object($id)))
		{
			$this->raise_error(ERR_ML_VAR_NO_CAT,"variables->gen_change_html($id): No such category!", true);
		}
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=variables&action=gen_list&parent=$row[parent]'>Muutujad</a> / Muuda kategooria");

		$this->read_template("change_var_cat.tpl");

		$this->vars(array(
			"parent"			=> $row["parent"], 
			"name"				=> $row["name"], 
			"comment"			=> $row["comment"], 
			"created"			=> $this->time2date($row["created"],2),
			"createdby"		=> $row["createdby"],
			"modified"		=> $this->time2date($row["modified"],2),
			"modifiedby"	=> $row["modifiedby"],
			"reforb" =>  $this->mk_reforb("submit_cat",array("id" => $id)),
		));
		return $this->parse();
	}

	// deletes a category from list
	function delete_cat($args = array())
	{
		extract($args);
		$this->delete_object($id,CL_ML_VAR_CAT);
		return $this->mk_my_orb("gen_list",array("parent" => 1));
	}

	function check_environment(&$sys, $fix = false)
	{
		$op_table = array(
			"name" => "ml_vars", 
			"fields" => array(
				"id" => array("name" => "id", "length" => 11, "type" => "int", "flags" => ""),
				"name" => array("name" => "name", "length" => 255, "type" => "string", "flags" => "")
			)
		);

		$op2_table = array(
			"name" => "ml_var_values", 
			"fields" => array(
				"var_id" => array("name" => "var_id", "length" => 11, "type" => "int", "flags" => ""),
				"user_id" => array("name" => "user_id", "length" => 11, "type" => "int", "flags" => ""),
				"value" => array("name" => "value", "length" => 65535, "type" => "blob", "flags" => "")
			)
		);

		$ret = $sys->check_admin_templates("mailinglist", array("add_var.tpl","list_vars.tpl","list_stamps.tpl","add_stamp.tpl","add_var_cat.tpl","change_var_cat.tpl"));
		$ret.= $sys->check_site_templates("mailinglist", array());
		$ret.= $sys->check_site_files(array("/images/puu_plus.gif","/images/puu_plusl.gif","/images/puu_lopp.gif","/images/puu_rist.gif","/images/puu_folder.gif","/images/puu_folderl.gif","/images/puu_tyhi.gif","/images/puu_joon.gif"));
		$ret.= $sys->check_db_tables(array($op_table,$op2_table),$fix);

		return $ret;
	}
}
?>
