<?php

global $orb_defs;
$orb_defs["quiz"] = array("upload"				=> array("function" => "upload", "params" => array()),
													"submit_upload" => array("function" => "submit_upload", "params" => array()),
													"list_files"		=> array("function" => "list_files", "params" => array("page")),
													"save_files"		=> array("function" => "save_files", "params" => array()),
													"delete_file"		=> array("function" => "delete_file", "params" => array("id","page"))
													);

// kysimuste tyybid:
// 0 - ylesanne
// 1 - lahendus
// 2 - vastus
define(YL_YLESANNE, 0);
define(YL_LAHENDUS, 1);
define(YL_VASTUS, 2);
define(YL_K6IK, 4);

define(PER_PAGE,15);

class quiz extends aw_template
{
	function quiz()
	{
		$this->tpl_init("quiz");
		$this->db_init();
		lc_load("definition");
	}

	function upload($arr)
	{
		extract($arr);
		$this->read_template("upload.tpl");

		$this->vars(array("reforb"	=> $this->mk_reforb("submit_upload", array()),
											"list"		=> $this->mk_orb("list_files", array("page" => 0))));
		return $this->parse();
	}

	function submit_upload($arr)
	{
		extract($arr);
		global $fail, $fail_type;

		if ($fail != "none" && $fail != "")
		{
			// uploaditi zipitud fail, pakime lahti
			$op = `/usr/bin/unzip -o -d /www/automatweb/public/quiz $fail`;
			echo "Pakin lahti faili:<br> <pre>$op</pre><Br>";
		}

		if ($dir != "")
		{
			// kopeerime kataloogist k6ik failid 6igesse kohta
			echo "exec /bin/cp -f $dir/* /www/automatweb/public/quiz <br>";
			exec('/bin/cp -f $dir/* /www/automatweb/public/quiz');
		}

		$this->sync_dir();
		die("<a href='".$this->mk_orb("upload", array())."'>Tagasi</a>");
//		return $this->mk_orb("list_files", array());
	}

	function sync_dir()
	{
		$far = array();
		$this->db_query("SELECT * FROM quiz_files");
		while ($row = $this->db_next())
		{
			$far[$row[file]] = $row;
		}
		echo "syncing!<br>";
		$h = opendir("/www/automatweb/public/quiz");
		while (($file = readdir($h))!==false) 
		{
			if ($file == "." || $file == "..")
			{
				continue;
			}
			echo "$file\n<br>";
			if (is_array($far[$file]))
			{
				echo "file in db, no changes<br>";
				// reupload file if changed
				$f = fopen("/www/automatweb/public/quiz/".$file,"r");
				$fc = fread($f,filesize("/www/automatweb/public/quiz/".$file));
				fclose($f);
				$this->quote(&$fc);
				$this->quote(&$fc);

				$this->db_query("update files SET content = '$fc' WHERE id = ".$far[$file][file_id]);
			}
			else
			{
				echo "file not in db, creating<br>";
				$this->create_file($file);
			}
		}
		closedir($h);
	}
	
