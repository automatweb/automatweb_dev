<?php
define(PER_PAGE,100);

class icons extends aw_template
{
	function icons()
	{
		$this->tpl_init("automatweb/config");
		$this->db_init();
		$this->sub_merge = 1;
	}

	function gen_db($page)
	{	
		$this->read_template("icon_list.tpl");
		global $ext;
		$this->mk_path(0,"<a href='config.$ext'>Saidi config</a> / Ikoonide baas");

		$start = $page*PER_PAGE;
		$end = ($page+1)*PER_PAGE;
		$this->db_query("SELECT * FROM icons ORDER BY id");
		$n = 0;
		while ($row = $this->db_next())
		{
			if ($n >= $start && $n <= $end)
			{
				$this->vars(array("page" => $page,"id" => $row[id], "name" => $row[name], "comment" => $row[comment],"programm" => $row[programm],"url" => 	$this->get_url($row)));
				$this->parse("LINE");
			}
			$n++;
		}

		// make pageselector
		$total = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM icons", "cnt");
		$pages = $total/PER_PAGE;
		for ($i=0; $i < $pages; $i++)
		{
			$this->vars(array("from" => $i*PER_PAGE, "to" => min($total,($i+1)*PER_PAGE), "num" => $i));
			if ($i == $page)
			{
				$p.=$this->parse("SEL_PAGE");
			}
			else
			{
				$p.=$this->parse("PAGE");
			}
		}
		$this->vars(array("PAGE" => $p, "SEL_PAGE" => ""));
		return $this->parse();
	}

	function add()
	{
		global $ext;
		$this->mk_path(0,"<a href='config.$ext'>Saidi config</a> / <a href='config.$ext?type=icon_db'>Ikoonide baas</a> / Lisa");

		$this->read_template("add_icon.tpl");
		return $this->parse();
	}

	function add_zip()
	{
		global $ext;
		$this->mk_path(0,"<a href='config.$ext'>Saidi config</a> / <a href='config.$ext?type=icon_db'>Ikoonide baas</a> / Lisa");

		$this->read_template("add_icon_zip.tpl");
		return $this->parse();
	}

