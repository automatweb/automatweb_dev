<?php
// $Header: /home/cvs/automatweb_dev/classes/syslog/syslog.aw,v 1.7 2005/04/21 08:54:57 kristo Exp $
// syslog.aw - syslog management
// syslogi vaatamine ja analüüs
class db_syslog extends aw_template
{
	function db_syslog()
	{
		$this->init("syslog");
		lc_load("definition");
		$this->lc_load("syslog","lc_syslog");
		$this->syslog_site_id = (aw_global_get("syslog_site_id")) ? aw_global_get("syslog_site_id") : $this->cfg["site_id"];
	}

	function display_sites()
	{
		$this->read_adm_template("sites.tpl");
		$old = aw_unserialize($this->get_cval("syslog_sites"));
		$c = "";
		$q = "SELECT distinct(site_id) FROM syslog";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if ($row["site_id"] > 0)
			{
				$this->vars(array(
					"id" => $row["site_id"],
					"name" => $old[$row["site_id"]],
					"active" => checked($row["site_id"] == $this->syslog_site_id),
				));
				$c .= $this->parse("line");
			};
		};
		$this->vars(array(
			"active" => checked(-1 == $this->syslog_site_id),
		));
		$c .= $this->parse("line");
		$this->vars(array(
			"line" => $c,
			"reforb" => $this->mk_reforb("save_sites")
		));
		$retval = $this->parse();
		return $retval;
	}

	function save_sites($args = array())
	{
		$names = $args["name"];
		$name_ser = aw_serialize($names,SERIALIZE_NATIVE);
		$conf = get_instance("config");
		$conf->set_simple_config("syslog_sites",$name_ser);
		global $syslog_site_id;
		$syslog_site_id = ($args["syslog_site_id"]) ? $args["syslog_site_id"] : $this->cfg["site_id"];
		session_register("syslog_site_id");
		return $this->mk_my_orb("site_id", array(), "syslog", false,true);
	}


	function display_last_10($arr)
	{
		global $syslog_types,$syslog_params,$filter_uid,$types;
		session_register("syslog_types");
		session_register("syslog_params");
		if (is_array($types))
		{
			$syslog_types = $types;
		};
		// faking global muutujad?
		$this->read_adm_template("parts.tpl");
		if (is_array($syslog_types))
		{
			if (count($syslog_types) > 1)	// dummy on alati olemas, j2relikult kui midagi tshekitud veel on, siis on 2
			{
				$ss = "WHERE syslog.type IN (".join(",",map("'%s'",$syslog_types)).")";
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
		$this->read_adm_template("syslog.tpl");

		if (!is_array($syslog_params))
		{
			$syslog_params = array();
			$syslog_params[number] = 30;
		};
			
		// limit the matches to last 2 days. Should speed up the query considerabely
		$tl = time() - (60 * 60 * 24 * 2);
		if ($ss == "")
		{
			$ss = "WHERE tm > $tl ";
		}
		else
		{
			$ss .= " AND tm < $tl ";
		};

		global $number,$from, $to,$user,$ip_addr,$act,$update,$sortby,$uid_c,$email_c;
		if (isset($number))		$syslog_params["number"] = $number;
		if (isset($from))			$syslog_params["from"] = $from;
		if (isset($to))				$syslog_params["to"] = $to;
		if (isset($user))			$syslog_params["user"] = $user;
		if (isset($ip_addr))	$syslog_params["ip_addr"] = $ip_addr;
		if (isset($act))			$syslog_params["act"] = $act;
		if (isset($update))		$syslog_params["update"] = $update;
		if (isset($sortby))		$syslog_params["sortby"] = $sortby;
		if (isset($uid_c))		$syslog_params["uid_c"] = $uid_c;
		if (isset($email_c))	$syslog_params["email_c"] = $email_c;

		if ($syslog_params["filter_uid"] != "" && $syslog_params["user"] == "")
		{
			$syslog_params["filter_uid"] = "";
			$GLOBALS["filter_uid"] = "";
		}

		if ($GLOBALS["filter_uid"] != "")
		{
			$syslog_params["filter_uid"] = $GLOBALS["filter_uid"];
			$syslog_params["user"] = $GLOBALS["filter_uid"];
		}

		if ($GLOBALS["filter_uid"] != "")
		{
			$fu = $GLOBALS["filter_uid"];
		}
		else
		{
			$fu = $syslog_params["filter_uid"];
		}
		$this->vars(array(
			"filter_uid" => $fu
		));

		if ($syslog_params["update"] < 1)
		{
			$syslog_params["update"] = 5;
		}
			
		$this->vars(array("update" => $syslog_params["update"]));

		$nums = array("-1" => LC_SYSLOG_ALL);
		$this->vars(array("number" => $syslog_params["number"]));

		if ($syslog_params["from"] != "")
		{
			list($f_day,$f_mon,$f_year) = explode("-",$syslog_params["from"]);
			$f_t = mktime(0,0,0,$f_mon,$f_day,$f_year);
			if ($ss == "")
			{
				$ss = "WHERE syslog.tm > $f_t ";
			}
			else
			{
				$ss .= " AND syslog.tm > $f_t ";
			}
		};

		if ($syslog_params["to"] != "")
		{
			list($t_day,$t_mon,$t_year) = explode("-",$syslog_params["to"]);
			$t_t = mktime(0,0,0,$t_mon,$t_day,$t_year);
			if ($ss == "")
			{
				$ss = "WHERE syslog.tm < $t_t ";
			}
			else
			{
				$ss .= " AND syslog.tm < $t_t ";
			}
		};

		$ol = new object_list(array(
			"class_id" => CL_USER,
			"site_id" => array(),
			"lang_id" => array()
		));
		$users = array("" => "");
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$users[$o->prop("uid")] = $o->prop("uid");
		}

		$this->vars(array("user" => $this->option_list($syslog_params["user"],$users)));

		if ($syslog_params["user"])
		{
			if ($ss == "")
			{
				$ss = " WHERE uid LIKE '%".$syslog_params["user"]."%' ";
			}
			else
			{
				$ss.=" AND uid LIKE '%".$syslog_params["user"]."%' ";		
			}
		};

		if ($syslog_params["ip_addr"] != "")
		{
			if ($ss == "")
			{
				$ss ="WHERE ip LIKE '%".$syslog_params["ip_addr"]."%' ";
			}
			else
			{
				$ss.=" AND ip LIKE '%".$syslog_params["ip_addr"]."%' ";
			}
		};

		if ($syslog_params["act"] != "")
		{
			if ($ss == "")
			{
				$ss = "WHERE ";
			}
			else
			{
				$ss.= " AND ";
			}
			$ss.=" action LIKE '%".$syslog_params["act"]."%' ";
		};
		
		$lim = "";
		if ($syslog_params["number"] != -1)
		{
			if ($syslog_params["number"] < 10)
			{
				$syslog_params["number"] = 10;
				$lim = " LIMIT 10";
			}
			else
			{
				$lim = " LIMIT ".$syslog_params["number"];
			}
		}
		
		if ($syslog_params["uid_c"] != "")
		{
			if ($ss == "")
			{
				$ss = " WHERE syslog.action LIKE '".$syslog_params["uid_c"]."%' ";
			}
			else
			{
				$ss .= " AND syslog.action LIKE '".$syslog_params["uid_c"]."%' ";
			}
		}

		if ($syslog_params["email_c"] != "")
		{
			if ($ss == "")
			{
				$ss = " WHERE syslog.action LIKE '%(%".$syslog_params["email_c"]."%)%' ";
			}
			else
			{
				$ss .= " AND syslog.action LIKE '%(%".$syslog_params["email_c"]."%)%' ";
			}
		}

		
		if ($this->cfg["has_site_id"] == 1 && $this->syslog_site_id != -1)
		{
			if ($ss == "")
			{
				$ss = " WHERE syslog.site_id = " . $this->syslog_site_id;
			}
			else
			{
				$ss .= " AND syslog.site_id = " . $this->syslog_site_id;
			};
		};


		
		$q = "SELECT * FROM syslog 
			$ss
			ORDER BY syslog.tm DESC
			$lim";

		$arr = unserialize($this->get_cval("blockedip"));
		$blocked_ips = (is_array($arr)) ? array_flip($arr) : array();

		$this->db_query($q);

    load_vcl("table");
    $t = new aw_table(array(
			"prefix" 	=> "syslog", 
		));
    $t->parse_xml_def($this->cfg["basedir"]."/xml/syslog.xml");
                                                                                            
		$content = "";
		while($row = $this->db_next())
		{
			list($addr,$ip) = inet::gethostbyaddr($row["ip"]);

			preg_match("/(.*) \((.*)\) /",$row["action"],$mat);
			if (!is_email($mat[2]))
			{
				$mat = array();
			}
			$action = str_replace($mat[1]." (".$mat[2].") ","",$row["action"]);
		
			if (!$blocked_ips[$ip])
			{
				$t->define_data(array(	
					"when" => $row["tm"],
					"uid"   => ($row["uid"]) ? "<b>$row[uid]</b>" : $row["tafkap"],
					"action"	=> $action,
					"ip"		=> "<a href=\"javascript:ipexplorer('".$addr."')\">".$addr."</a>",
					"parts"	=> $parts,
					"uid_c"	=> "&nbsp;".$mat[1]."&nbsp;",
					"email_c"	=> "&nbsp;".$mat[2]."&nbsp;"
				));
			};
		};
		
		$t->sort_by();
	
		if ($to && $from)
		{
			$pstring = "$from - $to";
		}
		else
		{
			$pstring = date("H:i");
		};
		$this->vars(array(	
			"LINE"	=> $t->draw(),
			"pstring"	=> $pstring,
			"from"	=> $syslog_params["from"],
			"to"		=> $syslog_params["to"],
			"ip_addr"	=> $syslog_params["ip_addr"],
			"act"		=> $syslog_params["act"],
			"parts"		=> $parts,
			"uid_c"	=> $syslog_params["uid_c"],
			"email_c" => $syslog_params["email_c"],
			"reforb" => $this->mk_reforb("show", array("no_reforb" => 1))
		)); 
		header("Refresh: ".($syslog_params["update"]*60).";url=".$this->mk_my_orb("show", array(),"syslog", false,true));
		$retval =  $this->parse("MAIN");
		return $retval;
	}
};

