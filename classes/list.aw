<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/list.aw,v 2.12 2001/07/12 04:23:46 kristo Exp $
class mlist extends aw_template
{
	function mlist($id = 0)
	{
		$this->tpl_init("mailinglist");
		$this->db_init();
		$this->id = $id;
		$this->db_query("SELECT * FROM objects WHERE oid = $id");
		//if (!($row = $this->db_next()))
		//	$this->raise_error("mlist->mlist($id): no such list!",true);

		$this->name = $row["name"];
		$this->l_vars = unserialize($row["last"]);
		$this->vars(array("list_name" => $this->name, "list_id" => $this->id));
		lc_load("definition");

	}
	
	function list_users()
	{
		$this->read_template("list_users.tpl");
		
		$c=""; $cnt=0; $s = ""; 
		$this->db_query("SELECT objects.oid as oid, objects.name as name,ml_users.mail as mail,ml_users.is_cut as is_cut, ml_users.is_copied as is_copied,acl FROM objects
										 LEFT JOIN acl ON acl.oid = objects.oid
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE objects.parent = $this->id AND objects.status != 0 AND objects.class_id = 17
										 GROUP BY objects.oid");
		while ($row = $this->db_next())
		{
			if ($row["is_copied"] == 1)
				$bgk = "class='fgtext_copied'";
			else
			if ($row["is_cut"] == 1)
				$bgk = "class='fgtext_cut'";
			else
				$bgk = "class='fgtext'";

			$this->vars(array("user_name" => $row["name"], "user_id" => $row["oid"], "user_mail" => $row["mail"], "row" => $cnt,
													"cut"		=> $bgk));

			$ch = $this->parse("U_CHANGE");
			$ac = $this->parse("U_ACL");

			$this->vars(array("U_CHANGE" => $ch,"U_ACL" => $ac));

			$cnt++;
			$c.=	$this->parse("LINE");
			$s.= $this->parse("SELLINE");
		}
		$p = "";
		if ($this->db_fetch_field("SELECT count(*) as cnt from ml_users WHERE is_cut = 1 OR is_copied = 1", "cnt") > 0)
			$p = $this->parse("PASTE");

		$ua = $this->parse("U_ADD");
		$ui = $this->parse("U_IMPORT");
		$this->vars(array("LINE" => $c, "SELLINE" => $s, "PASTE" => $p,"U_ADD" => $ua,"U_IMPORT"=>$ui,"count" => $cnt));
		return $this->parse();
	}
	
	function add_user()
	{
		$this->read_template("add_user.tpl");
		$this->vars(array("user_id" => "", "user_name" => "", "user_mail" => ""));
		
		$ls = "";
		if (is_array($this->l_vars))
		{
			reset($this->l_vars);
			while(list($id,) = each($this->l_vars))
				if ($ls == "")
					$ls = $id;
				else
					$ls.=",".$id;
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
		$this->vars(array("VARS" => $c));
		return $this->parse();
	}
	// data[name], data[email], vars -> array(var_id, var_value), acl not checked
	function db_add_user($data, $vars = "")
	{
		extract($data);

		$this->db_query("SELECT * FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE parent=$this->id AND status != 0 AND mail='$email'");
		if (($row = $this->db_next()))
			return $row["id"];	// if such user exists, do not add another

		$user_id = $this->new_object(array("parent" => $this->id, "name" => $name, "class_id" => CL_MAILINGLIST_MEMBER,"status" => 2));
		$this->db_query("INSERT INTO ml_users(id,mail) VALUES($user_id, '$email')");

		if (gettype($vars) == "array")
		{
			reset($vars);
			while (list($var_id, $var_value) = each($vars))
				$this->db_query("INSERT INTO ml_var_values VALUES($var_id, $user_id, '".$var_value."')");
		}

		$this->_log("mlist",sprintf(LC_LIST_ADD_USER,$name,$this->name));
		return $user_id;
	}

	function add_user_submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		$va = new variables;
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
						$this->db_query("UPDATE ml_var_values SET value='".$$v."' WHERE var_id = $var_id AND user_id = $user_id");
					else
						$this->db_query("INSERT INTO ml_var_values VALUES($var_id, $user_id, '".$$v."')");
				}
			}
			$this->_log("mlist",sprintf(LC_LIST_CHANGED_USER,$name,$this->name));
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
	}

	////
	// !Lisab kasutaja yhte voi enamasse listi
	// uid (string) - kasutaja uid, keda lisatakse
	// name (string) -
	// email (string) -
	// lists (array of int) - listide id-d, kuhu kasutaja liita
	function add_user_to_lists($args = array())
	{	
		$this->quote($args);
		extract($args);
		$t = time();
		if (is_array($list_ids))
		{
			foreach($list_ids as $val)
			{
				$q = "INSERT INTO ml_users (name,mail,list_id,uid,tm)
					VALUES('$name','$email','$val','$uid',$t)";
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
				
	function change_user($uid)
	{
		$this->read_template("add_user.tpl");
		$this->db_query("SELECT objects.*,ml_users.mail as mail,acl FROM objects
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 LEFT JOIN acl ON acl.oid = objects.oid
										 WHERE objects.oid = $uid
										 GROUP BY objects.oid");
		if (!($row = $this->db_next()))
			$this->raise_error("mlist->change_user($uid): No such user!", true);

		
		$this->vars(array("user_id" => $row["oid"], "user_name" => $row["name"], "user_mail" => $row["mail"]));

		$ls = "";
		if (is_array($this->l_vars))
		{
			reset($this->l_vars);
			while(list($id,) = each($this->l_vars))
				if ($ls == "")
					$ls = $id;
				else
					$ls.=",".$id;
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
		$this->vars(array("VARS" => $c));
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
					$ids = substr($k, 3);
				else
					$ids.=",".substr($k,3);
			}
		}
		return $ids;
	}

	////
	// !Kustutab mingist listist kasutajaid
	function delete(&$arr)
	{
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
				if ($rows[$v] == 1)
					$this->delete_object($v);

			$this->_log("mlist",sprintf(LC_LIST_ERASED_USER,$this->name));
		}
	}

	function copy(&$arr)
	{
		$ids = $this->get_ids_from_vars(&$arr);

		$this->db_query("UPDATE ml_users SET is_copied = 0");
		$this->db_query("UPDATE ml_users SET is_cut = 0");

		if ($ids != "")
			$this->db_query("UPDATE ml_users SET is_copied = 1 WHERE id IN ($ids)");
	}

	function cut(&$arr)
	{
		$ids = $this->get_ids_from_vars(&$arr);

		$this->db_query("UPDATE ml_users SET is_cut = 0");
		$this->db_query("UPDATE ml_users SET is_copied = 0");

		if ($ids != "")
			$this->db_query("UPDATE ml_users SET is_cut = 1 WHERE id IN ($ids)");
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
				$vars[$vrow["var_id"]] = $vrow["value"];

			$this->db_add_user(array("name" => $row["name"], "email" => $row["mail"]), $vars);
			$this->restore_handle();
		}
	}

	function paste($list_id)
	{
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
	}

	function import_mails()
	{
		$this->read_template("import_emails.tpl");
		$this->vars(array("list_id" => $this->id));
		return $this->parse();
	}
	
	function import_mail_submit($arr)
	{
		extract($arr);

		global $pilt;
		$arr = file($pilt);
		
		reset($arr);
		while(list($num, $line) = each($arr))
		{	
			$line = str_replace("\n" , "", $line);
			list($name, $mail) = explode(",", $line);
			echo "Leidsin $name ( $mail )<br>";
			flush();
			$this->db_add_user(array("name" => $name, "email" => $mail));
		}			
		$this->_log("mlist",sprintf(LC_LIST_IMPORTED_USER,$this->name));
	}

	function change_vars($parent)
	{
		$this->read_template("sel_vars.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = 24 AND status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array("var_name"	=> $row["name"], 
												"var_id"		=> $row["oid"],
												"var_ch"		=> ($this->l_vars[$row[oid]] == 1 ? "CHECKED" : "")));
			$line.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $line, "list_id" => $this->id,"parent"=>$parent));
		return $this->parse();
	}

	function submit_change_vars($arr)
	{
		$this->quote(&$arr);
		$this->l_vars = array();
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			if (substr($k,0,3) == "ch_")
				$this->l_vars[substr($k,3)] = $v;
		}

		$s = serialize($this->l_vars);
		$this->upd_object(array("oid" => $this->id, "last" => $s));
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
					$ret[] = $k;
			}
		}
		return $ret;
	}

	function db_remove_user($email)
	{
		$id = $this->db_fetch_field("SELECT id FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE ml_users.mail = '$email' AND status != 0","id");

		if ($id)
			$this->delete_object($id);
	}

	function export_mails()
	{
		header("Content-type: text/plain");
		$this->db_query("SELECT ml_users.mail as mail, objects.name as name FROM objects LEFT JOIN ml_users ON objects.oid = ml_users.id WHERE class_id = 17 AND status != 0");
		while ($row = $this->db_next())
			echo $row["name"],",",$row["mail"],"\n";
	}

	function is_member($email)
	{
		$this->db_query("SELECT * FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE parent=$this->id AND status != 0 AND mail='$email'");
		if (($row = $this->db_next()))
			return $row[id];	
		else
			return false;
	}
};
?>