	function create_file($file)
	{
		$vars = explode("_",$file);
		reset($vars);
		list(,$menu1) = each($vars);
		list(,$menu2) = each($vars);
		list(,$menu3) = each($vars);
		list(,$test_no) = each($vars);
		list(,$difficulty) = each($vars);
		list(,$exam) = each($vars);
		list(,$type) = each($vars);
		list(,$teacher_id) = each($vars);
		list(,$name) = each($vars);

		list($name, $ext) = explode(".",$name);

//		echo "m1 = $menu1 , m2 = $menu2 , m3 = $menu3 , m4 = $menu4 , tid = $teacher_id, tn = $test_no , ex = $exam , ty = $type , name = $name <br>";

		if (!$menu1 || !$menu2 || !$menu3)
		{
			echo "viga failinimes! $file <br>";
			return;
		}
		// parent = 32 ehk siis TUNNID/matemaatika riigieksam menyy
		 
		// leiame 6ige menyy
		$this->db_query("SELECT menu.*,objects.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE parent = 32 AND status != 0 AND number = $menu1");
		if (!($l1r = $this->db_next()))
		{
			echo "sellise numbriga menyyd pole 1sel tasemel! ($menu1)<br>";
			return;
		}

		$this->db_query("SELECT menu.*,objects.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE parent = ".$l1r[oid]." AND status != 0 AND number = $menu2");
		if (!($l2r = $this->db_next()))
		{
			echo "sellise numbriga menyyd pole 2sel tasemel! ($menu2)<br>";
			return;
		}

		$this->db_query("SELECT menu.*,objects.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE parent = ".$l2r[oid]." AND status != 0 AND number = $menu3");
		if (!($l3r = $this->db_next()))
		{
			echo "sellise numbriga menyyd pole 3dal tasemel! ($menu3)<br>";
			return;
		}

		echo "menyy id = $l3r[oid] <br>";

		// tshekime et kas selle ylesande jaox on juba dokument olemas
		// sellex, et test_id olex unikaalne liidame k6ikide menyyde numbrid kokku ja siis test_id ka otsa ja kasutame seda
		$test_no = $menu1."_".$menu2."_".$menu3."_".$test_no;

		$this->db_query("SELECT * FROM quiz_files WHERE test_id = '".$test_no."'");
		if (!($row = $this->db_next()))
		{
			// peame uue dokumendi tegema selle testi jaox. 
			classload("document");
			$t = new document;
			$t->submit_add(array("parent" => $l3r[oid], "name" => $name));
			$docid = $t->id;
			$this->upd_object(array("oid" => $docid, "status" => 2));
			echo "created doc $docid <br>";
		}
		else
		{
			$docid = $row[docid];
		}

		// nyyd tuleb see fail doku sisse aliastada.
		// k6igepealt muidugi baasi uploadida.
		$pid = $this->new_object(array("parent" => $l3r[oid],"name" => $file,"class_id" => CL_FILE));

		// lisame dokule aliase
		$this->add_alias($docid,$pid);

		$f = fopen("/www/automatweb/public/quiz/".$file,"r");
		$fc = fread($f,filesize("/www/automatweb/public/quiz/".$file));
		fclose($f);
		$this->quote(&$fc);
		$this->quote(&$fc);

		$this->db_query("INSERT INTO files (id,showal,type,content) VALUES('$pid','0','application/pdf','$fc')");
		$this->_log("fail","Lisas faili $pid");

		$this->db_query("INSERT INTO quiz_files(file,docid,test_id,file_id,type,parent,name,exam,teacher) values('$file',$docid,'$test_no',$pid,$type,".$l3r[oid].",'$name','$exam','$teacher_id')");

		$this->update_doc_content($test_no,$docid);
		echo "---------------------------------------------<br>";
	}

	function update_doc_content($test_no,$docid)
	{
		// loome dokumendi sisu uuesti.
		$qar = array();
		$this->db_query("SELECT * FROM quiz_files WHERE test_id = '$test_no'");
		while ($row = $this->db_next())
		{
			$qar[$row[type]] = $row;
		}

		$cont = "";
		if (is_array($qar[YL_YLESANNE]))
		{
			$cont.="<a target=\"_new\"  href=\"".$GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$qar[YL_YLESANNE][file_id]."/".$qar[YL_YLESANNE][file]."\">Ülesanne</a><br><br>";
		}
		if (is_array($qar[YL_LAHENDUS]))
		{
			$cont.="<a target=\"_new\"  href=\"".$GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$qar[YL_LAHENDUS][file_id]."/".$qar[YL_LAHENDUS][file]."\">Lahendus</a><br><br>";
		}
		if (is_array($qar[YL_VASTUS]))
		{
			$cont.="<a target=\"_new\"  href=\"".$GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$qar[YL_VASTUS][file_id]."/".$qar[YL_VASTUS][file]."\">Vastus</a><br><br>";
		}
		if (is_array($qar[YL_K6IK]))
		{
			$cont.="<a target=\"_new\"  href=\"".$GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$qar[YL_K6IK][file_id]."/".$qar[YL_K6IK][file]."\">Kogu &Uuml;lesanne</a><br><br>";
		}
		$this->db_query("UPDATE documents SET content = '$cont' WHERE docid = $docid");
	}

