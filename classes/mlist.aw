<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/mlist.aw,v 2.9 2003/01/20 14:25:50 kristo Exp $
class mlist extends aw_template
{
	function mlist($id = 0)
	{
		lc_load("automatweb");
		$this->init("mailinglist");
		$this->id = $id;
		$this->db_query("SELECT * FROM objects WHERE oid = $id");
		$row = $this->db_next();

		$this->name = $row["name"];
		$this->l_vars = unserialize($row["last"]);
		$this->vars(array("list_name" => $this->name, "list_id" => $this->id));
		lc_load("definition");
		$this->lc_load("mailinglist","lc_mailinglist");
	}
	
	function list_members($args = array())
	{
		extract($args);
		$list_obj = $this->get_object(array("oid" => $id,"class_id" => CL_MAILINGLIST));
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=lists&action=gen_list&parent=$list_obj[parent]'>Listid</a> / Listi '$list_obj[name]' liikmed");
		$this->read_template("list_users.tpl");
		
		$c=""; $cnt=0; $s = ""; 
		// this should be converted to use aw_table
		global $sortby, $order;
		$this->vars(array(
			"add_link" => $this->mk_my_orb("add_member",array("list_id" => $id)),
			"paste_link" => $this->mk_my_orb("paste",array("list_id" => $id)),
			"import_link" => $this->mk_my_orb("import_members",array("list_id" => $id)),
			"export_link" => $this->mk_my_orb("export_members",array("list_id" => $id)),
			"check_link" => $this->mk_my_orb("checkit",array("list_id" => $id)),
			"del_link" => $this->mk_my_orb("list_del", array("list_id" => $id)),
		));
		if ($sortby != "")
		{
			$sb="ORDER BY ".$sortby;
			if ($order == "")
			{
				$order = "ASC";
			}
			$sb.=" " .$order;
		}

		$tot_cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM objects
										 LEFT JOIN acl ON acl.oid = objects.oid
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE objects.parent = $this->id AND objects.status != 0 AND objects.class_id = 17", "cnt");
		$num_pages = $tot_cnt / 200;

		$p = "";
		for ($i=0; $i < $num_pages; $i++)
		{
			$this->vars(array(
				"from" => $i*200,
				"to" => min($tot_cnt, ($i+1)*200),
				"link" => "list.aw?type=list_inimesed&id=".$this->id."&page=$i&sortby=".$GLOBALS["sortby"]."&order=".$GLOBALS["order"]
			));
			if ($i == $page)
			{
				$p.=$this->parse("SEL_PAGE");
			}
			else
			{
				$p.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"SEL_PAGE" => "",
			"PAGE" => $p,
		));

		$this->db_query("SELECT objects.oid as oid, objects.name as name,ml_users.mail as mail,ml_users.is_cut as is_cut, ml_users.is_copied as is_copied,acl FROM objects
										 LEFT JOIN acl ON acl.oid = objects.oid
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE objects.parent = $id AND objects.status != 0 AND objects.class_id = 17 
										 $sb");
		while ($row = $this->db_next())
		{
			if ($row["is_copied"] == 1)
			{
				$bgk = "class='fgtext_copied'";
			}
			else
			if ($row["is_cut"] == 1)
			{
				$bgk = "class='fgtext_cut'";
			}
			else
			{
				$bgk = "class='fgtext'";
			}

			$this->vars(array(
				"user_name" => $row["name"],
				"user_id" => $row["oid"],
				"user_mail" => $row["mail"],
				"row" => $cnt,
				"cut"		=> $bgk,
				"change_link" => $this->mk_my_orb("change_member",array("id" => $id,"user_id" => $row["oid"])),
			));

			$ch = $this->parse("U_CHANGE");
			$ac = $this->parse("U_ACL");

			$this->vars(array("U_CHANGE" => $ch,"U_ACL" => $ac));

			$cnt++;
			$c.=	$this->parse("LINE");
			$s.= $this->parse("SELLINE");
		}
		$p = "";
		if ($this->db_fetch_field("SELECT count(*) as cnt from ml_users WHERE is_cut = 1 OR is_copied = 1", "cnt") > 0)
		{
			$p = $this->parse("PASTE");
		}

		$ua = $this->parse("U_ADD");
		$ui = $this->parse("U_IMPORT");
		$revo = ($order == "ASC" ? "DESC" : "ASC" );
		$up = "<img src='".$this->cfg["baseurl"]."/images/up.gif'>";
		$down = "<img src='".$this->cfg["baseurl"]."/images/down.gif'>";
		$this->vars(array(
			"LINE" => $c, 
			"SELLINE" => $s, 
			"PASTE" => $p,
			"U_ADD" => $ua,
			"U_IMPORT"=>$ui,
			"count" => $cnt,
			"is_so" => $revo,
			"id_sort_img" => ($sortby == "oid" ? ($order == "ASC" ? $up : $down) : "" ),
			"name_sort_img" => ($sortby == "name" ? ($order == "ASC" ? $up : $down) : "" ),
			"email_sort_img" => ($sortby == "email" ? ($order == "ASC" ? $up : $down) : "" ),
			"list_id" => $id,
			"reforb" => $this->mk_reforb("void",array("list_id" => $id, "page" => $page)),
		));
		return $this->parse();
	}
	
	function add_member($args = array())
	{
		extract($args);
		$list_obj = $this->get_object(array("oid" => $list_id,"class_id" => CL_MAILINGLIST));
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=lists&action=gen_list&parent=$list_obj[parent]'>Listid</a> / <a href='orb.aw?class=mlist&action=list_members&id=$list_id'>Listi liikmed</a> / Lisa liige");
		
		$this->read_template("add_user.tpl");
		
		$ls = "";
		$this->db_query("SELECT * FROM objects WHERE oid = $list_id");
		$row = $this->db_next();
		$this->l_vars = unserialize($row["last"]);
		if (is_array($this->l_vars))
		{
			reset($this->l_vars);
			while(list($id,) = each($this->l_vars))
			{
				if ($ls == "")
				{
					$ls = $id;
				}
				else
				{
					$ls.=",".$id;
				}
			}
		}

		$c="";	
		if ($ls != "")
		{
			$this->db_query("SELECT objects.* FROM objects
											 WHERE objects.class_id = 18 AND objects.status != 0 AND objects.parent IN ($ls)
											 GROUP BY objects.oid");
		
			while($row = $this->db_next())
			{
				$this->vars(array("var_name" => $row["name"], "var_id" => $row["oid"], "var_value" => ""));
				$c.=$this->parse("VARS");
			}
		}
		$this->vars(array(
			"VARS" => $c,
			"reforb" => $this->mk_reforb("submit_member",array("id" => $list_id)),
			"list_name" => $list_obj["name"],
		));
		return $this->parse();
	}
	// data[name], data[email], vars -> array(var_id, var_value), acl not checked
	function db_add_user($data, $vars = "")
	{
		extract($data);
		if (strlen($email) < 4)
		{
			return false;
		};

		$this->db_query("SELECT * FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE parent=$this->id AND status != 0 AND mail='$email'");
		if (($row = $this->db_next()))
		{
			return $row["id"];	// if such user exists, do not add another
		}

		$user_id = $this->new_object(array("parent" => $this->id, "name" => $name, "class_id" => CL_MAILINGLIST_MEMBER,"status" => 2));
		$this->db_query("INSERT INTO ml_users(id,mail) VALUES($user_id, '$email')");

		if (gettype($vars) == "array")
		{
			reset($vars);
			while (list($var_id, $var_value) = each($vars))
			{
				$this->db_query("INSERT INTO ml_var_values VALUES($var_id, $user_id, '".$var_value."')");
			}
		}

		$this->_log(ST_ML_USER, SA_ADD, sprintf(LC_LIST_ADD_USER,$name,$this->name), $user_id);
		return $user_id;
	}

	function submit_member($arr)
	{
		extract($arr);
		$this->id = $id;
		$va = get_instance("variables");
		$va->db_list();
		while ($row = $va->db_next())
		{
			$arrv[$row["oid"]] = $row["name"];
		}

		if ($user_id != "")
		{
			$this->upd_object(array("oid" => $user_id, "name" => $name));
			$this->db_query("UPDATE ml_users SET mail = '$email' WHERE id = $user_id");

			if (gettype($arrv) == "array")
			{
				reset($arrv);
				while (list($var_id, $var_name) = each($arrv))
				{
					$v = "var_".$var_id;
					$this->db_query("SELECT * FROM ml_var_values WHERE var_id = $var_id AND user_id = $user_id");
					if ($this->db_next())
					{
						$this->db_query("UPDATE ml_var_values SET value='".$$v."' WHERE var_id = $var_id AND user_id = $user_id");
					}
					else
					{
						$this->db_query("INSERT INTO ml_var_values VALUES($var_id, $user_id, '".$$v."')");
					}
				}
			}
			$this->_log(ST_ML_USER, SA_CHANGE, sprintf(LC_LIST_CHANGED_USER,$name,$this->name), $user_id);
		}
		else
		{
			$vars = array();
			if (gettype($arrv) == "array")
			{
				reset($arrv);
				while (list($var_id, $var_name) = each($arrv))
				{
					$v = "var_".$var_id;
					$vars[$var_id] = $$v;
				}
			}
			$user_id = $this->db_add_user(array("name" => $name, "email" => $email), $vars);
		}
		return $this->mk_my_orb("list_members",array("id" => $id));
	}

	////
	// !Lisab kasutaja yhte voi enamasse listi
	// uid (string) - kasutaja uid, keda lisatakse
	// name (string) -
	// email (string) -
	// lists (array of int) - listide id-d, kuhu kasutaja liita
	function add_user_to_lists($args = array())
	{	
		extract($args);
		$t = time();
		if (is_array($list_ids))
		{
			foreach($list_ids as $val)
			{
				$id = $this->new_object(array(
					"parent" => $val,
					"class_id" => CL_MAILINGLIST_MEMBER,
					"name" => $name,
					"status" => 2,
				));
				$q = "INSERT INTO ml_users (id,name,mail,list_id,uid,tm)
					VALUES('$id','$name','$email','$val','$uid',$t)";

				$this->db_query($q);
			}
		};
	}

	////
	// !Eemaldab kasutaja koigist listidest
	// argumendid:
	// uid (string) - uid
	function remove_user_from_lists($args = array())
	{
		extract($args);
		$q = "SELECT * FROM ml_users WHERE uid = '$uid'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->upd_object(array(
				"oid" => $row["id"],
				"status" => 0,
			));
		};
		$q = "DELETE FROM ml_users WHERE uid = '$uid'";
		$this->db_query($q);
	}

	////
	// !Teeb koigi listide nimekirja, kuhu kasutaja kuulub
	// listide id-d on tagastava array keyd-eks
	// argumendid:
	// uid (string) - uid
	function get_user_lists($args = array())
	{
		extract($args);
		$q = "SELECT * FROM ml_users WHERE uid = '$uid'";
		$this->db_query($q);
		$res = array();
		while($row = $this->db_next())
		{
			$res[$row["list_id"]] = 1;
		};
		return $res;
	}
				
	function change_member($args = array())
	{
		extract($args);
		$uid = $user_id;
		$list_obj = $this->get_object(array("oid" => $id,"class_id" => CL_MAILINGLIST));
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=lists&action=gen_list&parent=$list_obj[parent]'>Listid</a> / <a href='orb.".$this->cfg["ext"]."?class=mlist&action=list_members&id=$id'>Listi liikmed</a> / Muuda liiget");
		$this->read_template("add_user.tpl");
		$this->db_query("SELECT objects.*,ml_users.mail as mail,acl FROM objects
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 LEFT JOIN acl ON acl.oid = objects.oid
										 WHERE objects.oid = $uid
										 GROUP BY objects.oid");
		if (!($row = $this->db_next()))
		{
			$this->raise_error(ERR_LIST_NOUSER,"mlist->change_user($uid): No such user!", true);
		}

		$this->vars(array("user_id" => $row["oid"], "user_name" => $row["name"], "user_mail" => $row["mail"]));

		$this->db_query("SELECT * FROM objects WHERE oid = $id");
		$row = $this->db_next();
		$this->l_vars = unserialize($row["last"]);

		$ls = "";
		if (is_array($this->l_vars))
		{
			reset($this->l_vars);
			while(list($id,) = each($this->l_vars))
			{
				if ($ls == "")
				{
					$ls = $id;
				}
				else
				{
					$ls.=",".$id;
				}
			}
		}

		$c="";	
		if ($ls != "")
		{
			$this->db_query("SELECT objects.* FROM objects
											 WHERE objects.class_id = 18 AND objects.status != 0 AND objects.parent IN ($ls)
											 GROUP BY objects.oid");
			while ($vrow = $this->db_next())
			{
				$this->save_handle();

				$this->db_query("SELECT value FROM ml_var_values WHERE var_id = ".$vrow[oid]." AND user_id = $uid");
				$row = $this->db_next();
				
				$this->vars(array("var_name" => $vrow["name"], "var_id" => $vrow["oid"], "var_value" => $row["value"]));
				$c.=$this->parse("VARS");

				$this->restore_handle();
			}
		}
		$this->vars(array(
			"VARS" => $c,
			"list_name" => $list_obj["name"],
			"reforb" => $this->mk_reforb("submit_member",array("user_id" => $user_id,"id" => $id)),
		));
		return $this->parse();
	}
	
	function get_ids_from_vars(&$arr)
	{
		$ids = "";
		while (list($k,$v) = each($arr))
		{
			if (substr($k, 0, 3) == "ch_" && $v == 1)
			{
				if ($ids == "")
				{
					$ids = substr($k, 3);
				}
				else
				{
					$ids.=",".substr($k,3);
				}
			}
		}
		return $ids;
	}

	////
	// !Kustutab mingist listist kasutajaid
	function delete(&$arr)
	{
		extract($arr);
		$this->id = $list_id;
		$ids = $this->get_ids_from_vars(&$arr);

		if ($ids != "")
		{
			$rows = array();
			// 17 on listiliige
			$this->db_query("SELECT objects.oid as oid,acl  FROM objects
											 LEFT JOIN acl ON acl.oid = objects.oid
											 WHERE objects.class_id = 17 AND objects.status != 0 AND objects.parent = $this->id
											 GROUP BY objects.oid");
			while ($row = $this->db_next())
			{
				$rows[$row["oid"]] = 1;		// mark all the rows that can be deleted
			}
			$uidarr = explode(",",$ids);
			reset($uidarr);
			while (list(,$v) = each($uidarr))
			{
				if ($rows[$v] == 1)
				{
					$this->delete_object($v);
					$this->_log(ST_ML_USER, SA_DELETE,sprintf(LC_LIST_ERASED_USER,$this->name)." user $v", $v);
				}
			}
		}
		return $this->mk_my_orb("list_members",array("id" => $list_id));
	}

	function copy(&$arr)
	{
		extract($arr);
		$this->id = $list_id;
		$ids = $this->get_ids_from_vars(&$arr);

		$this->db_query("UPDATE ml_users SET is_copied = 0");
		$this->db_query("UPDATE ml_users SET is_cut = 0");

		if ($ids != "")
		{
			$this->db_query("UPDATE ml_users SET is_copied = 1 WHERE id IN ($ids)");
		}

		return $this->mk_my_orb("list_members",array("id" => $list_id));
	}

	function cut(&$arr)
	{
		extract($arr);
		$this->id = $list_id;
		$ids = $this->get_ids_from_vars(&$arr);

		$this->db_query("UPDATE ml_users SET is_cut = 0");
		$this->db_query("UPDATE ml_users SET is_copied = 0");

		if ($ids != "")
		{
			$this->db_query("UPDATE ml_users SET is_cut = 1 WHERE id IN ($ids)");
		}
		
		return $this->mk_my_orb("list_members",array("id" => $list_id));
	}

	function do_paste($list_id)
	{
		while ($row = $this->db_next())
		{
			$this->save_handle();

			// copy user variables
			$vars = array();
			$this->db_query("SELECT * FROM ml_var_values WHERE user_id = ".$row["oid"]);
			while ($vrow = $this->db_next())
			{
				$vars[$vrow["var_id"]] = $vrow["value"];
			}

			$this->db_add_user(array("name" => $row["name"], "email" => $row["mail"]), $vars);
			$this->restore_handle();
		}
	}

	function paste($args = array())
	{
		extract($args);
		$this->id = $list_id;
		$this->db_query("SELECT objects.*,ml_users.mail as mail FROM objects 
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE is_copied = 1");
		$this->do_paste($list_id);
		$this->db_query("SELECT objects.*,ml_users.mail as mail FROM objects 
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE is_cut = 1");
		$this->do_paste($list_id);

		$this->db_query("SELECT objects.*,acl FROM objects
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 LEFT JOIN acl ON acl.oid = objects.oid
										 WHERE ml_users.is_cut = 1
										 GROUP BY objects.oid");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->delete_object($row["oid"]);
			$this->restore_handle();
		}
		return $this->mk_my_orb("list_members",array("id" => $list_id));
	}

	function import_members($args = array())
	{
		extract($args);
		$list_obj = $this->get_object(array("oid" => $list_id,"class_id" => CL_MAILINGLIST));
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=lists&action=gen_list&parent=$list_obj[parent]'>Listid</a> / <a href='orb.".$this->cfg["ext"]."?class=mlist&action=list_members&id=$list_id'>Listi liikmed</a> / Impordime failist meiliaadresse list");
		$this->read_template("import_emails.tpl");

		$this->db_query("SELECT * FROM objects WHERE oid = $list_id");
		$row = $this->db_next();
		$this->l_vars = unserialize($row["last"]);

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_MAILINGLIST_VARIABLE." AND status != 0");
		while ($row = $this->db_next())
		{
			if ($this->l_vars[$row["parent"]] == 1)
			{
				$this->vars(array(
					"var_name"	=> $row["name"], 
					"var_id"		=> $row["oid"],
					"ord"		=> ++$ord
				));
				$line.=$this->parse("V_LINE");
			}
		}

		$this->vars(array(
			"V_LINE" => $line,
			"reforb" => $this->mk_reforb("import_members_submit",array("list_id" => $list_id)),
		));
		return $this->parse();
	}
	
	function import_members_submit($arr)
	{
		extract($arr);
		$this->id = $list_id;

		global $pilt;
		$arr = file($pilt);
		
		$variables = array();
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_MAILINGLIST_VARIABLE." AND status != 0");
		while ($row = $this->db_next())
		{
			$variables[$row["oid"]] = $row["name"];
		}

		reset($arr);
		while(list($num, $line) = each($arr))
		{	
			$line = str_replace("\n" , "", $line);
			$res = explode(",", $line);
			$name = $res[0];
			$mail = $res[1];
			$varst = array();
			$usid = $this->db_add_user(array("name" => $name, "email" => $mail));
			if (is_array($vars))
			{
				foreach($vars as $v_id => $v_v)
				{
					$varst[] = $variables[$v_id]." = ".$res[$ord[$v_id]+1];
					$vvl = $res[$ord[$v_id]+1];
					$this->quote(&$vvl);
					$this->db_query("INSERT INTO ml_var_values(var_id,user_id,value) VALUES('$v_id','$usid','$vvl')");
				}
			}
			echo "Leidsin $name ( $mail ) muutujad: ".join(",",$varst)."<br>";
			flush();
		}			
		$this->_log(ST_ML_LIST, SA_IMPORT,$this->name);
		$or = $this->mk_my_orb("list_members",array("id" => $list_id)); 
		print "Kliki <a href='$or'>siia</a> jätkamiseks";
		exit;
	}

	function change_vars($args = array())
	{
		extract($args);
		$this->id = $list_id;
		$list_obj = $this->get_object(array("oid" => $list_id,"class_id" => CL_MAILINGLIST));
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=lists&action=gen_list&parent=$list_obj[parent]'>Listid</a> / <a href='orb.".$this->cfg["ext"]."?class=mlist&action=list_members&id=$list_id'>Listi liikmed</a> / Vali listi muutujad");
		$this->read_template("sel_vars.tpl");
		$this->l_vars = unserialize($list_obj["last"]);

		$this->db_query("SELECT * FROM objects WHERE class_id = 24 AND status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"var_name"	=> $row["name"], 
				"var_id"		=> $row["oid"],
				"var_ch"		=> checked($this->l_vars[$row["oid"]] == 1)
			));
			$line.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $line,
			"reforb" => $this->mk_reforb("submit_change_vars",array("list_id" => $list_id)),
		));
		return $this->parse();
	}

	function submit_change_vars($arr)
	{
		extract($arr);
		$this->id = $list_id;
		$this->l_vars = array();
		if (is_array($arr))
		{
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "ch_")
				{
					$this->l_vars[substr($k,3)] = $v;
				}
			}
		};
		$s = serialize($this->l_vars);
		$this->upd_object(array("oid" => $this->id, "last" => $s));
		return $this->mk_my_orb("change_vars",array("list_id" => $list_id));
	}

	function var_list()
	{
		$ret = array();
		if (is_array($this->l_vars))
		{
			reset($this->l_vars);
			while (list($k,$v) = each($this->l_vars))
			{
				if ($v == 1)
				{
					$ret[] = $k;
				}
			}
		}
		return $ret;
	}

	function db_remove_user($email)
	{
		$id = $this->db_fetch_field("SELECT id FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE ml_users.mail = '$email' AND status != 0 AND parent = $this->id","id");

		if ($id)
		{
			$this->delete_object($id);
		}
	}

	function export_members($args = array())
	{
		extract($args);
		header("Content-type: text/plain");
		$this->db_query("SELECT ml_users.mail as mail, objects.name as name FROM objects LEFT JOIN ml_users ON objects.oid = ml_users.id WHERE class_id = 17 AND status != 0 AND parent = '$list_id'");
		while ($row = $this->db_next())
		{
			echo $row["name"],",",$row["mail"],"\n";
		}

		exit;
	}

	function is_member($email)
	{
		$this->db_query("SELECT * FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE parent=$this->id AND status != 0 AND mail='$email'");
		if (($row = $this->db_next()))
		{
			return $row["id"];	
		}
		else
		{
			return false;
		}
	}

	function checkit($args = array())
	{
		extract($args);
		$this->read_template("checkit.tpl");
		$list_obj = $this->get_object(array("oid" => $list_id,"class_id" => CL_MAILINGLIST));
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=lists&action=gen_list&parent=$list_obj[parent]'>Listid</a> / <a href='orb.".$this->cfg["ext"]."?class=mlist&action=list_members&id=$list_id'>Listi liikmed</a> / Kontrolli listi liikmeid");

		$this->id = $list_id;

		// teeme listide nimekirja.
		$lar = array();
		$this->db_query("SELECT oid,name FROM objects WHERE class_id = 15 AND status != 0");
		while ($row = $this->db_next()) 
		{
			$lar[$row["oid"]] = $row["name"];
		}

		// mailide nimekirja. whee. loeme aga terve baasi korraga m2llu, mis ikka juhtub :p
		$mails =array();
		$this->db_query("SELECT objects.*,ml_mails.* FROM objects LEFT JOIN ml_mails ON ml_mails.id = objects.oid WHERE class_id = 20 AND status != 0");
		while ($row = $this->db_next()) 
		{
			$mails[$row["parent"]] = $row["sent"];
		}

		$this->vars(array("blid" => $this->id));

		// see küsib järjest kõiki käsiloleva listi liikmeid
		$this->db_query("SELECT objects.oid as oid, objects.name as name,ml_users.mail as mail,ml_users.is_cut as is_cut, ml_users.is_copied as is_copied FROM objects
			LEFT JOIN ml_users ON ml_users.id = objects.oid
			WHERE objects.parent = $this->id AND objects.status != 0 AND objects.class_id = 17
			GROUP BY objects.oid");


		while ($row = $this->db_next())
		{
			$this->save_handle(); 

			$ls = "";
			// iga liikme jaoks küsime infot selle kohta, millistest listides ta veel on
			$q = "SELECT objects.parent as list_id,objects.oid as oid FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE mail='".$row["mail"]."' AND objects.status != 0";
			$this->db_query($q);
			while ($us = $this->db_next())
			{
				if ($lar[$us["list_id"]] && $us["list_id"] != $this->id)
				{
					$this->vars(array(
						"list_id" => $us["list_id"], "list_name" => $lar[$us["list_id"]],
						"user_id" => $us["oid"],
						"mail_sent" => ($mails[$us["list_id"]] ? "Jah" : "Ei"),
						"mail_when" => ($mails[$us["list_id"]] ? $this->time2date($mails[$us["list_id"]],2) : ""),
						"delete_link" => $this->mk_my_orb("del_user",array("list_id" => $this->id,"user_id" => $us["oid"])),
						"list_link" => $this->mk_my_orb("list_members",array("id" => $us["list_id"])),
					));
					$ls.=$this->parse("LIST");
				}
			}
			$this->vars(array("user_name" => $row["name"], "user_mail" => $row["mail"], "LIST" => $ls));
			$hl = "";
			if ($ls != "")
			{
				$hl = $this->parse("HAS_LIST");
			}
			$this->vars(array(
				"HAS_LIST" => $hl,
				"delete_link" => $this->mk_my_orb("del_user",array("list_id" => $this->id,"user_id" => $row["oid"])),
			));
			$l.=$this->parse("LINE");
			$this->restore_handle();
			}
		$this->vars(array("LINE" => $l));
		return $this->parse();
	}

	function del_user($args = array())
	{
		extract($args);
		$this->delete_object($user_id,CL_MAILINGLIST_MEMBER);
		return $this->mk_my_orb("checkit",array("list_id" => $list_id));
	}

	function list_del($arr)
	{
		extract($arr);
		$list_obj = $this->get_object(array("oid" => $list_id,"class_id" => CL_MAILINGLIST));
		$this->mk_path(0,"<a href='orb.".$this->cfg["ext"]."?class=lists&action=gen_list&parent=$list_obj[parent]'>Listid</a> / Listi '$list_obj[name]' liikmed");

		$this->read_template("list_del.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_list_del", array("list_id" => $list_id))
		));
		return $this->parse();
	}

	function submit_list_del($arr)
	{
		extract($arr);

		$mails = explode("\n", $mails);
		foreach($mails as $ml)
		{
			$ml = trim($ml);
			if ($ml != "")
			{
				$mmid = $this->db_fetch_field("SELECT objects.oid as oid FROM objects
												 LEFT JOIN ml_users ON ml_users.id = objects.oid
												 WHERE objects.parent = $list_id AND objects.status != 0 AND objects.class_id = 17 
												 AND ml_users.mail = '$ml'", "oid");
				if ($mmid)
				{
					echo "kustutan aadressi $ml ($mmid) <br>";
					$this->delete_object($mmid);
				}
				else
				{
					echo "kasutajat aadressiga $ml pole selles listis! <Br>";
				}
			}
		}
		die("<a href='".$this->mk_my_orb("list_members", array("id" => $list_id))."'>Tagasi</a>");
	}
};
?>
