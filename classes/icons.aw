<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/icons.aw,v 2.32 2004/10/28 11:21:02 kristo Exp $

class icons extends aw_template
{
	function icons()
	{
		$this->init("automatweb/config");
		$this->sub_merge = 1;
		lc_load("definition");
	}

	/**  
		
		@attrib name=new params=name default="0"
		
		@param parent required
		@param alias_to optional
		@param return_url optional
		
		@returns
		
		
		@comment

	**/
	function orb_add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa ikoon");
		}
		else
		{
			$this->mk_path($parent,"Lisa ikoon");
		}
		$this->read_template("add_icon.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=icon_db params=name default="0"
		
		@param page optional
		@param grp optional
		
		@returns
		
		
		@comment

	**/
	function gen_db($arr)
	{	
		extract($arr);
		$this->read_template("icon_list.tpl");
		$this->mk_path(0,sprintf(LC_ICONS_SITE_CONFIG,$this->mk_my_orb("config", array(),"config")));

		if ($grp)
		{
			$ss = "WHERE grp_id = $grp";
		}
		else
		{
			$ss = "WHERE grp_id = 0 OR grp_id IS NULL";
		}

		$start = $page*$this->cfg["per_page"];
		$end = ($page+1)*$this->cfg["per_page"];
		$this->db_query("SELECT * FROM icons $ss ORDER BY id");
		$n = 0;
		while ($row = $this->db_next())
		{
			if (($n >= $start && $n <= $end) || $page == "all")
			{
				$this->vars(array(
					"page" => $page,
					"id" => $row["id"], 
					"name" => $row["name"], 
					"comment" => $row["comment"],
					"programm" => $row["programm"],
					"url" => 	$this->get_url($row),
					"change" => $this->mk_my_orb("change_icon", array("id" => $row["id"])),
					"delete" => $this->mk_my_orb("delete_icon", array("id" => $row["id"],"page" => $page))
				));
				$this->parse("LINE");
			}
			$n++;
		}

		// make pageselector
		$total = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM icons $ss", "cnt");
		$pages = $total/$this->cfg["per_page"];
		for ($i=0; $i < $pages; $i++)
		{
			$this->vars(array(
				"from" => $i*$this->cfg["per_page"], 
				"to" => min($total,($i+1)*$this->cfg["per_page"]), 
				"num" => $i,
				"grp" => $grp,
				"pg_url" => $this->mk_my_orb("icon_db", array("page" => $i,"grp" => $grp))
			));
			if ($i == $page && $page != "all")
			{
				$p.=$this->parse("SEL_PAGE");
			}
			else
			{
				$p.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"all_url" => $this->mk_my_orb("icon_db", array("page" => "all" ,"grp" => $grp))
		));
		$all = $this->parse("ALL");
		if ($page == "all")
		{
			$all = $this->parse("ALL_SEL");
		}
		$this->vars(array(
			"PAGE" => $p, 
			"SEL_PAGE" => "",
			"ALL" => $all,
			"ALL_SEL" => ""
		));

		// make grp listbox
		$icarr = array(0 => "");
		$this->db_query("SELECT * from icon_grps");
		while ($row = $this->db_next())
		{
			$icarr[$row["id"]] = $row["name"];
		}
		$this->vars(array(
			"grps" => $this->picker($grp,$icarr),
			"reforb" => $this->mk_reforb("export_icons"),
			"add_icon" => $this->mk_my_orb("add_icon"),
			"add_zip" => $this->mk_my_orb("add_zip")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=add_icon params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function add()
	{
		$this->mk_path(0,sprintf(LC_ICONS_SITE_CONFIG_ADD,$this->mk_my_orb("config", array(),"config"),$this->mk_my_orb("icon_db")));

		$this->read_template("add_icon.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_icon")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=add_zip params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function add_zip()
	{
		$this->mk_path(0,sprintf(LC_ICONS_SITE_CONFIG_ADD,$this->mk_my_orb("config", array(),"config"),$this->mk_my_orb("icon_db")));

		$this->read_template("add_icon_zip.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_icon_zip")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=change params=name default="0"
		
		@param id required
		@param return_url optional
		
		@returns
		
		
		@comment

	**/
	/**  
		
		@attrib name=change_icon params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function change($arr)
	{
		extract($arr);
		$ic = $this->get($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ikooni");
		}
		else
		{
			$this->mk_path($ic["parent"], "Muuda ikooni");
		}
		$this->read_template("change_icon.tpl");

		$this->vars(array(
			"ref" => $ic["url"], 
			"name" => $ic["name"], 
			"comment" => $ic["comment"], 
			"id" => $id,
			"meie"				=> checked($ic["kelle"] == "meie"),
			"nende"				=> checked($ic["kelle"] == "nende"),
			"puhastatud"	=> checked($ic["puhastatud"]),
			"praht"				=> checked($ic["praht"]),
			"m2kk"				=> checked($ic["opsys"] == "m2kk"),
			"winblows"		=> checked($ic["opsys"] == "winblows"),
			"l33nox"			=> checked($ic["opsys"] == "l33nox"),
			"p2rit"				=> $ic["p2rit"],
			"m2rks6nad"		=> $ic["m2rks6nad"],
			"programm"		=> $ic["programm"],
			"m2rks6nad2"		=> $ic["m2rks6nad2"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));

		return $this->parse();
	}

	function get($id)
	{
		if (function_exists("aw_cache_get") && is_array(aw_cache_get("icon_cache",$id)))
		{
			return aw_cache_get("icon_cache",$id);
		}

		$this->db_query("SELECT * FROM icons WHERE id = $id");
		$ret = $this->db_next(false);
		if ($ret == false)
		{
			return false;
		}

		$ob = $this->db_fetch_row("SELECT * FROM objects WHERE oid = '$id'");
		if (is_array($ob))
		{
			$ret += $ob;
		}

		$ret["url"] = $this->cfg["baseurl"]."/automatweb/icon.".$this->cfg["ext"]."?id=$id";

		aw_cache_set("icon_cache",$id,$ret);

		return $ret;
	}

	/**  
		
		@attrib name=show params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function show()
	{
		$arg = func_get_arg(0);
		if (is_array($arg))
		{
			extract($arg);
		}
		else
		{
			$id = $arg;
		};

		if (!$id)
		{
			header("Location: ".$this->cfg["baseurl"]."/automatweb/images/icon_aw.gif");
			die();
		}

		$ic = $this->get($id);
		if (!is_array($ic))
		{
			header("Location: ".$this->cfg["baseurl"]."/automatweb/images/icon_aw.gif");
			die();
		}
		header("Content-type: ".$ic["file_type"]);
		echo $ic["file"];
	}

	function get_url($row)
	{
		return $this->cfg["baseurl"]."/automatweb/icon.".$this->cfg["ext"]."?id=".$row["id"];
	}

	/**  
		
		@attrib name=delete_icon params=name default="0"
		
		@param id required
		@param page optional
		
		@returns
		
		
		@comment

	**/
	function delete($arr)
	{
		extract($arr);
		$this->db_query("DELETE FROM icons WHERE id = $id");
		header("Location: ".$this->mk_my_orb("icon_db", array("page" => $page)));
	}

	/**  
		
		@attrib name=del_icons params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function del_icons($arr)
	{
		extract($arr);
		if(is_array($sel))
		{
			foreach($sel as $icon_id)
			{
				$this->delete($icon_id);
			}
		}
		return $this->mk_my_orb("icon_db");
	}

	/**  
		
		@attrib name=sel_icon params=name default="0"
		
		@param rtype required
		@param rid required
		@param sstring optional
		@param sstring2 optional
		@param grp optional
		
		@returns
		
		
		@comment

	**/
	function sel_icon($arr)
	{
		extract($arr);
		global $kelle,$puhastatud,$praht,$opsys,$p2rit,$m2rks6nad,$m2rks6nad2,$search,$programm;
		$this->mk_path(0,sprintf(LC_ICONS_SITE_CONFIG_FIND,$this->mk_my_orb("config", array(),"config")));

		$this->read_template("search_icon.tpl");
		$this->vars(array(
			"rtype" => $rtype, "rid" => $rid, "sstring" => (!$search ? "%" : $sstring), "sstring2" => $sstring2,
			"meie"				=> checked($kelle == "meie"),
			"nende"				=> checked($kelle == "nende"),
			"puhastatud"	=> checked($puhastatud),
			"praht"				=> checked($praht),
			"m2kk"				=> checked($opsys == "m2kk"),
			"winblows"		=> checked($opsys == "winblows"),
			"l33nox"			=> checked($opsys == "l33nox"),
			"p2rit"				=> $p2rit,
			"programm"				=> $programm,
			"m2rks6nad"		=> $m2rks6nad,
			"m2rks6nad2"		=> $m2rks6nad2,
			"add" => $this->mk_my_orb("add_icon")
		));
		$icarr = array(0 => "");
		$this->db_query("SELECT * from icon_grps");
		while ($row = $this->db_next())
		{
			$icarr[$row["id"]] = $row["name"];
		}
		$this->vars(array("grps" => $this->picker($grp,$icarr)));

		if ($search)
		{
			$sp = array();
			if ($kelle != "")
			{
				$sp[] = " kelle = '$kelle' ";
			}
			if ($puhastatud != "")
			{
				$sp[] = " puhastatud = '$puhastatud' ";
			}
			if ($praht != "")
			{
				$sp[] = " praht = '$praht' ";
			}
			if ($opsys != "")
			{
				$sp[] = " opsys = '$opsys' ";
			}
			if ($p2rit != "")
			{
				$sp[] = " p2rit = '$p2rit' ";
			}
			if ($programm != "")
			{
				$sp[] = " programm = '$programm' ";
			}
			if ($m2rks6nad != "")
			{
				$sp[] = " m2rks6nad LIKE '%$m2rks6nad%' ";
			}
			if ($m2rks6nad2 != "")
			{
				$sp[] = " m2rks6nad2 LIKE '%$m2rks6nad2%' ";
			}
			if ($grp > 0)
			{
				$sp[] = " grp_id = $grp ";
			}

			$sps = join("AND",$sp);
			if ($sps != "")
			{
				$sps= "AND ".$sps;
			}

			$this->db_query("SELECT * FROM icons WHERE name LIKE '%$sstring%' AND comment LIKE '%$sstring2%' $sps");
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"], 
					"comment" => $row["comment"], 
					"id" => $row["id"], 
					"url" => $this->get_url($row),
					"select" => $this->mk_my_orb($rtype,array("id" => $rid,"icon_id" => $row["id"]),"config")
				));
				$this->parse("LINE");
			}
		}
		return $this->parse();
	}

	/**  
		
		@attrib name=export_icons params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function export($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			return;
		}

		$sels = join(",",$sel);
		$this->db_query("SELECT * FROM icons WHERE id IN ($sels)");
		header("Content-type: automatweb/icon-export");
		while ($row = $this->db_next(false))
		{
			$ret.= "\x01icon\x02\n".serialize($row)."\n";
		}
		die($ret);
	}

	function export_all()
	{
		$this->db_query("SELECT * FROM icons");
		header("Content-type: automatweb/icon-export");
		while ($row = $this->db_next(false))
		{
			$ret.= "\x01icon\x02\n".serialize($row)."\n";
		}
		return $ret;
	}

	/**  
		
		@attrib name=import_icons params=name default="0"
		
		@param level optional
		
		@returns
		
		
		@comment

	**/
	function import($arr)
	{
		extract($arr);
		if (!$level)
		{
			$this->mk_path(0,sprintf(LC_ICONS_SITE_CONFIG_IMPORT,$this->mk_my_orb("config", array(),"config")));
			$this->read_template("import_icons.tpl");
			$this->vars(array(
				"reforb" => $this->mk_reforb("import_icons", array("level" => 1))
			));
			return $this->parse();
		}
		else
		{
			global $fail;
			if (is_uploaded_file($fail))
			{
				if (!($f = fopen($fail,"r")))
				{
					$this->raise_error(ERR_ICONS_EOPEN,LC_ICONS_SOME_IS_WRONG,true);
				}
				$fc = fread($f,filesize($fail));
				fclose($f);
			}
			$this->core_import($fc);
			die();
		}
	}

	function core_import($fc)
	{
		// nyyd asume faili parsima. 
		// splitime ta lahti, eraldajaks on string \x01icon\x02\n
		$arr = explode("\x01icon\x02\n",$fc);
		reset($arr);
		list(,$v) = each($arr); // skipime tyhja
		while (list(,$v) = each($arr))
		{
			$v = unserialize($v);
			if (!$this->get_icon_by_file($v["file"]))
			{
				$this->add_array($v);
				$cnt++;
			}
		}
		return $this->mk_my_orb("icon_db",array());
//                echo sprintf(LC_ICONS_IMPORTED_ICONS,$cnt,$this->mk_my_orb("icon_db"));
	}

	////
	// adds a new icon to the database. the icon is described in the array
	function add_array($v)
	{
		$this->quote(&$v["file"]);
		$id = $this->db_fetch_field("SELECT MAX(id) as id FROM icons","id")+1;
		$this->db_query("INSERT INTO icons (id,name,comment,kelle,puhastatud,praht,opsys,p2rit,m2rks6nad,m2rks6nad2,programm,file,file_type) 
										VALUES($id,'$v[name]','$v[comment]','$v[kelle]','$v[puhastatud]','$v[praht]','$v[opsys]','$v[p2rit]','$v[m2rks6nad]','$v[m2rks6nad2]','$v[programm]','$v[file]','$v[file_type]')");
		return $id;
	}

	function get_icon_by_file($fila)
	{
		$this->quote(&$fila);
		$this->db_query("SELECT id FROM icons WHERE file = '$fila'");
		$row = $this->db_next();
		return $row["id"];
	}

	/**  
		
		@attrib name=submit_icon_zip params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function upload_zip($arr)
	{
		extract($arr);
		global $fail;
		$tmpdir = aw_ini_get("server.tmpdir");

		$cnt = 0;
		if ($fail != "none" && $fail != "")
		{
			// teeme temp. kataloogi kuhu faili lahti pakime. 
			$dir = $tmpdir."/".gen_uniq_id();
			if (!mkdir($dir,0777))
			{
				$this->raise_error(ERR_ICONS_NOTEMP,"Unable to create temp directory $dir !", true);
			}

			// siin konverdime
			$tdir = $tmpdir."/".gen_uniq_id();
			if (!mkdir($tdir,0777))
			{
				$this->raise_error(ERR_ICONS_NOTEMP,"Unable to create temp directory $tdir !", true);
			}

			$unzip_path = aw_ini_get("server.unzip_path");
			$op = `$unzip_path -o -j -d $dir $fail`;
			echo "<pre>".$op."</pre><br />";
			flush();

			$h = opendir($dir);
			while ($file = readdir($h))
			{
				echo sprintf(LC_ICONS_PROCESSING_FILE,$file);
				flush();
				if ($file != "." && $file != "..")
				{
					$ext = substr($file,strrpos($file,"."));
					// kui on vaja konvertida, siis konverdime. 
					if (strcasecmp($ext,".ico") == 0)
					{
						echo LC_ICONS_CONVERTING_FILE;
						// ok, this is da biaatch. 
						// ysnaga. convertiga teeme giffideks. tekib mitu giffi
						// siis identifyga tunneme 6ige 2ra
						$pfile = $this->convert_icon($dir,$file,$tdir);
						if (!$pfile)
						{
							// faili ei leidnud. kustutame selle 2ra ja j2tkame luupi.
							echo "file not found, deleting ",$dir."/".$file," and continuing loop <br />";
							unlink($dir."/".$file);
							continue;
						}
						$file = $pfile;
					}
					$ext = substr($file,strrpos($file,"."));
					// lisame baasi.
					// loeme faili sisse.
					$f = fopen($dir."/".$file,"r");
					$fc = fread($f,filesize($dir."/".$file));
					fclose($f);
					// k6igepealt tshekime et kas seda juba olemas pole.
					if (!$this->get_icon_by_file($fc))
					{
						// leiame faili tyybi.
						if (strcasecmp($ext,".gif") == 0)
						{
							$type = "image/gif";
						}
						else
						if (strcasecmp($ext,".jpg") == 0 || strcasecmp($ext,".jpeg") == 0)
						{
							$type = "image/jpeg";
						}
						else
						{
							rmdir($dir);
							$this->raise_error(ERR_ICONS_WTYPE,"image type not supported, only .gif/.jpg/.jpeg supported! file $file , ext = $ext", true);
						}

						// kui pole, siis lisame. 
						$this->add_array(array("file" => $fc, "file_type" => $type, "name" => substr($file,0,strrpos($file,"."))));
						$cnt++;
					}
					// kustutame faili et rmdir t88tax ka. 
					unlink($dir."/".$file);
				}
			}
			// kustutame kataloogi 2ra ka. 
			rmdir($dir);
			rmdir($tdir);
		}
		die(sprintf(LC_ICONS_IMPORTING,$cnt,$this->mk_my_orb("icon_db")));
	}

	function convert_icon($idir, $icon, $tdir)
	{
		$convert_dir = aw_ini_get("server.convert_dir");
		$identify_dir = aw_ini_get("server.identify_dir");

		$basename = substr($icon,0,strrpos($icon,"."));
		$nname = $basename.".gif";
		echo "nname = $nname <br />";
		$icon = $idir."/".$icon;
		$odir = $tdir."/".$nname;
		// now $tdir contains files $icon.gif.0 / $icon.gif.1 ...
		$op = `$convert_dir +adjoin "$icon" "$odir"`;
		echo "$convert_dir (convert +adjoin $icon $odir) result: <pre>$op</pre><br />";
		// find the right size image with identify
		$h = opendir($tdir);
		$found = false;
		$c_file = false;
		while ($file = readdir($h))
		{
			echo "scanning, file = $file <br />";
			// loopi ei l6peta 2ra kui faili leiame sellep2rast et vaja k6ik failid 2ra kustutada. 
			if ($file != "." && $file != "..")
			{
				if (!$found)
				{
					$fi = $tdir."/".$file;
					$e_fi = str_replace("/","\/",$fi);
					$op = `$identify_dir "$fi"`;
					echo "$identify_dir $fi result:<br /><pre>$op</pre><br />";
					$res = preg_match("/$e_fi (\d*)x(\d*)/",$op,$mat);

					echo "res = $res filesize (/$e_fi (\d*)x(\d*)/) match = ", $mat[1]," , ",$mat[2],"<br />";
					if ($mat[1] == "16" && $mat[2] == "16")
					{
						$found = true;
						$c_file = $file;
						echo "found!<br />";
						// now replace file.ico with file.gif
						unlink($icon);
						rename($fi,$idir."/".$nname);						
						echo "deleted $icon and moved $fi to ",$idir."/".$nname,"<br />";
						$c_file = $nname;
						// now also crop the image to the right size
						$nm = $idir."/".$nname;
						$op = `$convert_dir -crop 16x16 "$nm" "$nm"`;
						echo "crop result: $op <br />";
					}

					if ($mat[1] == "32" && $mat[2] == "32")
					{
						// uh, kui leiame 32x32 ikooni, siis konverdime selle 16x16x ja impordime ikka
						$found = true;
						$c_file = $file;
						echo "found!<br />";
						// now replace file.ico with file.gif
						unlink($icon);
						rename($fi,$idir."/".$nname);						
						echo "deleted $icon and moved $fi to ",$idir."/".$nname,"<br />";
						$c_file = $nname;
						// now also crop the image to the right size
						$nm = $idir."/".$nname;
						$op = `$convert_dir -geometry 16x16! "$nm" "$nm"`;
						echo "crop result: $op <br />";
						$op = `$convert_dir -crop 16x16 "$nm" "$nm"`;
						echo "size result: $op <br />";
					}
				}
				echo "deleting ",$tdir."/".$file,"<br />";
				@unlink($tdir."/".$file);
			}
		}
		if (!$found)
		{
			echo "!!!!!!!!!!!!!!!notfound '$icon'\n\n";
		}
		return $c_file;
	}

	/** aads the selected icons to a new group 
		
		@attrib name=grp_icons params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function grp_icons($arr)
	{
		extract($arr);
		if(is_array($sel))
		{
			$ics = join("|",$sel);
		}
		return $this->mk_my_orb("ic_grp_name", array("ics"=> $ics));
	}

	/** asks user new grp name 
		
		@attrib name=ic_grp_name params=name default="0"
		
		@param ics required
		
		@returns
		
		
		@comment

	**/
	function ic_grp_name($arr)
	{
		extract($arr);
		$this->read_template("add_icon_grp.tpl");

		$icarr = array(0 => "");
		$this->db_query("SELECT * from icon_grps");
		while ($row = $this->db_next())
		{
			$icarr[$row["id"]] = $row["name"];
		}
		$this->vars(array(
			"ics" => $ics,
			"grps" => $this->picker($grp,$icarr),
			"reforb" => $this->mk_reforb("submit_ic_grp", array("ics" => $ics))
		));
		return $this->parse();
	}

	/** creates the new group of icons 
		
		@attrib name=submit_ic_grp params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_ic_grp($arr)
	{
		extract($arr);
		$ics = explode("|",$ics);
		if ($act == "new")
		{
			$grp = $this->db_fetch_field("SELECT max(id) as id FROM icon_grps","id")+1;
			$this->db_query("INSERT INTO icon_grps(id,name) values($grp,'$name')");
		}
		$ics = join(",",$ics);
		$this->db_query("update icons set grp_id = $grp where id in($ics)");
		return $this->mk_my_orb("icon_db", array("grp" => $grp));
	}

	/** deletes icon group $id 
		
		@attrib name=del_grp params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function del_grp($arr)
	{
		extract($arr);
		$this->db_query("UPDATE icons SET grp_id = 0 WHERE grp_id = '$grp'");
		$this->db_query("DELETE FROM icon_grps WHERE id = $grp");
		return $this->mk_my_orb("icon_db");
	}

	////
	// !checks the icon url, to see if it complies to the rules. 
	// removes host part of url
	// this rewrites site/icon.aw to site/automatweb/icon.aw
	// prepends baseurl 
	function check_url($url)
	{
/*		if ($url == "")
		{*/
			return $url;
/*		}
		$retval = preg_replace("/^http:\/\/(.*)\//","/",$url);
		if (substr($retval,0,5) == "/icon")
		{
			// if the url refers to the site/icon, rewrite it to site/automatweb/icon
			$retval = aw_ini_get("baseurl")."/automatweb".$retval;
		}
		else
		{
			// prepend site baseurl, in case we are running in a subdirectory
			$retval = aw_ini_get("baseurl").$retval;
		}
		return $retval;*/
	}

	////
	// !Tagastab mingile klassile vastava ikooni
	function get_icon_url($arg1,$name = "")
	{
		if (is_object($arg1))
		{
			$clid = $arg1->class_id();
			$done = false;
			$done = $arg1->flags() & OBJ_IS_DONE;
		}
		else
		{
			$clid = $arg1;
		};

		if ($clid == CL_FILE)
		{
			$pi = pathinfo($name);
			return aw_ini_get("icons.server")."/ftype_".$pi["extension"].".gif";
		}
		else
		if (in_array($clid,array("promo_box","brother","conf_icon_other","conf_icon_programs","conf_icon_classes","conf_icon_ftypes","conf_icons","conf_jf","conf_users","conf_icon_import","conf_icon_db","homefolder","hf_groups","bugtrack")))
		{
			return aw_ini_get("icons.server")."/iother_".$clid.".gif";
		}
		else
		{
			$sufix = $done ? "_done" : "";
			return aw_ini_get("icons.server")."/class_".$clid.$sufix.".gif";
		}

		return aw_ini_get("baseurl")."/automatweb/images/icon_aw.gif";
	}
	
	function get_feature_icon_url($fid)
	{
		return aw_ini_get("icons.server")."/prog_".$fid.".gif";
	}

	/**  
		
		@attrib name=save_class_icons params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function save_class_icons()
	{
		classload("icons");
		$c = get_instance("config");
		$il = unserialize($c->get_simple_config("menu_icons"));

		$d = fopen(aw_ini_get("basedir")."/automatweb/images/icon_aw.gif", "r");
		$fc = fread($d, filesize(aw_ini_get("basedir")."/automatweb/images/icon_aw.gif"));
		fclose($d);

		reset(aw_ini_get("classes"));
		while (list($clid,$desc) = each(aw_ini_get("classes")))
		{
			if ($il["content"][$clid]["imgurl"] == "")
			{
				// save as aw icon
				//"/automatweb/images/icon_aw.gif" 
				echo "clid $clid icon is default icon_aw.gif <br />";

				$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/class_".$clid.".gif", "w");
				if (!$f)
				{
					echo "can!t open ya bassstardooo!! $clid<br />";
				}
				fwrite($f, $fc);
				fclose($f);
			}
			else
			{
				//echo "clid $clid data ".dbg::dump($il["content"][$clid])." <br />";
				// save!
				echo "writing clid $clids as ".aw_ini_get("basedir")."/automatweb/images/icons/class_".$clid.".gif <br />";
				$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/class_".$clid.".gif", "w");
				if (!$f)
				{
					echo "can!t open ya bassstardooo!! $clid<br />";
				}


				$ic = $this->get($il["content"][$clid]["imgid"]);
				fwrite($f, $ic["file"]);
				fclose($f);
			}
		}

		$c = get_instance("config");
		$d = unserialize($c->get_simple_config("file_icons"));
		echo dbg::dump($d);
		foreach($d as $ext => $dat)
		{
			$ext = str_replace(".", "", $ext);
			if ($dat["id"])
			{
				echo "writing ftype iconf for ext $ext as ".aw_ini_get("basedir")."/automatweb/images/icons/ftype_".$ext.".gif <br />";
				$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/ftype_".$ext.".gif", "w");
				if (!$f)
				{
					echo "can!t open ya bassstardooo!! $clid<br />";
				}


				$ic = $this->get($dat["id"]);
				fwrite($f, $ic["file"]);
				fclose($f);
			}
			else
			{
				echo "ftype $ext icon is default <br />";

				$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/ftype_".$ext.".gif", "w");
				if (!$f)
				{
					echo "can!t open ya bassstardooo!! $clid<br />";
				}
				fwrite($f, $fc);
				fclose($f);
			}
		}

		// other icons
		$ar = unserialize($c->get_simple_config("other_icons"));
		$v = $ar["promo_box"];
		if (!$v["id"])
		{
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_promo_box.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			fwrite($f, $fc);
			fclose($f);
		}
		else
		{
			echo "writing other iconf for ext $ext as ".aw_ini_get("basedir")."/automatweb/images/icons/iother_promo_box.gif <br />";
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_promo_box.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			$ic = $this->get($v["id"]);
			fwrite($f, $ic["file"]);
			fclose($f);
		}


		$v = $ar["brother"];
		if (!$v["id"])
		{
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_brother.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			fwrite($f, $fc);
			fclose($f);
		}
		else
		{
			echo "writing other iconf for ext $ext as ".aw_ini_get("basedir")."/automatweb/images/icons/iother_brother.gif <br />";
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_brother.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			$ic = $this->get($v["id"]);
			fwrite($f, $ic["file"]);
			fclose($f);
		}

		$v = $ar["homefolder"];
		if (!$v["id"])
		{
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_homefolder.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			fwrite($f, $fc);
			fclose($f);
		}
		else
		{
			echo "writing other iconf for ext $ext as ".aw_ini_get("basedir")."/automatweb/images/icons/iother_homefolder.gif <br />";
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_homefolder.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			$ic = $this->get($v["id"]);
			fwrite($f, $ic["file"]);
			fclose($f);
		}

		$v = $ar["hf_groups"];
		if (!$v["id"])
		{
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_hf_groups.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			fwrite($f, $fc);
			fclose($f);
		}
		else
		{
			echo "writing other iconf for ext $ext as ".aw_ini_get("basedir")."/automatweb/images/icons/iother_hf_groups.gif <br />";
			$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/iother_hf_groups.gif", "w");
			if (!$f)
			{
				echo "can!t open ya bassstardooo!! $clid<br />";
			}
			$ic = $this->get($v["id"]);
			fwrite($f, $ic["file"]);
			fclose($f);
		}

		$ar = aw_unserialize($c->get_simple_config("program_icons"));
		$prog = aw_ini_get("programs");
		foreach($prog as $prid => $pd)
		{
			if (!$ar[$prid]["id"])
			{
				// save as aw icon
				//"/automatweb/images/icon_aw.gif" 
				echo "prid $prid icon is default icon_aw.gif <br />";

				$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/prog_".$prid.".gif", "w");
				if (!$f)
				{
					echo "can!t open ya bassstardooo!! $clid<br />";
				}
				fwrite($f, $fc);
				fclose($f);
			}
			else
			{
				//echo "clid $clid data ".dbg::dump($il["content"][$clid])." <br />";
				// save!
				echo "writing prid $prid as ".aw_ini_get("basedir")."/automatweb/images/icons/prog_".$prid.".gif <br />";
				$f = fopen(aw_ini_get("basedir")."/automatweb/images/icons/prog_".$prid.".gif", "w");
				if (!$f)
				{
					echo "can!t open ya bassstardooo!! $clid<br />";
				}


				$ic = $this->get($ar[$prid]["id"]);
				fwrite($f, $ic["file"]);
				fclose($f);
			}
		}
	}

}
?>
