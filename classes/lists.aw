<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/lists.aw,v 2.1 2001/05/18 15:31:56 duke Exp $

	global $orb_defs;
	$orb_defs["lists"] = array(
		"new" => array("function" => "add_list", "params"=> array("parent")),
		"submit_list" => array("function" => "add_list_submit"),
		"change" => array("function" => "change_list", "params"=> array("id")),
		"delete" => array("function" => "delete_list", "params"=> array("id","parent"))
		);

	class lists extends aw_template
	{
		function lists()
		{
			$this->tpl_init("mailinglist");
			$this->db_init();
		}

		function add_list($ar)
		{
			if (is_array($ar))
			{
				extract($ar);
			}
			else
			{
				$parent = $ar;
			}
			$this->read_template("list_add.tpl");
			$this->vars(array(
				"name" => "", 
				"comment" => "",
				"reforb" => $this->mk_reforb("submit_list",array("parent" => $parent,"list_id" => 0))
				));
			return $this->parse();
		}

		function add_list_submit($arr)
		{
			$this->quote(&$arr);
			extract($arr);
			if ($id)
			{
				$this->upd_object(array("name" => $name, "comment" => $comment, "oid" => $id));
				$this->log_action($GLOBALS["uid"],"mlist","Muutis listi $name");
			}
			else
			{
				$id = $this->register_object($parent,$name, CL_MAILINGLIST,$comment);
				$this->log_action($GLOBALS["uid"],"mlist","Lisas listi $name");
			}
	
			return "list.aw?type=change_list&id=$id";
		}

		function change_list($ar)
		{
			if (is_array($ar))
			{
				extract ($ar);
			}
			else
			{
				$id = $ar;
			}
			$this->read_template("list_add.tpl");

			$row = $this->get_object($id);

			$this->vars(array(
				"name" => $row[name],
				"list_id" => $id,
				"comment" => $row[comment],
				"reforb" => $this->mk_reforb("submit_list",array("parent" => $row[parent],"id" => $id))
			));
			$this->parent = $row["parent"];
			return $this->parse();
		}

		function delete_list($ar)
		{
			if (is_array($ar))
			{
				extract($ar);
			}
			else
			{
				$id = $ar;
			}
			$this->delete_object($id);
			$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");
			$this->log_action($GLOBALS["uid"],"mlist","Kustutas listi $name");
			if (is_array($ar))
			{
				header("Location:orb.aw?class=menuedit&action=obj_list&parent=$parent");
			}
			else
			{
				return $id;
			}
		}

		//edasisi ei kasutata. hetkel. enam. uues menyyeditoris. hetk=2/27/01

		function rec_menu($parent,$space_images)
		{
			global $ext,$list_folders;

 			if (!is_array($this->menucache[$parent]))	// if no items on this level return immediately
				return;

			$this->level++;
			$ret = "";
			reset($this->menucache[$parent]);
			$num_els = count($this->menucache[$parent]);
			$cnt = 1;
			while (list(,$v) = each($this->menucache[$parent]))
			{
				$spim = $space_images;

				if ($list_folders[$v[data][oid]] == 1)	// if it's closed
					$op = "open";
				else
					$op = "close";

				if (is_array($this->menucache[$v[data][oid]]))	// has subitems
				{
					$image = "<a href='list.$ext?parent=".$v[data][oid]."&op=$op'><img src='";

					if ($list_folders[$v[data][oid]] == 1)	// if closed
						$image.="/images/puu_plus";
					else
						$image.="/images/puu_miinus";

					if ($cnt == $num_els)
						$image.="l.gif";
					else
						$image.=".gif";

					$image.="' border=0>";
				}
				else	// does not have subitems
				{
					$image = "<img src='";
					if ($cnt == $num_els)
						$image.="/images/puu_lopp.gif";
					else
						$image.="/images/puu_rist.gif";
					$image.="' border=0><a href='list.$ext?parent=".$v[data][oid]."&op=$op'>";
				}

				$image.="<img src='/images/";
				if ($this->selected == $v[data][oid])
					$image.="puu_folderl.gif";
				else
						$image.="puu_folder.gif";
				$image.="' border=0></a>";

				$this->vars(array("space_images"	=> $spim, 
													"image"					=> $image,
													"cat_name"			=> $v[data][name],
													"cat_comment"		=> $v[data][comment],
													"modifiedby"		=> $v[data][modifiedby],
													"modified"			=> $this->time2date($v[data][modified],2),
													"cat_id"				=> $v[data][oid],
													"op"						=> "&op=open",
													"parent"				=> $this->selected));

				$cc = "";
				$cc = $this->parse("CAN_CHANGE");

				$cd = "";
				if ($v[data][mtype] != 1)
					$cd = $this->parse("CAN_DELETE");

				$ca = "";
				$ca = $this->parse("CAN_ACL");

				$this->vars(array("CAN_CHANGE"		=> $cc,
													"CAN_DELETE"		=> $cd,
													"CAN_ACL"				=> $ca));

				$ret.=$this->parse("C_LINE");

				if ($cnt == $num_els)			// if we are not at the end of this level we need to show a line, otherwise empty space.
					$spim.="<img src='/images/puu_tyhi.gif' border=0>";
				else
					$spim.="<img src='/images/puu_joon.gif' border=0>";

				if ($list_folders[$v[data][oid]] == 0)	// if the folder is open
					$ret.=$this->rec_menu($v[data][oid],$spim);

				$cnt++;
			}
			$this->level--;
			return $ret;
		}
		

		function gen_list($parent)
		{
			$this->read_template("list_list.tpl");
			
			if ($parent < 1)
				$parent = 1;

			$l = $this->make_tree($parent);
			$this->vars(array("C_LINE" => $l,"parent" => $parent));

			$ac = $this->parse("ADD_CAT");
			$al = $this->parse("ADD_LIST");

			$c = ""; 
			$this->db_query("SELECT objects.*,acl FROM objects 
											 LEFT JOIN acl ON objects.oid = acl.oid
											 WHERE class_id = 15 AND status != 0 AND parent=$parent
											 GROUP BY objects.oid");
			while ($row = $this->db_next())
			{
				$this->vars(array("list_id"				=> $row[oid],
													"list_name"			=> $row[name],
													"list_comment"	=> $row[comment]));
				$ce = $this->parse("L_CHANGE");
				$cd = $this->parse("L_DELETE");
				$ca = $this->parse("L_ACL");
				$ci = $this->parse("L_IMPORT");

				$this->vars(array("L_CHANGE" => $ce,"L_DELETE" => $cd,"L_ACL" => $ca,"L_IMPORT" => $ci));
				$c.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $c,"ADD_CAT" => $ac,"ADD_LIST" => $al));
			return $this->parse();
		}

		function add_cat($parent)
		{
			$this->read_template("add_cat.tpl");
			$this->vars(array("parent" => $parent, "name" => "", "comment" => "", "id" => 0));
			return $this->parse();
		}
		
		function submit_cat(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);

			if ($id)
			{
				$this->update_object($id, $name, 2, $comment);
				$this->log_action($GLOBALS["uid"],"mlist","Muutis kategooriat $name");
			}
			else
			{
				$id = $this->register_object($parent, $name, CL_MAILINGLIST_CATEGORY, $comment);
				$this->log_action($GLOBALS["uid"],"mlist","Lisas kategooria $name");
			}

			return $parent;
		}

		function change_cat($id)
		{
			$this->db_query("SELECT * FROM objects WHERE oid = $id");
			if (!($row = $this->db_next()))
				$this->raise_error("menuedit->gen_change_html($id): No such menu!", true);

			$this->read_template("change_cat.tpl");

			$this->vars(array("parent"			=> $row[parent], 
												"name"				=> $row[name], 
												"comment"			=> $row[comment], 
												"id"					=> $id,
												"created"			=> $this->time2date($row[created],2),
												"createdby"		=> $row[createdby],
												"modified"		=> $this->time2date($row[modified],2),
												"modifiedby"	=> $row[modifiedby],
												"link"				=> $row[link],
												"sep_checked"	=> ($row[type] == 2 ? "CHECKED" : ""),
												"doc_checked"	=> ($row[type] == 6 ? "CHECKED" : "")));
			return $this->parse();
		}

		////
		// !Koikide listide nimekiri
		function db_list()
		{
			$this->get_objects_by_class(array(
						"class" => CL_MAILINGLIST,
						));
		}
		
		////
		// !Koostab koigi listide hierarhilise nimekirja (s.t. koos kategooriatega)
		function get_op_list()
		{
			$this->get_objects_by_clas(array(
						"class" => CL_MAILINGLIST_CATEGORY,
						));
			$this->lcarr = array();
			while ($row = $this->db_next())
				$this->lcarr[$row["parent"]][] = $row;

			$this->db_list();
			$this->liarr = array();
			while ($row = $this->db_next())
				$this->liarr[$row["parent"]][] = $row;

			$this->op_list = array();
			$this->rec_op_list(1,"");

			return $this->op_list;
		}

		////
		// !For internal use. Kutsutakse eelmisest välja
		function rec_op_list($parent,$str)
		{
			if (!is_array($this->lcarr[$parent]))
				return;

			reset($this->lcarr[$parent]);
			while (list(,$v) = each($this->lcarr[$parent]))
			{
				$ns = $str.($str == "" ? "" : " / ").$v["name"];

				if (is_array($this->liarr[$v["oid"]]))
				{
					reset($this->liarr[$v["oid"]]);
					while (list(,$lv) = each($this->liarr[$v["oid"]]))
					{
						$this->op_list[$lv["oid"]] = $ns." / ".$lv["name"];
					}
				}

				$this->rec_op_list($v["oid"],$ns);
			}
		}

		function delete_cat($id)
		{
			$this->delete_object($id);
		}

		function make_tree($selected)
		{
			$this->selected = $selected;

			global $op,$list_folders;
			if ($op == "close")
				$list_folders[$selected] = 1;
			else
			if ($op == "open")
				$list_folders[$selected] = 0;

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
												LEFT JOIN acl ON acl.oid = objects.oid
												WHERE objects.class_id = 16 AND objects.status != 0
												GROUP BY objects.oid
												ORDER BY objects.parent");
			while ($row = $this->db_next())
			{
					$this->menucache[$row[parent]][] = array("data" => $row);
			}

			$this->vars(array("space_images" => "", "image" => "<img src='/images/puu_site.gif'>", "cat_id" => "1", "op" => "", "cat_name" => "Site", "NFIRST" => "", "cat_comment" => "", "modifiedby" => "", "modified" => "", "CAN_CHANGE" => "", "CAN_DELETE" => "", "CAN_ACL" => ""));
			$ret = $this->parse("C_LINE");
			// now recursively show the menu
			$this->sel_level = 0;
			$this->level =0;
			return $ret.$this->rec_menu(1,"");
		}
	};
?>
