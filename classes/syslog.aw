<?php
// syslogi vaatamine ja analüüs
class db_syslog extends aw_template
{
	
	function db_syslog()
	{
		$this->db_init();
		$this->tpl_init("syslog");
	}
	
	function last_entries($limit)
	{
		$q = "SELECT * FROM syslog 
			ORDER BY syslog.tm DESC
			LIMIT $limit";
		$this->db_query($q);
	}

	function display_last_10()
	{
		// faking global muutujad?
		global $syslog_types,$syslog_params;
		$this->read_template("parts.tpl");
		if (is_array($syslog_types))
		{
			if (count($syslog_types) > 1)	// dummy on alati olemas, j2relikult kui midagi tshekitud veel on, siis on 2
			{
				$ss = "WHERE syslog.type IN (".join(",",$this->map("'%s'",$syslog_types)).")";
				reset($syslog_types);
				while(list(,$v) = each($syslog_types))
				{
					$this->vars(array($v."_sel" => "CHECKED"));
				}
			}
		}
		else
		{
			$ss = "";
		};
		$parts = $this->parse("selectors");
		$this->read_template("syslog.tpl");

		if (!is_array($syslog_params))
		{
			$syslog_params = array();
			$syslog_params[number] = 30;
		};

		global $number,$from, $to,$user,$ip_addr,$act,$update,$sortby,$uid_c,$email_c;
		if (isset($number))		$syslog_params[number] = $number;
		if (isset($from))			$syslog_params[from] = $from;
		if (isset($to))				$syslog_params[to] = $to;
		if (isset($user))			$syslog_params[user] = $user;
		if (isset($ip_addr))	$syslog_params[ip_addr] = $ip_addr;
		if (isset($act))			$syslog_params[act] = $act;
		if (isset($update))		$syslog_params[update] = $update;
		if (isset($sortby))		$syslog_params[sortby] = $sortby;
		if (isset($uid_c))		$syslog_params[uid_c] = $uid_c;
		if (isset($email_c))	$syslog_params[email_c] = $email_c;
		
		if ($syslog_params[update] < 1)
			$syslog_params[update] = 5;
			
		$this->vars(array("update" => $syslog_params[update]));

		$nums = array("-1" => "K&otilde;ik");
		$this->vars(array("number" => $syslog_params[number]));

		if ($syslog_params[from] != "")
		{
			list($f_day,$f_mon,$f_year) = explode("-",$syslog_params[from]);
			$f_t = mktime(0,0,0,$f_mon,$f_day,$f_year);
			if ($ss == "")
				$ss = "WHERE syslog.tm > $f_t ";
			else
				$ss .= " AND syslog.tm > $f_t ";
		};

		if ($syslog_params[to] != "")
		{
			list($t_day,$t_mon,$t_year) = explode("-",$syslog_params[to]);
			$t_t = mktime(0,0,0,$t_mon,$t_day,$t_year);
			if ($ss == "")
				$ss = "WHERE syslog.tm < $t_t ";
			else
				$ss .= " AND syslog.tm < $t_t ";
		};

		classload("users_user");
		$u = new users_user;
		$u->listall();
		// umh?
		$users = array("" => "");
		while ($ur = $u->db_next())
			$users[$ur[uid]] = $ur[uid];
		$this->vars(array("user" => $this->option_list($syslog_params[user],$users)));

		if ($ss == "")
			$ss = " WHERE uid LIKE '%".$syslog_params[user]."%' ";
		else
			$ss.=" AND uid LIKE '%".$syslog_params[user]."%' ";		

		if ($syslog_params[ip_addr] != "")
		{
			if ($ss == "")
				$ss ="WHERE ip LIKE '%".$syslog_params[ip_addr]."%' ";
			else
				$ss.=" AND ip LIKE '%".$syslog_params[ip_addr]."%' ";
		};

		if ($syslog_params[act] != "")
		{
			if ($ss == "")
				$ss = "WHERE ";
			else
				$ss.= " AND ";
			$ss.=" action LIKE '%".$syslog_params[act]."%' ";
		};
		
		$lim = "";
		if ($syslog_params[number] != -1)
		{
			if ($syslog_params[number] < 10)
			{
				$syslog_params[number] = 10;
				$lim = " LIMIT 10";
			}
			else
				$lim = " LIMIT ".$syslog_params[number];
		}
		
		if ($syslog_params[uid_c] != "")
		{
			if ($ss == "")
				$ss = " WHERE syslog.action LIKE '".$syslog_params[uid_c]."%' ";
			else
				$ss .= " AND syslog.action LIKE '".$syslog_params[uid_c]."%' ";
		}

		if ($syslog_params[email_c] != "")
		{
			if ($ss == "")
				$ss = " WHERE syslog.action LIKE '%(%".$syslog_params[email_c]."%)%' ";
			else
				$ss .= " AND syslog.action LIKE '%(%".$syslog_params[email_c]."%)%' ";
		}
		
		$q = "SELECT * FROM syslog 
			$ss
			ORDER BY syslog.tm DESC
			$lim";
		$blocked_ips = array_flip(unserialize($this->get_cval("blockedip")));
		$this->db_query($q);
	
    load_vcl("table");
    $t = new aw_table(array("prefix" 	=> "syslog", 
                            "sortby" 	=> $syslog_params[sortby],
                            "lookfor" => "",
                            "imgurl" 	=> $GLOBALS["baseurl"]."/images",
                            "self" 		=> $PHP_SELF));
    $t->parse_xml_def($GLOBALS["basedir"]."/xml/syslog.xml");
                                                                                            
		$content = "";
		while($row = $this->db_next())
		{
			$addr = $row[ip];
			preg_match("/(.*) \((.*)\) /",$row[action],$mat);
			$action = str_replace($mat[1]." (".$mat[2].") ","",$row[action]);
		
			if (!$blocked_ips[$addr])
			{
				$t->define_data(array(	"when" => $row[tm],
							"uid"	=> $row[uid],
							"action"	=> $action,
							"ip"		=> $addr,
							"parts"	=> $parts,
							"uid_c"	=> "&nbsp;".$mat[1]."&nbsp;",
							"email_c"	=> "&nbsp;".$mat[2]."&nbsp;"));
			};
		};
		
		$t->sort_by(array("field" => $sortby));
	
		if ($to && $from)
		{
			$pstring = "$from - $to";
		}
		else
		{
			$pstring = date("H:i");
		};
			$this->vars(array(	"LINE"	=> $t->draw(),
						"pstring"	=> $pstring,
						"from"	=> $syslog_params[from],
						"to"		=> $syslog_params[to],
						"ip_addr"	=> $syslog_params[ip_addr],
						"act"		=> $syslog_params[act],
						"parts"		=> $parts,
						"uid_c"	=> $syslog_params[uid_c],
						"email_c" => $syslog_params[email_c])); return
			$this->parse("MAIN");
	}
};
?>
