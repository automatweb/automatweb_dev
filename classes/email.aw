<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/email.aw,v 2.20 2002/09/23 17:47:27 duke Exp $
// mailinglist saadetavate mailide klass
class email extends aw_template
{
	function email()
	{
		$this->init("mailinglist");
		$this->sent = array();
		lc_load("definition");
		$this->lc_load("mailinglist","lc_mailinglist");
	}

	function list_mails($arr)
	{
		extract($arr);
		$p = $this->get_object($id);
		$this->mk_path(0,"<a href='".$this->mk_my_orb("gen_list", array(),"lists")."'>Listid</a> / Meilid");
		$this->read_template("list_mails.tpl");
		$this->vars(array("parent" => $id));
		$c = "";
		$this->db_query("SELECT objects.*,ml_mails.* FROM objects
										 LEFT JOIN ml_mails ON ml_mails.id = objects.oid
										 WHERE objects.class_id = 20 AND objects.status != 0 AND objects.parent = $id");
		while ($row = $this->db_next())
		{
			if ($row["mail_from_name"] != "")
			{
				$from = $row["mail_from_name"]." &lt;".$row["mail_from"]."&gt;";
			}
			else
			{
				$from = $row["mail_from"];
			}
			$this->vars(array(
				"mail_id"					=> $row["id"],
				"mail_from"				=> $from,
				"mail_subj"				=> $row["subj"],
				"mail_sent"				=> ($row["sent"] == 0 ? "Ei" : "Jah"),
				"mail_sent_when"	=> ($row["sent"] == 0 ? "&nbsp;" : $this->time2date($row["sent"],2)),
				"change" => $this->mk_my_orb("change", array("id" => $row["id"])),
				"delete_mail" => $this->mk_my_orb("delete", array("id" => $row["id"], "parent" => $id)),
				"send_mail" => $this->mk_my_orb("send_mail", array("id" => $row["id"], "parent" => $id)),
				"preview" => $this->mk_my_orb("preview", array("id" => $row["id"], "parent" => $id)),
				"print_mail" => $this->mk_my_orb("print", array("id" => $row["id"], "parent" => $id))
			));
			
			$mc = $this->parse("M_CHANGE");
			$md = $this->parse("M_DELETE");
			$ma = $this->parse("M_ACL");
			$ms = $this->parse("M_SEND");

			$this->vars(array(
				"M_CHANGE" => $mc,
				"M_DELETE" => $md,
				"M_ACL" => $ma,
				"M_SEND" => $ms,
				"checked" => checked($p["last"] == $row["oid"])
			));

			$c.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $c,"id" => $id,
			"reforb" => $this->mk_reforb("submit_default", array("id" => $id)),
			"new_mail" => $this->mk_my_orb("new_mail", array("parent" => $id))
		));
		return $this->parse();
	}

	function submit_default($args = array())
	{
		extract($args);
		$q = "UPDATE objects SET last = '$default' WHERE oid = '$id'";
		$this->db_query($q);
		return $this->mk_my_orb("list_mails", array("id" => $id));
	}

