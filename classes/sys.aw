<?php
// $Header: /home/cvs/automatweb_dev/classes/sys.aw,v 2.43 2005/01/14 07:43:47 kristo Exp $
// sys.aw - various system related functions

class sys extends aw_template
{
	function sys($args = array())
	{
		$this->init("automatweb");
		$this->lc_load("syslog","lc_syslog");
	}

	/** Genereerib andmebaasi struktuuri väljundi XML-is 
		
		@attrib name=gen_db_struct params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/** Genereerib andmebaasi tabelite loomise sqli 
		
		@attrib name=gen_create_tbl params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=convimages params=name default="0"
		
		
		@returns
		
		
		@comment
		Lisab piltidele aliases

	**/
	function convimages($args = array())
	{
		extract($args);
		$q = "SELECT * FROM objects WHERE class_id = " . CL_DOCUMENT . " ORDER BY oid";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$name = strip_tags($row["name"]);
			print "found document $row[oid] $name, checking images<br />";
			$this->save_handle();
			$q = "SELECT * FROM objects WHERE parent = $row[oid] AND class_id = " . CL_IMAGE;
			$this->db_query($q);
			while($row2 = $this->db_next())
			{
				print "&nbsp;&nbsp;&nbsp;found image $row2[name]<br />";
				print "&nbsp;&nbsp;&nbsp;checking aliases<br />";
				$q = "SELECT * FROM aliases WHERE source = '$row[oid]' AND target = '$row2[oid]'";
				$this->save_handle();
				$this->db_query($q);
				$row3 = $this->db_next();
				if ($row3)
				{
					print "<b>Found alias</b><br />";
					print "<pre>";
					print_r($row3);
					print "</pre>";
				}
				else
				{
					print "<b>No such alias, creating</b><br />";
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

	/**  
		
		@attrib name=dbsync params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
			"www.ut.ee" => "www.ut.ee",
			"intranet.automatweb.com" => "intranet.automatweb.com",
			"www.notar.ee" => "www.notar.ee",
			"prisma.struktuur.ee" => "prisma.struktuur.ee",
			"koolitus.automatweb.com" => "koolitus.automatweb.com",
			"sven.dev.struktuur.ee" => "sven.dev.struktuur.ee",
			"otto.struktuur.ee" => "otto.struktuur.ee",
			"rate.automatweb.com" => "rate.automatweb.com",
			"www.kiosk.ee" => "www.kiosk.ee",
			"prisma.struktuur.ee" => "prisma.struktuur.ee",

		);
		
		$this->read_template("compare_db_step1.tpl");

		$this->vars(array(
			"donors" => $this->picker(-1,$files),
			"reforb" => $this->mk_reforb("db_compare_dbs",array()),
		));
		return $this->parse();
	}

	/** Võrdleb kahte gen_db_sync poolt genereeritud andmebaasi definitsiooni 
		
		@attrib name=db_compare_dbs params=name default="0"
		
		
		@returns
		
		
		@comment
		argumendid:
		left,right(array) - baaside definitsioonid
		vasakul on sisseloetud, ehk external definitsioon, pareml on kohalik baas, kus vajalike
		väljade juures on linnukesed.

	**/
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

	/**  
		
		@attrib name=submit_compare_db params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_compare_db($args = array())
	{
		global $donor_struct;
		$orig = $this->db_get_struct();
		extract($args);
		if (is_array($check))
		{
			foreach($check as $table => $fields)
			{
				if ($table == "syslog")
				{
					continue;
				}
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
							echo "line = $line <br />";
							$orig[$table] = array();
						}
						else
						{
							$line = "ALTER TABLE $table ADD $key $dr[type] $flags $autoinc";
						};
					};

					print "Q1: $line<br />";
					aw_global_set("__from_raise_error",1);
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
						print "Q2: $line<br />";
						$this->db_query($line);
					};
					//print "updating field $key of table $table<br />";
					//print "donor value is <pre>";
					//print_r($donor_struct[$table][$key]);
					//print "</pre>";

				};
			}

		};
		print "all done<br />";
		if (!$args["no_exit"])
		{
			exit;
		}
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

	/**

		@attrib name=check_indexes nologin="1"

	**/
	function do_check_indexes($arr)
	{
		$indexes = array(
			"objects" => array(
				"oid","class_id","status","site_id","lang_id","jrk","modified","created"
			),
			"aliases" => array(
				"source","target", "reltype"
			),
			"acl" => array(
				"gid", "oid"
			),
			"files" => array(
				"showal"
			)
		);

		echo "checking indexes.. <br>\n";
		flush();

		foreach($indexes as $tbl => $td)
		{
			echo ".. table $tbl <br>\n";
			flush();
			$has_idx = array();
			$this->db_list_indexes($tbl);
			while($idd = $this->db_next_index())
			{
				$has_idx[$idd["col_name"]] = $idd;
			}

			foreach($td as $field)
			{
				if (!isset($has_idx[$field]))
				{
					echo "missing index for table $tbl field $field, create stmt:<br>";
					$this->db_add_index($tbl, array(
						"name" => $field,
						"col" => $field
					));
				}
			}
		}

		die("all done! ");
	}

	/** checks if any objects of the given class exist in the current database
	
		@attrib name=has_objects nologin=1

		@param clid required type=int 

		@comment

			clid - the class id to check for
			can be used (with foreach_site) to check if a class can be safely removed
	**/
	function has_objects($arr)
	{
		$ol = new object_list(array(
			"class_id" => $arr["clid"],
			"lang_id" => array(),
			"site_id" => array()
		));

		if ($ol->count())
		{
			echo "<font color='red' size='7'>site ".aw_ini_get("baseurl")." HAS ".$ol->count()." objects!!!</font><br><br>";
		}
		else
		{
			echo "NEIN!!.";
		}
	}

	/**

		@attrib name=last_mod

	**/
	function last_mod()
	{
		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		$t->define_field(array(
			"name" => "class_id",
			"caption" => "Klass",
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => "Muutmise aeg",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i",
			"sortable" => 1
		));

		$ol = new object_list(array(
			"modified" => new obj_predicate_compare(OBJ_COMP_GREATER, time()-3600*24*3),
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "objects.modified DESC",
			"limit" => 400
		));
		$clss = aw_ini_get("classes");
		foreach($ol->arr() as $o)
		{
			$tmp = $o->modifiedby();
			$t->define_data(array(
				"name" => html::get_change_url($o->id(), array(), parse_obj_name($o->name())),
				"modifiedby" => $tmp->name(),
				"modified" => $o->modified(),
				"class_id" => $clss[$o->class_id()]["name"]
			));
		}
		$t->set_default_sortby("modified");
		$t->set_default_sorder("desc");
		$t->sort_by();
		return $t->draw();
	}

	/**

		@attrib name=perf

	**/
	function perf()
	{
		$data = array();
		$this->db_query("show status");
		while ($row = $this->db_next())
		{
			$data[$row["Variable_name"]] = $row["Value"];
		}

		echo "key cache hit rate: ".number_format((100 - (($data["Key_reads"]  / $data["Key_read_requests"]) * 100)),2)."%<br>";
		echo "open tables: ".$data["Open_tables"]." vs opened tables: ".$data["Opened_tables"]." <br>";
		echo "------------------AW <br>";
		echo "class count = ".count(aw_ini_get("classes"))." <br>";
		echo "------------------STATS <br>\n";
		flush();

		// slurp in files, count by date and site 
		for($i = 0; $i < 30; $i++)
		{
			$date = mktime(0,0,0, date("m"), date("d")-$i, date("Y"));
			$fn = aw_ini_get("basedir")."/files/logs/".date("Y-m-d", $date).".log";
			if (!file_exists($fn))
			{
				continue;
			}

			echo "<B>".date("d.m.Y", $date)."</b><br>\n";
			flush();
			$lines = file($fn);
			$sites = array();
			$urls = array();
			$sid2url = array();
			$total = count($lines);
			$total_time = 0;
			$times = array();
			$page_times = array();
			$tot_page_times = array();

			foreach($lines as $line)
			{
				list($dp, $tm, $sid, $bu, $url, $time) = explode(" ", $line);
				$sites[$sid]++;
				$sid2url[$sid] = $bu;
				$urls[$bu.$url]++;
				$time = (float)$time;
				$times[$sid] += $time;
				$total_time += $time;
				$page_times[] = $time;
				$page_t2p[] = $bu.$url;
				$tot_page_times[$bu.$url] += $time;
			}

			$avg_page_times = array();
			foreach($tot_page_times as $pg => $time)
			{
				$avg_page_times[$pg] = $time / $urls[$pg];
			}

			arsort($sites);
			arsort($urls);
			arsort($page_times);	
			arsort($avg_page_times);

			echo "total pageviews: $total<Br>total time taken: $total_time seconds <br>top sites: <br>";
			$num = 0;
			foreach($sites as $site => $cnt)
			{
				echo "site ".$sid2url[$site]." got $cnt pageviews and took a total of ".$times[$site]." seconds, average pv is ".($times[$site] / $cnt)." <Br>";
				if (++$num > 10)
				{
					break;
				}
			}
			echo "<br>total number of sites touched: ".count($sites)."<br>top urls: <br>";
			$num = 0;
			foreach($urls as $url => $cnt)
			{
				echo "url <a href='$url'>$url</a> got $cnt pageviews <Br>";
				if (++$num > 10)
				{
					break;
				}
			}

			echo "<br>top 20 longest pages by longest time: <br>";
			$num = 0;
			foreach($page_times as $idx => $time)
			{
				echo "page ".$page_t2p[$idx]." took $time seconds <br>";
				if (++$num > 20)
				{
					break;
				}
			}

			echo "<br>top 20 longest pages by average: <br>";
			$num = 0;
			foreach($avg_page_times as $url => $time)
			{
				echo "page $url took $time seconds on average (count = ".$urls[$url].")<br>";
				if (++$num > 20)
				{
					break;
				}
			}

			echo "------------------------------------<br>";
		}
		die();
	}

	/** tests database by adding all possible types of objects

		@attrib name=test_object_types

		@param parent required acl=view;add

	**/
	function test_object_types($arr)
	{
		$clss = aw_ini_get("classes");
		foreach($clss as $clid => $cldata)
		{
			echo "clid = $clid , name = $cldata[name] <br>\n";
			flush();
			$o = obj();
			$o->set_parent($arr["parent"]);
			$o->set_class_id($clid);
			$o->set_name($cldata["name"]);
			$o->save();
		}
		die("all done!! database seems to be relatively ok!");
	}
};
?>
