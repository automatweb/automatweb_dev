<?php
// $Header: /home/cvs/automatweb_dev/classes/sys.aw,v 2.18 2003/04/17 12:50:44 kristo Exp $
// sys.aw - various system related functions

class sys extends aw_template
{
	function sys($args = array())
	{
		$this->init("automatweb");
		$this->lc_load("syslog","lc_syslog");
	}

	////
	// !Genereerib andmebaasi struktuuri väljundi XML-is
	function gen_db_struct($args = array())
	{
		$tables = $this->db_get_struct();
		$xml = get_instance("xml",array("ctag" => "tabledefs"));
		$ser = $xml->xml_serialize($tables);
		header("Content-Type: text/xml");
		header("Content-length: " . strlen($ser));
		header("Content-Disposition: filename=awtables.xml");
		print $ser;
		exit;
	}

	////
	// !Genereerib andmebaasi tabelite loomise sqli
	function gen_create_tbl($args = array())
	{
		$ret = array();
		$tables = $this->db_get_struct();
		foreach($tables as $tblname => $tbldat)
		{
			$this->db_query("SHOW CREATE TABLE $tblname");
			$row = $this->db_next();
			$ret[$tblname] = $row["Create Table"];
		}
		$ser = aw_serialize($ret, SERIALIZE_XML);
		header("Content-Type: text/xml");
		header("Content-length: " . strlen($ser));
		header("Content-Disposition: filename=awtables.xml");
		print $ser;
		exit;
	}

