<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/email.aw,v 2.3 2001/05/20 23:36:46 duke Exp $
// mailinglist saadetavate mailide klass

	class email extends aw_template
	{
		function email()
		{
			$this->db_init();
			$this->tpl_init("mailinglist");
			$this->sent = array();
		}

		function list_mails($parent)
		{
			$p = $this->get_object($parent);
			$this->read_template("list_mails.tpl");
			$this->vars(array("parent" => $parent));
			$c = "";
			$this->db_query("SELECT objects.*,ml_mails.* FROM objects
											 LEFT JOIN ml_mails ON ml_mails.id = objects.oid
											 WHERE objects.class_id = 20 AND objects.status != 0 AND objects.parent = $parent");
			while ($row = $this->db_next())
			{
				if ($row["mail_from_name"] != "")
					$from = $row["mail_from_name"]." &lt;".$row["mail_from"]."&gt;";
				else
					$from = $row["mail_from"];
				$this->vars(array("mail_id"					=> $row["id"],
													"mail_from"				=> $from,
													"mail_subj"				=> $row["subj"],
													"mail_sent"				=> ($row["sent"] == 0 ? "Ei" : "Jah"),
													"mail_sent_when"	=> ($row["sent"] == 0 ? "&nbsp;" : $this->time2date($row["sent"],2))));
				
				$mc = $this->parse("M_CHANGE");
				$md = $this->parse("M_DELETE");
				$ma = $this->parse("M_ACL");
				$ms = $this->parse("M_SEND");

				$this->vars(array(
						"M_CHANGE" => $mc,
						"M_DELETE" => $md,
						"M_ACL" => $ma,
						"M_SEND" => $ms,
						"checked" => ($p["last"] == $row["oid"]) ? "checked" : ""));

				$c.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $c,"id" => $parent));
			return $this->parse();
		}

		function submit_default($args = array())
		{
			extract($args);
			$q = "UPDATE objects SET last = '$default' WHERE oid = '$id'";
			$this->db_query($q);
		}

		function mk_ml_vars($parent)
		{
			$c="";
			$va = new mlist($parent);
			$vars = join(",",$va->var_list());
			if ($vars != "")
			{
				$this->db_query("SELECT objects.* FROM objects
												 WHERE objects.class_id = 18 AND objects.status != 0 AND objects.parent IN($vars)");

				while ($row = $this->db_next())
				{
					$this->vars(array("var_name" => "#".$row[name]."#"));
					$c.=$this->parse("LIST");
				}
			}

			return $c;
		}

		function new_mail($parent)
		{
			$this->read_template("mail.tpl");

			$c = $this->mk_ml_vars($parent);

			$s="";
			$va = new variables;
			$va->db_list_stamps();
			while ($row = $va->db_next())
			{
				$this->vars(array("stamp_name" => "#".$row[name]."#", "stamp_value" => $row[comment]));
				$s.=$this->parse("SLIST");
			}
			$this->vars(array("LIST" => $c, "SLIST" => $s, "mail_from" =>  "", "mail_subj" => "", "mail_content" => "", "mail_id" => "","parent" => $parent,"L_LIST" => "","mail_from_name"	=> ""));
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
				$this->_log("e-mail","Muutis meili $subject");
			}
			else
			{
				$mail_id = $this->new_object(array("parent" => $parent,"name" => "e-mail", "class_id" => CL_EMAIL));
				$this->db_query ("INSERT INTO ml_mails VALUES($mail_id, '$from' , '$subject' , '$contents' , 0,'$from_name')");
				$this->_log("e-mail","Lisas meili $subject");
			}

			if ($link_addr != "")
			{
				if (substr($link_addr,0,7) != "http://")
					$link_addr="http://".$link_addr;
				if (strpos($link_addr,"?") === false)
				{
					if (strpos(substr($link_addr,8),"/") === false)
						$link_addr.="/";
				}
				$this->register_object($mail_id,$link_addr,CL_MAIL_LINK);
				$this->_log("e-mail","Lisas meilile $subject lingi $link_addr");
			}
			return $mail_id;
		}

		function change_mail($id)
		{
			$this->read_template("mail.tpl");

			$this->db_query("SELECT * FROM objects WHERE parent = $id AND class_id = 25 AND status != 0 ORDER BY oid");
			$num=1;
			while ($row = $this->db_next())
			{
				$this->vars(array("link_name" => "#l".$num."#","link_addr" => $row[name]));
				$lll.=$this->parse("L_LIST");
				$num++;
			}

			$s="";
			$va = new variables;
			$va->db_list_stamps();
			while ($row = $va->db_next())
			{
				$this->vars(array("stamp_name" => "#".$row[name]."#", "stamp_value" => $row[comment]));
				$s.=$this->parse("SLIST");
			}

			$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $id");
			if (!($row = $this->db_next()))
				$this->raise_error("email->change_mail($id): No such e-mail!", true);

			$c=$this->mk_ml_vars($row[parent]);
			
			$this->vars(array("LIST" => $c, "SLIST" => $s, "mail_from" => $row[mail_from], "mail_subj" => $row[subj], "mail_content" => $row[contents], "mail_id" => $row[id],"parent" => $row[parent],"L_LIST" => $lll,"mail_from_name" => $row[mail_from_name]));
			return $this->parse();
		}

		function delete_mail($id)
		{
			$this->delete_object($id);
			$subject = $this->db_fetch_field("SELECT subj FROM ml_mails WHERE id = $id","subj");
			$this->_log("e-mail","Kustutas meili $subject");
		}

		function mail_preview($msg_id)
		{
			$this->read_template("mail_preview.tpl");
			$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $msg_id");
			if (!($mail = $this->db_next()))
				$this->raise_error("email->mail_preview($msg_id): no such email!", true);
			
			$this->db_query("SELECT objects.name as name, ml_users.* FROM objects
											 LEFT JOIN ml_users ON objects.oid = ml_users.id
											 WHERE objects.status != 0 AND objects.class_id = 17 AND objects.parent=".$mail[parent]);
			if (!($user = $this->db_next()))
				$this->raise_error("Systeemis pole yhtegi kasutajat, eelvaadet ei saa teha!", true);

			$this->mk_vars($msg_id);
			
			$c = $this->mk_mail($user[id], $mail[contents],$user[name], $user[mail],$msg_id);

			$c = $this->mk_stamps($c);
			$c = str_replace("\n", "<br>", $c);

			if ($mail[mail_from_name] != "")
				$from = $mail[mail_from_name]." &lt;".$mail[mail_from]."&gt;";
			else
				$from = $mail[mail_from];
			$this->vars(array("mail_from" => $from, "mail_subj" => $mail[subj], "mail_content" => $c));
			return $this->parse();
		}

		function mk_vars($mail_id)
		{
			unset($this->varis);
			$this->varis = array();
			$list_id = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = $mail_id","parent");
			$va = new mlist($list_id);
			$arr = $va->var_list();
			$vs = join(",",$arr);
			if ($vs != "")
			{
				$this->db_query("SELECT objects.* FROM objects WHERE objects.class_id = 18 AND objects.status != 0 AND objects.parent IN ($vs)");
				while ($row = $this->db_next())
				{
					$this->varis[$row[oid]] = $row[name];
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
				$this->db_query("SELECT value FROM ml_var_values WHERE var_id = $var_id AND user_id = $uid");
				$row = $this->db_next();
				
				$ret = str_replace("#".$var_name."#", $row[value], $ret);
			}
			
			// links
			$this->db_query("SELECT * FROM objects WHERE parent = $mail_id AND class_id = 25 AND status != 0 ORDER BY oid");
			$cnt = 1;
			while ($row = $this->db_next())
			{
				if (strpos($row[name],"?"))
					$ap = "&";
				else
					$ap = "?";

				$ret = str_replace("#l".$cnt."#",$row[name].$ap."artid=".$uid."&sid=".$mail_id,$ret);
				$cnt++;
			}

			return $ret;
		}
		
		function mk_stamps($ret)
		{
			$va = new variables;
			$va->db_list_stamps();
			while ($row = $va->db_next())
			{
				$ret=str_replace("#".$row[name]."#", $row[comment], $ret);
			}
			
			return $ret;
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
					echo "saatsin maili $row[mail]'le<br>";
					if ($cache)
					{
						$this->sent[$row["mail"]] = 1;
					};
					flush();
				};
			};
		}

			
		
		function send_mail($id)
		{
			$this->mk_vars($id);
			
			$this->db_query("SELECT ml_mails.*,objects.parent as parent FROM ml_mails LEFT JOIN objects ON objects.oid = ml_mails.id WHERE id = $id");
			if (!($mail = $this->db_next()))
				$this->raise_error("email->send_mail($id): No such e-mail!", true);
			
			$list_id=$mail[parent];
		
			echo "Saadan meile<br>";
			flush();
			
			$this->db_query("SELECT objects.oid as oid, objects.name as name,ml_users.mail as mail FROM objects
											 LEFT JOIN ml_users ON ml_users.id = objects.oid
											 WHERE parent = $list_id AND objects.status != 0 AND objects.class_id = 17");
			while ($row = $this->db_next())
			{
				$users[$row[oid]]["name"] = $row[name];
				$users[$row[oid]]["mail"] = $row[mail];
			}
			
			reset($users);
			while (list($user_id, $user) = each($users))
			{
				if (!$this->is_email($user[mail]))
					continue;

				if ($mail[mail_from_name] != "")
					$from = $mail[mail_from_name]." <".$mail[mail_from].">";
				else
					$from = $mail[mail_from];
					
				$f = popen("/usr/sbin/sendmail -f '$from' ".$user[mail], "w");
				fwrite($f, "From: $from\n");
				fwrite($f, "To: ".$user[name]." <".$user[mail].">\n");
				fwrite($f, "Return-Path: $from\n");
				fwrite($f, "Sender: $from\n");
				fwrite($f, "Subject: ".$mail[subj]."\n\n");
				
				$c = $this->mk_mail($user_id, $mail[contents], $user[name], $user[mail],$id);
				$c = $this->mk_stamps($c);
				
				$c = str_replace("\n","\n\r",$c);
				fwrite($f, "\n".$c."\n");
				pclose($f);
				echo "saatsin maili ", $user[name], "(" ,  $user[mail], ") 'le<br>";
				flush();
			}

			$this->db_query("INSERT INTO ml_sent VALUES($list_id, $id, ".time().")");
			$this->db_query("UPDATE ml_mails SET sent = ".time()." WHERE id = $id");
			$this->_log("e-mail","Saatis meili $mail[subj]");
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
				return true;
			else
				return false;
		}
	}
?>