	function mk_ml_vars($parent)
	{
		$c="";
		classload("mlist");
		$va = new mlist($parent);
		$vars = join(",",$va->var_list());
		if ($vars != "")
		{
			$this->db_query("SELECT objects.* FROM objects
											 WHERE objects.class_id = 18 AND objects.status != 0 AND objects.parent IN($vars)");

			while ($row = $this->db_next())
			{
				$this->vars(array("var_name" => "#".$row["name"]."#"));
				$c.=$this->parse("LIST");
			}
		}

		return $c;
	}

	function new_mail($arr)
	{
		extract($arr);
		$this->read_template("mail.tpl");

		$this->mk_path(0,"<a href='".$this->mk_my_orb("gen_list", array(),"lists")."'>Listid</a> / <a href='".$this->mk_my_orb("list_mails", array("id" => $parent))."'>Meilid</a> / Lisa");
		$c = $this->mk_ml_vars($parent);

		$s="";
		classload("variables");
		$va = new variables;
		$va->db_list_stamps();
		while ($row = $va->db_next())
		{
			$this->vars(array("stamp_name" => "#".$row["name"]."#", "stamp_value" => $row["comment"]));
			$s.=$this->parse("SLIST");
		}
		$this->vars(array(
			"LIST" => $c, 
			"SLIST" => $s, 
			"parent" => $parent,
			"reforb" => $this->mk_reforb("submit_mail", array("parent" => $parent))
		));
		return $this->parse();
	}

	function save_mail($arr)
	{
		$this->quote(&$arr);
		extract($arr);
		
		$id = $mail_id;
		
		if ($mail_id != "")
		{
			$this->upd_object(array("oid" => $mail_id));
			$this->db_query("UPDATE ml_mails SET mail_from = '$from', mail_from_name='$from_name', subj = '$subject' , contents='$contents' WHERE id = $mail_id");
			$this->_log("e-mail",sprintf(LC_EMAIL_CHANGED_EMAIL,$subject));
		}
		else
		{
			$mail_id = $this->new_object(array("parent" => $parent,"name" => "e-mail", "class_id" => CL_EMAIL));
			$this->db_query ("INSERT INTO ml_mails VALUES($mail_id, '$from' , '$subject' , '$contents' , 0,'$from_name')");
			$this->_log("e-mail",sprintf(LC_EMAIL_ADD_EMAIL,$subject));
		}

		if ($link_addr != "")
		{
			if (substr($link_addr,0,7) != "http://")
			{
				$link_addr="http://".$link_addr;
			}
			if (strpos($link_addr,"?") === false)
			{
				if (strpos(substr($link_addr,8),"/") === false)
				{
					$link_addr.="/";
				}
			}
			$this->new_object(array("parent" => $mail_id,"name" => $link_addr,"class_id" => CL_MAIL_LINK));
			$this->_log("e-mail",sprintf(LC_EMAIL_ADD_MAIL_LINK,$subject,$link_addr));
		}

		if ($send_mail != "")
		{
			$ob = $this->get_object($mail_id);
			return $this->mk_my_orb("send_mail", array("id" => $mail_id, "parent" => $ob["parent"]));
		}
		else
		{
			return $this->mk_my_orb("change", array("id" => $mail_id));
		}
	}

	function change_mail($arr)
	{
		extract($arr);
		$this->read_template("mail.tpl");

		$this->db_query("SELECT * FROM objects WHERE parent = $id AND class_id = 25 AND status != 0 ORDER BY oid");
		$num=1;
		while ($row = $this->db_next())
		{
			$this->vars(array("link_name" => "#l".$num."#","link_addr" => $row["name"]));
			$lll.=$this->parse("L_LIST");
			$num++;
		}

		$s="";
		classload("variables");
		$va = new variables;
		$va->db_list_stamps();
		while ($row = $va->db_next())
		{
			$this->vars(array("stamp_name" => "#".$row["name"]."#", "stamp_value" => $row["comment"]));
			$s.=$this->parse("SLIST");
		}

		$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $id");
		if (!($row = $this->db_next()))
		{
			$this->raise_error(ERR_EMAIL_NOEMAIL,"email->change_mail($id): No such e-mail!", true);
		}
		$this->mk_path(0,"<a href='".$this->mk_my_orb("gen_list", array(),"lists")."'>Listid</a> / <a href='".$this->mk_my_orb("list_mails", array("id" => $row["parent"]))."'>Meilid</a> / Muuda");

		$c=$this->mk_ml_vars($row["parent"]);
		
		$this->vars(array(
			"LIST" => $c, 
			"SLIST" => $s, 
			"mail_from" => $row["mail_from"], 
			"mail_subj" => $row["subj"], 
			"mail_content" => $row["contents"], 
			"mail_id" => $row["id"],
			"parent" => $row["parent"],
			"L_LIST" => $lll,
			"mail_from_name" => $row["mail_from_name"],
			"reforb" => $this->mk_reforb("submit_mail", array("mail_id" => $id))
		));
		return $this->parse();
	}

	function delete_mail($arr)
	{
		extract($arr);
		$this->delete_object($id);
		$subject = $this->db_fetch_field("SELECT subj FROM ml_mails WHERE id = $id","subj");
		$this->_log("e-mail",sprintf(LC_EMAIL_ERASED_MAIL,$subject));
		header("Location: ".$this->mk_my_orb("list_mails", array("id" => $parent)));
	}

	function mail_preview($arr)
	{
		extract($arr);
		$msg_id = $id;
		$this->read_template("mail_preview.tpl");
		$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $msg_id");
		if (!($mail = $this->db_next()))
		{
			$this->raise_error(ERR_EMAIL_NOEMAIL,"email->mail_preview($msg_id): no such email!", true);
		}
		
		$this->db_query("SELECT objects.name as name, ml_users.* FROM objects
										 LEFT JOIN ml_users ON objects.oid = ml_users.id
										 WHERE objects.status != 0 AND objects.class_id = 17 AND objects.parent=".$mail[parent]);
		if (!($user = $this->db_next()))
		{
			$this->raise_error(ERR_EMAIL_NOUSER,LC_EMAIL_NO_USER_IN_SYSTEM, true);
		}

		$this->mk_vars($msg_id);
		
		$c = $this->mk_mail($user["id"], $mail["contents"],$user["name"], $user["mail"],$msg_id);

		$c = $this->mk_stamps($c);
		$c = str_replace("\n", "<br>", $c);

		if ($mail["mail_from_name"] != "")
		{
			$from = $mail["mail_from_name"]." &lt;".$mail["mail_from"]."&gt;";
		}
		else
		{
			$from = $mail["mail_from"];
		}
		$this->vars(array("mail_from" => $from, "mail_subj" => $mail["subj"], "mail_content" => $c));
		return $this->parse();
	}

	function mk_vars($mail_id)
	{
		unset($this->varis);
		$this->varis = array();
		$list_id = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = $mail_id","parent");
		classload("mlist");
		$va = new mlist($list_id);
		$arr = $va->var_list();
		$vs = join(",",$arr);
		if ($vs != "")
		{
			$this->db_query("SELECT objects.* FROM objects WHERE objects.class_id = 18 AND objects.status != 0 AND objects.parent IN ($vs)");
			while ($row = $this->db_next())
			{
				$this->varis[$row["oid"]] = $row["name"];
			}
		}
	}
	
	function mk_mail($uid, $msg, $name, $mail,$mail_id)
	{
		$ret = str_replace("#nimi#", $name, $msg);
		$ret = str_replace("#email#", $mail, $ret);
		$ret = str_replace("#kuupaev#", $this->time2date(time(),2), $ret);

		reset($this->varis);
		while (list($var_id, $var_name) = each($this->varis))
		{
			$q = "SELECT value FROM ml_var_values WHERE var_id = $var_id AND user_id = $uid";
			$this->db_query($q);
			$row = $this->db_next();
			
			$ret = str_replace("#".$var_name."#", $row["value"], $ret);

			if ($var_name == "kasutajanimi")
			{
				$_member_uid = $row["value"];
			};
		}

		$ret = $this->mk_pw_hash($ret,$_member_uid);


		
		// links
		$this->db_query("SELECT * FROM objects WHERE parent = $mail_id AND class_id = 25 AND status != 0 ORDER BY oid");
		$cnt = 1;
		while ($row = $this->db_next())
		{
			if (strpos($row["name"],"?"))
			{
				$ap = "&";
			}
			else
			{
				$ap = "?";
			}

			$ret = str_replace("#l".$cnt."#",$row["name"].$ap."artid=".$uid."&sid=".$mail_id,$ret);
			$cnt++;
		}

		return $ret;
	}
	
	function mk_stamps($ret)
	{
		classload("variables");
		$va = new variables;
		$va->db_list_stamps();
		while ($row = $va->db_next())
		{
			$ret=str_replace("#".$row["name"]."#", $row["comment"], $ret);
		}
		
		return $ret;
	}

	function mk_pw_hash($c,$uid)
	{
		if (strpos($c,"#pwhash#"))
		{
			$hash = substr($this->gen_uniq_id(),0,15);
			$c = str_replace("#pwhash#","a=$hash",$c);

			$data = array("uid" => $uid,"ts" => time());

			$_data = aw_serialize($data);		
			$this->quote($_data);

			$q = "REPLACE INTO storage (skey,data) VALUES ('$hash','$_data')";
			$this->db_query($q);
		};

		return $c;
	}

	////
	// !saadab meili mingi listi liikmetele
	// argumendid:
	// list_id (int) - listi ID
	// from - from
	// subject - subject
	// content - kirja sisu
	function mail_members($args = array())
	{
		extract($args);
		$q = "SELECT * FROM ml_users WHERE list_id = '$list_id'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if ($cache && (!$this->sent[$row["mail"]]))
			{
				$c = str_replace("#nimi#",$row["name"],$content);
				$f = popen("/usr/sbin/sendmail -f $from", "w");
				fwrite($f, "From: $from\n");
				fwrite($f, "To: ".$row["mail"] . ">\n");
				fwrite($f, "Return-Path: $from\n");
				fwrite($f, "Sender: $from\n");
				fwrite($f, "Subject: ".$subject."\n\n");
			
				fwrite($f, "\n".$c."\n");
				pclose($f); 
				echo sprintf(LC_EMAIL_SENT_MAIL,$row["mail"]);
				if ($cache)
				{
					$this->sent[$row["mail"]] = 1;
				};
				flush();
			};
		};
	}

	function get_members($args = array())
	{
		extract($args);
		classload("defs");
		$q = "SELECT ml_users.mail AS mail FROM objects LEFT JOIN ml_users ON (objects.oid = ml_users.id)
			WHERE objects.parent = '$list_id' AND status = 2";
		$this->db_query($q);
		$retval= array();
		while($row = $this->db_next())
		{
			$maddr = trim($row["mail"]);
			if (is_email($maddr))
			{
				$retval[] = $row["mail"];
			};
		};
		return $retval;
	}

	function get_member($args = array())
	{
		extract($args);
		if (not($mail))
		{
			return false;
		};
		$q = "SELECT * FROM ml_users LEFT JOIN objects ON (ml_users.id = objects.oid) 
			WHERE objects.parent = '$list_id' AND ml_users.mail = '$mail' AND status = 2";
		$this->db_query($q);
		return $this->db_next();
	}

		
	function send_mail($arr)
	{
		extract($arr);
		set_time_limit(0);
		$this->mk_vars($id);
		
		$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $id");
		if (!($mail = $this->db_next()))
		{
			$this->raise_error(ERR_EMAIL_NOEMAIL,"email->send_mail($id): No such e-mail!", true);
		}
		
		$list_id=$mail["parent"];
	
		echo LC_EMAIL_SENDIBG_EMAIL;
		flush();
		
		$this->db_query("SELECT objects.oid as oid, objects.name as name,ml_users.mail as mail FROM objects
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE parent = $list_id AND objects.status != 0 AND objects.class_id = 17");
		while ($row = $this->db_next())
		{
			$users[$row["oid"]]["name"] = $row["name"];
			$users[$row["oid"]]["mail"] = $row["mail"];
		}

		$send = false;
		reset($users);
		while (list($user_id, $user) = each($users))
		{
			if (!$this->is_email($user["mail"]))
			{
				continue;
			}

			if ($mail["mail_from_name"] != "")
			{
				$from = $mail["mail_from_name"]." <".$mail["mail_from"].">";
			}
			else
			{
				$from = $mail["mail_from"];
			}
				
/*				$f = popen("/usr/libexec/sendmail/sendmail -f '$from' ".$user[mail], "w");
			fwrite($f, "From: $from\n");
			fwrite($f, "To: ".$user[name]." <".$user[mail].">\n");
			fwrite($f, "Return-Path: $from\n");
			fwrite($f, "Sender: $from\n");
			fwrite($f, "Subject: ".$mail[subj]."\n\n");
			
			$c = $this->mk_mail($user_id, $mail[contents], $user[name], $user[mail],$id);
			$c = $this->mk_stamps($c);
			
			$c = str_replace("\r","",$c);
//				$c = str_replace("\n\n","\n\n\n",$c);
			fwrite($f, "\n".$c."\n");
			pclose($f);*/
			// now use smtp class to send the email

			$msg = "Return-path: $from\n";
			$msg.= "Date: ".gmdate("D, j M Y H:i:s T",time())."\n";
			$msg.= "From: $from\n";
			$msg.= "Return-Path: $from\n";
			$msg.= "Subject: ".$mail["subj"]."\n";
			$msg.= "To: ".$user["name"]." <".$user["mail"].">\n";
			$msg.= "Sender: $from\n";
			$msg.= "X-Mailer: Autom@tMail\n\n";
			$msg.= $content;
			$c = $this->mk_mail($user_id, $mail["contents"], $user["name"], $user["mail"],$id);
			$c = $this->mk_stamps($c);
			$c = str_replace("\r","",$c);
			$msg.= "\n".$c."\n";

			classload("smtp");
			$t = new smtp;
			$t->send_message(aw_ini_get("mail.smtp_server"), $mail["mail_from"], $user["mail"], $msg);

			echo LC_EMAIL_SENT_EMAIL3, $user["name"], "(" ,  $user["mail"], ") 'le<br>";
			flush();
		}

		$this->db_query("INSERT INTO ml_sent VALUES($list_id, $id, ".time().")");
		$this->db_query("UPDATE ml_mails SET sent = ".time()." WHERE id = $id");
		$this->_log("e-mail",sprintf(LC_EMAIL_SENT_MAIL2,$mail["subj"]));
	}
	
	function send_plain_mail($from, $to, $subj, $text)
	{
		$f = popen("/usr/sbin/sendmail -f ".$from." ".$to, "w");
		fwrite($f, "From: ".$from."\n");
		fwrite($f, "Return-Path: ".$from."\n");
		fwrite($f, "Sender: ".$from."\n");
		fwrite($f, "Subject: ".$subj."\n\n");
		fwrite($f, "\n".$text."\n");
		pclose($f);
	}

	function is_email($ml)
	{
		if (preg_match("/.*@.*/",$ml))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function orb_print($arr)
	{
		extract($arr);
		set_time_limit(0);
		$this->mk_vars($id);
		
		$this->read_template("print.tpl");

		$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $id");
		if (!($mail = $this->db_next()))
		{
			$this->raise_error(ERR_EMAIL_NOEMAIL,"email->print($id): No such e-mail!", true);
		}
		
		$list_id=$mail["parent"];
		$this->mk_path(0,"<a href='".$this->mk_my_orb("gen_list", array(),"lists")."'>Listid</a> / <a href='".$this->mk_my_orb("list_mails", array("id" => $list_id))."'>Meilid</a>");
	
		$this->db_query("SELECT objects.oid as oid, objects.name as name,ml_users.mail as mail FROM objects
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE parent = $list_id AND objects.status != 0 AND objects.class_id = 17");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"link" => $this->mk_my_orb("print_mail", array("id" => $id, "user_id" => $row["oid"])),
				"user" => $row["name"],
				"email" => $row["mail"]
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l
		));
		return $this->parse();
	}

	function orb_print_mail($arr)
	{
		extract($arr);
		set_time_limit(0);
		$this->mk_vars($id);
		
		$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $id");
		if (!($mail = $this->db_next()))
		{
			$this->raise_error(ERR_EMAIL_NOEMAIL,"email->print_mail($id): No such e-mail!", true);
		}
		
		$list_id=$mail["parent"];
	
		$this->db_query("SELECT objects.oid as oid, objects.name as name,ml_users.mail as mail FROM objects
										 LEFT JOIN ml_users ON ml_users.id = objects.oid
										 WHERE parent = $list_id AND objects.status != 0 AND objects.class_id = 17 AND oid = '$user_id'");
		while ($row = $this->db_next())
		{
			$c = $this->mk_mail($user_id, $mail["contents"], $row["name"], $row["mail"],$id);
			$c = $this->mk_stamps($c);
			$c = str_replace("\r","",$c);
			$msg.= "\n".$c."\n";
		}
		die("<pre>".$msg."</pre>");
	}
}
?>