	////
	// Lisab piltidele aliases
	function convimages($args = array())
	{
		extract($args);
		$q = "SELECT * FROM objects WHERE class_id = " . CL_DOCUMENT . " ORDER BY oid";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$name = strip_tags($row["name"]);
			print "found document $row[oid] $name, checking images<br>";
			$this->save_handle();
			$q = "SELECT * FROM objects WHERE parent = $row[oid] AND class_id = " . CL_IMAGE;
			$this->db_query($q);
			while($row2 = $this->db_next())
			{
				print "&nbsp;&nbsp;&nbsp;found image $row2[name]<br>";
				print "&nbsp;&nbsp;&nbsp;checking aliases<br>";
				$q = "SELECT * FROM aliases WHERE source = '$row[oid]' AND target = '$row2[oid]'";
				$this->save_handle();
				$this->db_query($q);
				$row3 = $this->db_next();
				if ($row3)
				{
					print "<b>Found alias</b><br>";
					print "<pre>";
					print_r($row3);
					print "</pre>";
				}
				else
				{
					print "<b>No such alias, creating</b><br>";
					$q = "INSERT INTO aliases (source,target,type)
						VALUES('$row[oid]','$row2[oid]',6)";
					$this->db_query($q);
				};
				$this->restore_handle();
			};
			$this->restore_handle();
		}
		exit;
	}
		

	////
	// !Laeb predefined serverist andmebaasi struktuuri, ning kuvab selle, ning kohaliku
	// andmebaasi vordleva tabeli
	// argumendid:
	// server(string) - serveri nimi
	// port(int) - port
	// url(string) - mida lugeda?

	// njaa, see peaks hakkama üle XML-RPC serveri käima tegelikult. But for now, 
	// we will do it this way.
	function db_sync($args = array())
	{
		extract($args);

		$server = ($server) ? $server : "aw.struktuur.ee";
		$port = ($port) ? $port : 80;

		$fp=fsockopen($server,$port,&$this->errno, &$this->errstr);
		if (!$fp)
		{
			$this->raise_error(ERR_DBSYNC_NOSERVER,"Failed opening connection to server $server",true);
		};

		$request = "http://" . $server . $url;
		$op = "GET $request HTTP/1.1\r\n";
		$op .= "User-Agent: Autom@tWeb\r\n";
		$op .= "Host: $server\r\n\r\n";

		if (!fputs($fp, $op, strlen($op))) 
		{
			$this->errstr="Write error";
			return 0;
    }
	  $ipd="";

		while($data=fread($fp, 32768)) 
		{
			$ipd.=$data;
		}

		list(,$res) = explode("\r\n\r\n",$ipd);
		
		// 1) laeme serverist andmebaasi struktuuri
		// 2) laeme kohalikust masinast baasi struktuuri
		// 3) kuvame vordleva tabeli, kuhu saab erinevate kirjete juurde checkboxe panna
		// 4) teeme muudatused. 
		// 5) Toome poest uue õlle
		return $res;
	}

	function db_compare_choose_donor($args = array())
	{
		$files = array(
			"work.struktuur.ee" => "work.struktuur.ee",
			"aw.struktuur.ee" => "aw.struktuur.ee",
			"idaviru.struktuur.ee" => "idaviru.struktuur.ee",
			"awvibe.struktuur.ee" => "awvibe.struktuur.ee",
			"horizon.struktuur.ee" => "horizon.struktuur.ee",
			"star.automatweb.com" => "star.automatweb.com",
			"arin.struktuur.ee" => "arin.struktuur.ee",
		);
		
		$this->read_template("compare_db_step1.tpl");

		$this->vars(array(
			"donors" => $this->picker(-1,$files),
			"reforb" => $this->mk_reforb("db_compare_dbs",array()),
		));
		return $this->parse();
	}

	////
	// !Võrdleb kahte gen_db_sync poolt genereeritud andmebaasi definitsiooni
	// argumendid:
	// left,right(array) - baaside definitsioonid
	// vasakul on sisseloetud, ehk external definitsioon, pareml on kohalik baas, kus vajalike
	// väljade juures on linnukesed.
	function _db_compare_dbs($args)
	{
		extract($args);
		$right = $this->db_get_struct();
		$block = $this->db_sync(array(
			"server" => $donor,
			"url" => "/?class=sys&action=gen_db_struct",
		));
		$xml = get_instance("xml",array("ctag" => "tabledefs"));
		global $donor_struct;
		$dsource = $xml->xml_unserialize(array("source" => $block));
		$donor_struct = $dsource;
		session_register("donor_struct");
		$all_keys = array_merge(array_flip(array_keys($dsource)),array_flip(array_keys($right)));
		ksort($all_keys);
		$this->read_template("compare_db.tpl");
		$c = "";
		foreach($all_keys as $key => $value)
		{
			$c .= $this->_db_compare_tables($key,$dsource[$key],$right[$key]);
		};
		$this->vars(array(
			"block" => $c,
			"reforb" => $this->mk_reforb("submit_compare_db",array()),
		));
		print $this->parse();
		exit;
	}

	////
	// !Is called from _db_compare_db-s for each invidual table	
	function _db_compare_tables($name,$arg1,$arg2)
	{
		// koigepealt leiame siis molema tabelidefinitsiooni väljade nimed, ning
		// moodustame neist ühise array
		if (is_array($arg1) && is_array($arg2))
		{
			$all_keys = array_merge(array_flip(array_keys($arg1)),array_flip(array_keys($arg2)));
		}
		elseif (is_array($arg1))
		{
			$all_keys = array_flip(array_keys($arg1));
		}
		else
		{
			$all_keys = array_flip(array_keys($arg2));
		};
		ksort($all_keys);
		global $left,$right;
		$gproblems = 0;
		global $problems;
		$problems = 0;
		$c = "";
		$this->vars(array("name" => $name));

		foreach($all_keys as $key => $val)
		{
			list($typematch,$flagmatch,$keymatch) = $this->_db_compare_fields($arg1[$key],$arg2[$key]);
			$color1 = ($typematch) ? "#FFFFFF" : "#FFCCCC";
			$color2 = ($keymatch)  ? "#FFFFFF" : "#FFCCCC";
			$color3 = ($flagmatch) ? "#FFFFFF" : "#FFCCCC";
			$flags1 = is_array($arg1[$key]["flags"]) ? join(" ",$arg1[$key]["flags"]) : "";
			$flags2 = is_array($arg2[$key]["flags"]) ? join(" ",$arg2[$key]["flags"]) : "";

			// kui koik matchivad, siis pole checkboxi vaja kuvada
			if ($typematch && $flagmatch && $keymatch)
			{	
				$check = "";
			}
			else
			{
				// ehk siis, kui doonoris vastav väli olemas on, siis teeme selle operatsiooni.
				if ($arg1[$key]["type"])
				{
					$check = "checked";
				}
				else
				{
					$check = "";
				};
			};

			$this->vars(array(
				"key" => $key,
				"color1" => $color1,
				"color2" => $color2,
				"color3" => $color3,
				"name" => $name,
				"type1" => $arg1[$key]["type"],
				"key1" => $arg1[$key]["key"],
				"flags1" => $flags1,
				"type2" => $arg2[$key]["type"],
				"key2" => $arg2[$key]["key"],
				"flags2" => $flags2,
				"checked" => $check,
			));
			$c .= $this->parse("block.line");
		};
		$this->vars(array(
			"line" => $c,
		));
		return $this->parse("block");
	}

	////
	// !Is called from _db_compare_tables for each invidual field
	function _db_compare_fields($field1,$field2)
	{
	  global $problems;
		if ($field1["type"] == $field2["type"])
		{
			$res1 = true;
		}
		else
		{
			$problems++;
			$res1 = false;
		};
 
		$flags1 = (is_array($field1["flags"])) ? join(",",$field1["flags"]) : "";
		$flags2 = (is_array($field2["flags"])) ? join(",",$field2["flags"]) : "";
		$res2 = ( $flags1 == $flags2 );
		if (not($res2))
		{
			$problems++;
		};

		if ( isset($field1["key"]) == isset($field2["key"]) )
		{
			$res3 = ( $field1["key"] == $field2["key"] );
		}
		else
		{
			$problems++;
			$res3 = false;
		};
 
		return array($res1,$res2,$res3);
	}

	function submit_compare_db($args = array())
	{
		global $donor_struct;
		$orig = $this->db_get_struct();
		extract($args);
		if (is_array($check))
		{
			foreach($check as $table => $fields)
			{
				foreach($fields as $key => $val)
				{
					$dr = $donor_struct[$table][$key];
					$og = $orig[$table][$key];
					if (is_array($dr["flags"]))
					{
						$flags = join(" ",$dr["flags"]);
					}
					else
					{
						$flags = "";
					};

					$prim_key_added = false;

					if ($og["type"])
					{
						// kui lokaalsel koopial on index ja remote pole,
						// siis igal juhul lisame me lokaasele NOT NULL votme
						if ($og["key"])
						{
							if (strpos($flags,"NOT NULL") === false)
							{
								$flags .= " NOT NULL";
							};
						};
						$line = "ALTER TABLE $table CHANGE $key $key $dr[type] $flags";
					}
					else
					{
						if (is_array($dr["flags"]) && in_array("auto_increment",$dr["flags"]))
						{
							$flags = str_replace("auto_increment","",$flags);
							// primary keys NEED not null
							if (not(in_array("NOT NULL",$dr["flags"])))
							{	
								$flags .= " NOT NULL";
							};
							$autoinc = " PRIMARY KEY";
							$prim_key_added = true;
						}
						else
						{
							$autoinc = "";
						};

						if (not(is_array($orig[$table])))
						{
							$line = "CREATE table $table ($key $dr[type] $flags $autoinc)";
							echo "line = $line <br>";
							$orig[$table] = array();
						}
						else
						{
							$line = "ALTER TABLE $table ADD $key $dr[type] $flags $autoinc";
						};
					};

					print "Q1: $line<br>";
					$this->db_query($line);
					$line = "";
					if ( ($dr["key"] == "PRI") && ($prim_key_added == false))
					{
						$line = "ALTER TABLE $table ADD PRIMARY KEY ($key)";
					}
					elseif ($dr["key"] == "MUL")
					{
						$line = "ALTER TABLE $table ADD KEY ($key)";
					};
					if ($line)
					{
						print "Q2: $line<br>";
						$this->db_query($line);
					};
					//print "updating field $key of table $table<br>";
					//print "donor value is <pre>";
					//print_r($donor_struct[$table][$key]);
					//print "</pre>";

				};
			}

		};
		print "all done<br>";
		if (!$args["no_exit"])
		{
			exit;
		}
	}

	////
	// !Calls the check_enviroment methods of all listed modules
	// could be handy to check whether the system is configured correctly
	function check($args = array())
	{
		// modules whose check_environment functions are called
		$modules = array(
			"file",
			"formgen/form_output",
			"accessmgr",
			"acl",
			"acl_base",
			"variables",
			"users_user",
			"users",
			"table",
			"syslog",
			"style",
			"stat",
			"shop_stat",
			"shop_item",
			"shop_eq"
		);
		$messages = "";
		foreach($modules as $module)
		{
			echo "checking $module ... <br>";
			flush();
			$t = get_instance($module);
			$_msg = $t->check_environment(&$this,$args["fix"]);
			if ($_msg)
			{
				$messages .= "Module '$module' reported the following errors<br>\n";
				$messages .= $_msg;
				$messages .= "<br>\n<br>\n";
			};
		};
		if ($messages)
		{
			print $messages;
			print "Please correct the above errors before proceeding<br>";
		}
		else
		{
			print "Everything seems to be ok!";
		};
		exit;
	}

	////
	// !this function takes any number of table definition arrays and checks if the tables really exist in the database
	// if $fix is true, then it also creates the missing tables and tries to fix the broken ones
	function check_db_tables($arr,$fix=false)
	{
		foreach($arr as $op_table)
		{
			$cur_table = $this->db_get_table($op_table["name"]);

			if ($cur_table != $op_table)
			{
				$s1 = $this->db_print_table($op_table); 
				$s2 = $this->db_print_table($cur_table); 
				if (!$fix)
				{
					return "Table ".$op_table["name"]." differs from correct version, must have: <br>".$s1."<br>got:<br>".$s2."<br>";
				}
				else
				{
					$this->db_sync_tables($op_table,$op_table["name"]);
					return "Table ".$op_table["name"]." differs from correct version, modified from: <br>".$s1."<br>to:<br>".$s2."<br>";
				}
			}
		lc_load("definition");}
	}

	////
	// !this function tries to read all the templates in $arr and complains if it cannot do that
	// it tries to find the templates in the automatweb folder
	function check_admin_templates($td, $arr)
	{
		$tpldir = $this->cfg["basedir"] . "/templates/".$td;
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($tpldir."/".$tpl) && is_readable($tpldir."/".$tpl)))
			{
				$ret.="Cannot open template ".$tpldir."/".$tpl." <br>";
			}
		}
		return $ret;
	}

	////
	// !this function tries to read all the templates in $arr and complains if it cannot do that
	// it tries to find the templates in the site's folder
	function check_site_templates($td,$arr)
	{
		$td=$this->cfg["tpldir"]."/".$td;
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($td."/".$tpl) && is_readable($td."/".$tpl)))
			{
				$ret.="Cannot open template ".$td."/".$tpl." <br>";
			}
		}
		return $ret;
	}

	////
	// !this function checks if all the files are readable and exist
	function check_site_files($arr)
	{
		$site_basedir = $this->cfg["site_basedir"]; 
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($site_basedir."/public/".$tpl) && is_readable($site_basedir."/public/".$tpl)))
			{
				$ret.="Cannot find file ".$site_basedir."/public/".$tpl." <br>";
			}
		}
		return $ret;
	}

	////
	// !this function checks if all the xml defs are readable and exist
	function check_orb_defs($arr)
	{
		$basedir = $this->cfg["basedir"];
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($basedir."/xml/orb/".$tpl.".xml") && is_readable($basedir."/xml/orb/".$tpl.".xml")))
			{
				$ret.="Cannot find file ".$basedir."/xml/orb/".$tpl.".xml <br>";
			}
		}
		return $ret;
	}

	////
	// !updates the syslog table to contain information about site_id-s where possible (pageview actions)
	function conv_syslog_site_id($args = array())
	{
		$q = "SELECT oid,count(*) AS cnt FROM syslog GROUP BY oid ORDER BY cnt DESC";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			print "oid = $row[oid], cnt = $row[cnt]<br>";
		};

	}

	function get_table($args = array())
	{
		extract($args);
		$table = $this->db_get_table("objects");
		print "<pre>";
		print_r($table);
		print "</pre>";
		exit;
	}

	function on_site_init(&$dbi, $site, $ini_opts, &$log)
	{
		// no need to dbsync if we are not creating a new site
		if (!$site['site_obj']['use_existing_database'])
		{
			// do a dbsync from aw.struktuur.ee
			$block = $this->db_sync(array(
				"server" => "aw.struktuur.ee",
				"url" => "/?class=sys&action=gen_create_tbl",
			));
			$tbls = aw_unserialize($block);

			foreach($tbls as $tbl => $sql)
			{
				$dbi->db_query($sql);
			}

			$log->add_line(array(
				"uid" => aw_global_get("uid"),
				"msg" => "L&otilde;i saidi andmebaasi tabelid",
				"comment" => "",
				"result" => "OK"
			));
		}
	}
};
?>