	function get_q_list($section = 0)
	{
		// teeme sobiva andmestruktuuri
		$qar = array();

		if ($section)
		{
			$ss = "WHERE parent = '$section' ";
		}
		$this->db_query("SELECT quiz_files.* FROM quiz_files $ss ORDER BY test_no");
		while ($row = $this->db_next())
		{
			$qar[$row[test_id]][$row[type]] = $row;
		}
		return $qar;
	}

	function mk_q_list($section)
	{
		// genereerime ylesannete nimekirja selle menyy all.
		$this->read_template("q_list.tpl");

		$qar = $this->get_q_list($section);

		reset($qar);
		while (list(,$var) = each($qar))
		{
			$name = $var[YL_YLESANNE][name] != "" ? $var[YL_YLESANNE][name] : 
							$var[YL_LAHENDUS][name] != "" ? $var[YL_LAHENDUS][name] :
							$var[YL_VASTUS][name] != "" ? $var[YL_VASTUS][name] :
							$var[YL_K6IK][name];

			$exam = is_array($var[YL_YLESANNE]) ? $var[YL_YLESANNE][exam] : 
							is_array($var[YL_LAHENDUS]) ? $var[YL_LAHENDUS][exam] :
							is_array($var[YL_VASTUS])   ? $var[YL_VASTUS][exam] :
							$var[YL_K6IK][exam];

			$teacher = is_array($var[YL_YLESANNE]) ? $var[YL_YLESANNE][teacher] : 
							is_array($var[YL_LAHENDUS]) ? $var[YL_LAHENDUS][teacher] :
							is_array($var[YL_VASTUS])   ? $var[YL_VASTUS][teacher] :
							$var[YL_K6IK][teacher];

			$docid = is_array($var[YL_YLESANNE]) ? $var[YL_YLESANNE][docid] : 
							is_array($var[YL_LAHENDUS]) ? $var[YL_LAHENDUS][docid] :
							is_array($var[YL_VASTUS])   ? $var[YL_VASTUS][docid] :
							$var[YL_K6IK][docid];

			$yl = "&nbsp;";
			if ($var[YL_YLESANNE][file_id])
			{
				$yl = "<a target=\"_new\"  href=\"".$GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$var[YL_YLESANNE][file_id]."/".$var[YL_YLESANNE][file]."\">Ülesanne</a>";
			}
			$la = "&nbsp;";
			if($var[YL_LAHENDUS][file_id])
			{
				$la = "<a target=\"_new\"  href=\"".$GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$var[YL_LAHENDUS][file_id]."/".$var[YL_LAHENDUS][file]."\">Lahendus</a>";
			}
			$va = "&nbsp;";
			if ($var[YL_VASTUS][file_id])
			{
				$va = "<a target=\"_new\"  href=\"".$GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$var[YL_VASTUS][file_id]."/".$var[YL_VASTUS][file]."\">Vastus</a>";
			}

			$this->vars(array("name" => $name, 
												"docid" => $docid,
												"ylesanne" => $yl,
												"lahendus" => $la,
												"vastus" => $va,
												"exam" => $exam == 1 ? "Jah" : "Ei",
												"teacher" => $teacher));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l));
		return $this->parse();
	}

	function list_files($arr)
	{
		extract($arr);
		$this->read_template("list_files.tpl");

		// kustutame k6ik maha.
/*		$this->db_query("SELECT * FROM quiz_files");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->delete_object($row[docid]);
			$this->delete_object($row[file_id]);
			$this->restore_handle();
		}
		$this->db_query("DELETE FROM quiz_files");*/

		// teeme pageselektori
		$num = $this->db_fetch_field("SELECT count(*) as cnt FROM quiz_files","cnt");
		$num_p = $num / PER_PAGE;
		for ($i = 0; $i <= $num_p; $i++)
		{
			$to = ($i+1)*PER_PAGE-1;
			if ($to > $num)
				$to = $num;

			$this->vars(array("from" => $i*PER_PAGE, "to" => $to, "page" => $i,
												"list" => $this->mk_orb("list_files", array("page" => $i))));
			if ($i == $page)
			{
				$ps.=$this->parse("SEL_PAGE");
			}
			else
			{
				$ps.=$this->parse("PAGE");
			}
		}
		$this->vars(array("PAGE" => $ps, "SEL_PAGE" => ""));

		$this->db_query("SELECT * FROM quiz_files LIMIT ".($page*PER_PAGE+PER_PAGE));
		while ($row = $this->db_next())
		{
			$cnt++;
			if ($cnt <= ($page*PER_PAGE))
			{
				continue;
			}
			$this->vars(array("menu1" => $row[menu1], "menu2" => $row[menu2], "menu3" => $row[menu3], 
												"id" => $row[id], "number" => $row[test_no], "raskus" => $row[level], 
												"exam" => $row[exam] ? "CHECKED" : "", 
												"type" => $row[type], "teacher" => $row[teacher],
												"delete"	=> $this->mk_orb("delete_file", array("id" => $row[id], "page" => $page)),
												"file" => $row[file], "fid" => $row[file_id]));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l,
											"reforb" => $this->mk_reforb("save_files", array("page" => $page))));
		return $this->parse();
	}

	function save_files($arr)
	{
		extract($arr);

		$idar = array();
		reset($menu1);
		while (list($id,) = each($menu1))
			$idar[] = $id;

		$ids = join(",",$idar);
		if ($ids != "")
		{
			$far = array();
			$this->db_query("SELECT * FROM quiz_files WHERE id IN ($ids)");
			while ($row = $this->db_next())
			{
				$far[$row[id]] = $row;
			}

			// nini. nyyd peax hakkama tshekkima et kas on muutunud midagi ja siis updeitima.
			reset($idar);
			while (list(,$id) = each($idar))
			{
				$changes = array();
				if ($far[$id][teacher] != $teacher[$id])
				{
					$changes[] = "teacher='".$teacher[$id]."'";
				}

				if ($far[$id][exam] != $exam[$id])
				{
					$changes[] = "exam='".$exam[$id]."'";
				}

				if ($far[$id][level] != $raskus[$id])
				{
					$changes[] = "level='".$raskus[$id]."'";
				}

				if ($far[$id][type] != $type[$id])
				{
					$changes[] = "type='".$type[$id]."'";
					$this->update_doc_content($far[$id][test_id],$far[$id][docid]);
				}

				if ($far[$id][test_no] != $number[$id])
				{
					$changes[] = "test_no='".$number[$id]."'";
					$changes[] = "test_id='".$menu1[$id]."_".$menu2[$id]."_".$menu3[$id]."_".$number[$id]."'";
				}
				
				$chs = join(",",$changes);
				if ($chs != "")
				{
					$this->db_query("UPDATE quiz_files SET $chs WHERE id = $id");

					// now we must also rename the file according to the data in the db.
					$this->db_query("SELECT * FROM quiz_files WHERE id = $id");
					$row = $this->db_next();
					$fname = $row[menu1]."_".$row[menu2]."_".$row[menu3]."_".$row[test_no]."_".$row[level]."_".($row[exam] ? "1" : "0")."_".$row[type]."_".$row[teacher]."_".$row[name].".pdf";

					rename("/www/automatweb/public/quiz/".$row[file],"/www/automatweb/public/quiz/".$fname);

					$this->db_query("UPDATE quiz_files SET file = '$fname' WHERE id = $id");
				}
			}
		}
		return $this->mk_orb("list_files", array("page" => $page));
	}

	function delete_file($arr)
	{
		extract($arr);
		$this->db_query("SELECT * FROM quiz_files WHERE id = $id");
		if (!($row = $this->db_next()))
		{
			$this->raise_error("No such file ($id)", true);
		}

		// kustutame baasist uploaditud faili
		$this->delete_object($row[file_id]);

		// kustutame quiz_files'ist
		$this->db_query("DELETE FROM quiz_files WHERE id = ".$row[id]);

		// kustutame failisysteemist
		unlink("/www/automatweb/public/quiz/".$row[file]);

		// tshekime et kas selle numbriga ylsandeid on veel alles
		$cnt = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM quiz_files WHERE test_id = '".$row[test_id]."'","cnt");
		if ($cnt < 1)
		{
			// ei ole, ksututame doku ka 2ra
			$this->delete_object($row[docid]);
		}

		//peax k6ik olema?
		header("Location: ".$this->mk_orb("list_files", array("page" => $page)));
	}
}
?>