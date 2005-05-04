<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_import.aw,v 1.30 2005/05/04 10:21:21 kristo Exp $
// otto_import.aw - Otto toodete import 
/*

@classinfo syslog_type=ST_OTTO_IMPORT relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property base_url type=textbox
@caption Toodete csv failide baasaadress

@property folder_url type=textbox
@caption Kataloogideks jagamise csv url

@property prod_folder type=relpicker reltype=RELTYPE_FOLDER
@caption Toodete kataloog

@property file_ext type=textbox
@caption Failide laiend

@property do_i type=checkbox ch_value=1
@caption Teosta import

@property do_pict_i type=checkbox ch_value=1
@caption Teosta piltide import

@property restart_pict_i type=checkbox ch_value=1
@caption Alusta piltide importi algusest

@property restart_prod_i type=checkbox ch_value=1
@caption Alusta toodete importi algusest

@property just_update_prod_data type=checkbox ch_value=1
@caption Uuenda ainult toote andmed

@property last_import_log type=text store=no
@caption Viimase impordi logi

@groupinfo imgs caption="Pildid"
@default group=imgs

	@property orig_img type=textbox size=10 store=no
	@caption Algne pilt

	@property view_img type=text

	@property to_img type=textbox store=no
	@caption Mis pildid asendada

@groupinfo files caption="Failid"

	@property fnames type=textarea rows=30 cols=80 group=files
	@caption Failinimed

@groupinfo foldersa caption="Kataloogid"

@groupinfo folders caption="Kataloogid" parent=foldersa

@property folders type=table store=no group=folders no_caption=1

@property inf_pages type=textarea rows=3 cols=40 group=folders field=meta method=serialize table=objects
@caption L&otilde;pmatus vaatega lehed

@groupinfo folderspri caption="Kataloogide m&auml;&auml;rangud" parent=foldersa

@property foldpri type=textarea rows=20 cols=20 table=objects field=meta method=serialize group=folderspri
@caption T&auml;htede prioriteedid

@property sideways_pages type=textarea rows=4 cols=30 table=objects field=meta method=serialize group=folderspri
@caption Landscape vaatega lehed

@groupinfo views caption="Vaated"

@property force_7_view type=textbox table=objects field=meta method=serialize group=views
@caption 7 pildiga lehed

@property force_inf_view type=textbox table=objects field=meta method=serialize group=views
@caption L&otilde;pmatute pildiga lehehed

@property force_10_view type=textbox table=objects field=meta method=serialize group=views
@caption 10 pildiga lehed

@property force_8_view type=textbox table=objects field=meta method=serialize group=views
@caption 8 pildiga lehed

@property force_no_side_view type=textbox table=objects field=meta method=serialize group=views
@caption Ilma detailvaate lisapiltideta lehed

@groupinfo jm caption="J&auml;relmaks"

	@property jm_clothes type=textarea rows=5 cols=50 table=objects field=meta method=serialize group=jm
	@caption R&otilde;ivad

	@property jm_lasting type=textarea rows=5 cols=50 table=objects field=meta method=serialize group=jm
	@caption Kestvuskaubad

	@property jm_furniture type=textarea rows=5 cols=50 table=objects field=meta method=serialize group=jm
	@caption M&ouml;&ouml;bel

@groupinfo delete caption="Kustutamine"

	@property del_prods type=textarea rows=10 cols=50 store=no group=delete
	@caption Kustuta tooted koodidega (komaga eraldatud)

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@groupinfo post_import_fix caption="Parandused"

@property do_fixes type=checkbox ch_value=1 method=serialize field=meta group=post_import_fix
@caption Soorita parandus

*/