class syslog extends db_syslog
{
	function syslog()
	{
		$this->db_syslog();
	}

	/**  
		
		@attrib name=block params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function block($arr)
	{
		extract($arr);
		$this->read_adm_template("block.tpl");
		$old = unserialize($this->get_cval("blockedip"));
		$c = "";
		while(list($k,$v) = each($old))
		{
			$this->vars(array(
				"ip" => $v,
				"id" => $k,
				"checked" => "checked",
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
			"reforb" => $this->mk_reforb("saveblock")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=saveblock params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function saveblock($arr)
	{
		extract($arr);
		$old = unserialize($this->get_cval("blockedip"));
		$store = array();
		if (is_array($check))
		{
			while(list($k,$v) = each($check))
			{
				$store[] = $old[$k];
			};
		};
		if (inet::is_ip($new))
		{
			$store[] = $new;
		};
		$old_s = serialize($store);
		$this->quote($old_s);
		$q = "UPDATE config SET content = '$old_s' WHERE ckey = 'blockedip'";
		$this->db_query($q);
		return $this->mk_my_orb("block",array(),"syslog",false,true);
	}


	/**  
		
		@attrib name=convert_syslog params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function convert_syslog()
	{
		// fills the site_id field in syslog table
		$q = "SELECT oid FROM syslog GROUP BY (oid)";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			$q = "SELECT site_id FROM objects WHERE oid = '$row[oid]'";
			$this->db_query($q);
			$row2 = $this->db_next();
			$site_id = (int)$site_id;
			// just in case, avoid writing to the table while we are reading
			// from it. 
			$ids[$row["oid"]] = (int)$row2["site_id"];
			$this->restore_handle();
		};

		if (is_array($ids))
		{
			foreach($ids as $key => $val)
			{
				$q = "UPDATE syslog SET site_id = '$val' WHERE oid = '$key'";
				print $q;
				print "<br />";
			};
		};
	}
}
?>
