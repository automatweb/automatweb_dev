<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_import.aw,v 1.43 2006/03/14 14:53:33 dragut Exp $
// otto_import.aw - Otto toodete import 
/*

@classinfo syslog_type=ST_OTTO_IMPORT relationmgr=yes no_status=1 no_comment=1 prop_cb=1

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

	@groupinfo files_import caption="Imporditavad failid" parent=files

		@property fnames type=textarea rows=30 cols=80 group=files_import
		@caption Failinimed

		@property first_site_to_search_images type=select group=files_import field=meta method=serialize
		@caption Esimene leht kust pilte otsitakse

	@groupinfo files_order caption="Failide j&auml;rjekord" parent=files

		@property files_order type=table group=files_order
		@caption Failide n&auml;itamise j&auml;rjekord
	
	@groupinfo file_suffix caption="Failide suffiksid" parent=files

		@property file_suffix type=table group=file_suffix
		@caption Failide suffiksid

@groupinfo discount_products caption="Soodustooted"

	@property discount_products_file type=textbox size=60 group=discount_products field=meta method=serialize
	@caption Soodustoodete faili asukoht
		
	@property discount_products_parents type=textbox size=60 group=discount_products field=meta method=serialize
	@caption Kausta id, kus all soodustooted asuvad

	@property discount_products_count type=text store=no group=discount_products
	@caption Ridu tabelis

	@property import_discount_products type=text store=no group=discount_products
	@caption &nbsp;

	@property clear_discount_products type=text store=no group=discount_products
	@caption &nbsp;

@groupinfo foldersa caption="Kataloogid"

	@groupinfo categories caption="Kategooriad" parent=foldersa
		
		@property categories type=table store=no group=categories no_caption=1
		@caption Kategooriad
	
	@groupinfo category_settings caption="Kategooriate seaded" parent=foldersa

		@property bubble_pictures type=table group=category_settings
		@caption Mullipildid

		@property firm_pictures type=table group=category_settings
		@caption Firmapildid

		@property sideways_pages type=textarea rows=4 cols=80 table=objects field=meta method=serialize group=category_settings
		@comment Ilmselt hetkel ei t&ouml;&ouml;ta!
		@caption Landscape vaatega lehed

	@groupinfo folders caption="Kataloogid (deprecated)" parent=foldersa

		@property folders type=table store=no group=folders no_caption=1

		@property inf_pages type=textarea rows=3 cols=40 group=folders field=meta method=serialize table=objects
		@caption L&otilde;pmatus vaatega lehed

	@groupinfo folderspri caption="Kataloogide m&auml;&auml;rangud (deprecated)" parent=foldersa

		@property foldpri type=textarea rows=20 cols=20 table=objects field=meta method=serialize group=folderspri
		@caption T&auml;htede prioriteedid

	@groupinfo foldersnames caption="Kaustade nimed (deprecated)" parent=foldersa

		@property foldernames type=table store=no group=foldersnames
		@caption Kaustade nimed impordi jaoks

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

	property force_7_view_for_trends type=textbox table=objects field=meta method=serialize group=views
	caption 7 pildiga trendide lehed
	comment Ainult BonPrix. lk koodide asemel kaustade id-d, mille all 7st vaadet n&auml;idata

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

			case "first_site_to_search_images":
				// this one for Bonprix only:
				if (aw_ini_get("site_id") == 276 || aw_ini_get("site_id") == 277)
				{
					$prop['options'] = array(
						"bp_pl" => "Poola Bonprix",
						"bp_de" => "Saksa Bonprix"	
					);
					$retval = PROP_OK;
				}
				else
				{
					$retval = PROP_IGNORE;
				}
				break;
			
			case "import_discount_products":
				$prop['value'] = html::href(array(
					"caption" => t("Impordi soodustooted"),
					"url" => $this->mk_my_orb("import_discount_products", array(
						"id" => $arr['obj_inst']->id(),
					)),
				));
				break;

			case "clear_discount_products":
				$prop['value'] = html::href(array(
					"caption" => t("T&uuml;hjenda soodustoodete tabel ( olenemata keelest! )"),
					"url" => $this->mk_my_orb("clear_discount_products", array(
						"id" => $arr['obj_inst']->id(),
					)),
				));
				break;

			case "discount_products_count":
				$all_products_count = $this->db_fetch_field("select count(*) as count from bp_discount_products", "count");
				$products_count = $this->db_fetch_field("select count(*) as count from bp_discount_products where lang_id=".aw_global_get('lang_id'), "count");
				$prop['value'] = $products_count." / ".$all_products_count;
				break;

			case "foldernames":
				$this->_foldernames($arr);
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
					$product_codes = explode(",", $prop["value"]);
					foreach ($product_codes as $key => $product_code)
					{
						$product_codes[$key] = str_replace(" ", "", $product_code);
					}
					$this->_do_del_prods($product_codes);
				}
				break;

			case "foldernames":
				$dat = $arr["request"]["dat"];
				$inf = array();
				foreach(safe_array($dat) as $cnt => $entry)
				{
					if (trim($entry["cat"]) != "" && trim($entry["fld"]) != "")
					{
						foreach(explode(",", $entry["fld"]) as $r_fld)
						{
							$inf[] = $r_fld."=".$entry["cat"];
						}
					}
				}
				$val = join(",", $inf);
				$arr["obj_inst"]->set_meta("foldernames", $val);
				break;
		}
		return $retval;
	}	

	function callback_mod_tab($arr)
	{
		if ($arr['id'] == 'discount_products')
		{
			// lets show the tab only in bonprix
			if (aw_ini_get("site_id") != 276 && aw_ini_get("site_id") != 277)
			{
				return false;
			}
		}
	}

	function _init_fn_t(&$t)
	{
		$t->define_field(array(
			"name" => "cat_name",
			"caption" => t("Kategooria nimi"),
		));

		$t->define_field(array(
			"name" => "fld_name",
			"caption" => t("AW Kataloogi ID"),
		));
	}
	function _foldernames($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_fn_t($t);

		$val = $arr["obj_inst"]->meta("foldernames");
		$inf = explode(",", $val);
		$dat = array();
		foreach($inf as $pair)
		{
			list($k, $v) = explode("=", $pair);
			$dat[trim($k)] = trim($v);
		}

		$cnt = 1;
		foreach($dat as $aw_fld => $name)
		{
			$t->define_data(array(
				"cat_name" => html::textbox(array(
					"name" => "dat[$cnt][cat]",
					"value" => $name
				)),
				"fld_name" => html::textbox(array(
					"name" => "dat[$cnt][fld]",
					"value" => $aw_fld
				)),
			));
			$cnt++;
		}

		for($i = 0; $i<10; $i++)
		{
			$t->define_data(array(
				"cat_name" => html::textbox(array(
					"name" => "dat[$cnt][cat]",
					"value" => ""
				)),
				"fld_name" => html::textbox(array(
					"name" => "dat[$cnt][fld]",
					"value" => ""
				)),
			));
			$cnt++;
		}
		$t->set_sortable(false);
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
			echo "START IMPORT<br>";
			if ($arr["obj_inst"]->prop("do_pict_i"))
			{
				echo "[ Tee piltide import ]<br>\n";
				$this->doing_pict_i = true;
			}
			if ($arr['obj_inst']->prop("restart_pict_i"))
			{
				echo "[ Piltide import algusest ]<br>\n";
				$this->restart_pictures_import = true;
			}
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
		$this->added_images = array();
		set_time_limit(0);
		echo "-----------[ start of picture import function ]------------------<br>";
		if (is_object($arr))
		{
			$import_obj = $arr;
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
			$data = array("947824");
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
			$skip_to = "";
			echo "fixing not found codes:".join(", ",$data)." <br><br>";
		}

		$total = count($data);
		$cur_cnt = -1;
		$start_time = time();
		//$data = array("2671881");
		foreach ($data as $pcode) 
		{
			$pcode = str_replace(" ", "", $pcode);
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
			
			// BONPRIX:
			if (aw_ini_get("site_id") == 276 || aw_ini_get("site_id") == 277)
			{
				$this->bonprix_picture_import(array(
					'pcode' => $pcode,
					'import_obj' => $import_obj,
					'start_time' => $start_time,
				));
			}
			else
			{

//			$url = "http://www.otto.de/is-bin/INTERSHOP.enfinity/WFS/OttoDe/de_DE/-/EUR/OV_ParametricSearch-Progress;sid=bwNBYJMEb6ZQKdPoiDHte7MOOf78U0shdsyx6iWD?_PipelineID=search_pipe_ovms&_QueryClass=MallSearch.V1&ls=0&Orengelet.sortPipelet.sortResultSetSize=10&SearchDetail=one&Query_Text=".$pcode;

			$url = "http://www.otto.de/is-bin/INTERSHOP.enfinity/WFS/Otto-OttoDe-Site/de_DE/-/EUR/OV_ViewSearch-SearchStart;sid=mDuGagg9T0iHakspt6yqShOR_0e4OZ2Xs5qs8J39FNYYHvjet0FaQJmF?ls=0&Orengelet.sortPipelet.sortResultSetSize=15&SearchDetail=one&stype=N&Query_Text=".$pcode;

//			echo "url = $url <br>";
			arr($url);
			flush();
			$html = $this->file_get_contents($url);
			echo "Page content loaded, parsing ...<br>";
			flush();
			// image is http://image01.otto.de:80/pool/OttoDe/de_DE/images/formatb/[number].jpg
			if (strpos($html,"Leider konnten wir im gesamten OTTO") !== false)
			{ 
				// read from baur.de
				echo "can't find an product for <b>$pcode</b> from otto.de, so searching from baur.de<br>\n";
				$this->read_img_from_baur($pcode);
			}
			else
			if (true /*!preg_match("/pool\/formatd\/(\d+).jpg/imsU",$html, $mt)*/)
			{
				echo "for product $pcode multiple images! <br>\n";
				flush();

				$o_html = $html;


				// subrequest for two images
				//die($html);
				if (!preg_match_all("/<\/table>\n<a href=\"Javascript:document\.location\.href='(.*)'\+urlParameter\"/imsU", $html, $mt, PREG_PATTERN_ORDER))
		//$data = array("");
				{
					preg_match_all("/<td valign=\"middle\" align=\"center\" height=\"\d+\" width=\"\d+\"><a href=\"Javascript:document\.location\.href='(.*)'\+urlParameter\"/imsU", $html, $mt, PREG_PATTERN_ORDER);
				}

				$urld = array();
				//echo (dbg::dump($mt));
				foreach($mt[1] as $url)
				{
					$url = $url."&SearchDetail=one&stype=N&Orengelet.sortPipelet.sortResultSetSize=15&Orengelet.SimCategorize4OttoMsPipelet.Similarity_Parameter=&Orengelet.sortPipelet.sortCursorPosition=0&Query_Text=".$pcode;

					$urld[$url] = $url;
				}
//die(dbg::dump($urld));
				foreach($urld as $url)
				{
					echo "url = $url <br>";
					$html = $this->file_get_contents($url);
//echo "got html $html <br>";
					preg_match_all("/Javascript:setImage\('(.*)\.jpg', '(\d+)'\)/imsU", $html, $mt, PREG_PATTERN_ORDER);
					$f_imnr = NULL;
//echo "mt = ".dbg::dump($mt)." \n";
//flush();
					// ach! if only single image then no js!!!
					if (count($mt[1]) == 0)
					{
						preg_match("/pool\/formatb\/(\d+)\.jpg/imsU",$html, $mt2);
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
							$this->added_images[] = $mt2[1];
						}
					}
					else
					{
						foreach($mt[1] as $idx => $img)
						{
							$imnr = basename($img, ".jpg");
							$q = "SELECT pcode FROM otto_prod_img WHERE imnr = '$imnr' AND nr = '".$mt[2][$idx]."' AND pcode = '$pcode'";
//echo "q = $q <br>";
							$t_imnr = $this->db_fetch_field($q, "pcode");
							if (!$f_imnr)
							{
								$f_imnr = $t_imnr.".jpg";
							}
//echo dbg::dump($t_imnr);
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
								$this->added_images[] = $mt[2][$idx];
							}
						}
					}

					// check for rundumanshiftph
					if (strpos($html, "rundum") !== false)
					{
						preg_match_all("/javascript:OpenPopUpZoom\('690','540','(.*)'\+selectedImage\);/imsU", $html, $mt);
						// get the rundum image number from the popup :(
						$r_html = file_get_contents($mt[1][1].$f_imnr);

						// save rundum
						// get rundum imnr from html
						preg_match("/http:\/\/image01\.otto\.de:80\/pool\/format360\/(.*)\.swf/imsU", $r_html, $mt);
						echo "set flash to true <br>";
						$this->db_query("UPDATE otto_prod_img SET has_flash = '$mt[1]' WHERE pcode = '$pcode' AND nr = 1");
					}

				}
			}
			else
			{
				$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
					values('$pcode',1,'$mt[1]')");	
				$this->added_images[] = $mt[1];

				$add = 0;
				// check if we got the main img
				if (preg_match("/pool\/formatd\/(\d+)\.jpg/imsU", $html, $mt))
				{
					$add = 1;
					$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
						values('$pcode','1','$mt[1]')");
					echo "rewrote first image as $mt[1] <Br>\n";
					flush();
					$this->added_images[] = $mt[1];
				}

				// also, other images, detect them via the jump_img('nr') js func
				preg_match_all("/Javascript:setImage\('(.*)\.jpg', '(\d+)'\)/imsU", $html, $mt, PREG_PATTERN_ORDER);
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
						$this->added_images[] = $mt[1];
					}
				}
			}
			} // if bonprix

			$stat = fopen($this->cfg["site_basedir"]."/files/status.txt","w");
		
			fwrite($stat, $pcode);
			fclose($stat);
			//sleep(1);
			$cur_cnt++;
			$time_cur_cnt++;
		}

		echo "all done! <br>\n";
		echo "-----------[ end of picture import function ]------------------<br>";

		//die();
	}

	function bonprix_picture_import($arr)
	{
		$pcode = $arr['pcode'];
		$params = array(
			'pcode' => $arr['pcode'],
			'start_time' => $arr['start_time']
		);
		// so, here should i check which will be the first site to check for pictures
		$first_site = $arr['import_obj']->prop("first_site_to_search_images");
		switch ($first_site)
		{
			case "bp_de":
				// if set so, search images from German Bonprix first
				if ($this->bonprix_picture_import_de($params) === false)
				{
					if ($this->bonprix_picture_import_pl($params) === false)
					{
						echo "Toodet ei leitud! <br>";
					}
				}
				break;
			default:
				// by default we search images from Polish Bonprix first
				if ($this->bonprix_picture_import_pl($params) === false)
				{
					if ($this->bonprix_picture_import_de($params) === false)
					{
						echo "Toodet ei leitud!<br>";
					}
				}
		}
		
	}

	////
	// Picture import from Polish Bonprix (www.bonprix.pl)
	// Parameters:
	// 	pcode - product code which will be searched
	// return:
	// 	(boolean) true if product is found
	// 	(boolean) false if not found
	function bonprix_picture_import_pl($arr)
	{
		$pcode = $arr['pcode'];
		$start_time = $arr['start_time'];
		$url = "http://www.bonprix.pl/katalog.php?ss=".$pcode;
		$html = $this->file_get_contents($url);

		if (strpos($html, "Niestety, ale nie ma") === false)
		{
			echo "[ BONPRIX POOLA ]<br>";
			echo "-- Leidsin toote <strong>[ $pcode ]</strong><br />";
			preg_match_all("/images\/all\/(\d+)\/(.*).jpg/", $html, $mt, PREG_PATTERN_ORDER);
			$num = 0;

			foreach($mt[2] as $idx => $nr)
			{
				$im = $mt[1][$idx]."/".$nr;
				$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$im' AND nr = '$num' AND pcode = '$pcode'", "pcode");
				echo "---- Otsin baasist pilti [$im] numbriga [$num] ja tootekoodiga [$pcode] <br>";
				if (!$imnr)
				{
					echo "------ image not found, insert new image $im <br>\n";
					flush();

					$q = ("
						INSERT INTO 
							otto_prod_img(pcode, nr,imnr, server_id, mod_time) 
							values('$pcode','$num','$im', 7, $start_time)
						");
						//echo "q = $q <br>";
						$this->db_query($q);
						$this->added_images[] = $im;
				}
				else
				{
					echo "------ found image, update mod_time to $start_time (".date("d.m.Y H:m:s", $start_time).")<br>\n";
					$this->db_query("UPDATE otto_prod_img SET mod_time=$start_time WHERE imnr = '$im' AND nr = '$num' AND pcode = '$pcode'");	
				}
				$num++;
			}
		}
		else
		{
			// Poola Bonprixist toodet ei leitud
			return false;
		}

		return true;

	}
	////
	// Picture import from German Bonprix (www.bonprix.de)
	// Parameters:
	// 	pcode - product code which will be searched
	// Return:
	//	(boolean) true - product is found
	//	(boolean) false - product is not found
	function bonprix_picture_import_de($arr)
	{
		$pcode = $arr['pcode'];
		$start_time = $arr['start_time'];
		$url = "http://www.bonprix-shop.de/bp/search.htm?id=188035177146052928-0&nv=0%7C0%7C1&sc=0&pAnfrage=".$pcode;
		$html = $this->file_get_contents($url);

		if (strpos($html, "Leider konnten wir") === false)
		{
			echo "[ BONPRIX SAKSA ]<br>";
			echo "-- Leidsin toote <strong>[ $pcode ]</strong> <br />";

			$patterns = array(
				"/http:\/\/image01\.otto\.de\/bonprixbilder\/shopposiklein\/7er\/gross\/var(\d+)\/(.*).jpg/imsU",
				"/\/\/image01\.otto\.de\/bonprixbilder\/shopposiklein\/7er\/gross\/var(\d+)\/(.*).jpg/imsU",
				"/\/\/image01\.otto\.de\/bonprixbilder\/varianten\/artikel_ansicht\/var(\d+)\/(.*).jpg/imsU",
			);

			// lets make the search:
			foreach ($patterns as $pattern)
			{
				if (preg_match($pattern, $html, $mt))
				{
					$first_im = $mt[2]."_var".$mt[1];
					break;
				}
			}

			echo "---- Kontrollin baasist pilti [ $first_im ] <br>\n";
			flush();
			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
			echo "---- Sellele pildile vastab tootekood [ $imnr ]<br>\n";
			flush();
			if (!$imnr && $first_im)
			{	
				echo "";
				echo "------ insert new first image [ $first_im ]<br>\n";
				flush();
	
				$nr = $first_im{strlen($first_im)-1};
				$q = ("
					INSERT INTO 
						otto_prod_img(pcode, nr,imnr, server_id, mod_time) 
						values('$pcode','$nr','$first_im', 6, $start_time)
				");
				//echo "q = $q <br>";
				$this->db_query($q);
				$this->added_images[] = $first_im;
			}
			else
			{
				echo "------ found first image, update mod_time $start_time (".date("d.m.Y H:m:s", $start_time).")<br>\n";
				$this->db_query("UPDATE otto_prod_img SET mod_time=$start_time WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'");
			}
	
			// get other images
			list($r_i) = explode("_", $first_im);
			echo "---- Otsin teisi pilte: <br>";
			if (!preg_match_all("/http:\/\/image01\.otto\.de\/bonprixbilder\/shopposiklein\/7er\/klein\/(.*)\/".$r_i.".jpg/imsU", $html, $mt, PREG_PATTERN_ORDER))
			{
				preg_match_all("/\/\/image01\.otto\.de\/bonprixbilder\/shopposiklein\/7er\/klein\/(.*)\/".$r_i.".jpg/imsU", $html, $mt, PREG_PATTERN_ORDER);
			}
			$otherim = $mt[1];
			foreach($otherim as $nr)
			{
				$im = $r_i."_".$nr;
				$nr = $nr{strlen($nr)-1};
				echo "---- Kontrollin baasist pilti [ $im ] <br>\n";
				flush();
				$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$im' AND nr = '$nr' AND pcode = '$pcode'", "pcode");
				echo "---- Sellele pildile vastab tootekood [ $imnr ]<br>\n";
				flush();
				if (!$imnr)
				{
					echo "------ insert new image [ $im ]<br>\n";
					flush();
					$q = ("
						INSERT INTO 
							otto_prod_img(pcode, nr,imnr, server_id, mod_time) 
							values('$pcode','$nr','$im', 6, $start_time)
					");
					//echo "q = $q <br>";
					$this->db_query($q);
					$this->added_images[] = $im;
				}
				else
				{
					echo "------ found image, update mod_time $start_time (".date("d.m.Y H:m:s", $start_time).")<br>\n";

					$this->db_query("UPDATE otto_prod_img SET mod_time=$start_time WHERE imnr = '$im' AND nr = '$nr' AND pcode = '$pcode'");
				}
			}
		}
		else
		{
			return false;
		}

		return true;

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
		echo "- START UPDATE CSV DB -<br>";
//		if ($o->prop("restart_pict_i"))
		if ($this->restart_pictures_import)
		{
			echo "Restarting pictures import ";
			@unlink($this->cfg["site_basedir"]."/files/status.txt");
			echo "[ ok ]<br>\n";
		}
//		if ($o->prop("do_pict_i"))
		if ($this->doing_pict_i)
		{
			echo "Starting pictures import ... <br>\n";
			$this->pictimp($o);
			$doing_pict_imp = 1;
			echo "Pictures import is done<br>\n";
		}

		$fldnames_t = explode(",", trim($o->prop("foldersnames")));
		$fldnames = array();
		foreach($fldnames_t as $nm)
		{
			list($a, $b) = explode("=", $nm);
			$fldnames[$b] = $a; // name=id
		}

		obj_set_opt("no_cache", 1);

		$imp_stat_file = aw_ini_get("site_basedir")."/files/impstatus.txt";
		if ($o->prop("restart_prod_i"))
		{
			echo "- product import restarted<br>";
			@unlink($imp_stat_file);
		}

		if (file_exists($imp_stat_file))
		{
			//$skip_to = $this->get_file(array("file" => $this->cfg["site_basedir"]."/files/status.txt"));
			echo "restarting from product $skip_to <br>";
		}

		$this->db_query("DELETE FROM otto_imp_t_prod");
		$this->db_query("DELETE FROM otto_imp_t_codes");
		$this->db_query("DELETE FROM otto_imp_t_prices");
		//$this->db_query("DELETE FROM otto_imp_t_p2p WHERE lang_id = ".aw_global_get("lang_id"));

		echo "from url ".$o->prop("folder_url")." read: <br>";
		echo "-------------------------------------------------------------<br>";

		$fext = ($o->prop("file_ext") != "" ? $o->prop("file_ext") : "xls");

		$first = true;

		$log = array();

		echo "<b>[!!]</b> start reading data from csv files <b>[!!]</b><br>\n";

		$import_time = time();

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
			echo "[ reading from the first file ]<br>\n";
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

//echo dbg::dump($row);
				$this->quote(&$row);
				$row = $this->char_replacement($row);
				$row[2] = $this->conv($row[2]);

				if (true || aw_ini_get("site_id") == 276 || aw_ini_get("site_id") == 277)
				{
					$extrafld = trim($row[3]);
					$desc = $this->conv(trim($row[4]." ".$row[5]." ".$row[6]." ".$row[7]." ".$row[8]." ".$row[9]." ".$row[10]." ".$row[11]." ".$row[12]." ".$row[13]." ".$row[14]." ".$row[15]." ".$row[16]." ".$row[17]." ".$row[18]." ".$row[19]." ".$row[20]." ".$row[21]." ".$row[22]." ".$row[23]." ".$row[24]." ".$row[25]." ".$row[26]." ".$row[27]." ".$row[28]." ".$row[29]." ".$row[30]." ".$row[31]." ".$row[32]." ".$row[33]." ".$row[34]." ".$row[35]." ".$row[36]." ".$row[37]." ".$row[38]." ".$row[39]." ".$row[40]." ".$row[41]." ".$row[42]));
				}
				else
				{
					//$extrafld = trim($row[3]);
					$desc = $this->conv(trim($row[3]." ".$row[5]." ".$row[6]." ".$row[7]." ".$row[8]." ".$row[9]." ".$row[10]." ".$row[11]." ".$row[12]." ".$row[13]." ".$row[14]." ".$row[15]." ".$row[16]." ".$row[17]." ".$row[18]." ".$row[19]." ".$row[20]." ".$row[21]." ".$row[22]." ".$row[23]." ".$row[24]." ".$row[25]." ".$row[26]." ".$row[27]." ".$row[28]." ".$row[29]." ".$row[30]." ".$row[31]." ".$row[32]." ".$row[33]." ".$row[34]." ".$row[35]." ".$row[36]." ".$row[37]." ".$row[38]." ".$row[39]." ".$row[40]." ".$row[41]." ".$row[42]));
				}

				$this->db_query("
					INSERT INTO otto_imp_t_prod(pg,nr,title,c,extrafld)
					VALUES('$cur_pg','$row[1]','$row[2]','$desc','$extrafld')
				");

				if ($row[2] == "")
				{
					echo "ERROR ON LINE $num title ".$row[2]." <br>";
					$log[] = "VIGA real $num failis $fld_url nimi: ".$row[2];
				}
				$num++;

				echo "-- Lisasin toote numbriga [".$row[1]."], leht: [".$cur_pg."], extrafld/kategooria: [".$extrafld."],  nimi: [".$row[2]."]<br>\n";

			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}

			echo "[ ...got $num titles from file $fld_url] <br><br>";
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
			echo "[ reading from the second file ]<br>\n";
			echo "from url ".$fld_url." read: <br>\n";
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

				echo "-- Lisasin koodi numbriga $row[1], leht: [$cur_pg], suurus: [$row[2]], v2rv: [$color], kood: [$row[4]], t2iskood: [$full_code], set_f_img: [$set_f_img]<br>\n";
			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}

			echo "[... got $num codes from file $fld_url] <br><br>\n";
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
			echo "[ reading from the third file ]<br>\n";
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

				echo "-- Lisasin hinna numbriga [$row[1]], leht: [$cur_pg], tyyp: [$row[2]], suurus: [$row[3]], yhik: [$row[4]], hind: [$row[5]]<br>\n";
			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}
			echo "[... got $num prices from file $fld_url ] <br>\n";
			$log[] = "lugesin failist $fld_url $num hinda";
			flush();
		}
		
		echo "<br><b>[!!]</b>  end reading data from the csv files <b>[!!]</b><br><br>\n";

		$this->db_query("SELECT * FROM otto_imp_t_codes");
		echo "[Select all codes from otto_imp_t_codes db table]<br>\n";
		$tmp_counter = 0;
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->db_query("UPDATE otto_prod_img SET p_pg = '$row[pg]', p_nr = '$row[nr]' WHERE pcode = '$row[code]'");
			echo "-- Update otto_prod_img table: set p_pg: $row[pg], p_nr: $row[nr] where pcode: $row[code]<br>\n";
			$this->restore_handle();
			$tmp_counter++;
		}
		echo "[ Uuendati $tmp_counter rida ]<br>\n";

		echo "wrote temp db <br>\n";
		flush();

		echo "rewrite first images (if set_f_img is set in second csv file)<br>\n";
		$this->db_query("SELECT * FROM otto_imp_t_codes WHERE set_f_img != ''");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->db_query("UPDATE otto_prod_img SET imnr = '$row[set_f_img]' WHERE pcode = '$row[full_code]' AND nr = 1 ");
			$this->restore_handle();
		}

		echo "[ make existing products lut ] <br>\n";
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

		echo "[ get products count ... ";
		$total = $this->db_fetch_field("
			SELECT  
				count(*) as cnt
			FROM 
				otto_imp_t_prod p
				LEFT JOIN otto_imp_t_codes c ON (c.pg = p.pg AND c.nr = p.nr)
		", "cnt");
		echo "got: $total products (otto_imp_t_prod left join otto_imp_t_codes on pg and nr) ]<br>\n";

		// structure is this:
		// packet - contains all prods that share the same page number and the same image
			// product in packet, one for each row in the combination (first + second table)
				// packaging in product, one for each price

		// so first we create all the products
		// then all the packagings for them
		// then finally we group by the image number and create packages based on that

		// now, go over all the damn prods and create the correct data from them
		$query = "
			SELECT  
				p.pg as pg,
				p.nr as nr,
				p.title as title,
				p.c as c,
				c.code as code,
				c.color as color,
				c.size as s_type,
				c.full_code as full_code,
				p.extrafld as extrafld
			FROM 
				otto_imp_t_prod p
				LEFT JOIN otto_imp_t_codes c ON (c.pg = p.pg AND c.nr = p.nr)
		";
		$this->db_query($query);
		echo "<br><br>[!!] Looping through all the products [!!]<br>\n";
		$start_time = time();
		while ($row = $this->db_next())
		{
			$this->save_handle();
			echo "-- [ import package ] <br>\n";
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
			echo "---- original product code: pcode=$orig_pcode<br>\n";
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

			echo "---- import product $row[title] (kood: $row[code]) , ".($total - $items_done)." items to go , estimated time remaining: $rem_hr hrs, $rem_min minutes <br>\n";
			flush();
			// check if it exists

			$prod_id = $this->_get_id_by_code($row["code"], $row["s_type"]);

			echo "---- checking if prod_id: [$prod_id] is oid<br>\n";
			flush();
			$new = true;
			if ($this->can("view", $prod_id))
			{
				echo "------ prod_id is oid [".$prod_id."] <br>";
				$dat = obj($prod_id);
				echo "------ found existing product object [oid:".$dat->id().", kood:".$row['code']."]<br>\n";
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

				echo "------ created new product object: id:".$dat->id()."  kood: ".$row['code']." <br>\n";
				flush();
			}

			// try find correct folder
			$try_fld = $this->db_fetch_field("SELECT fld FROM otto_imp_t_p2p WHERE pg = '$row[pg]' and lang_id = ".aw_global_get("lang_id"), "fld");
			if ($try_fld)
			{
				echo "------ found parent for prod as $try_fld <br>\n";
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
			$dat->set_prop("user11", $row["extrafld"]);


			// i need to add those categories or extraflds to the 'otto_imp_t_prod_to_cat' table too
			if (!empty($row['extrafld']))
			{

				$this->save_handle();
				$categories = explode(',', $row['extrafld']);
				foreach ($categories as $category)
				{
					$prod_to_cat_id = $this->db_fetch_field("SELECT id FROM otto_imp_t_prod_to_cat WHERE product_code='".$row['code']."' AND category='".$category."' AND lang_id=".aw_global_get('lang_id')." ", "id");
					if (empty($prod_to_cat_id))
					{
						$this->db_query("
							INSERT INTO 
								otto_imp_t_prod_to_cat 
							SET
								product_code = '".$row['code']."',
								category = '".$category."',
								page = '".$row['pg']."',
								import_time = ".$import_time.",
								lang_id = '".aw_global_get('lang_id')."'
						");
					}
					else
					{
						$this->db_query("
							UPDATE 
								otto_imp_t_prod_to_cat 
							SET
								product_code = '".$row['code']."',
								category = '".$category."',
								page = '".$row['pg']."',
								import_time = ".$import_time.",
								lang_id = '".aw_global_get('lang_id')."'
							WHERE
								id = ".$prod_to_cat_id."
						");
					}
				}
				// now, the otto_imp_t_prod_to_cat table is updated, so i can now delete the unmodified rows
				$this->db_query("
					DELETE FROM 
						otto_imp_t_prod_to_cat 
					WHERE 
						import_time < $import_time
						AND page = '".$row['pg']."'
						AND lang_id = '".aw_global_get('lang_id')."'
			
				");
				$this->restore_handle();
			}

			if (!$new)
			{
				echo "---- if not new object: <br>\n";
				$_ids = $this->_get_ids_by_code($row["code"]);
				echo "---- getting ids by code [$row[code]]<br>\n";
				foreach($_ids as $tmp_dat)
				{	
					echo "------ also set ".$tmp_dat->id()." to page $row[pg] <br>";
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
			echo "---- [Iga hinna jaoks tekita packaging objekt]<br>\n";
			echo "---- q = "."SELECT * FROM otto_imp_t_prices WHERE pg = '$row[pg]' AND nr = '$row[nr]' AND type IN ($typestr) <br>";
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
						echo "------ for prod ".$dat->name()." got (".$pk->id().") packaging ".$row["price"]." for type ".$orig_row["s_type"]." <bR>";
					}
					else
					{
						echo "------ for prod ".$dat->name()." got NEW packaging ".$row["price"]." for type ".$orig_row["s_type"]." <bR>";
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

		echo "[!!] hear hear. prods done. Imporditi $items_done toodet [!!] <br>\n";

		$log[] = "importisin $items_done toodet";

		if ($o->prop("just_update_prod_data") != 1)
		{

		flush();
		// to make packages, group by image number and for all images where cnt > 1 create a package for all those prods

		echo "<br><br>[!!] make existing packet lut [!!]<br>\n";
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

		$imgs = "";
		if ($this->doing_pict_i && !count($this->added_images))
		{
			$imgs = " AND 1 = 0 ";
		}
		else
		if (count($this->added_images))
		{
			$imgs = " AND imnr IN (".join(",", map("'%s'", $this->added_images)).") ";
		}
		
		$query ="select *, count(*) as cnt from otto_prod_img ".
					"where nr = 1 AND p_pg is not null $imgs  group ".
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

//		$this->pictfix(array());
		$this->pictfix(array(
			'import_obj' => $o
		));

		$this->fix_image_codes();

		$this->fix_prices();

		// clear cache
		$cache = get_instance("cache");
 		$mt->file_clear_pt("menu_area_cache");
		$mt->file_clear_pt("storage_search");
		$mt->file_clear_pt("storage_object_data");
		$mt->file_clear_pt("html");
		$mt->file_clear_pt("acl");

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
		if (aw_global_get("lang_id") == 6 || aw_global_get("lang_id") == 7)
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
			//chr(199), //226
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
			chr(158), //236
			chr(202),
			chr(200), // "
			chr(199),  // "
			chr(161), // &deg;
			chr(181), // 205
			chr(227), //34
			chr(234), //&#382;
			chr(139), //&#269;
			);

			
			
			
				
			
			$haystack = array(
			chr(254),
			chr(251),
			chr(238),
			chr(234),
			chr(242),
			//chr(226),
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
			chr(234),
			"",
			"&quot;",
			"&quot;",
			"&deg;",
			chr(205),
			chr(34),
			"&#382;",
			"&#269;",
			);
		}
		else
		{
   $needle = array(
			chr(158),	// &#381;
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
			chr(236), //&#382;
			chr(227), //34
			chr(225), //&#352;
			chr(149), //z

		);
		$haystack = array(
			"&#381;",
			chr(180),	// ylakoma;
			chr(158),// zhee;
			chr(252),// &uuml;
			chr(220),	// &Uuml;
			chr(246),// &ouml;
			chr(185), // shaa-enne oli 154
			chr(228),// &auml;
			chr(213),// &Otilde;
			chr(245),	// &otilde;
			chr(34),
			chr(34),
			chr(34),
			chr(34),
			chr(216),
			"&#382;",
			chr(34),
			"&#352;",
			"z",
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
/*
		if (!$arr["testjs"])
		{
			//return $retval;
		}
*/
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
/*
if ($_SERVER["REMOTE_ADDR"] == "82.131.23.210")
{
	arr($arr);
}
*/
			$vars["order_data"] = array();
			$vars["order_data"][$arr["add_to_cart"]]["color"] = ($arr["order_data_color"] != "" ? $arr["order_data_color"] : "---");
			$vars["order_data"][$arr["add_to_cart"]]["size"] = $arr["size_name"];
			$vars["order_data"][$arr["add_to_cart"]]["new_price"] = $arr["new_price"];
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
	//	$this->pictimp(array(), true);
		$this->pictimp($arr['import_obj'], true);
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

	function _get_files_order($args)
	{
		$t = &$args['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'file',
			'caption' => t('Fail'),
		));
		$t->define_field(array(
			'name' => 'order',
			'caption' => t('J&auml;rjekord'),	
		));

		$count = 0;
		$saved_data = $args['obj_inst']->meta('files_order');
		foreach (safe_array($saved_data) as $file => $order)
		{
			$t->define_data(array(
				'file' => html::textbox(array(
					'name' => 'files_order['.$count.'][file]',
					'value' => $file,
					'size' => '10'
				)),
				'order' => html::textbox(array(
					'name' => 'files_order['.$count.'][order]',
					'value' => $order
				)),
			));
			$count++;
		}

		$t->define_data(array(
			'file' => html::textbox(array(
				'name' => 'files_order['.$count.'][file]',
				'size' => '10'
			)),
			'order' => html::textbox(array(
				'name' => 'files_order['.$count.'][order]'
			)),
		));
		return PROP_OK;
	}

	function _set_files_order($args)
	{
		$valid_data = array();
		foreach (safe_array($args['request']['files_order']) as $data)
		{
			if (!empty($data['file']) && !empty($data['order']))
			{
				$valid_data[$data['file']] = $data['order'];
			}
		}
		$args['obj_inst']->set_meta('files_order', $valid_data);
		// i think that to avoid the scannig for orders from otto_prod_img table
		// i should keep them in meta too ... maybe it isn't necessary, anyway, this is
		// the place where i should update otto_prod_img table and set the order
		
		foreach ($valid_data as $file => $order)
		{
			// i need the short version of the file name, aka. page (in otto_prod_img p_pg field)
			list(, $cur_pg) = explode(".", $file);
			$cur_pg = substr($cur_pg,1);
			if ((string)((int)$cur_pg{0}) === (string)$cur_pg{0})
			{
				$cur_pg = (int)$cur_pg;
			}
			$cur_pg = trim($cur_pg);
			$this->db_query("UPDATE otto_prod_img set file_order='".(int)$order."' WHERE p_pg='$cur_pg'");
		}
		return PROP_OK;
	}

	function _get_file_suffix($args)
	{
		$t = &$args['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'file',
			'caption' => t('Fail'),
		));
		$t->define_field(array(
			'name' => 'suffix',
			'caption' => t('Suffiks'),	
		));

		$count = 0;
		$saved_data = $args['obj_inst']->meta('file_suffix');
		foreach (safe_array($saved_data) as $file => $suffix)
		{
			$t->define_data(array(
				'file' => html::textbox(array(
					'name' => 'file_suffix['.$count.'][file]',
					'value' => $file,
					'size' => '10'
				)),
				'suffix' => html::textbox(array(
					'name' => 'file_suffix['.$count.'][suffix]',
					'value' => $suffix
				)),
			));
			$count++;
		}

		$t->define_data(array(
			'file' => html::textbox(array(
				'name' => 'file_suffix['.$count.'][file]',
				'size' => '10'
			)),
			'suffix' => html::textbox(array(
				'name' => 'file_suffix['.$count.'][suffix]'
			)),
		));
		return PROP_OK;
	}

	function _set_file_suffix($args)
	{
		$valid_data = array();
		foreach (safe_array($args['request']['file_suffix']) as $data)
		{
			if (!empty($data['file']) && !empty($data['suffix']))
			{
				$valid_data[$data['file']] = $data['suffix'];
			}
		}
		$args['obj_inst']->set_meta('file_suffix', $valid_data);
		return PROP_OK;
	}


	function _get_categories($args)
	{
		$t = &$args['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'jrk',
			'caption' => t('Jrk'),
			'align' => 'center'
		));
		$t->define_field(array(
			'name' => 'aw_folder_id',
			'caption' => t('AW Kataloogi ID'),
			'align' => 'center'
		));
		$t->define_field(array(
			'name' => 'categories',
			'caption' => t('Kategooriad'),
		));
		
		$count = 1;
		$data = array();
		
		// otto lehed lähevad kategooriateks:
		$this->db_query("SELECT * FROM otto_imp_t_p2p WHERE lang_id = ".aw_global_get('lang_id'));
		while ($row = $this->db_next())
		{
			$data[$row['fld']][] = $row['pg'];
		}

		// bonpriksi stiilis kategooriad ka kõik üheks kategooriate värgiks kokku:
		$foldernames = explode(',', $args['obj_inst']->meta('foldernames'));
		foreach ($foldernames as $pair)
		{
			list($aw_folder_id, $category) = explode('=', $pair);
			if (!in_array($category, $data[$aw_folder_id]))
			{
				$data[$aw_folder_id][] = $category;
			}
		}

		// and i should merge here the categories table content too (the new table)
		// in some point of time, i can have the categories only from the categories table
		$this->db_query("SELECT * FROM otto_imp_t_aw_to_cat WHERE lang_id=".aw_global_get('lang_id'));
		while ($row = $this->db_next())
		{
			if (!in_array($row['category'], $data[$row['aw_folder']]))
			{
				$data[$row['aw_folder']][] = $row['category'];
			}
		}


		foreach ($data as $aw_folder => $categories)
		{

			$t->define_data(array(
				'jrk' => $count,
				'aw_folder_id' => html::textbox(array(
					'name' => 'data['.$count.'][aw_folder_id]',
					'value' => $aw_folder,
					'size' => '10'
				)),
				'categories' => html::textbox(array(
					'name' => 'data['.$count.'][categories]',
					'value' => implode(',', $categories),
					'size' => '80',
				)),
			));
			$count++;

		}

		for ($i = 0; $i<10; $i++)
		{
			$t->define_data(array(
				'aw_folder_id' => html::textbox(array(
					'name' => 'data['.$count.'][aw_folder_id]',
					'value' => '',
					'size' => '10'
				)),
				'categories' => html::textbox(array(
					'name' => 'data['.$count.'][categories]',
					'value' => '',
					'size' => '80'
				)),
			));
			$count++;
		}

		return PROP_OK;
	}

	function _set_categories($args)
	{
		$this->db_query('DELETE FROM otto_imp_t_aw_to_cat where lang_id = '.aw_global_get('lang_id'));
		foreach (safe_array($args['request']['data']) as $count => $data)
		{
			foreach (explode(',', $data['categories']) as $category)
			{
				if (!empty($category) && !empty($data['aw_folder_id']))
				{
					$this->db_query("INSERT INTO otto_imp_t_aw_to_cat set 
						category = '$category',
						aw_folder = ".$data['aw_folder_id'].",
						lang_id = ".aw_global_get('lang_id')."
					");
				}
			}
		}
		return PROP_OK;
	}

	function _get_bubble_pictures($args)
	{
		$t = &$args['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'category',
			'caption' => t('Kategooria'),
		));
		$t->define_field(array(
			'name' => 'image_url',
			'caption' => t('Pildi aadress'),
		));

		$t->define_field(array(
			'name' => 'title',
			'caption' => t('Nimetus'),
		));
/*
		// maybe it would be nice to make the image upload here
		// but where can i upload those images, so they will be 
		// accessible via web?
		// can i use any aw objects for that? i don't need image
		// image objects, just regular image files possibly under
		// public/img/bubble directory or smth
		$t->define_field(array(
			'name' => 'image_upload',
			'caption' => t('Pildi &uuml;leslaadimine'),
		));
*/
		$count = 0;
		$saved_data = $args['obj_inst']->meta('bubble_pictures');
		foreach (safe_array($saved_data) as $category => $data)
		{
			$t->define_data(array(
				'category' => html::textbox(array(
					'name' => 'bubble_data['.$count.'][category]',
					'value' => $category
				)),
				'image_url' => html::textbox(array(
					'name' => 'bubble_data['.$count.'][image_url]',
					'value' => $data['image_url']
				)),
				'title' => html::textbox(array(
					'name' => 'bubble_data['.$count.'][title]',
					'value' => $data['title']
				)),
/*
				'image_upload' => html::fileupload(array(
					'name' => 'bubble_data['.$count.'][image_upload]'
				)),
*/
			));
			$count++;
		}
		$t->define_data(array(
			'category' => html::textbox(array(
				'name' => 'bubble_data['.$count.'][category]'
			)),
			'image_url' => html::textbox(array(
				'name' => 'bubble_data['.$count.'][image_url]'
			)),
			'title' => html::textbox(array(
				'name' => 'bubble_data['.$count.'][title]',
			)),
		));
		return PROP_OK;
	}

	function _set_bubble_pictures($args)
	{
		$valid_data = array();
		foreach (safe_array($args['request']['bubble_data']) as $data)
		{
			if (!empty($data['category']) && !empty($data['image_url']))
			{
				$valid_data[$data['category']] = array(
					'image_url' => $data['image_url'], 
					'title' => $data['title']
				);
			}
		}
		$args['obj_inst']->set_meta('bubble_pictures', $valid_data);
		return PROP_OK;
	}

	function _get_firm_pictures($args)
	{
		$t = &$args['prop']['vcl_inst'];
		$t->set_sortable(false);		

		$t->define_field(array(
			'name' => 'category',
			'caption' => t('Kategooria'),
		));
		$t->define_field(array(
			'name' => 'image_url',
			'caption' => t('Pildi aadress'),
		));
		$t->define_field(array(
			'name' => 'title',
			'caption' => t('Nimetus'),
		));
		$count = 0;
		$saved_data = $args['obj_inst']->meta('firm_pictures');

		foreach (safe_array($saved_data) as $category => $data)
		{
			$t->define_data(array(
				'category' => html::textbox(array(
					'name' => 'firm_data['.$count.'][category]',
					'value' => $category
				)),
				'image_url' => html::textbox(array(
					'name' => 'firm_data['.$count.'][image_url]',
					'value' => $data['image_url']
				)),
				'title' => html::textbox(array(
					'name' => 'firm_data['.$count.'][title]',
					'value' => $data['title']
				)),
			));
			$count++;
		}
	
		$t->define_data(array(
			'category' => html::textbox(array(
				'name' => 'firm_data['.$count.'][category]'
			)),
			'image_url' => html::textbox(array(
				'name' => 'firm_data['.$count.'][image_url]'
			)),
			'title' => html::textbox(array(
				'name' => 'firm_data['.$count.'][title]'
			)),
		));
		return PROP_OK;
	}

	function _set_firm_pictures($args)
	{
		$valid_data = array();
		foreach (safe_array($args['request']['firm_data']) as $data)
		{
			if (!empty($data['category']) && !empty($data['image_url']))
			{
				$valid_data[$data['category']] = array(
					'image_url' => $data['image_url'],
					'title' => $data['title']
				);
			}
		}
		$args['obj_inst']->set_meta('firm_pictures', $valid_data);
		return PROP_OK;
	}

	function read_img_from_baur($pcode)
	{
		$pcode = str_replace(" ", "", $pcode);
		$url = "http://www.baur.de/is-bin/INTERSHOP.enfinity/WFS/BaurDe/de_DE/-/EUR/BV_ParametricSearch-Progress;sid=9wziDKL5zmzox-N_94eyWWD0hj6lQBejDB2TPuW1?ls=0&_PipelineID=search_pipe_bbms&_QueryClass=MallSearch.V1&Servicelet.indexRetrieverPipelet.threshold=0.7&Orengelet.sortPipelet.sortResultSetSize=10&Query_Text=".$pcode."&Kategorie_Text=&x=23&y=13";
		arr($url);
		$fc = $this->file_get_contents($url);
//		if (strpos($fc, "leider keine Artikel gefunden") !== false)
		if (strpos($fc, "search/topcontent/noresult_slogan.gif") !== false)
		{
			echo "can't find a product for <b>$pcode</b> from baur.de, so searching from schwab<br>\n";
			return $this->read_img_from_schwab($pcode);
			
		}

//		preg_match_all("/ProductRef=(.*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
//		preg_match_all("/ProductRef=(\d.*)\"/ims", $fc, $mt, PREG_PATTERN_ORDER);
//		preg_match_all("/ProductRefID=(.*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
//		preg_match_all("/ProductRefID=(\d.*)\"/ims", $fc, $mt, PREG_PATTERN_ORDER);
		preg_match_all("/redirectIt\( \"(.*)\" \)/ims", $fc, $mt, PREG_PATTERN_ORDER);

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
					$this->added_images[] = $pn;
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
		arr($url);
		$fc = $this->file_get_contents($url);

		if (strpos($fc, "Wir konnten leider keine Ergebnisse") !== false)
		{
			echo "can't find a product for <b>$pcode</b> from schwab.de, so searching from albamoda<br>\n";
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
				$this->added_images[] = $first_im;
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
					$this->added_images[] = $im;
				}
			}
		}		
	}

	function read_img_from_albamoda($pcode)
	{
		$url = "http://www.albamoda.de/is-bin/INTERSHOP.enfinity/WFS/AlbaModaDe/de_DE/-/EUR/AM_ParametricSearch-Progress;sid=ytMKUs3doZEKUo_WSsAnctZxm9kZ5q0_w_o_iYvu?_PipelineID=search_pipe_am_de&Orengelet.sortPipelet.sortResultSetSize=10&Query_Text=".$pcode."&_QueryClass=MallSearch.V1";
		arr($url);
		$fc = $this->file_get_contents($url);
		if (strpos($fc, "Es wurden leider keine Artikel") !== false)
		{
			echo "can't find a product for <b>$pcode</b> from albamoda.de, so searching from heine<br>\n";
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
				$this->added_images[] = $first_im;
			}
		}
	}

	function read_img_from_heine($pcode)
	{
		// no spaces in product code ! --dragut
		$pcode = str_replace(" ", "", $pcode);
		$url = "http://www.neu.heine.de/is-bin/INTERSHOP.enfinity/WFS/HeineDe/de_DE/-/EUR/SH_ParametricSearch-Progress;sid=YtPBfo9Zn47Dfs1V6VzvXpT13mqu32H0mc0eO27a?ls=&ArtikelID_Text=".$pcode."&y=9&x=11";
		arr($url);
		$fc = $this->file_get_contents($url);

		if (strpos($fc, "keine passenden Ergebnisse") !== false)
		{
			echo "heine.de-st ka pilti ei leidnud<br>";
			echo "NO IMAGE FOUND FOR PCODE $pcode <br>\n";
			flush();
			return;
		}

		// get prods
		preg_match_all("/ProductRef=([^\"].*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
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
			if (strpos($fc2, "Ihrer Anforderung sind technische Probleme aufgetreten.") !== false)
			{
				$fc2 = $fc;
			}

/*
			if (!preg_match("/http:\/\/image01\.otto\.de:80\/pool\/HeineDe\/de_DE\/images\/format_hv_ds_a\/(\d+).jpg/imsU", $fc2, $mt))
			{
				// i'm not really sure that it works, so i have to look over it
				// maybe this one works, but there are some other image urls which are not listed here
				preg_match("/http:\/\/image01.otto.de\/pool\/images\/format_hv_ds_a\/(\d+).jpg/imsU", $fc2, $mt);
			}
*/
			$patterns = array(
				"/http:\/\/image01\.otto\.de:80\/pool\/HeineDe\/de_DE\/images\/format_hv_ds_a\/(\d+).jpg/imsU",
				"/http:\/\/image01.otto.de\/pool\/images\/format_hv_ds_a\/(\d+).jpg/imsU",
				"/http:\/\/image01.otto.de:80\/pool\/images\/format_hv_ds_a\/(\d+).jpg/imsU",

			);

			foreach ($patterns as $pattern)
			{
				if (preg_match($pattern, $fc2, $mt))
				{
					break;
				}
			}
			$first_im = $mt[1];
arr("<b>".$first_im."</b>");
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
				$this->added_images[] = $first_im;
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
		set_time_limit(0);
//		error_reporting(E_ALL);
//////////////////////////////

///////////////////////
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"user20" => $prods
		));

		if (!$ol->count())
		{
			echo "Tooteid ei leitud:<br>";
			arr($prods);
			return;
		}
// selles koodis see vist hetkel ei tööta --dragut 03.02.2006
//		aw_global_set("no_cache_flush", 1);
		foreach($ol->arr() as $o)
		{
			$pkol = new object_list($o->connections_from(array("type" => "RELTYPE_PACKAGING")));
			echo "kusututan pakendid ".join(",", $pkol->names())." <br>";
			$pkol->delete();
			echo "kustutamine 6nnestus<br>";
			flush();
		}
		// get all packagings that have the prods
		$c = new connection();
		$list = $c->find(array(
			"from.class_id" => CL_SHOP_PACKET,
			"type" => 1,
			"to.oid" => $ol->ids()
		));

		echo "kusututan tooted ".join(",", $ol->names())." <br>";
		flush();
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
					flush();
					$pkt->delete(true);
				}
			}
		}
		echo "valmis! <br>";
	}

	function _get_id_by_code($code, $s_type = NULL)
	{
		if ($s_type != "")
		{
			$ad_sql = " AND user17 LIKE '%($s_type)%' ";
		}
		$id = $this->db_fetch_field("SELECT aw_oid FROM aw_shop_products LEFT JOIN objects ON objects.oid = aw_shop_products.aw_oid  WHERE user20 = '$code' $ad_sql AND objects.status > 0 AND objects.lang_id = ".aw_global_get("lang_id"), "aw_oid");
		return $id;
	}

	function _get_ids_by_code($code)
	{
		$ret = array();
		$this->db_query("SELECT aw_oid FROM aw_shop_products LEFT JOIN objects ON objects.oid = aw_shop_products.aw_oid  WHERE user20 = '$code' AND objects.status > 0 AND objects.lang_id = ".aw_global_get("lang_id"));
		while ($row = $this->db_next())
		{
			$ret[] = obj($row["aw_oid"]);
		}
		return $ret;
	}
	/**
		@attrib name=import_discount_products
		@param id required type=int
	**/
	function import_discount_products($args)
	{
		$object_id = $args['id'];
		$object = new object($object_id);

		$file_url = $object->prop("discount_products_file");
		if (!empty($file_url))
		{
			$rows = file($file_url);

			// fucking mackintosh
			if (count($rows) == 1)
			{
				$lines = $this->mk_file($file_url, "\t");
				if (count($lines) > 1)
				{
					$tmpf = tempnam("/tmp", "aw-ott-imp-5");
					$fp = fopen($tmpf,"w");
					fwrite($fp, join("\n", $lines));
					fclose($fp);
					$file_url = $tmpf;
				}
			}
			
			$rows = file($file_url);




			if ($rows !== false)
			{
				// unset the firs row:
				unset($rows[0]);
				// first of all, empty the table
				$this->db_query("delete from bp_discount_products where lang_id=".aw_global_get('lang_id'));
			//	echo "importing ".count($rows)." products<br>";
				foreach($rows as $row)
				{
					$fields = explode("\t", $row);
					
					// fields 5 & 6 contain price-s, and they should not contain
					// any spaces or commas or double quotas:
					$fields[5] = str_replace(" ", "", $fields[5]);
					$fields[5] = str_replace(",", "", $fields[5]);
					$fields[5] = str_replace('"', "", $fields[5]);


					$fields[6] = str_replace(" ", "", $fields[6]);
					$fields[6] = str_replace(",", "", $fields[6]);
					$fields[6] = str_replace('"', "", $fields[6]);

					$sql = "insert into bp_discount_products set ";
					$sql .= "prom='".$fields[0]."',";
					$sql .= "product_code='".$fields[1]."',";
					$sql .= "name='".$fields[2]."',";
					$sql .= "size='".$fields[3]."',";
					$sql .= "amount=".(int)$fields[4].",";
					$sql .= "old_price=".(int)$fields[5].",";
					$sql .= "new_price=".(int)$fields[6].",";
					$sql .= "category='".$fields[7]."',";
					$sql .= "lang_id=".aw_global_get('lang_id')." ;";

					$this->db_query($sql);
				}
			//	echo ".::[ import complete ]::.<br>";
			}
			else
			{
				echo "<span style=\"color:red;\">Faili ei &otilde;nnestunud lugeda!</span><br>";
			}
			
		}
		return $this->mk_my_orb("change", array(
			"id" => $object_id,
			"group" => "discount_products",
		));
	}

	/**
		@attrib name=clear_discount_products
		@param id optional int
	**/
	function clear_discount_products($args)
	{
		$this->db_query("delete from bp_discount_products");

		return $this->mk_my_orb("change", array(
			"id" => $args['id'],
			"group" => "discount_products",
		));	
	}
}

?>
