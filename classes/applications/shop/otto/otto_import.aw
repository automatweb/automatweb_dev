<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_import.aw,v 1.1 2004/08/23 09:11:33 kristo Exp $
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

@property do_i type=checkbox ch_value=1
@caption Teosta import

@property do_pict_i type=checkbox ch_value=1
@caption Teosta piltide import

@property restart_pict_i type=checkbox ch_value=1
@caption Alusta piltide importi algusest

@property restart_prod_i type=checkbox ch_value=1
@caption Alusta toodete importi algusest

@groupinfo files caption="Failid"

@property fnames type=textarea rows=30 cols=80 group=files
@caption Failinimed

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

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

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
		$folder = "/www/otto.struktuur.ee/public/vv_pimg";

		if (is_object($arr))
		{
			$data = array();
			foreach(explode("\n", $arr->prop("fnames")) as $fname)
			{
				if ($fname == "")
				{
					continue;
				}

				$fld_url = $arr->prop("base_url")."/".trim($fname)."-2.csv";
				echo "from url ".$fld_url." read: <br>";
				list(, $cur_pg) = explode(".", $fname);
				$cur_pg = (int)substr($cur_pg,1);

				$first = true;
				$num =0;

				$fp = fopen($fld_url, "r");
				while ($row = fgetcsv($fp,1000,"\t"))
				{
					if ($first)
					{
						$first = false;
						continue;
					}
					$row = $this->char_replacement($row);
					$row[4] = substr(str_replace(" ","", $row[4]), 0, 6);
					$data[] = $row[4];
				}
			}
		}
		else
		{
			$data = array_unique(explode(",", $this->get_file(array("file" => "/www/otto.struktuur.ee/ottids.txt"))));
		}

		if (!$fix_missing && file_exists("/www/otto.struktuur.ee/public/vv_pimg/status.txt"))
		{
			$skip_to = $this->get_file(array("file" => "/www/otto.struktuur.ee/public/vv_pimg/status.txt"));
			echo "restarting from product $skip_to <br>";
		}
		else
		if (!$fix_missing)
		{
			$this->db_query("DELETE FROM otto_prod_img");
		}
		
		if ($fix_missing)
		{
			$this->db_query("select c.code from otto_imp_t_codes c left join otto_prod_img p on p.pcode = c.code where p.imnr is null");
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

			$url = "http://ww2.otto.de/is-bin/INTERSHOP.enfinity/WFS/OttoDe/de_DE/-/EUR/OV_ParametricSearch-Progress;sid=bwNBYJMEb6ZQKdPoiDHte7MOOf78U0shdsyx6iWD?_PipelineID=search_pipe_ovms&_QueryClass=MallSearch.V1&ls=0&Orengelet.sortPipelet.sortResultSetSize=10&SearchDetail=one&Query_Text=".$pcode;
		
			$html = file_get_contents($url);

			// image is http://image01.otto.de:80/m2bilder/OttoDe/de_DE/images/formatb/[number].jpg
			if (!preg_match("/m2bilder\/OttoDe\/de_DE\/images\/formatb\/(\d+).jpg/imsU",$html, $mt))
			{
				echo "for product $pcode multiple images! <br>\n";
				flush();

				// subrequest for two images
				preg_match_all("/<a href=\"Javascript:document\.location\.href='(.*)'\+link_ext\" class=\"produkt\">/imsU", $html, $mt, PREG_PATTERN_ORDER);
				$urld = array();
				foreach($mt[1] as $url)
				{
					$urld[$url] = $url;
				}

				foreach($urld as $url)
				{
					$html = file_get_contents($url);
					if (!preg_match("/m2bilder\/OttoDe\/de_DE\/images\/formatb\/(\d+).jpg/imsU",$html, $mt))
					{
						echo "total fakap <br>";
						flush();
					}
					else
					{
						$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
							values('$pcode',1,'$mt[1]')");	
						echo "got img $mt[1]  <br>";


						// also, other images, detect them via the jump_img('nr') js func
						preg_match_all("/jump_img\('(\d+)'\)/imsU", $html, $mt, PREG_PATTERN_ORDER);
						$nrs = $mt[1];
						foreach($nrs as $nr)
						{
							$nurl = $url."&bild_nr=".$nr;
							echo "fetch other image nr $nr $url<br>\n";
							flush();
							$ismatch = false;
							$cnt = 1;
							do {
								//sleep(1);
								$nhtml = file_get_contents($nurl);
								$ismatch = preg_match("/m2bilder\/OttoDe\/de_DE\/images\/formatb\/(\d+)\.jpg/imsU",$nhtml, $mt);
								if ($cnt > 1)
								{
									echo "try nr $cnt <Br>\n";
									flush();
								}
							} while(!$ismatch && $cnt++ < 9);

							if (!$ismatch)
							{
								error::throw(array(
									"id" => "ERR_NO_FETCH",
									"msg" => "otto_import::images(): could not fetch html for url $nurl!"
								));
							}
							
							if (!preg_match("/m2bilder\/OttoDe\/de_DE\/images\/formatb\/(\d+)\.jpg/imsU",$nhtml, $mt))
							{
								echo "for product $pcode no image number $nr! <br>\n";
								echo "html is $nhtml <br>";
								flush();
							}
							else
							{
								$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
									values('$pcode','$nr','$mt[1]')");
							}
						}
					
					}
				}
			}
			else
			{
				$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
					values('$pcode',1,'$mt[1]')");	


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
						$nhtml = file_get_contents($nurl);
						$ismatch = preg_match("/m2bilder\/OttoDe\/de_DE\/images\/formatb\/(\d+)\.jpg/imsU",$nhtml, $mt);
						if ($cnt > 1)
						{
							echo "try nr $cnt <Br>\n";
							flush();
						}
					} while(!$ismatch && $cnt++ < 9);

					if (!$ismatch)
					{
						error::throw(array(
							"id" => "ERR_NO_FETCH",
							"msg" => "otto_import::images(): could not fetch html for url $nurl!"
						));
					}
					
					if (!preg_match("/m2bilder\/OttoDe\/de_DE\/images\/formatb\/(\d+)\.jpg/imsU",$nhtml, $mt))
					{
						echo "for product $pcode no image number $nr! <br>\n";
						echo "html is $nhtml <br>";
						flush();
					}
					else
					{
						$this->db_query("INSERT INTO otto_prod_img (pcode, nr, imnr) 
							values('$pcode','$nr','$mt[1]')");
					}
				}
			}
			$stat = fopen("/www/otto.struktuur.ee/public/vv_pimg/status.txt","w");
		
			fwrite($stat, $pcode);
			fclose($stat);
			//sleep(1);
			$cur_cnt++;
			$time_cur_cnt++;
		}

		echo "all done! <br>\n";
		die();
	}

	function do_prod_import($o)
	{
		// read csvs into db for temp crap
		$this->update_csv_db($o);
	}

	function update_csv_db($o)
	{
		set_time_limit(0);
		if ($o->prop("restart_pict_i"))
		{
			unlink("/www/otto.struktuur.ee/public/vv_pimg/status.txt");
		}
		if ($o->prop("do_pict_i"))
		{
			$this->pictimp($o);
		}

		obj_set_opt("no_cache", 1);

		$imp_stat_file = aw_ini_get("site_basedir")."/files/impstatus.txt";
		if ($o->prop("restart_prod_i"))
		{
			unlink($imp_stat_file);
		}

		if (file_exists($imp_stat_file))
		{
			$skip_to = $this->get_file(array("file" => "/www/otto.struktuur.ee/public/vv_pimg/status.txt"));
			echo "restarting from product $skip_to <br>";
		}

		$this->db_query("DELETE FROM otto_imp_t_prod");
		$this->db_query("DELETE FROM otto_imp_t_codes");
		$this->db_query("DELETE FROM otto_imp_t_prices");
		$this->db_query("DELETE FROM otto_imp_t_p2p");

		echo "from url ".$o->prop("folder_url")." read: <br>";

		$first = true;

		$fp = fopen($o->prop("folder_url"), "r");
		while ($row = fgetcsv($fp,1000))
		{
			if ($first)
			{
				$first = false;
				continue;
			}

			echo "$pg => \n";
			$row = $this->char_replacement($row);
			flush();
			foreach(explode(",",$row[1]) as $pg)
			{
				$this->db_query("
					INSERT INTO otto_imp_t_p2p(pg,fld)
					VALUES('$pg','$row[2]')
				");
				echo ".\n";
				flush();
			}
			echo "<br>\n";
		}

		foreach(explode("\n", $o->prop("fnames")) as $fname)
		{
			if ($fname == "")
			{
				continue;
			}

			$fld_url = $o->prop("base_url")."/".trim($fname)."-1.csv";
			echo "from url ".$fld_url." read: <br>";
			list(, $cur_pg) = explode(".", $fname);
			$cur_pg = (int)substr($cur_pg,1);

			$first = true;
			$num = 0;

			$fp = fopen($fld_url, "r");
			while ($row = fgetcsv($fp,1000,"\t"))
			{
				if ($first)
				{
					$first = false;
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
				}
				$num++;
			}
			echo ".. got $num titles <br>";
		}

		foreach(explode("\n", $o->prop("fnames")) as $fname)
		{
			if ($fname == "")
			{
				continue;
			}

			$fld_url = $o->prop("base_url")."/".trim($fname)."-2.csv";
			echo "from url ".$fld_url." read: <br>";
			list(, $cur_pg) = explode(".", $fname);
			$cur_pg = (int)substr($cur_pg,1);

			$first = true;
			$num =0;

			$fp = fopen($fld_url, "r");
			while ($row = fgetcsv($fp,1000,"\t"))
			{
				if ($first)
				{
					$first = false;
					continue;
				}
				$this->quote(&$row);
				$row = $this->char_replacement($row);
				$row[4] = substr(str_replace(" ","", $row[4]), 0, 6);
				$color = $row[3];
				if ($row[2] != "")
				{
					$color .= " (".$row[2].")";
				}
				$this->db_query("
					INSERT INTO otto_imp_t_codes(pg,nr,size,color,code)
					VALUES('$cur_pg','$row[1]','$row[2]','$color','$row[4]')
				");
				$num++;
				if (!$row[4])
				{
					echo "ERROR ON LINE $num code ".$row[4]." <br>";
				}
			}
			echo ".. got $num codes <br>\n";
			flush();
		}

		foreach(explode("\n", $o->prop("fnames")) as $fname)
		{
			if ($fname == "")
			{
				continue;
			}

			$fld_url = $o->prop("base_url")."/".trim($fname)."-3.csv";
			echo "from url ".$fld_url." read: <br>";
			list(, $cur_pg) = explode(".", $fname);
			$cur_pg = (int)substr($cur_pg,1);

			$first = true;

			$num = 0;
			$fp = fopen($fld_url, "r");
			while ($row = fgetcsv($fp,1000,"\t"))
			{
				if ($first)
				{
					$first = false;
					continue;
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
					echo "ERROR ON LINE $num price = $row[5] <br>";
					for ($i = 0; $i < strlen($orig); $i++)
					{
						echo "at pos ".$i." cahar = ".ord($orig{$i})." v = ".$orig{$i}." <br>";
					}
				}
				$num++;
			}
			echo ".. got $num prices <br>\n";
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

		echo "make existing prod lut <br>\n";
		flush();
		$exist = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
		));
		$pl = array();

		$exist_arr = $exist->arr();
		foreach($exist_arr as $t)
		{
			$pl[$t->prop("user20")] = $t->id();
		}
		
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
				c.size as s_type
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
			echo "checking if \$pl[".$row['code']."] is oid<br>\n";
			flush();
			if (is_oid($pl[$row["code"]]))
			{
				echo "oid = ".$pl[$row["code"]]." <br>";
				$dat = obj($pl[$row["code"]]);
				echo "found existing ".$dat->id()."   ".$row['code']."<br>\n";
				flush();
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
				$pl[$row["code"]] = $dat->id();
			}

			// try find correct folder
			$try_fld = $this->db_fetch_field("SELECT fld FROM otto_imp_t_p2p WHERE pg = '$row[pg]'", "fld");
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
			$dat->set_prop("user17", $row["color"]);
			$dat->set_prop("user18", $row["pg"]);
			$dat->set_prop("user19", $row["nr"]);


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
			// now, for each price, create packaging objects
			$this->db_query("SELECT * FROM otto_imp_t_prices WHERE pg = $row[pg] AND nr = '$row[nr]' AND type = '".$orig_row["s_type"]."'");
			while ($row = $this->db_next())
			{
				// gotta split the sizes and do one packaging for each
				$s_tmpc = explode(",", $row["size"]);
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

				foreach($s_tmp as $tmpcc)
				{
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
						$pk->set_class_id(CL_SHOP_PRODUCT_PACKAGING);
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
			$dat->save();
			$this->restore_handle();

			$stat = fopen($imp_stat_file,"w");
			fwrite($stat, $orig_pcode);
			fclose($stat);

			flush();
			$items_done++;
		}

		echo "hear hear. prods done. <br>\n";
		flush();
		// to make packages, group by image number and for all images where cnt > 1 create a package for all those prods

		echo "make existing packet lut <br>\n";
		flush();
		$exist = new object_list(array(
			"class_id" => CL_SHOP_PACKET,
			"parent" => $o->prop("prod_folder")
		));
		$pktl = array();
		foreach($exist->arr() as $t)
		{
			// user5 is image nr
			$pktl[$t->prop("user3")] = $t->id();
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
				$used[$row["pcode"]] = 1;

				echo "connected packet ".$pko->id()." to prod ".$row["pcode"]." <br>";
				if (!$cprods[$row["pcode"]])
				{
					if ($pl[$row["pcode"]])
					{
						$pko->connect(array(
							"to" => $pl[$row["pcode"]],
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
			$this->restore_handle();
		}

		// fix-missing images
		echo "try fix missing images! <br>";
		$this->pictfix(array());

		// clear cache
		$cache = get_instance("maitenance");
		$cache->cache_clear(array("clear" => 1));

		die("all done! <br>");
	}

	function conv($str)
	{
		$str = str_replace(chr(207), "ž", $str);
		return $str;
	}

	function char_replacement($str)
	{
		$needle = array('Î','Ï',chr(137));
		$haystack = array(chr(158),chr(158),chr(154));
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
		// rewrite some vars that are hard to rewire in js and forward to shop order cart
		$vars = $arr;
		$vars["order_data"] = array();
		$vars["order_data"][$arr["add_to_cart"]]["color"] = ($arr["order_data_color"] != "" ? $arr["order_data_color"] : "---");
		$vars["order_data"][$arr["add_to_cart"]]["size"] = $arr["size_name"];
		/*$vars["ord_content"] = array();
		$vars["ord_content"][$arr["add_to_cart"]]["color"] = ($arr["order_data_color"] != "" ? $arr["order_data_color"] : "---");
		$vars["ord_content"][$arr["add_to_cart"]]["size"] = $arr["size_name"];*/
		$vars["add_to_cart"] = array();
		$vars["add_to_cart"][$arr["add_to_cart"]] = $arr["add_to_cart_count"];

		$i = get_instance("applications/shop/shop_order_cart");
		return $i->submit_add_cart($vars);
	}

	/** 

	@attrib name=pictfix

	**/
	function pictfix($arr)
	{
		die($this->pictimp(array(), true));
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
}

?>