class otto_import extends class_base
{
	function otto_import()
	{
		$this->init(array(
			"tpldir" => "applications/shop/otto/otto_import",
			"clid" => CL_OTTO_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "last_import_log":
				$prop["value"] = join("<br>\n", @file(aw_ini_get("site_basedir")."/files/import_last_log.txt"));
				break;

			case "folders":
				$this->do_folders_tbl($arr);
				break;

			case "view_img":
				$prop["value"] = "<a href='javascript:void(0)' onClick='viewimg()'>Vaata pilti</a>";
				$prop["value"] .= "<script language=\"javascript\">\n";
				$prop["value"] .= "function viewimg() { var url;\n
					url = \"http://image01.otto.de/pool/OttoDe/de_DE/images/formata/\"+document.changeform.orig_img.value+\".jpg\";
					window.open(url,\"popupx\", \"width=400,height=600\");
				}\n";
				$prop["value"] .= "</script>\n";
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "folders":
				$this->db_query("DELETE FROM otto_imp_t_p2p WHERE lang_id = ".aw_global_get("lang_id"));
				foreach(safe_array($arr["request"]["dat"]) as $cnt => $row)
				{
					foreach(explode(",", $row["pgs"]) as $pg)
					{
						if ($pg && $row["awfld"])
						{
							$this->db_query ("INSERT INTO otto_imp_t_p2p (pg,fld,lang_id)
								VALUES('$pg','$row[awfld]','".aw_global_get("lang_id")."')
							");
						}
					}
				}
				break;

			case "to_img":
				if ($prop["value"] != "" /*&& $arr["request"]["orig_img"] != ""*/)
				{
					// do replace
					$toims = explode(",", $prop["value"]);
					$q = "
						UPDATE 
							otto_prod_img
						SET 
							show_imnr = '".$arr["request"]["orig_img"]."' 
						WHERE
							imnr IN (".join(",", map("'%s'", $toims)).")
					";
					$this->db_query($q);
				}
				break;
			
			case "del_prods":
				if ($prop["value"] != "")
				{
					$this->_do_del_prods(explode(",", $prop["value"]));
				}
				break;
		}
		return $retval;
	}	

	function callback_pre_save($arr)
	{
		if($arr['obj_inst']->prop('do_fixes'))
		{
			$arr['obj_inst']->set_prop('do_fixes',0);
			
			$this->do_post_import_fixes($arr['obj_inst']);
		}
		
		if ($arr["obj_inst"]->prop("do_i"))
		{
			$arr["obj_inst"]->set_prop("do_i", 0);
			$arr["obj_inst"]->set_prop("do_pict_i", 0);
			$arr["obj_inst"]->set_prop("restart_pict_i", 0);
			
			$this->do_prod_import($arr["obj_inst"]);
		}
	}

	/**

		@attrib name=pictimp

	**/
	function pictimp($arr,$fix_missing = false)
	{
		set_time_limit(0);

		if (is_object($arr))
		{
			$data = array();
			foreach(explode("\n", $arr->prop("fnames")) as $fname)
			{
				if (trim($fname) == "" )
				{
					continue;
				}

				$fext = ($arr->prop("file_ext") != "" ? $arr->prop("file_ext") : "xls");
				$fld_url = $arr->prop("base_url")."/".trim($fname)."-2.".$fext;
				if (!$this->is_csv($fld_url))
				{
					continue;
				}
				echo "from url ".$fld_url." read: <br>";
				list(, $cur_pg) = explode(".", $fname);
				$cur_pg = substr($cur_pg,1);
				if ((string)((int)$cur_pg{0}) === (string)$cur_pg{0})
				{
					$cur_pg = (int)$cur_pg;
				}
				$cur_pg = trim($cur_pg);

				$first = true;
				$num =0;

				// fucking mackintosh
				if (count(file($fld_url)) == 1)
				{
					$lines = $this->mk_file($fld_url, "\t");
					if (count($lines) > 1)
					{
						$tmpf = tempnam("/tmp", "aw-ott-imp-5");
						$fp = fopen($tmpf,"w");
						fwrite($fp, join("\n", $lines));
						fclose($fp);
						$fld_url = $tmpf;
					}
				}

				$fp = fopen($fld_url, "r");
				while ($row = fgetcsv($fp,1000,"\t"))
				{
					if ($first)
					{
						$first = false;
						continue;
					}
					$row = $this->char_replacement($row);
					$row[4] = str_replace(".","", $row[4]);
					$row[4] = substr(str_replace(" ","", $row[4]), 0, 7);
					$data[] = $row[4];
				}
			}
		}
		else
		{
			//$data = array_unique(explode(",", $this->get_file(array("file" => "/www/otto.struktuur.ee/ottids.txt"))));
			$data = array("198052Y");
		}

		if (!$fix_missing && file_exists($this->cfg["site_basedir"]."/files/status.txt"))
		{
			//$skip_to = $this->get_file(array("file" => $this->cfg["site_basedir"]."/files/status.txt"));
			echo "restarting from product $skip_to <br>";
		}
		else
		if (!$fix_missing)
		{
			//$this->db_query("DELETE FROM otto_prod_img");
		}
		
		if ($fix_missing)
		{
			$this->db_query("select c.code from otto_imp_t_codes c left join otto_prod_img p on p.pcode = c.code where (p.imnr is null or p.imnr = '')");
			$data = array();
			while ($row = $this->db_next())
			{
				$data[] = $row["code"];
			}
			echo "fixing not found codes:".join(", ",$data)." <br><br>";
		}
		//$data = array("392232");
		$total = count($data);
		$cur_cnt = -1;
		$start_time = time();
		//$data = array("887978");
		foreach ($data as $pcode) 
		{
			if ($pcode == "")
			{
				continue;
			}

			if ($skip_to && $pcode != $skip_to)
			{
				$cur_cnt++;
				continue;
			}
			if ($skip_to && $pcode == $skip_to)
			{
				$time_total = $total - $cur_cnt;
				$time_cur_cnt = 0;
				$skip_to = false;
			}

			$elapsed_time = time() - $start_time;
			if ($time_cur_cnt == 0)
			{
				$time_per_code = 0;
			}
			else
			{
				$time_per_code = $elapsed_time / $time_cur_cnt;
			}
			$time_remaining = ($time_total - $time_cur_cnt) * $time_per_code;
			$rem_hr = (int)($time_remaining / 3600);
			$rem_min = (int)(($time_remaining - ($rem_hr * 3600)) / 60);

			echo "process pcode $pcode (".($total - $cur_cnt)." to go, estimated time remaining $rem_hr hr, $rem_min min) <br>\n";
			flush();

			$url = "http://www.otto.de/is-bin/INTERSHOP.enfinity/WFS/OttoDe/de_DE/-/EUR/OV_ParametricSearch-Progress;sid=bwNBYJMEb6ZQKdPoiDHte7MOOf78U0shdsyx6iWD?_PipelineID=search_pipe_ovms&_QueryClass=MallSearch.V1&ls=0&Orengelet.sortPipelet.sortResultSetSize=10&SearchDetail=one&Query_Text=".$pcode;
		echo "url = $url <br>";
			$html = $this->file_get_contents($url);

			// image is http://image01.otto.de:80/pool/OttoDe/de_DE/images/formatb/[number].jpg
			if (strpos($html,"Leider konnten wir im gesamten OTTO") !== false)
			{ 
				// read from baur.de
				$this->read_img_from_baur($pcode);
			}
			else
			if (!preg_match("/pool\/OttoDe\/de_DE\/images\/formatb\/(\d+).jpg/imsU",$html, $mt))
			{
				echo "for product $pcode multiple images! <br>\n";
				flush();

				$o_html = $html;


				// subrequest for two images
				//die($html);
				if (!preg_match_all("/<\/table>\n<a href=\"Javascript:document\.location\.href='(.*)'\+urlParameter\"/imsU", $html, $mt, PREG_PATTERN_ORDER))
				{
					preg_match_all("/<td valign=\"middle\" align=\"center\" height=\"\d+\" width=\"\d+\"><a href=\"Javascript:document\.location\.href='(.*)'\+urlParameter\"/imsU", $html, $mt, PREG_PATTERN_ORDER);
				}

				$urld = array();
				//die(dbg::dump($mt));
				foreach($mt[1] as $url)
				{
					$url = $url."&SearchDetail=one&stype=N&Orengelet.sortPipelet.sortResultSetSize=15&Orengelet.SimCategorize4OttoMsPipelet.Similarity_Parameter=&Orengelet.sortPipelet.sortCursorPosition=0&Query_Text=".$pcode;
					$urld[$url] = $url;
				}

				foreach($urld as $url)
				{
					//echo "url = $url <br>";
					$html = $this->file_get_contents($url);

					preg_match_all("/Javascript:setImage\('(.*)\.jpg', '(\d+)'\)/imsU", $html, $mt, PREG_PATTERN_ORDER);
					$f_imnr = NULL;

					// ach! if only single image then no js!!!
					if (count($mt[1]) == 0)
					{
						preg_match("/pool\/OttoDe\/de_DE\/images\/formatb\/(\d+)\.jpg/imsU",$html, $mt2);
						$t_imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '".$mt2[1]."' AND nr = '1' AND pcode = '$pcode'", "pcode");
						if (!$f_imnr)
						{
							$f_imnr = $t_imnr.".jpg";
						}
						if (!$t_imnr)
						{
							echo "insert new image ".$mt2[1]." <br>\n";
							flush();
							$q = ("
								INSERT INTO 
									otto_prod_img(pcode, nr,imnr, server_id) 
									values('$pcode','1','".$mt2[1]."', 1)
							");
							//echo "q = $q <br>";
							$this->db_query($q);
						}
					}
					else
					{
						foreach($mt[1] as $idx => $img)
						{
							$imnr = basename($img, ".jpg");
							$t_imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$imnr' AND nr = '".$mt[2][$idx]."' AND pcode = '$pcode'", "pcode");
							if (!$f_imnr)
							{
								$f_imnr = $t_imnr.".jpg";
							}
							if (!$t_imnr)
							{
								echo "insert new image $imnr <br>\n";
								flush();
								$q = ("
									INSERT INTO 
										otto_prod_img(pcode, nr,imnr, server_id) 
										values('$pcode','".$mt[2][$idx]."','$imnr', 1)
								");
								//echo "q = $q <br>";
								$this->db_query($q);
							}
						}
					}

					// check for rundumanshiftph
					if (strpos($html, "rundum_eb") !== false)
					{
						preg_match_all("/javascript:OpenPopUpZoom\('690','540','(.*)'\+selectedImage\);/imsU", $html, $mt);
						// get the rundum image number from the popup :(
						$r_html = file_get_contents($mt[1][1].$f_imnr);

						// save rundum
						// get rundum imnr from html
						preg_match("/http:\/\/image01\.otto\.de:80\/pool\/OttoDe\/de_DE\/images\/format360\/(.*)\.swf/imsU", $r_html, $mt);
						echo "set flash to true <br>";
						$this->db_query("UPDATE otto_prod_img SET has_flash = '$mt[1]' WHERE pcode = '$pcode' AND nr = 1");
					}

				}
			}
			else
			{
				$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
					values('$pcode',1,'$mt[1]')");	

				$add = 0;
				// check if we got the main img
				if (preg_match("/pool\/OttoDe\/de_DE\/images\/formatd\/(\d+)\.jpg/imsU", $html, $mt))
				{
					$add = 1;
					$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
						values('$pcode','1','$mt[1]')");
					echo "rewrote first image as $mt[1] <Br>\n";
					flush();
				}

				// also, other images, detect them via the jump_img('nr') js func
				preg_match_all("/jump_img\('(\d+)'\)/imsU", $html, $mt, PREG_PATTERN_ORDER);
				$nrs = $mt[1];
				foreach($nrs as $nr)
				{
					$nurl = $url."&bild_nr=".$nr;
					echo "fetch other image nr $nr $nurl!<br>\n";
					flush();
					$ismatch = false;
					$cnt = 1;
					do {
						//sleep(1);
						$nhtml = $this->file_get_contents($nurl);
						$ismatch = preg_match("/pool\/OttoDe\/de_DE\/images\/formatb\/(\d+)\.jpg/imsU",$nhtml, $mt);
						if ($cnt > 1)
						{
							echo "try nr $cnt <Br>\n";
							flush();
						}
					} while(!$ismatch && $cnt++ < 9);

					if (!$ismatch)
					{
						error::raise(array(
							"id" => "ERR_NO_FETCH",
							"msg" => sprintf(t("otto_import::images(): could not fetch html for url %s!"), $nurl)
						));
					}
					
					if (!preg_match("/pool\/OttoDe\/de_DE\/images\/formatb\/(\d+)\.jpg/imsU",$nhtml, $mt))
					{
						echo "for product $pcode no image number $nr! <br>\n";
						echo "html is $nhtml <br>";
						flush();
					}
					else
					{
						$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
							values('$pcode','".($nr+$add)."','$mt[1]')");
					}
				}
			}

			$stat = fopen($this->cfg["site_basedir"]."/files/status.txt","w");
		
			fwrite($stat, $pcode);
			fclose($stat);
			//sleep(1);
			$cur_cnt++;
			$time_cur_cnt++;
		}

		echo "all done! <br>\n";
		//die();
	}

	function do_prod_import($o)
	{
		// read csvs into db for temp crap
		$this->update_csv_db($o);

		// flush cache
		$this->cache_files = array();
		$fld = aw_ini_get("site_basedir")."/prod_cache";
		$this->_get_cache_files($fld);
		foreach($this->cache_files as $file)
		{
			$fp = $fld."/".$file{0}."/".$file{1}."/".$file;
			unlink($fp);
		}
	}

	function _get_cache_files($fld)
	{
		if ($dir = @opendir($fld))
		{
			while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					if (is_dir($fld."/".$file))
					{
						$this->_get_cache_files($fld."/".$file);
					}
					else
					{
						$this->cache_files[] = $file;
					};
				};
			};
		}
	}

	function update_csv_db($o)
	{
		set_time_limit(0);
		if ($o->prop("restart_pict_i"))
		{
			@unlink($this->cfg["site_basedir"]."/files/status.txt");
		}
		if ($o->prop("do_pict_i"))
		{
			$this->pictimp($o);
		}

		obj_set_opt("no_cache", 1);

		$imp_stat_file = aw_ini_get("site_basedir")."/files/impstatus.txt";
		if ($o->prop("restart_prod_i"))
		{
			@unlink($imp_stat_file);
		}

		if (file_exists($imp_stat_file))
		{
			$skip_to = $this->get_file(array("file" => $this->cfg["site_basedir"]."/files/status.txt"));
			echo "restarting from product $skip_to <br>";
		}

		$this->db_query("DELETE FROM otto_imp_t_prod");
		$this->db_query("DELETE FROM otto_imp_t_codes");
		$this->db_query("DELETE FROM otto_imp_t_prices");
		//$this->db_query("DELETE FROM otto_imp_t_p2p WHERE lang_id = ".aw_global_get("lang_id"));

		echo "from url ".$o->prop("folder_url")." read: <br>";

		$fext = ($o->prop("file_ext") != "" ? $o->prop("file_ext") : "xls");

		$first = true;

		$log = array();

		foreach(explode("\n", $o->prop("fnames")) as $fname)
		{
			if (trim($fname) == "")
			{
				continue;
			}

			$fld_url = $o->prop("base_url")."/".trim($fname)."-1.".$fext;
			if (!$this->is_csv($fld_url))
			{
				continue;
			}
			echo "from url ".$fld_url." read: <br>";
			list(, $cur_pg) = explode(".", $fname);
			$cur_pg = substr($cur_pg,1);
			if ((string)((int)$cur_pg{0}) === (string)$cur_pg{0})
			{
				$cur_pg = (int)$cur_pg;
			}
			$cur_pg = trim($cur_pg);

			$first = true;
			$num = 0;

			// fucking mackintosh
			if (count(file($fld_url)) == 1)
			{
				$lines = $this->mk_file($fld_url, "\t");
				if (count($lines) > 1)
				{
					$tmpf = tempnam("/tmp", "aw-ott-imp");
					$fp = fopen($tmpf,"w");
					fwrite($fp, join("\n", $lines));
					fclose($fp);
					$fld_url = $tmpf;
				}
			}

			$fp = fopen($fld_url, "r");
			while ($row = fgetcsv($fp,1000,"\t"))
			{
				if ($first)
				{
					$first = false;
					continue;
				}
				if (count($row) < 2)
				{
					continue;
				}

				if ($row[2] == "" && $row[1] == "" && $row[3] == "")
				{
					continue;
				}

				$this->quote(&$row);
				$row = $this->char_replacement($row);
				$row[2] = $this->conv($row[2]);
				$desc = $this->conv(trim($row[3]." ".$row[4]." ".$row[5]." ".$row[6]." ".$row[7]." ".$row[8]." ".$row[9]." ".$row[10]." ".$row[11]." ".$row[12]." ".$row[13]." ".$row[14]." ".$row[15]." ".$row[16]." ".$row[17]." ".$row[18]." ".$row[19]." ".$row[20]." ".$row[21]." ".$row[22]." ".$row[23]." ".$row[24]." ".$row[25]." ".$row[26]." ".$row[27]." ".$row[28]." ".$row[29]." ".$row[30]." ".$row[31]." ".$row[32]." ".$row[33]." ".$row[34]." ".$row[35]." ".$row[36]." ".$row[37]." ".$row[38]." ".$row[39]." ".$row[40]." ".$row[41]." ".$row[42]));
				$this->db_query("
					INSERT INTO otto_imp_t_prod(pg,nr,title,c)
					VALUES('$cur_pg','$row[1]','$row[2]','$desc')
				");
				if ($row[2] == "")
				{
					echo "ERROR ON LINE $num title ".$row[2]." <br>";
					$log[] = "VIGA real $num failis $fld_url nimi: ".$row[2];
				}
				$num++;
			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}

			echo ".. got $num titles <br>";
			$log[] = "lugesin failist $fld_url $num toodet";
		}

		foreach(explode("\n", $o->prop("fnames")) as $fname)
		{
			if (trim($fname) == "")
			{
				continue;
			}

			$fld_url = $o->prop("base_url")."/".trim($fname)."-2.".$fext;
			if (!$this->is_csv($fld_url))
			{
				continue;
			}
			echo "from url ".$fld_url." read: <br>";
			list(, $cur_pg) = explode(".", $fname);
			$cur_pg = substr($cur_pg,1);
			if ((string)((int)$cur_pg{0}) === (string)$cur_pg{0})
			{
				$cur_pg = (int)$cur_pg;
			}
			$cur_pg = trim($cur_pg);

			$first = true;
			$num =0;

			if (count(file($fld_url)) == 1)
			{
				$lines = $this->mk_file($fld_url, "\t");
				if (count($lines) > 1)
				{
					$tmpf = tempnam("/tmp", "aw-ott-imp");
					$fp = fopen($tmpf,"w");
					fwrite($fp, join("\n", $lines));
					fclose($fp);
					$fld_url = $tmpf;
				}
			}

			$fp = fopen($fld_url, "r");
			while ($row = fgetcsv($fp,1000,"\t"))
			{
				if ($first)
				{
					$first = false;
					continue;
				}
				if (count($row) < 2)
				{
					continue;
				}

				if ($row[2] == "" && $row[1] == "" && $row[3] == "")
				{
					continue;
				}

				$this->quote(&$row);
				$row = $this->char_replacement($row);
				$full_code = str_replace(".","", $row[4]);
				$full_code = str_replace(" ","", $full_code);

				$row[4] = substr(str_replace(".","", str_replace(" ","", $row[4])), 0, 7);
				$color = $row[3];
				if ($row[2] != "")
				{
					$color .= " (".$row[2].")";
				}

				$set_f_img = trim($row[5]);

				$this->db_query("
					INSERT INTO otto_imp_t_codes(pg,nr,size,color,code, full_code, set_f_img)
					VALUES('$cur_pg','$row[1]','$row[2]','$color','$row[4]','$full_code', '$set_f_img')
				");
				$num++;
				if (!$row[4])
				{
					echo "ERROR ON LINE $num code ".$row[4]." <br>";
					$log[] = "VIGA real $num failis $fld_url kood: $row[4]";
				}
			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}

			echo ".. got $num codes <br>\n";
			$log[] = "lugesin failist $fld_url $num koodi";
			flush();
		}

		foreach(explode("\n", $o->prop("fnames")) as $fname)
		{
			if (trim($fname) == "")
			{
				continue;
			}

			$fld_url = $o->prop("base_url")."/".trim($fname)."-3.".$fext;
			if (!$this->is_csv($fld_url))
			{
				continue;
			}
			echo "from url ".$fld_url." read: <br>";
			list(, $cur_pg) = explode(".", $fname);
			$cur_pg = substr($cur_pg,1);
			if ((string)((int)$cur_pg{0}) === (string)$cur_pg{0})
			{
				$cur_pg = (int)$cur_pg;
			}
			$cur_pg = trim($cur_pg);

			$first = true;

			if (count(file($fld_url)) == 1)
			{
				$lines = $this->mk_file($fld_url, "\t");
				if (count($lines) > 1)
				{
					$tmpf = tempnam("/tmp", "aw-ott-imp-3");
					$fp = fopen($tmpf,"w");
					fwrite($fp, join("\n", $lines));
					fclose($fp);
					$fld_url = $tmpf;
				}
			}

			$num = 0;
			$fp = fopen($fld_url, "r");
			while ($row = fgetcsv($fp,1000,"\t"))
			{
				if ($first)
				{
					$first = false;
					continue;
				}
				if (count($row) < 2)
				{
					continue;
				}

				if ($row[2] == "" && $row[1] == "" && $row[3] == "")
				{
					continue;
				}

				$orow = $row;
				if (count($row) == 5)
				{
					$row[5] = $row[4];
					$row[4] = "";
				}
				$row = $this->char_replacement($row);
				$this->quote(&$row);
				$orig = $row[5];
				$row[5] = (double)trim(str_replace(",",".", str_replace("-", "",str_replace(chr(160), "", $row[5]))));
				if ($row[4] == "")
				{
					$row[4] = "tk";
				}
				$this->db_query("
					INSERT INTO otto_imp_t_prices(pg,nr,type,size,unit,price)
					VALUES('$cur_pg','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]')
				");

				
				if (!$row[5])
				{
					echo "ERROR ON LINE $num price = $row[5] (orig = $orig)<br>".dbg::dump($orow);
					$log[] = "VIGA real $num hind = $row[5]";

					for ($i = 0; $i < strlen($orig); $i++)
					{
						echo "at pos ".$i." cahar = ".ord($orig{$i})." v = ".$orig{$i}." <br>";
					}
				}
				$num++;
			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}
			echo ".. got $num prices <br>\n";
			$log[] = "lugesin failist $fld_url $num hinda";
			flush();
		}

		$this->db_query("SELECT * FROM otto_imp_t_codes");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->db_query("UPDATE otto_prod_img SET p_pg = '$row[pg]', p_nr = '$row[nr]' WHERE pcode = '$row[code]'");
			$this->restore_handle();
		}

		echo "wrote temp db <br>\n";
		flush();

		echo "rewrite first images <br>\n";
		$this->db_query("SELECT * FROM otto_imp_t_codes WHERE set_f_img != ''");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->db_query("UPDATE otto_prod_img SET imnr = '$row[set_f_img]' WHERE pcode = '$row[full_code]' AND nr = 1 ");
			$this->restore_handle();
		}

		echo "make existing prod lut <br>\n";
		flush();
		/*$exist = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
		));
		$pl = array();
		$pl_full = array();

		$exist_arr = $exist->arr();
		foreach($exist_arr as $t)
		{
			$pl[$t->prop("user20")] = $t->id();
			$pl_full[$t->prop("user20")][$t->id()] = $t;
		}*/
		
		$total = $this->db_fetch_field("
			SELECT  
				count(*) as cnt
			FROM 
				otto_imp_t_prod p
				LEFT JOIN otto_imp_t_codes c ON (c.pg = p.pg AND c.nr = p.nr)
		", "cnt");



		// structure is this:
		// packet - contains all prods that share the same page number and the same image
			// product in packet, one for each row in the combination (first + second table)
				// packaging in product, one for each price

		// so first we create all the products
		// then all the packagings for them
		// then finally we group by the image number and create packages based on that

		// no, go over all the damn prods and create the correct data from them
		$query = "
			SELECT  
				p.pg as pg,
				p.nr as nr,
				p.title as title,
				p.c as c,
				c.code as code,
				c.color as color,
				c.size as s_type,
				c.full_code as full_code
			FROM 
				otto_imp_t_prod p
				LEFT JOIN otto_imp_t_codes c ON (c.pg = p.pg AND c.nr = p.nr)
		";
		$this->db_query($query);
		$start_time = time();
		while ($row = $this->db_next())
		{
			$this->save_handle();
			//echo "import package 
			if ($skip_to && $row["code"] != $skip_to)
			{
				$total--;
				continue;
			}
			if ($skip_to && $row["code"] == $skip_to)
			{
				$skip_to = false;
			}
			
			$orig_pcode = $row["code"];
			$orig_row = $row;

			$elapsed_time = time() - $start_time;
			if ($items_done == 0)
			{
				$time_per_code = 0;
			}
			else
			{
				$time_per_code = $elapsed_time / $items_done;
			}
			$time_remaining = ($total - $items_done) * $time_per_code;
			$rem_hr = (int)($time_remaining / 3600);
			$rem_min = (int)(($time_remaining - ($rem_hr * 3600)) / 60);

			echo "import prod $row[title] ($row[code]) , ".($total - $items_done)." items to go , estimated time remaining: $rem_hr hrs, $rem_min minutes <br>\n";
			flush();
			// check if it exists
			$prod_id = $this->_get_id_by_code($row["code"]);

			echo "checking if $prod_id is oid<br>\n";
			flush();
			$new = true;
			if (is_oid($prod_id))
			{
				echo "oid = ".$prod_id." <br>";
				$dat = obj($prod_id);
				echo "found existing ".$dat->id()."   ".$row['code']."<br>\n";
				flush();
				$new = false;
			}
			else
			{
				$dat = obj();
				$dat->set_class_id(CL_SHOP_PRODUCT);
				$dat->set_parent($o->prop("prod_folder"));
				$dat->set_prop("user20", $row["code"]);
				$dat->save();

				echo "created new ".$dat->id()."  ".$row['code']." <br>\n";
				flush();
			}

			// try find correct folder
			$try_fld = $this->db_fetch_field("SELECT fld FROM otto_imp_t_p2p WHERE pg = '$row[pg]' and lang_id = ".aw_global_get("lang_id"), "fld");
			if ($try_fld)
			{
				echo "found parent for prod as $try_fld <br>\n";
				flush();
				$dat->set_parent($try_fld);
			}

			$dat->set_meta("cfgform_id", 599);
			$dat->set_meta("object_type", 1040);
			$dat->set_prop("item_type", 593);

			$dat->set_name($row["title"]);
			$dat->set_prop("userta2", $row["c"]);
			$dat->set_prop("user16", $row["full_code"]);
			$dat->set_prop("user17", $row["color"]);
			$dat->set_prop("user18", $row["pg"]);

			if (!$new)
			{
				$_ids = $this->_get_ids_by_code($row["code"]);
				foreach($_ids as $tmp_dat)
				{	
					echo "also set ".$tmp_dat->id()." to page $row[pg] <br>";
					if ($tmp_dat->prop("user18") != $row["pg"])
					{
						$tmp_dat->set_prop("user18", $row["pg"]);
						$tmp_dat->save();
					}
				}
			}

			$dat->set_prop("user19", $row["nr"]);

			// log prods with codes that have more than one char
			$fc = preg_replace("/[0-9]/", "", $row["full_code"]);
			if (strlen(trim($fc)) > 1)
			{
				$log[] = "Tootel ".$dat->name()." (".trim($dat->prop("user16")).") on kood ".trim($row["full_code"])." , kus on rohkem kui &uuml;ks t&auml;ht!";
			}

			$dat->save();

			// get list of attached packaging objects
			$pkgs = array();
			$pak_sl = array();
			foreach($dat->connections_from(array("type" => "RELTYPE_PACKAGING")) as $c)
			{
				$t = $c->to();
				$pkgs[$t->prop("price")][$t->prop("user5")] = $t->id();
				$pak_sl[] = $t->id();
			}

			$found = array();

			$lowest = 10000000;
			$typestr_a = array($orig_row["s_type"]);

			if (strpos($orig_row["s_type"], "+") !== false)
			{
				$typestr_a += explode("+", $orig_row["s_type"]);
			}
			$typestr = join(",", map("'%s'", $typestr_a));

			// now, for each price, create packaging objects
			echo "q = "."SELECT * FROM otto_imp_t_prices WHERE pg = '$row[pg]' AND nr = '$row[nr]' AND type IN ($typestr) <br>";
			$this->db_query("SELECT * FROM otto_imp_t_prices WHERE pg = '$row[pg]' AND nr = '$row[nr]' AND type IN ($typestr) ");
			$rows = array();
			while ($row = $this->db_next())
			{
				$rows[] = $row;
			}

			if (count($rows) == 0)
			{
				$tmp = $this->db_fetch_row("SELECT * FROM otto_imp_t_prices WHERE pg = '$row[pg]' AND nr = '$row[nr]' ");
				if ($tmp)
				{
					$rows[] = $tmp;
				}
			}
	
			$sizes = false;
			foreach($rows as $row)
			{
				// gotta split the sizes and do one packaging for each
				$s_tmpc = explode(",", $row["size"]);
				//echo "got price row , s_tmpc = ".dbg::dump($s_tmpc)." <br>";
				$s_tmp = array();
				foreach($s_tmpc as $tmpcc)
				{
					// because the bloody csv files don't containt 100 106, that would mean 100,102,104,106, but they contain 100106
					// so try to be intelligent and split those
					if ($tmpcc > 100000)
					{
						$s_from = $tmpcc{0}.$tmpcc{1}.$tmpcc{2};
						$s_to = $tmpcc{3}.$tmpcc{4}.$tmpcc{5};
						for ($pup = $s_from; $pup <= $s_to; $pup+=2)
						{
							$s_tmp[] = $pup;
						}
					}
					else
					if ($tmpcc > 10000)
					{
						$s_from = $tmpcc{0}.$tmpcc{1};
						$s_to = $tmpcc{2}.$tmpcc{3}.$tmpcc{4};
						for ($pup = $s_from; $pup <= $s_to; $pup+=2)
						{
							$s_tmp[] = $pup;
						}
					}
					else
					{
						$s_tmp[] = $tmpcc;
					}
				}

				//echo "final sizes are ".dbg::dump($s_tmp);	
				foreach($s_tmp as $tmpcc)
				{
					$sizes = true;
					$row["size"] = $tmpcc;
					if (is_oid($pkgs[$row["price"]][$row["size"]]))
					{
						$pk = obj($pkgs[$row["price"]][$row["size"]]);
						echo "for prod ".$dat->name()." got (".$pk->id().") packaging ".$row["price"]." for type ".$orig_row["s_type"]." <bR>";
					}
					else
					{
						echo "for prod ".$dat->name()." got NEW packaging ".$row["price"]." for type ".$orig_row["s_type"]." <bR>";
						$pk = obj();
						$pk->set_class_id(CL_SHOP_PRODUCT_PACKAGING	);
						$pk->set_parent($dat->id());
						$pk->save();

						$dat->connect(array(
							"to" => $pk->id(),
							"reltype" => 2 // RELTYPE_PACKAGING
						));
					}

					$pk->set_parent($dat->id());
					$pk->set_prop("price", $row["price"]);
					$pk->set_prop("user5", $row["size"]);
					$pk->set_name($dat->name());
					$pk->save();

					$lowest = min($lowest, $row["price"]);

					$used[$pk->id()] = true;
					$first = false;
				}
			}

			foreach($pak_sl as $pak_sl_id)
			{
				if (!$used[$pak_sl_id])
				{
					$dat->disconnect(array(
						"from" => $pak_sl_id
					));
					echo "disconnect from $pak_sl_id <br>";
				}
			}

			$dat->set_prop("price", $lowest);
			if (!$sizes)
			{
				echo "no size, setting status to not active <br>";
				$dat->set_status(STAT_NOTACTIVE);
				$log[] = "Panin toote ".$dat->name()." (".$dat->prop("user16").") staatuse mitteakttivseks, kuna ei leidnud &uuml;htegi suurust! ";
			}

			$dat->save();

			$this->restore_handle();


			$stat = fopen($imp_stat_file,"w");
			fwrite($stat, $orig_pcode);
			fclose($stat);

			flush();
			$items_done++;
		}

		echo "hear hear. prods done. <br>\n";

		$log[] = "importisin $items_done toodet";

		if ($o->prop("just_update_prod_data") != 1)
		{

		flush();
		// to make packages, group by image number and for all images where cnt > 1 create a package for all those prods

		echo "make existing packet lut <br>\n";
		flush();
		$pktl = array();
		/*$exist = new object_list(array(
			"class_id" => CL_SHOP_PACKET,
			"parent" => $o->prop("prod_folder")
		));
		foreach($exist->arr() as $t)
		{
			// user5 is image nr
			$pktl[$t->prop("user3")] = $t->id();
		}*/
		$this->db_query("SELECT aw_oid,user3 FROM aw_shop_packets");
		while ($row = $this->db_next())
		{
			$pktl[$row["user3"]] = $row["aw_oid"];
		}
		
		$query ="select *, count(*) as cnt from otto_prod_img ".
					"where nr = 1 AND p_pg is not null  group ".
					"by imnr having cnt > 1";
		$this->db_query($query);
		while ($row = $this->db_next())
		{
			if (is_oid($pktl[$row["imnr"]]))
			{
				$pko = obj($pktl[$row["imnr"]]);
				echo "for code $row[pcode] found packet ".$pko->id()." <br>\n";
				flush();
			}
			else
			{
				$pko = obj();
				$pko->set_class_id(CL_SHOP_PACKET);
				$pko->set_parent($o->prop("prod_folder"));
				$pko->set_name("Pildi ".$row["imnr"]." pakett");
				$pktl[$row["imnr"]] = $pko->save();
				echo "for code ".$row["pcode"]." created packet ".$pko->id()." <br>\n";
				flush();
			}

			$pko->set_prop("user3", $row["imnr"]);
			$pko->set_prop("user4", $row["p_pg"]);
			$pko->set_prop("user5", $row["p_nr"]);
			$pko->save();

			// now connect all prods to it
			
			// make list of curr conns by prod code
			$cprods = array();
			foreach($pko->connections_from(array("type" => 1 )) as $c)	 // PRODUCT
			{
				$tmp = $c->to();
				$cprods[$tmp->prop("user20")] = $tmp;
			}

			$used = array();
			$this->save_handle();
			$this->db_query("SELECT pcode FROM otto_prod_img WHERE imnr = '$row[imnr]' group by pcode");
			while ($row = $this->db_next())
			{
				if (!$row["pcode"])
				{
					continue;
				}
				$used[$row["pcode"]] = 1;

				echo "connected packet ".$pko->id()." to prod ".$row["pcode"]." <br>";
				if (!$cprods[$row["pcode"]])
				{
					$_id = $this->_get_id_by_code($row["pcode"]);
					if (is_oid($_id) && $this->can("view", $_id))
					{
						$pko->connect(array(
							"to" => $_id,
							"reltype" => 1 // PRODUCT
						));
					}
				}
			}

			// go over cprods and if some are not used, kill them
			foreach($cprods as $cpcode => $cp)
			{
				if (!$used[$cpcode])
				{
					$pko->disconnect(array(
						"from" => $cp->id()
					));
				}
			}

			// check page
			$pk_pages = array();
			foreach($pko->connections_from(array("type" => 1)) as $c)
			{
				$tmp = $c->to();
				$pk_pages[$tmp->prop("user18")] = $tmp->prop("user18");
			}

			if (true || count($pk_pages) == 1)
			{
				$page = reset($pk_pages);
				//echo "cnt = 1 , user4 = ".$o->prop("user4")." page = $page <br>\n";
				if ($pko->prop("user4") != $page)
				{
					$pko->set_prop("user4", $page);
					$pko->save();

					// check image table
					if ($pko->prop("user3") != "")
					{
						$impg = $this->db_fetch_field("SELECT p_pg FROM otto_prod_img WHERE imnr = '".$pko->prop("user3")."'", "p_pg");
						if ($impg != $page)
						{
							$this->db_query("UPDATE otto_prod_img SET p_pg = '$page' WHERE imnr = '".$pko->prop("user3")."'");
							//echo "q = UPDATE otto_prod_img SET p_pg = '$page' WHERE imnr = '".$o->prop("user3")."' <br>";
						}
					}
					echo "changed page to $page <br>\n";
					flush();
				}
			}

			$this->restore_handle();
		}

		} // just update prod data

		// fix-missing images
		echo "try fix missing images! <br>";

		$lf = aw_ini_get("site_basedir")."/files/import_last_log.txt";
		$this->put_file(array(
			"file" => $lf,
			"content" => join("\n", $log)
		));

		$this->pictfix(array());

		$this->fix_image_codes();

		$this->fix_prices();

		// clear cache
		$cache = get_instance("cache");
		$cache->full_flush();

		$fld = aw_ini_get("site_basedir")."/prod_cache";
		$cache = get_instance("cache");
		$cache->_get_cache_files($fld);
		echo 'about to delete '.count($cache->cache_files2).' files<br />';

		foreach(safe_array($cache->cache_files2) as $file)
		{
			unlink($file);
		}

		die(t("all done! <br>"));
	}

	function conv($str)
	{
		$str = str_replace(chr(207), "ž", $str);
		return $str;
	}

	function char_replacement($str)
	{
		/* l2ti t2hed
		,
		,chr(226)
		,chr(238)
		,chr(239)
		,chr(231)
		

		Andmete allikaks oli:
		Impordifail: http://terryf.struktuur.ee/str/otto/import/data/LAT.T004-11.txt
		Tekst saidil (skrolli alla): http://otto-latvia.struktuur.ee/134393
		kooditabel: http://www.science.co.il/Language/Character-Code.asp?s=1257
		*/
		if (aw_global_get("lang_id") == 6)
		{
			/* uus */
			$needle = array();
			$haystack = array();

			$needle = array(
			chr(207), //254
			chr(240), //251
			chr(165), //238
			chr(236), //234
			chr(191), //242
			chr(199), //226
			chr(148), //199
			chr(239), //231
			chr(134), //239
			chr(174), //236
			chr(149), //231
			chr(192), //242
			chr(228), //240
			chr(180), //238
			chr(250), //237
			chr(137), //200
			chr(208), //45
			chr(130), //226
			chr(153), //237
			chr(179), //34
			chr(129), //194
			chr(210), //34
			chr(211), //34
			chr(178), //34
			chr(175), //236
			chr(183), //208
			chr(177), //206
			chr(185), //207
			chr(225), //208
			chr(186), //239
			chr(158) //236
			);

			
			
			
				
			
			$haystack = array(
			chr(254),
			chr(251),
			chr(238),
			chr(234),
			chr(242),
			chr(226),
			chr(199),
			chr(231),
			chr(239),
			chr(236),
			chr(231),
			chr(242),
			chr(240),
			chr(238),
			chr(237),
			chr(200),
			chr(45),
			chr(226),
			chr(237),
			chr(34),
			chr(194),
			chr(34),
			chr(34),
			chr(34),
			chr(236),
			chr(208),
			chr(206),
			chr(207),
			chr(208),
			chr(239),
			chr(234)
			);
		}
		else
		{
   $needle = array(
			chr(213),	// ylakoma;
			chr(235),	// zhee;
			chr(159),	// &uuml;
			chr(134), 	// &Uuml;
			chr(154),	// &ouml;
			chr(228), // shaa
			chr(138),	// &auml;
			chr(205),	// &Otilde;
			chr(155), 	// &otilde;
			chr(199),
			chr(200),
			chr(210),
			chr(211),
			chr(175),

		);
		$haystack = array(
			chr(180),	// ylakoma;
			chr(158),// zhee;
			chr(252),// &uuml;
			chr(220),	// &Uuml;
			chr(246),// &ouml;
			chr(154), // shaa
			chr(228),// &auml;
			chr(213),// &Otilde;
			chr(245),	// &otilde;
			chr(34),
			chr(34),
			chr(34),
			chr(34),
			chr(216),
		);
		}

		if(is_array($str))
		{
			foreach($str as $key=>$value)
			{
				$str[$key]= str_replace($needle,$haystack,$value);
			}
		}
		else
		{
			$str = str_replace($needle,$haystack,$str);
		}
		return $str;
	}

	/**

		@attrib name=submit_add_cart nologin=1

	**/
	function submit_add_cart($arr)
	{
		$afv = 1;

		if (!$arr["testjs"])
		{
			$afv = 2;
		}

		if (strpos($arr["return_url"], "?") === false)
		{
			$retval = aw_ini_get("baseurl").str_replace("afto=1", "", $arr["return_url"])."?afto=".$afv;
		}
		else
		{
			$retval = aw_ini_get("baseurl").str_replace("afto=1", "", $arr["return_url"])."&afto=".$afv;
		}

		if (!$arr["testjs"])
		{
			//return $retval;
		}

		// rewrite some vars that are hard to rewire in js and forward to shop order cart
		$vars = $arr;
		if ($arr["spid"])
		{
			$vars["order_data"] = array();
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["color"] = ($arr["order_data_color".$arr["spid"]] != "" ? $arr["order_data_color".$arr["spid"]] : "---");
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["size"] = $arr["size_name".$arr["spid"]];
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["url"] = $retval;

			$vars["add_to_cart"] = array();
			$vars["add_to_cart"][$arr["add_to_cart".$arr["spid"]]] = $arr["add_to_cart_count".$arr["spid"]];
		}
		else
		{
			$vars["order_data"] = array();
			$vars["order_data"][$arr["add_to_cart"]]["color"] = ($arr["order_data_color"] != "" ? $arr["order_data_color"] : "---");
			$vars["order_data"][$arr["add_to_cart"]]["size"] = $arr["size_name"];
			$vars["order_data"][$arr["add_to_cart"]]["url"] = $retval;

			$vars["add_to_cart"] = array();
			$vars["add_to_cart"][$arr["add_to_cart"]] = $arr["add_to_cart_count"];
		}

		$i = get_instance(CL_SHOP_ORDER_CART);
		$i->submit_add_cart($vars);

		return $retval;
	}

	/** 

	@attrib name=pictfix

	**/
	function pictfix($arr)
	{
		$this->pictimp(array(), true);
	}

	function do_post_import_fixes($obj)
	{
		/*echo chr(154);
		echo chr(137);
		die();*/
		//'user17' => '%Î
		$query = 'select aw_oid, tauser2 from aw_shop_products where '.
						' tauser2 like "%Î%" or tauser2 like "%Ï%" or tauser2 like"%'.chr(137).'%"';
		//echo $query,"<br><br>";
		$this->db_query($query);

		//echo $this->num_rows(),"<br>";
		while($arr = $this->db_next())
		{
			//echo $arr['user17'],"    ";
			$arr['tauser2'] = $this->char_replacement($arr['tauser2']);
			//echo $arr['user17'],"    ";
			$query = 'update aw_shop_products set tauser2="'.$arr['tauser2'].'" where aw_oid='.$arr['aw_oid'].' limit 1';
			echo $query,"<br>";
			$this->save_handle();
			$this->db_query($query);
			$this->restore_handle();
			//echo $query,"<br>";
		}
	}

	/** if no random other images show for some products, call this
	
		@attrib name=fix_image_codes

	**/
	function fix_image_codes()
	{
		return;
		echo "fixing image pages <br>\n";
		flush();
		$this->db_query("SELECT * FROM otto_prod_img WHERE p_pg IS NULL or p_nr IS NULL ");
		while ($row = $this->db_next())
		{
			if ($row["pcode"] == "hall" || substr($row["pcode"], 0, 3) == "bee")
			{
				continue;
			}
			echo "pcode = $row[pcode] <br>\n";
			flush();			
			$this->save_handle();
			// find the correct ones from the prod by code
			$ol = new object_list(array(
				"class_id" => CL_SHOP_PRODUCT,
				"user20" => $row["pcode"]
			));
			if ($ol->count() > 0)
			{
				$o = $ol->begin();
				$pg = $o->prop("user18");
				$nr = $o->prop("user19");
				$this->db_query("UPDATE otto_prod_img SET p_pg = '$pg', p_nr = '$nr' WHERE pcode = '$row[pcode]' AND imnr = '$row[imnr]' AND nr = '$row[nr]'");
				echo "fixed code $row[pcode] <br>\n";
				flush();
			}
			$this->restore_handle();
		}
		echo ("all done! ");
	}

	/**

		@attrib name=fix_prices

	**/
	function fix_prices()
	{
		$ol = new object_list(array("class_id" => CL_SHOP_PRODUCT_PACKAGING, "price" => 0));
		foreach($ol->arr() as $o)
		{
			$c = reset($o->connections_to(array("type" => 2, "from.class_id" => CL_SHOP_PRODUCT)));
			if (!$c)
			{
				echo ("unconnected packaging ".$o->id()."!!!");
				continue;
			}
			$p = $c->from();
			$pg = $p->prop("user18");
			$nr = $p->prop("user19");
			$size = $o->prop("user5");

			$this->db_query("SELECT * FROM otto_imp_t_prices WHERE pg = '$pg' AND nr = '$nr'");
			while ($row = $this->db_next())
			{
				// find the correct size
				$sizes = $this->make_keys($this->_proc_size($row["size"]));
				if (isset($sizes[$size]))
				{
					echo "found price $row[price] for packet ".$o->name()."! <br>";
					$o->set_prop("price", $row["price"]);
					$o->save();
				}
			}
		}
		echo "all done! <br>";	
	}

	function _proc_size($size)
	{
		$s_tmpc = explode(",", $size);
		$s_tmp = array();
		foreach($s_tmpc as $tmpcc)
		{
			// because the bloody csv files don't containt 100 106, that would mean 100,102,104,106, but they contain 100106
			// so try to be intelligent and split those
			if ($tmpcc > 100000)
			{
				$s_from = $tmpcc{0}.$tmpcc{1}.$tmpcc{2};
				$s_to = $tmpcc{3}.$tmpcc{4}.$tmpcc{5};
				for ($pup = $s_from; $pup <= $s_to; $pup+=2)
				{
					$s_tmp[] = $pup;
				}
			}
			else
			if ($tmpcc > 10000)
			{
				$s_from = $tmpcc{0}.$tmpcc{1};
				$s_to = $tmpcc{2}.$tmpcc{3}.$tmpcc{4};
				for ($pup = $s_from; $pup <= $s_to; $pup+=2)
				{
					$s_tmp[] = $pup;
				}
			}
			else
			{
				$s_tmp[] = $tmpcc;
			}
		}

		return $s_tmp;
	}

	function mk_file($file,$separator)
	{
		$filestr = file_get_contents($file);

		$len = strlen($filestr);
		$linearr = array();
		$in_cell = false;
		for ($pos=0; $pos < $len; $pos++)
		{
			if ($filestr[$pos] == "\"")	
			{
				if ($in_cell == false)
				{
					// pole celli sees ja jutum2rk. j2relikult algab quoted cell
					$in_cell = true;
					$line.=$filestr[$pos];
				}
				else
				if ($in_cell == true && ($filestr[$pos+1] == $separator || $filestr[$pos+1] == "\n" || $filestr[$pos+1] == "\r"))
				{
					// celli sees ja jutum2rk ja j2rgmine on kas semikas v6i reavahetus, j2relikult cell l6peb
					$in_cell = false;
					$line.=$filestr[$pos];
				}
				else
				{
					// dubleeritud jutum2rk
					$line.=$filestr[$pos];
				}
			}
			else
			if ($filestr[$pos] == $separator && $in_cell == false)
			{
				// semikas t2histab celli l6ppu aint siis, kui ta pole jutum2rkide vahel
				$in_cell = false;
				$line.=$filestr[$pos];
			}
			else
			if (($filestr[$pos] == "\n" || $filestr[$pos] == "\r") && $in_cell == false)
			{
				// kui on reavahetus ja me pole quotetud celli sees, siis algab j2rgmine rida

				// clearime j2rgneva l2bu ka 2ra
				if ($filestr[$pos+1] == "\n" || $filestr[$pos+1] == "\r")
					$pos++;
				$linearr[] = $line;
				$line = "";
			}
			else
				$line.=$filestr[$pos];
		}

		if (trim($line) != "")
		{
			$linearr[] = $line;
		}
		return $linearr;
	}

	function is_csv($url)
	{
		$fc = file_get_contents($url);
		if (strpos($fc, "onLoad") !== false || strpos($fc, "javascript") !== false)
		{
			return false;
		}
		return true;
	}

	function _init_folders_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "pgs",
			"caption" => t("Lehed komaga eraldatult"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "awfld",
			"caption" => t("AW Kataloogi ID"),
			"align" => "center"
		));
	}

	function do_folders_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_folders_tbl($t);

		$data = $this->_get_fld_dat();

		$cnt = 1;
		foreach($data as $fld => $row)
		{
			if (!$fld)
			{
				continue;
			}
			$t->define_data(array(
				"pgs" => html::textbox(array(
					"name" => "dat[$cnt][pgs]",	
					"value" => join(",", $row),
					"size" => "80"
				)),
				"awfld" => html::textbox(array(
					"name" => "dat[$cnt][awfld]",	
					"value" => $fld,
					"size" => "10"
				)),
			));
			$cnt++;
		}
		$t->define_data(array(
			"pgs" => html::textbox(array(
				"name" => "dat[$cnt][pgs]",	
				"value" => "",
				"size" => "80"
			)),
			"awfld" => html::textbox(array(
				"name" => "dat[$cnt][awfld]",	
				"value" => "",
				"size" => "10"
			)),
		));

		$t->set_sortable(false);
	}

	function _get_fld_dat()
	{
		$ret = array();
		$this->db_query("SELECT * FROM otto_imp_t_p2p WHERE lang_id = ".aw_global_get("lang_id"));
		while ($row = $this->db_next())
		{
			$ret[$row["fld"]][] = $row["pg"];
		}
		return $ret;
	}

	function read_img_from_baur($pcode)
	{
		$url = "http://www.baur.de/is-bin/INTERSHOP.enfinity/WFS/BaurDe/de_DE/-/EUR/BV_ParametricSearch-Progress;sid=9wziDKL5zmzox-N_94eyWWD0hj6lQBejDB2TPuW1?ls=0&_PipelineID=search_pipe_bbms&_QueryClass=MallSearch.V1&Servicelet.indexRetrieverPipelet.threshold=0.7&Orengelet.sortPipelet.sortResultSetSize=10&Query_Text=".$pcode."&Kategorie_Text=&x=23&y=13";

		$fc = $this->file_get_contents($url);
		if (strpos($fc, "leider keine Artikel gefunden") !== false)
		{
			return $this->read_img_from_schwab($pcode);
		}

		preg_match_all("/ProductRef=(.*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
		$pcs = array_unique($mt[1]);

		foreach($pcs as $n_pc)
		{
			$url2 = "http://www.baur.de/is-bin/INTERSHOP.enfinity/WFS/BaurDe/de_DE/-/EUR/BV_DisplayProductInformation-ProductRef;sid=vawch68xzhk1fe62PgtM0m08zJ5byxprRr3IpZL-?ls=0&ProductRef=".$n_pc."&SearchBack=true&SearchDetail=true";
			$fc = $this->file_get_contents($url2);

			preg_match_all("/http\:\/\/image01(.*)jpg/imsU", $fc, $mt, PREG_PATTERN_ORDER);
			$pics = array_unique($mt[0]);
			$fp = basename($pics[0], ".jpg");
			
			preg_match("/OpenPopUpZoom\('\d*','\d*','(.*)'\)/imsU", $fc, $mt);
			$popurl = $mt[1];
			
			$fc_p = $this->file_get_contents($popurl);

			preg_match("/<frame name=\"_popcont\" src=\"(.*)\"/imsU", $fc_p, $mt);
			$contenturl = $mt[1];

			$fc_c = $this->file_get_contents($contenturl);

			preg_match_all("/http\:\/\/image01(.*)jpg/imsU", $fc_c, $mt, PREG_PATTERN_ORDER);
			$pics = array_unique($mt[0]);

			$pa = array($fp => $fp);
			foreach($pics as $pic)
			{
				$tmp = basename($pic, ".jpg");
				$pa[$tmp] = $tmp;
			}

			// now pa contains all images for this one. 

			$cnt = 1;
			// insert images in db
			foreach($pa as $pn)
			{
				// check if the image combo already exists
				$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$pn' AND nr = '$cnt' AND pcode = '$pcode'", "pcode");
				if (!$imnr)
				{
					echo "insert new image $pn <br>\n";
					flush();
					$q = ("
						INSERT INTO 
							otto_prod_img(pcode, nr,imnr, server_id) 
							values('$pcode','$cnt','$pn', 2)
					");
					//echo "q = $q <br>";
					$this->db_query($q);
				}
				else
				{
					echo "existing image $pn <br>\n";
				}
				$cnt++;
			}
		}
	}

	/**

		@attrib name=swt

		@param pcode required

	**/
	function swt($arr)
	{
		return $this->pictimp(false,false);
	}

	function read_img_from_schwab($pcode)
	{
		$url = "http://ww2.schwab.de/is-bin/INTERSHOP.enfinity/WFS/SchwabDe/de_DE/-/EUR/SV_ParametricSearch-Progress;sid=CUEKcPjDjXgLcLrISj06UONvQYLj_AIgPN2HQ_xO?_PipelineID=search_pipe_svms&_QueryClass=MallSearch.V1&ls=0&Orengelet.sortPipelet.sortCursorPosition=0&Orengelet.sortPipelet.sortResultSetSize=10&SearchDetail=one&Query_Text=".$pcode;
		$fc = $this->file_get_contents($url);

		if (strpos($fc, "Wir konnten leider keine Ergebnisse") !== false)
		{
			return $this->read_img_from_albamoda($pcode);
		}

		// match prod urls
		preg_match_all("/ProductRef=(.*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
		$pcs = array_unique($mt[1]);
		//echo "got pcs as ".dbg::dump($pcs)."\n";

		foreach($pcs as $prodref)
		{
			if ($prodref == "")
			{
				continue;
			}


			$prod_url = "http://ww2.schwab.de/is-bin/INTERSHOP.enfinity/WFS/SchwabDe/de_DE/-/EUR/SV_DisplayProductInformation-ProductRef;sid=CUEKcPjDjXgLcLrISj06UONvQYLj_AIgPN2HQ_xO?ls=&ProductRef=".$prodref."&SearchDetail=1&aktPage=&Query_Text=371388&ArtikelID_Text=&Personen_Text=&PreisMin_Text=&PreisMax_Text=&Hersteller_Text=&Artikel_Text=&Stichwoerter_Text=&Artikel=&Hersteller=&Trend=";

			$fc2 = $this->file_get_contents($prod_url);

			// get first image
			preg_match("/http:\/\/image01\.otto\.de:80\/pool\/formatb\/(\d+).jpg/imsU", $fc2, $mt);
			$first_im = $mt[1];

			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
			if (!$imnr)
			{
				echo "insert new image $first_im <br>\n";
				flush();
				$q = ("
					INSERT INTO 
						otto_prod_img(pcode, nr,imnr, server_id) 
						values('$pcode','1','$first_im', 3)
				");
				//echo "q = $q <br>";
				$this->db_query($q);
			}

			// get other images
			preg_match_all("/jump_img\('(\d+)'\)/imsU", $fc2, $mt, PREG_PATTERN_ORDER);
			$otherim = $mt[1];

			foreach($otherim as $nr)
			{
				$o_url = $prod_url."&bild_nr=".$nr;
				$fc3 = $this->file_get_contents($o_url);

				preg_match("/http:\/\/image01\.otto\.de:80\/pool\/formatb\/(\d+).jpg/imsU", $fc3, $mt);
				$im = $mt[1];

				$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$im' AND nr = '$nr' AND pcode = '$pcode'", "pcode");
				if (!$imnr)
				{
					echo "insert new image $im <br>\n";
					flush();
					$q = ("
						INSERT INTO 
							otto_prod_img(pcode, nr,imnr, server_id) 
							values('$pcode','$nr','$im', 3)
					");
					//echo "q = $q <br>";
					$this->db_query($q);
				}
			}
		}		
	}

	function read_img_from_albamoda($pcode)
	{
		$url = "http://www.albamoda.de/is-bin/INTERSHOP.enfinity/WFS/AlbaModaDe/de_DE/-/EUR/AM_ParametricSearch-Progress;sid=ytMKUs3doZEKUo_WSsAnctZxm9kZ5q0_w_o_iYvu?_PipelineID=search_pipe_am_de&Orengelet.sortPipelet.sortResultSetSize=10&Query_Text=".$pcode."&_QueryClass=MallSearch.V1";
		$fc = $this->file_get_contents($url);
		if (strpos($fc, "Es wurden leider keine Artikel") !== false)
		{
			return $this->read_img_from_heine($pcode);
		}

		// match prod urls
		preg_match_all("/displayART\('(.*)'\)/imsU", $fc, $mt, PREG_PATTERN_ORDER);
		$pcs = array_unique($mt[1]);
		//echo "got pcs as ".dbg::dump($pcs)."\n";

		foreach($pcs as $prodref)
		{
			if ($prodref == "")
			{
				continue;
			}
			$prod_url = "http://www.albamoda.de/is-bin/INTERSHOP.enfinity/WFS/AlbaModaDe/de_DE/-/EUR/AM_ViewProduct-ProductRef;sid=ytMKUs3doZEKUo_WSsAnctZxm9kZ5q0_w_o_iYvu?SearchArt1=".$prodref."&SearchDetail=1&ProductRef=".$prodref."&aktProductRef=".$prodref."&Query_Text=".$pcode."&OsPsCP=0&searchpipe=search_pipe_am_de";
			$fc2 = $this->file_get_contents($prod_url);

			// get first image
			preg_match("/http:\/\/image01\.otto\.de:80\/pool\/AlbaModaDe\/de_DE\/images\/albamoda_formatb\/(\d+).jpg/imsU", $fc2, $mt);
			$first_im = $mt[1];

			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
			if (!$imnr)
			{
				echo "insert new image $first_im <br>\n";
				flush();
				$q = ("
					INSERT INTO 
						otto_prod_img(pcode, nr,imnr, server_id) 
						values('$pcode','1','$first_im', 4)
				");
				//echo "q = $q <br>";
				$this->db_query($q);
			}
		}
	}

	function read_img_from_heine($pcode)
	{
		$url = "http://www.neu.heine.de/is-bin/INTERSHOP.enfinity/WFS/HeineDe/de_DE/-/EUR/SH_ParametricSearch-Progress;sid=YtPBfo9Zn47Dfs1V6VzvXpT13mqu32H0mc0eO27a?ls=&ArtikelID_Text=".$pcode."&y=9&x=11";
		$fc = $this->file_get_contents($url);

		if (strpos($fc, "keine passenden Ergebnisse") !== false)
		{
			echo "NO IMAGE FOUND FOR PCODE $pcode <br>\n";
			flush();
			return;
		}

		// get prods
		preg_match_all("/\?ProductRef=(.*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
		$pcs = array_unique($mt[1]);
		//echo "got pcs as ".dbg::dump($pcs)."\n";

		foreach($pcs as $prodref)
		{
			if ($prodref == "")
			{
				continue;
			}
			$prod_url = "http://www.neu.heine.de/is-bin/INTERSHOP.enfinity/WFS/HeineDe/de_DE/-/EUR/SH_ViewProduct-ProductRef;sid=YtPBfo9Zn47Dfs1V6VzvXpT13mqu32H0mc0eO27a?ProductRef=".$prodref."&Source=Search";
			$fc2 = $this->file_get_contents($prod_url);
			

			preg_match("/http:\/\/image01\.otto\.de:80\/pool\/HeineDe\/de_DE\/images\/format_hv_ds_a\/(\d+).jpg/imsU", $fc2, $mt);
			$first_im = $mt[1];

			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
			if (!$imnr)
			{
				echo "insert new image $first_im <br>\n";
				flush();
				$q = ("
					INSERT INTO 
						otto_prod_img(pcode, nr,imnr, server_id) 
						values('$pcode','1','$first_im', 5)
				");
				//echo "q = $q <br>";
				$this->db_query($q);
			}

		}
	}

	function file_get_contents($url)
	{
		for($i = 0; $i < 3; $i++)
		{
			$fc = @file_get_contents($url);
			if ($fc != "")
			{
				return $fc;
			}
		}
		echo "SITE $url seems to be <font color=red>DOWN</font> <br>\n";
		flush();
		return "";
	}

	function _do_del_prods($prods)
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"user20" => $prods
		));

		if (!$ol->count())
		{
			return;
		}

		foreach($ol->arr() as $o)
		{
			$pkol = new object_list($o->connections_from(array("type" => "RELTYPE_PACKAGING")));
			echo "kusututan pakendid ".join(",", $pkol->names())." <br>";
			$pkol->delete(true);
		}

		// get all packagings that have the prods
		$c = new connection();
		$list = $c->find(array(
			"from.class_id" => CL_SHOP_PACKET,
			"type" => 1,
			"to.oid" => $ol->ids()
		));

		echo "kusututan tooted ".join(",", $ol->names())." <br>";
		$ol->delete(true);

		// go over packets and see if some have no prods
		foreach($list as $conn)
		{
			if ($this->can("view", $conn["from"]))
			{
				$pkt = obj($conn["from"]);
				if (count($pkt->connections_from(array("type" => 1))) == 0)
				{
					echo "kustutan paketi ".$pkt->name()." <br>";
					$pkt->delete(true);
				}
			}
		}
		echo "valmis! <br>";
	}

	function _get_id_by_code($code)
	{
		$id = $this->db_fetch_field("SELECT aw_oid FROM aw_shop_products LEFT JOIN objects ON objects.oid = aw_shop_products.aw_oid = objects.oid WHERE user20 = '$code' AND objects.status > 0 AND objects.lang_id = ".aw_global_get("lang_id"), "aw_oid");
		return $id;
	}

	function _get_ids_by_code($code)
	{
		$ret = array();
		$this->db_query("SELECT aw_oid FROM aw_shop_products LEFT JOIN objects ON objects.oid = aw_shop_products.aw_oid = objects.oid WHERE user20 = '$code' AND objects.status > 0 AND objects.lang_id = ".aw_global_get("lang_id"));
		while ($row = $this->db_next())
		{
			$ret[] = obj($row["aw_oid"]);
		}
		return $ret;
	}
}

?>