	function submit_icon($arr)
	{
		extract($arr);
		global $fail, $fail_type;

		$itypes = array("image/jpeg",
		                "image/gif",
							      "image/jpg",
								    "image/pjpeg");
		if (!in_array($fail_type,$itypes) && $fail != "none")
		{
			$this->raise_error("Ikoon peab olema kas gif v6i jpeg formaadis!",true);
		}

		if ($id)
		{
			if ($fail == "none")
			{
				$this->db_query("UPDATE icons SET name = '$name' , comment = '$comment', kelle = '$kelle', puhastatud='$puhastatud', praht = '$praht', opsys = '$opsys', p2rit = '$p2rit', m2rks6nad = '$m2rks6nad', m2rks6nad2 = '$m2rks6nad2', programm = '$programm'  WHERE id = $id");
			}
			else
			{
				$f = fopen($fail,"r");
				$fc = fread($f,filesize($fail));
				fclose($f);
				$this->quote(&$fc);
				$this->db_query("UPDATE icons SET name='$name', comment = '$comment', kelle = '$kelle', puhastatud='$puhastatud', praht = '$praht', opsys = '$opsys', p2rit = '$p2rit', m2rks6nad = '$m2rks6nad', m2rks6nad2 = '$m2rks6nad2', programm = '$programm', file = '$fc', file_type = '$fail_type' WHERE id = $id");
			}
		}
		else
		{
			$id = $this->db_fetch_field("SELECT MAX(id) as id FROM icons","id")+1;

			if ($fail == "none")
			{
				$this->db_query("INSERT INTO icons (id,name,comment,kelle,puhastatud,praht,opsys,p2rit,m2rks6nad,m2rks6nad2,programm) 
																		 VALUES($id,'$name','$comment','$kelle','$puhastatud','$praht','$opsys','$p2rit','$m2rks6nad','$m2rks6nad2','$programm')");
			}
			else
			{
				$f = fopen($fail,"r");
				$fc = fread($f,filesize($fail));
				fclose($f);
				$this->quote(&$fc);
				$this->db_query("INSERT INTO icons (id,name,comment,kelle,puhastatud,praht,opsys,p2rit,m2rks6nad,m2rks6nad2,programm,file,file_type) 
																		 VALUES($id,'$name','$comment','$kelle','$puhastatud','$praht','$opsys','$p2rit','$m2rks6nad','$m2rks6nad2','$programm','$fc','$fail_type')");
			}
		}
		return $id;
	}

	function change($id)
	{
		global $ext;
		$this->mk_path(0,"<a href='config.$ext'>Saidi config</a> / <a href='config.$ext?type=icon_db'>Ikoonide baas</a> / Muuda");
		$this->read_template("change_icon.tpl");

		// mdx, yx ennustus. MITTE KEEGI ei hakka MITTE KUNAGI neid v2lju k6iki t2itma. 
		// aga, nagu duke ytles, SUUR VALGE MASSA tahtis, niiet teeme siis.

		$ic = $this->get($id);
		$this->vars(array("ref" => $ic[url], "name" => $ic[name], "comment" => $ic[comment], "id" => $id,
											"meie"				=> $ic[kelle] == "meie" ? "CHECKED" : "",
											"nende"				=> $ic[kelle] == "nende" ? "CHECKED" : "",
											"puhastatud"	=> $ic[puhastatud] ? "CHECKED" : "",
											"praht"				=> $ic[praht] ? "CHECKED" : "",
											"m2kk"				=> $ic[opsys] == "m2kk" ? "CHECKED" : "",
											"winblows"		=> $ic[opsys] == "winblows" ? "CHECKED" : "",
											"l33nox"			=> $ic[opsys] == "l33nox" ? "CHECKED" : "",
											"p2rit"				=> $ic[p2rit],
											"m2rks6nad"		=> $ic[m2rks6nad],
											"programm"		=> $ic[programm],
											"m2rks6nad2"		=> $ic[m2rks6nad2]));

		return $this->parse();
	}

	function get($id)
	{
		if (is_array($GLOBALS["icon_cache"][$id]))
			return $GLOBALS["icon_cache"][$id];

		$this->db_query("SELECT * FROM icons WHERE id = $id");
		$ret = $this->db_next(false);
		global $ext,$baseurl;
		$ret["url"] = $baseurl."/icon.$ext?id=$id";

		$GLOBALS["icon_cache"][$id] = $ret;

		return $ret;
	}

	function show($id)
	{
		if (!$id)
		{
			header("Content-type: image/gif");
			readfile($GLOBALS["baseurl"]."/images/icon_aw.gif");
		}

		$ic = $this->get($id);
		header("Content-type: ".$ic["file_type"]);
		echo $ic["file"];
	}

	function get_url($row)
	{
		return $GLOBALS["baseurl"]."/icon.".$GLOBALS["ext"]."?id=".$row["id"];
	}

	function delete($id)
	{
		$this->db_query("DELETE FROM icons WHERE id = $id");
	}

	function del_icons($sel)
	{
		if(is_array($sel))
		{
			foreach($sel as $icon_id)
			{
				$this->delete($icon_id);
			}
		}
	}

	function sel_icon($rtype,$rid,$sstring = "",$sstring2 = "")
	{
		global $ext,$kelle,$puhastatud,$praht,$opsys,$p2rit,$m2rks6nad,$m2rks6nad2,$search,$programm;
		$this->mk_path(0,"<a href='config.$ext'>Saidi config</a> / Otsi ikooni");

		$this->read_template("search_icon.tpl");
		$this->vars(array("rtype" => $rtype, "rid" => $rid, "sstring" => (!$search ? "%" : $sstring), "sstring2" => $sstring2,
											"meie"				=> $kelle == "meie" ? "CHECKED" : "",
											"nende"				=> $kelle == "nende" ? "CHECKED" : "",
											"puhastatud"	=> $puhastatud ? "CHECKED" : "",
											"praht"				=> $praht ? "CHECKED" : "",
											"m2kk"				=> $opsys == "m2kk" ? "CHECKED" : "",
											"winblows"		=> $opsys == "winblows" ? "CHECKED" : "",
											"l33nox"			=> $opsys == "l33nox" ? "CHECKED" : "",
											"p2rit"				=> $p2rit,
											"programm"				=> $programm,
											"m2rks6nad"		=> $m2rks6nad,
											"m2rks6nad2"		=> $m2rks6nad2));

		if ($search)
		{
			$sp = array();
			if ($kelle != "")
				$sp[] = " kelle = '$kelle' ";
			if ($puhastatud != "")
				$sp[] = " puhastatud = '$puhastatud' ";
			if ($praht != "")
				$sp[] = " praht = '$praht' ";
			if ($opsys != "")
				$sp[] = " opsys = '$opsys' ";
			if ($p2rit != "")
				$sp[] = " p2rit = '$p2rit' ";
			if ($programm != "")
				$sp[] = " programm = '$programm' ";
			if ($m2rks6nad != "")
				$sp[] = " m2rks6nad LIKE '%$m2rks6nad%' ";
			if ($m2rks6nad2 != "")
				$sp[] = " m2rks6nad2 LIKE '%$m2rks6nad2%' ";

			$sps = join("AND",$sp);
			if ($sps != "")
				$sps= "AND ".$sps;

			$this->db_query("SELECT * FROM icons WHERE name LIKE '%$sstring%' AND comment LIKE '%$sstring2%' $sps");
			while ($row = $this->db_next())
			{
				$this->vars(array("name" => $row[name], "comment" => $row[comment], "id" => $row[id], "url" => $this->get_url($row)));
				$this->parse("LINE");
			}
		}
		return $this->parse();
	}

	function export($arr)
	{
		extract($arr);
		if (!is_array($sel))
			return;

		$sels = join(",",$sel);
		$this->db_query("SELECT * FROM icons WHERE id IN ($sels)");
		header("Content-type: automatweb/icon-export");
		while ($row = $this->db_next(false))
		{
			$ret.= "\x01icon\x02\n".serialize($row)."\n";
		}
		return $ret;
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

	function import($level)
	{
		if (!$level)
		{
			global $ext;
			$this->mk_path(0,"<a href='config.$ext'>Saidi config</a> / Impordi ikoone");
			$this->read_template("import_icons.tpl");
			return $this->parse();
		}
		else
		{
			global $fail;
			if (!($f = fopen($fail,"r")))
				$this->raise_error("Miskit l2x uploadimisel viltu",true);

			$fc = fread($f,filesize($fail));
			fclose($f);
			$this->core_import($fc);
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
		global $ext;
		echo "Importisin $cnt ikooni. <a href='config.aw?type=icon_db'>Tagasi</a><br>";
	}

	function add_array($v)
	{
		// @desc: adds a new icon to the database. the icon is described in the array
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

	function upload_zip($arr)
	{
		extract($arr);
		global $fail,$tmpdir;

		$cnt = 0;
		if ($fail != "none" && $fail != "")
		{
			// teeme temp. kataloogi kuhu faili lahti pakime. 
			$dir = $tmpdir."/".$this->gen_uniq_id();
			if (!mkdir($dir,0777))
			{
				$this->raise_error("Unable to create temp directory $dir !", true);
			}

			// siin konverdime
			$tdir = $tmpdir."/".$this->gen_uniq_id();
			if (!mkdir($tdir,0777))
			{
				$this->raise_error("Unable to create temp directory $tdir !", true);
			}

			$op = `/usr/bin/unzip -o -j -d $dir $fail`;
			echo "<pre>".$op."</pre><br>";
			flush();

			$h = opendir($dir);
			while ($file = readdir($h))
			{
				echo "protsessin faili $file <br>";
				flush();
				if ($file != "." && $file != "..")
				{
					$ext = substr($file,strrpos($file,"."));
					// kui on vaja konvertida, siis konverdime. 
					if (strcasecmp($ext,".ico") == 0)
					{
						echo "konverdin faili gifiks!<br>";
						// ok, this is da biaatch. 
						// ysnaga. convertiga teeme giffideks. tekib mitu giffi
						// siis identifyga tunneme 6ige 2ra
						$pfile = $this->convert_icon($dir,$file,$tdir);
						if (!$pfile)
						{
							// faili ei leidnud. kustutame selle 2ra ja j2tkame luupi.
							echo "file not found, deleting ",$dir."/".$file," and continuing loop <br>";
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
							$this->raise_error("image type not supported, only .gif/.jpg/.jpeg supported! file $file , ext = $ext", true);
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
		return "Importisin $cnt ikooni!<br><a href='config.aw?type=icon_db'>Tagasi</a>";
	}

	function convert_icon($idir, $icon, $tdir)
	{
		global $convert_dir,$identify_dir;

		$basename = substr($icon,0,strrpos($icon,"."));
		$nname = $basename.".gif";
		echo "nname = $nname <br>";
		$icon = $idir."/".$icon;
		$odir = $tdir."/".$nname;
		// now $tdir contains files $icon.gif.0 / $icon.gif.1 ...
		$op = `$convert_dir +adjoin $icon $odir`;
		echo "$convert_dir (convert +adjoin $icon $odir) result: <pre>$op</pre><Br>";
		// find the right size image with identify
		$h = opendir($tdir);
		$found = false;
		$c_file = false;
		while ($file = readdir($h))
		{
			echo "scanning, file = $file <br>";
			// loopi ei l6peta 2ra kui faili leiame sellep2rast et vaja k6ik failid 2ra kustutada. 
			if ($file != "." && $file != "..")
			{
				if (!$found)
				{
					$fi = $tdir."/".$file;
					$e_fi = str_replace("/","\/",$fi);
					$op = `$identify_dir $fi`;
					echo "$identify_dir $fi result:<br><pre>$op</pre><br>";
					$res = preg_match("/$e_fi (\d*)x(\d*)/",$op,$mat);

					echo "res = $res filesize (/$e_fi (\d*)x(\d*)/) match = ", $mat[1]," , ",$mat[2],"<Br>";
					if ($mat[1] == "16" && $mat[2] == "16")
					{
						$found = true;
						$c_file = $file;
						echo "found!<br>";
						// now replace file.ico with file.gif
						unlink($icon);
						rename($fi,$idir."/".$nname);						
						echo "deleted $icon and moved $fi to ",$idir."/".$nname,"<br>";
						$c_file = $nname;
						// now also crop the image to the right size
						$nm = $idir."/".$nname;
						$op = `$convert_dir -crop 16x16 $nm $nm`;
						echo "crop result: $op <br>";
					}
				}
				echo "deleting ",$tdir."/".$file,"<br>";
				@unlink($tdir."/".$file);
			}
		}
		return $c_file;
	}
}
?>
