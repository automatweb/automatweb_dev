<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_import.aw,v 1.135 2010/02/25 11:51:27 dragut Exp $
// otto_import.aw - Otto toodete import
/*

@classinfo syslog_type=ST_OTTO_IMPORT relationmgr=yes no_status=1 no_comment=1 prop_cb=1  maintainer=dragut

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property base_url type=textbox
	@caption Toodete csv failide baasaadress

	@property prod_folder type=relpicker reltype=RELTYPE_FOLDER
	@caption Toodete kataloog

	@property shop_product_config_form type=relpicker reltype=RELTYPE_SHOP_PRODUCT_CONFIG_FORM
	@caption Lao toote seadete vorm

	@property images_folder type=textbox
	@caption Serveri kaust kuhu pildid salvestatakse

	@property files_to_import type=text store=no
	@caption Imporditavad failid

@groupinfo files caption="Failid"

	@layout hbox_files_import type=hbox width=20%:80% group=files

		@layout vbox_files_import_filenames type=vbox closeable=1 area_caption=Toodete&nbsp;otsing group=files parent=hbox_files_import

			@property fnames type=textarea rows=15 cols=30 group=files parent=vbox_files_import_filenames
			@caption Failinimed


		@layout vbox_files_import_sites type=vbox group=files parent=hbox_files_import

			@property files_import_sites_order type=table field=meta method=serialize group=files parent=vbox_files_import_sites captionside=top
			@caption Saitide j&auml;rjekord


@groupinfo products_xml caption="[dev] Toodete XML"
@default group=products_xml

	@property csv_files_location type=textbox field=meta method=serialize
	@caption CSV failid

	@property xml_file_link type=text 
	@caption Genereeritud XML fail

	@property csv_files_list type=table no_caption=1

@groupinfo availability caption="Laoseisud"
@default group=availability

	@property availability_ftp_host type=textbox table=objects field=meta method=serialize
	@caption FTP aadress
	@comment FTP serveri aadress

	@property availability_ftp_user type=textbox table=objects field=meta method=serialize
	@caption FTP kasutaja
	@comment Kasutajanimi, millega FTP serverisse logitakse

	@property availability_ftp_password type=password table=objects field=meta method=serialize
	@caption FTP parool
	@comment Parool FTP kasutajale

	@property availability_ftp_file_location type=textbox table=objects field=meta method=serialize size=70
	@caption Faili asukoht

	@property availability_import_link type=text store=no
	@caption Laoseisu import

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype SHOP_PRODUCT_CONFIG_FORM value=2 clid=CL_CFGFORM
@caption Lao toote seadete vorm

*/

define('BIG_PICTURE', 1);
define('SMALL_PICTURE', 2);

class otto_import extends class_base implements warehouse_import_if
{
	var $not_found_products = array();

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
			case 'files_to_import':
				$prop['value'] = nl2br($arr['obj_inst']->prop('fnames'));
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

		}
		return $retval;
	}

	function callback_mod_layout($arr)
	{
		return true;
	}

	function callback_mod_reforb($arr)
	{

	}

	function callback_mod_retval($arr)
	{

	}

	function callback_pre_save($arr)
	{

	}

	function callback_post_save($arr)
	{

	}

	function _get_files_import_sites_order($arr)
	{
		$table = &$arr['prop']['vcl_inst'];
		$table->set_sortable(false);

		$table->define_field(array(
			'name' => 'active',
			'caption' => t('Aktiivne'),
			'align' => 'center',
			'width' => '10%'
		));

		$table->define_field(array(
			'name' => 'order',
			'caption' => t('Jrk'),
			'align' => 'center',
			'width' => '10%'
		));
		$table->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi'),
		));
		$sites = $arr['obj_inst']->meta("files_import_sites_order");
		$sites_active = $arr['obj_inst']->meta('files_import_sites_active');
		$names = array(
			"heine" => "heine.de",
			"otto" => "otto.de",
			"schwab" => "schwab.de",
			"albamoda" => "albamoda.de",
			"baur" => "baur.de",
			"bp_pl" => "Poola Bonprix",
			"bp_de" => "Saksa Bonprix",
		);
	
		if (empty($sites))
		{
			$sites = array(
				"heine" => 1,
				"otto" => 2,
				"schwab" => 3,
				"albamoda" => 4,
				"baur" => 5,
				"bp_pl" => 6,
				"bp_de" => 7
			);
		}

		foreach ($sites as $key => $value)
		{
			$table->define_data(array(
				'active' => html::checkbox(array(
					'name' => 'sites_active['.trim($key).']',
					'value' => 1,
					'checked' => (!empty($sites_active[$key])) ? true : false
				)),
				'order' => html::textbox(array(
					'name' => "sites_order[".trim($key)."]",
					'value' => $value,
					'size' => 3
				)),
				'name' => $names[$key]
			));
		}
		return PROP_OK;
	}

	function _set_files_import_sites_order($arr)
	{
		asort($arr['request']['sites_order']);
		$arr['obj_inst']->set_meta("files_import_sites_order", $arr['request']['sites_order']);
		$arr['obj_inst']->set_meta("files_import_sites_active", $arr['request']['sites_active']);
		return PROP_OK;
	}

	/*
		new csv files import function
	*/
	function load_data_from_csv($o) 
	{
		$this->cleanup_tmp_tables();

		if (!headers_sent())
		{
			header('Content-type: text/html; charset=UTF-8');
		}

		echo "Load data from CSV [new and refactored version]<br />\n";

	//	$fext = '.xls';
	//	$fext = '.txt';
		$fext = '';

		$fnames = explode("\n", $o->prop('fnames'));

		foreach ($fnames as $fname)
		{
			// In case there are some empty lines in file names textarea:
			$fname = trim($fname);
			if (empty($fname))
			{
				continue;
			}

			echo "Load data from CSV file \"".$fname."\" <br />\n";
			// first file (titles and descriptions):
			$file_path = $o->prop("base_url")."/".trim($fname)."-1".$fext;
			$prod_descs = $this->read_csv_file_content($file_path);
			echo " -- Got ".count($prod_descs)." product descriptions from ".$file_path."<br />\n";
			$this->fill_tmp_product_descs_table($prod_descs);

			// second file (colors and product codes)
			$file_path = $o->prop("base_url")."/".trim($fname)."-2".$fext;
			$prod_colors = $this->read_csv_file_content($file_path);
			echo " -- Got ".count($prod_colors)." product codes/colors from ".$file_path."<br />\n";
			$this->fill_tmp_product_codes_table($prod_colors);

			// third file (sizes and prices):
			$file_path = $o->prop("base_url")."/".trim($fname)."-3".$fext;
			$prod_prices = $this->read_csv_file_content($file_path);
			echo " -- Got ".count($prod_prices)." product prices/sizes from ".$file_path."<br />\n";
			$this->fill_tmp_product_prices_table($prod_prices);
		}
	}
	
	private function read_csv_file_content($file)
	{
		$f = file($file);

		// remove the first line:
		unset($f[0]);

		$result = array();
		foreach ($f as $k => $l)
		{
			// here is 2 important things:
			// - the line $l has to be ltrim()-d before passed to mb_convert_encoding() fn. I think it removes the null byte in front of the line.
			// - the from encoding has to be precisely UTF-16LE
			$l = mb_convert_encoding(ltrim($l), "UTF-8", "UTF-16LE");
			if (!empty($l))
			{
				$result[] = explode("\t", $l);
			}
		}
		return $result;
	}

	private function cleanup_tmp_tables()
	{
		$this->db_query("DELETE FROM otto_imp_t_prod WHERE lang_id=".aw_global_get('lang_id'));
	
		$this->db_query("DELETE FROM otto_imp_t_codes WHERE lang_id=".aw_global_get('lang_id'));

		$this->db_query("DELETE FROM otto_imp_t_prices WHERE lang_id=".aw_global_get('lang_id'));
	}

	// fill the temporary table with the data from csv
	private function fill_tmp_product_descs_table($data)
	{
		/*
		mysql> desc otto_imp_t_prod;
		+----------+--------------+------+-----+---------+----------------+
		| Field    | Type         | Null | Key | Default | Extra          |
		+----------+--------------+------+-----+---------+----------------+
		| c        | text         | YES  |     | NULL    |                | 
		| id       | int(11)      | NO   | PRI | NULL    | auto_increment | 
		| nr       | varchar(5)   | YES  | MUL | NULL    |                | 
		| pg       | varchar(50)  | NO   | MUL |         |                | 
		| title    | varchar(255) | YES  |     | NULL    |                | 
		| lang_id  | int(11)      | YES  |     | NULL    |                | 
		| extrafld | varchar(255) | YES  |     | NULL    |                | 
		+----------+--------------+------+-----+---------+----------------+
		7 rows in set (0.00 sec)
		*/

		foreach ($data as $item)
		{
			$item = $this->string_cleanup($item);

			$sql = "
				insert into otto_imp_t_prod set
					pg = '".addslashes($item[0])."',
					nr = '".addslashes($item[1])."',
					title = '".addslashes($item[2])."',
					c = '".addslashes($item[4])."',
					extrafld = '".addslashes($item[3])."',
					lang_id = '".aw_global_get('lang_id')."'
			";
			$this->db_query($sql);
		}
	
	}

	// fill the temporary table with the data from csv
	private function fill_tmp_product_codes_table($data)
	{
		/*
		mysql> desc otto_imp_t_codes;
		+---------------+--------------+------+-----+---------+----------------+
		| Field         | Type         | Null | Key | Default | Extra          |
		+---------------+--------------+------+-----+---------+----------------+
		| code          | varchar(100) | YES  | MUL | NULL    |                | 
		| original_code | varchar(255) | YES  |     | NULL    |                | 
		| color         | varchar(100) | YES  |     | NULL    |                | 
		| full_code     | varchar(10)  | YES  |     | NULL    |                | 
		| id            | int(11)      | NO   | PRI | NULL    | auto_increment | 
		| nr            | varchar(5)   | YES  | MUL | NULL    |                | 
		| pg            | varchar(50)  | NO   | MUL |         |                | 
		| s_type        | varchar(255) | YES  |     | NULL    |                | 
		| set_f_img     | varchar(20)  | YES  |     | NULL    |                | 
		| size          | varchar(50)  | YES  |     | NULL    |                | 
		| lang_id       | int(11)      | YES  |     | NULL    |                | 
		+---------------+--------------+------+-----+---------+----------------+
		11 rows in set (0.00 sec)
		*/

		foreach ($data as $item)
		{
			$item = $this->string_cleanup($item);

			// not used at the moment
			$full_code = '';
			$original_code = ''; 
			$set_f_img = '';

			$color = (!empty($item[2])) ? $item[3] . '('.$item[2].')' : $item[3];

			$code = $item[4];

			$sql = "
				insert into otto_imp_t_codes set
					pg = '".$item[0]."',
					nr = '".$item[1]."',
					s_type = '".$item[2]."',
					color = '".$color."',
					code = '".$code."',
					original_code = '".$original_code."',
					full_code = '".$full_code."',
					set_f_img = '".$set_f_img."',
					lang_id = ".aw_global_get('lang_id')."
			";

			$this->db_query($sql);
		}
	}

	// fill the temporary table with the data from csv
	private function fill_tmp_product_prices_table($data)
	{
		/*
		mysql> desc otto_imp_t_prices;
		+---------------+--------------+------+-----+---------+----------------+
		| Field         | Type         | Null | Key | Default | Extra          |
		+---------------+--------------+------+-----+---------+----------------+
		| id            | int(11)      | NO   | PRI | NULL    | auto_increment | 
		| nr            | varchar(5)   | YES  | MUL | NULL    |                | 
		| pg            | varchar(50)  | NO   | MUL |         |                | 
		| price         | varchar(100) | YES  |     | NULL    |                | 
		| special_price | varchar(255) | YES  |     | NULL    |                | 
		| s_type        | varchar(255) | YES  |     | NULL    |                | 
		| size          | varchar(50)  | YES  |     | NULL    |                | 
		| type          | varchar(50)  | YES  |     | NULL    |                | 
		| unit          | varchar(100) | YES  |     | NULL    |                | 
		| lang_id       | int(11)      | YES  |     | NULL    |                | 
		+---------------+--------------+------+-----+---------+----------------+
		10 rows in set (0.00 sec)
		*/

		foreach ($data as $item)
		{
			$item = $this->string_cleanup($item);

			$price = str_replace(
				array(',', '-', '_'),
				array('.', '', ''),
				$item[5]
				);

			$price = (double)trim($price);

			$special_price = 0;
			if (!empty($item[6]))
			{
				$special_price = str_replace(
					array(',', '-', '_'),
					array('.', '', ''),
					$item[6]
					);

				$special_price = (double)trim($special_price);
			}

			$sql = "
				insert into otto_imp_t_prices set
					pg = '".$item[0]."',
					nr = '".$item[1]."',
					s_type = '".$item[2]."',
					size = '".$item[3]."',
					unit = '".$item[4]."',
					price = '".$price."',
					special_price = '".$special_price."',
					lang_id = ".aw_global_get('lang_id')."
			";
			$this->db_query($sql);	
		}
	
	}

	private function string_debug($str) 
	{
		echo "[[ STRING DEBUG START ]]<br />\n";
		for ($i = 0; $i < strlen($str); $i++)
		{
			echo "-- [ ".$str{$i}." ] [ ".ord($str{$i})." ] [ ".dechex(ord($str{$i}))." ]<br />\n";
		}
		echo "[[ STRING DEBUG END ]]<br />\n";
	}

	/** 
		Removes the UTF-8 BOM from the string, anywhere it appears. The BOM is not needed and only screwes up text processing
		In dec it is "239 187 191"
		In hex it is "ef bb bf"

		There is also Unicode space lingering in the texts somewhere, so maybe it is safe to remove this too:
		In dec it is "226 128 130"
		In hex it is "e2 80 82"
		U+2002 EN SPACE 
		UTF-8: e2 80 82   UTF-16BE: 2002   Decimal: &#8194;

		There is also Unicode space lingering in the texts somewhere, so maybe it is safe to remove this too:
		In dec it is "226 128 136"
		In hex it is "e2 80 88"
		U+2008 PUNCTUATION SPACE 
		UTF-8: e2 80 88   UTF-16BE: 2008   Decimal: &#8200;

		If the parameter is a string, then those characters/character sequences will be remove and the clean string will be returned
		If the parameter is an array of strings, then the cleanup will be performed on every string in the array and the array of clean strings will be returned

	**/
	private function string_cleanup($var)
	{
		$search = array(
			chr(239).chr(187).chr(191), // UTF-8 BOM
			chr(226).chr(128).chr(130), // Unicode (UTF-16) space
			chr(226).chr(128).chr(136), // Unicode (UTF-16) space
		);

		if (is_array($var))
		{
			foreach ($var as $k => $v)
			{
				$var[$k] = str_replace($search, ' ', $v);
			}
			return $var;
		}
		return str_replace($search, '', $var);
	}

	/**
		Returns the array of URL-s to pictures
	**/
	private function get_pictures($arr)
	{
		echo "#### [BEGIN] Getting pictures #### <br />\n";
		$sites = $arr['import_obj']->meta("files_import_sites_order");
		$active = $arr['import_obj']->meta("files_import_sites_active");

		$pictures = false;
		foreach ($sites as $site => $order)
		{
			// In the UI one can mark which sites are "active" - where the pictures should be searched
			// If a site is not marked as active, don't search from there
			if (empty($active[$site]))
			{
				continue;
			}

			switch ($site)
			{
				case "otto":
					$pictures = $this->get_images_from_otto($arr);
					break;
				case "heine":
					$pictures = $this->get_images_from_heine($arr);
					break;
				case "schwab":
					$pictures = $this->read_img_from_schwab($arr);
					break;
				case "albamoda":
				// lets comment this thing out for now cause it gave some db error
				// and searching from albamoda doesn't work at the moment anyway --dragut@19.08.2009
				//	$pictures = $this->read_img_from_albamoda($arr);
					break;
				case "baur":
					$pictures = $this->read_img_from_baur($arr);
					break;
				case "bp_pl":
					$pictures = $this->bonprix_picture_import_pl($arr);
					break;
				case "bp_de":
					$pictures = $this->bonprix_picture_import_de($arr);
					break;
			}
			if (!empty($pictures))
			{
				break;
			}
		}
		echo "#### [END] Getting pictures #### <br />\n";
		return $pictures;
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
		echo "-- Searching pictures from Polish Bonprix:<br />\n";
		$pcode = substr(str_replace(' ', '', $arr['pcode']), 0, 6);
		$start_time = $arr['start_time'];
		$import_obj = $arr['import_obj'];

		/*
			Poola bp saidist ei ole vaja pilte enam otsima minna, vaid need on juba olemas
			ja seosed piltide ja toodete vahel on defineeritud seosetabelis mille saab ftp-st.
		*/

		$result_images = array();

		$f = file('/www/www.bonprix.ee/public/vv_bp_pl_img/linkage.txt');
		$f = array_unique($f);
		foreach ($f as $line)
		{
			$items = explode(';', $line);
			if ($items[0] == $pcode)
			{
				echo '---- '.$pcode ." - ". $line."<br>\n";
				$mask = $items[2];
				$filename = basename($items[1], '.jpg');
				for ( $i = 0; $i < strlen($mask); $i++ )
				{
					if ($mask{$i} == 1)
					{
						$image_url = 'http://www.bonprix.ee/vv_bp_pl_img/'.$i.'/'.$filename.'_600.jpg';
						$result_images[$image_url] = $image_url;
					}
				}
			}
		}
		return $result_images;

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
		echo "-- Searching pictures from German Bonprix:<br />\n";
		$pcode = $arr['pcode'];
		$start_time = $arr['start_time'];
		$import_obj = $arr['import_obj'];
		$url = "http://www.bonprix-shop.de/bp/search.htm?id=188035177146052928-0&nv=0%7C0%7C1&sc=0&pAnfrage=".$pcode;
		$html = $this->file_get_contents($url);

		if (strpos($html, "Leider konnten wir") === false)
		{
			echo "[ BONPRIX SAKSA ]<br>";
			echo "-- Leidsin toote <strong>[ $pcode ]</strong> [<a href=\"$url\">url</a>]<br />";

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
					$first_im_name = $mt[2];
					$first_im_var = $mt[1];
					break;
				}
			}
			echo "---- Kontrollin baasist pilti [ $first_im ] <br>\n";
			flush();
				$image_ok = $this->get_image(array(
					'source' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$first_im_var.'/'.$first_im_name.'.jpg',
					'format' => 2,
					'otto_import' => $import_obj,
					'filename' => $first_im_name.'_var'.$first_im_var,
					'debug' => true
				));
				if ($image_ok)
				{
					// download the big version of the image too:
					$this->get_image(array(
						'source' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$first_im_var.'/'.$first_im_name.'.jpg',
						'format' => 1,
						'otto_import' => $import_obj,
						'filename' => $first_im_name.'_var'.$first_im_var,
						'debug' => true
					));
				}
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
			//	$var = $nr;
				$nr = $nr{strlen($nr)-1};
				echo "---- Kontrollin baasist pilti [ $im ] <br>\n";
				flush();
				$image_ok = $this->get_image(array(
					'source' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$nr.'/'.$r_i.'.jpg',
					'format' => 2,
					'otto_import' => $import_obj,
					'filename' => $im,
					'debug' => true
				));
				if ($image_ok)
				{
					// download the big version of the image too:
					$this->get_image(array(
						'source' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$nr.'/'.$r_i.'.jpg',
						'format' => 1,
						'otto_import' => $import_obj,
						'filename' => $im,
						'debug' => true
					));
				}

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
/*
	function otto_picture_import($arr)
	{
		$sites = $arr['import_obj']->meta("files_import_sites_order");
		$picture_found = false;
		foreach ($sites as $site => $order)
		{
			switch ($site)
			{
				case "otto":
					$picture_found = $this->get_images_from_otto($arr);
					break;
				case "heine":
					$picture_found = $this->get_images_from_heine($arr);
					break;
				case "schwab":
					$picture_found = $this->read_img_from_schwab($arr);
					break;
				case "albamoda":
				// lets comment this thing out for now cause it gave some db error
				// and searching from albamoda doesn't work at the moment anyway --dragut@19.08.2009
				//	$picture_found = $this->read_img_from_albamoda($arr);
					break;
				case "baur":
					$picture_found = $this->read_img_from_baur($arr);
					break;
			}
			if ($picture_found !== false)
			{
				break;
			}
		}
		// TODO have to refactor this place here
		return $picture_found;
	}
*/
	function get_images_from_otto($arr)
	{
		$return_images = array();

		$full_pcode = $arr['pcode'];
		$pcode = substr(str_replace(' ', '', $arr['pcode']), 0, 6);

		$import_obj = $arr['import_obj'];
		$start_time = $arr['start_time'];

		echo "[ OTTO ] Searching images for product code <strong>".$pcode." (length: ".strlen($pcode).")</strong> / full code: ".$full_pcode." (length: ".strlen($full_pcode).")<br />\n";
		$url = "http://www.otto.de/is-bin/INTERSHOP.enfinity/WFS/Otto-OttoDe-Site/de_DE/-/EUR/OV_ViewFHSearch-Search?ls=0&commit=true&fh_search=".urlencode($pcode)."&fh_search_initial=".urlencode($pcode)."&fh_search_requested=".str_replace(' ', '+', $pcode)."&sterm=".str_replace(' ', '+', $pcode)."&stype=N&noMediaSearchLink=true";

		echo "[ OTTO ] Loading <a href=\"$url\">page</a> content ... ";
		flush();

		$html = file_get_contents($url);

		echo "[ OK ]<br />\n";
		flush();

		// image is http://image01.otto.de:80/pool/OttoDe/de_DE/images/formatb/[number].jpg

		if (strpos($html,"Leider konnten wir") !== false)
		{
			echo "[ OTTO ] Can't find a product for <b>$pcode</b> from otto.de, return false<br>\n";
			return false;
		}
		else
		{
			$o_html = $html;

			// If it finds only one product,then there will be some JS transition page which directs to product detailed view
			preg_match_all("/function goon\(\) \{(.*)\}/imsU", $html, $mt, PREG_PATTERN_ORDER);
			$js_code = $mt[1][0];
			if (!empty($mt[1]))
			{
				$pattern = "/\" \+ encodeURIComponent\(\"(.*)\"\)/U";

				preg_match_all($pattern, $js_code, $m);
				foreach ($m[0] as $k => $v)
				{
					$js_code = str_replace($m[0][$k], urlencode($m[1][$k]).'"', $js_code);
				}
				$pattern = "/\"(.*)\"/U";
				preg_match_all($pattern, $js_code, $m);

				$urld[] = implode('', $m[1]);
			}
			else
			{
				echo "[ OTTO ] No transition page, so lets get the products urls from products list<br />\n";
				if (preg_match_all("/gotoSearchArticle\('(.*)&FromSearch=true'\)/mU", $html, $m))
				{
					echo "[ OTTO ] Found ".count($m[1])." products (urls)<br />\n";
					$urld[] = $m[1][0];
				}
				else
				{
					echo "[ OTTO ] Found nothing! <br />\n";
				}
			}

			foreach($urld as $url)
			{
				echo "[ OTTO ] Searching pictures from <a href=\"$url\">url</a> <br />\n";

				// Now this is here because for some reason, otto.de site refuses to accpet query, which contains data that was not urlencoded
				$url_parts = parse_url($url);
				$url_params = explode('&', $url_parts['query']);
				foreach ($url_params as $id => $url_param)
				{
					list($key, $value) = explode('=', $url_param);
					switch ($key)
					{
						case 'fh_search_initial':
						case 'query_text':
							$url_params[$id] = $key.'='.urlencode($value);
					}
				}

				$url = $url_parts['scheme'].'://'.$url_parts['host'].$url_parts['path'].'?'.implode('&', $url_params);

				$html = file_get_contents($url);

				// Getting the main image:
				if (!preg_match_all("/<img id=\"mainimage\" src=\"(.*)\.jpg\"/imsU", $html, $mt, PREG_PATTERN_ORDER))
				{
					echo "[ OTTO ] If we can't find image from otto.de product view, then return false <br />\n";
					return false;
				}

				// we need that connecting picture:
				$connection_image = '';
				$pattern = "/<img width=.* title=.* src=\"http:\/\/image01\.otto\.de:80\/pool\/ov_formatg\/(.*)\.jpg\"/imsU";
				if (preg_match($pattern, $html, $matches ))
				{
					$connection_image = $matches[1];
					if (!empty($connection_image))
					{
						$return_images[] = 'http://image01.otto.de:80/pool/formata/'.$connection_image.'.jpg';
					}
				}

				foreach($mt[1] as $idx => $img)
				{
					if (strpos($img, 'leer.gif') !== false )
					{
						echo "[ OTTO ] tundub, et sellele variandile pilti ei ole <br>\n";
						continue;
					}
					$imnr = basename($img, ".jpg");

					if (file_get_contents(str_replace($imnr, $imnr.'.jpg', $img)) === false)
					{
						echo "[ OTTO ] selle variandi pilti ei &otilde;nnestu k&auml;tte saada<br />\n";
						continue;
					}

					echo "[ OTTO ] Image name: <strong>".$imnr."</strong><br>\n";

					// NEW for new import
					if (file_get_contents('http://image01.otto.de:80/pool/formata/'.$imnr.'.jpg') === false)
					{
						$return_images[] = "http://image02.otto.de/pool/ov_formatg/".$imnr.".jpg";
					}
					else
					{
						$return_images[] = 'http://image01.otto.de:80/pool/formata/'.$imnr.'.jpg';
					}
				}

				// check for rundumanshiftph (flash)
				if (strpos($html, "rundum_ansicht") !== false)
				{
					echo "[ OTTO ] video ";

					$pattern = "/'".preg_quote("http://www.otto.de/is-bin/INTERSHOP.enfinity/WFS/Otto-OttoDe-Site/de_DE/-/EUR/OV_DisplayProductInformation-SuperZoom3D;", "/").".*'/imsU";
					preg_match_all($pattern, $html, $mt, PREG_PATTERN_ORDER);
					$popup_url = str_replace("'", "", $mt[0][0].$f_imnr);
					echo " - from <a href=\"".$popup_url."\">url</a>";

					// get the rundum image number from the popup :(
					$r_html = file_get_contents($popup_url);

					// save rundum
					// get rundum imnr from html
					preg_match_all("/writeFlashCode_superzoom3d\('(.*)'\);/imsU", $r_html, $mt, PREG_PATTERN_ORDER);

					$flash_file_urls = $mt[1];
					foreach ($flash_file_urls as $flash_file_url)
					{
						$flash_file_name = basename($flash_file_url);

						$flash_file_url .= '.swf';

						// NEW for new import
						$return_images[] = $flash_file_url;
					}
					echo "<br /> \n";
				}
			}
		}

		return $return_images;
	}
	function read_img_from_baur($arr)
	{
		$pcode = str_replace(" ", "", $arr['pcode']);
		$import_obj = $arr['import_obj'];

		$url = "http://suche.baur.de/servlet/weikatec.search.SearchServletMmx?ls=0&source=&resultsPerPage=99&searchandbrowse=&category2=&query=".$pcode."&category=";

		echo "[ BAUR ] Loading <a href=\"$url\">page</a> content ";
		$fc = $this->file_get_contents($url);
		echo " [ok]<br />\n";
//		if (strpos($fc, "leider keine Artikel gefunden") !== false)
		if ( (strpos($fc, "search/topcontent/noresult_slogan.gif") !== false) || (strpos($fc, "Entschuldigung,<br>diese Seite konnte nicht gefunden werden.") !== false) || true) // xxx disable baur import for now
		{
			echo "[ BAUR ] Can't find a product for <b>$pcode</b> from baur.de, so return false<br>\n";
			return false;

		}

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
				$image_ok = $this->get_image(array(
					'source' => 'http://image01.otto.de/pool/BaurDe/de_DE/images/formatb/'.$pn.'.jpg',
					'format' => 2,
					'otto_import' => $import_obj,
					'debug' => true
				));
				if ($image_ok)
				{
					// download the big version of the image too:
					$this->get_image(array(
						'source' => 'http://image01.otto.de/pool/BaurDe/de_DE/images/formatb/'.$pn.'.jpg',
						'format' => 1,
						'otto_import' => $import_obj,
						'debug' => true
					));
				}

				// check if the image combo already exists
				$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$pn' AND nr = '$cnt' AND pcode = '$pcode'", "pcode");
				if (!$imnr)
				{
					echo "[ BAUR ] insert new image $pn <br>\n";
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
					echo "[ BAUR ] existing image $pn <br>\n";
				}
				$cnt++;
			}
		}
	}

	function read_img_from_schwab($arr)
	{
		echo "[ SCHWAB ] - product search url is changed <br />\n";
		return false;
		$pcode = $arr['pcode'];
		$import_obj = $arr['import_obj'];

		$url = "http://suche.schwab.de/Schwab/Search.ff?query=".$pcode;
		echo "[ SCHWAB ] Loading <a href=\"$url\">page</a> content ";
		$fc = $this->file_get_contents($url);
		echo "[ok]<br />\n";
		if (strpos($fc, "Keine passenden Ergebnisse f".chr(252)."r:") !== false)
		{
			echo "[ SCHWAB ] can't find a product for <b>$pcode</b> from schwab.de, so returning false<br>\n";
			return false;
		}

		// match prod urls
	//	preg_match_all("/ProductRef=(.*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
	//	$pcs = array_unique($mt[1]);
	//	echo "[schwab] got pcs as ".dbg::dump($pcs)."\n";

	// I assume that this is not exactly needed, cause I have the product code already
	//	preg_match("/query: '(.*)'/", $fc, $mt);
	//	$pcode = $mt[1];

	// It doesn't have the the support for multiple products in search result right now.
	// Because:
	//	a) I assume, that product code is products identifier, so one code == one product
	//	b) I don't have a test case at the moment where there are multiple products/pictures in search result
	//		and I don't know how it looks like in html source
		preg_match("/articleId: '(.*)'/", $fc, $mt);
		$articleId = $mt[1];

		$product_url = "http://www.schwab.de/is-bin/INTERSHOP.enfinity/WFS/Schwab-SchwabDe-Site/de_DE/-/EUR/SV_DisplayProductInformation-SearchDetail?ls=0&query=".$pcode."&ArticleNo=".$articleId;

		// ok, lets keep this loop for now, maybe in real life examples there will be multiple products/pictures in search results:
		$pcs = array(
			0 => $product_url
		);

		foreach($pcs as $prod_url)
		{
			echo "[ SCHWAB ] product <a href=\"$prod_url\">url</a>: <br />\n";
			$fc2 = $this->file_get_contents($prod_url);

			// get first image
			preg_match("/http:\/\/image01\.otto\.de:80\/pool\/formatb\/(\d+).jpg/imsU", $fc2, $mt);
			$first_im = $mt[1];

			$image_ok = $this->get_image(array(
				'source' => 'http://image01.otto.de/pool/formatb/'.$first_im.'.jpg',
				'format' => 2,
				'otto_import' => $import_obj,
				'debug' => true
			));
			if ($image_ok)
			{
				// download the big version of the image too:
				$this->get_image(array(
					'source' => 'http://image01.otto.de/pool/formata/'.$first_im.'.jpg',
					'format' => 1,
					'otto_import' => $import_obj,
					'debug' => true
				));
			}

			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
			if (!$imnr)
			{
				echo "[ SCHWAB ] insert new image $first_im <br>\n";
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

// apparently there seems to be no other images ...
// again, if in real life there actually are, then lets keep the possibility for now:
/*
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
					echo "[ SCHWAB ] insert new image $im <br>\n";
					flush();
					$q = ("
						INSERT INTO
							otto_prod_img(pcode, nr,imnr, server_id)
							values('$pcode','$nr','$im', 3)
					");
					$this->db_query($q);
					$this->added_images[] = $im;
				}
			}
*/
		}
		return true;
	}

	function read_img_from_albamoda($arr)
	{
return false;
		$pcode = $arr['pcode'];
		$import_obj = $arr['import_obj'];

		$url = "http://suche.albamoda.de/servlet/SearchServlet?clientId=AlbaModa-AlbaModaDe-Site&query=".$pcode."&resultsPerPage=120&category=&color=&manufacturer=&minPrice=&maxPrice=&prodDetailUrl=http%3A//www.albamoda.de/is-bin/INTERSHOP.enfinity/WFS/AlbaModa-AlbaModaDe-Site/de_DE/-/EUR/AM_ViewProduct-ProductRef%3Bsid%3DYxKYQ1BufUk5QxZUapu1Y0vC4a2r_3Im9-K6C8SemFURf8RYYg66C8SeC-oUEg%3D%3D%3Fls%3D%26ProductRef%3D%253CSKU%253E%2540AlbaModa-AlbaModaDe%26SearchBack%3D-1%26SearchDetail%3Dtrue";

		echo "[ ALBAMODA ] Loading <a href=\"$url\">page</a> content ";
		$fc = $this->file_get_contents($url);
		echo "[ok]<br />\n";


		if (strpos($fc, "Es wurden leider keine Artikel gefunden.") !== false)
		{
			echo "[ ALBAMODA ] can't find a product for code <b>$pcode</b> from albamoda.de, so return false<br>\n";
			return false;
		}

		// if we found only one product, then the user is redirected directly to the products page
		// if multiple products were found, then I need collect all the urls to products
		if (strpos($fc, "<!-- redirect_proddetail.vm -->") !== false)
		{
			// one product, lets get the url where the user is redirected to
			preg_match("/var url = \"(.*)\"/imsU", $fc, $mt);
			$url = $mt[1];
			echo "[ ALBAMODA ] One product found: <a href=\"$url\">page</a> <br />\n";
			$prod_urls = array($mt[1]);
		}
		else
		{
			// multiple products, lets collect all the product urls from the search results
			$pattern = "/<a href=\"(http\:\/\/.*)\">/i";
			preg_match_all($pattern, $fc, $mt, PREG_PATTERN_ORDER);
			$prod_urls = array_unique($mt[1]);
			echo "[ ALBAMODA ] Found ".count($prod_urls)." products: ";
			$prod_count = 0;
			foreach ($prod_urls as $prod_url)
			{
				$prod_count++;
				echo " <a href=\"".$prod_url."\">[ ".$prod_count." ]</a> ";
			}
			echo "<br />\n";
		}


		foreach($prod_urls as $url)
		{
			$fc = $this->file_get_contents($url);
			echo "<br />\n";

			echo "[ ALBAMODA ] Loading product <a href=\"$url\">page</a> <br />\n";

			// apparently some products are broken in albamoda as well, so lets chek if we got a product from this url at the first place:
			if (strpos($fc, "Liebe Kundin, lieber Kunde.") !== false)
			{
				echo "[ ALBAMODA ] Product page seems to be unavailable <a href=\"".$url."\">[ url ]</a><br />\n";
				continue;
			}

			$pattern = "/SRC=\"http\:\/\/image01\.otto\.de\:80\/pool\/albamoda_formatK\/(.*)\.jpg\"/U";
			preg_match_all($pattern, $fc, $mt, PREG_PATTERN_ORDER);

			$image_name = $mt[1][0];

			echo "[ ALBAMODA ] Image files: ";

			$small_image_src = 'http://image01.otto.de:80/pool/albamoda_formatL/'.$image_name.'.jpg';
			$small_image_ok = $this->get_image(array(
				'source' => $small_image_src,
				'format' => SMALL_PICTURE,
				'otto_import' => $import_obj,
				'debug' => true
			));

			if ($small_image_ok)
			{
				echo "<a href=\"".$small_image_src."\">[ Small image ]</a> ";
			}
			else
			{
				echo "<a href=\"".$small_image_src."\">[ Getting small image failed ]</a> ";
			}

			// download the big version of the image too:
			$big_image_src = 'http://image01.otto.de:80/pool/albamoda_formatK/'.$image_name.'.jpg';
			$big_image_ok = $this->get_image(array(
				'source' => $big_image_src,
				'format' => BIG_PICTURE,
				'otto_import' => $import_obj,
				'debug' => true
			));

			if ($big_image_ok)
			{
				echo "<a href=\"".$big_image_src."\">[ Big image ]</a> ";
			}
			else
			{
				echo "<a href=\"".$big_image_src."\">[ Getting big image failed ]</a> ";
			}

			echo " <br />\n";

			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$image_name' AND nr = '1' AND pcode = '$pcode'", "pcode");
			if (!$imnr)
			{
				echo "[ ALBAMODA ] Database: insert new image $image_name <br>\n";
				flush();
				$q = ("
					INSERT INTO
						otto_prod_img(pcode, nr,imnr, server_id)
						values('$pcode','1','$image_name', 4)
				");
				$this->db_query($q);
			// seems it is not used, so lets start to remove this to get removed unused code -- dragut@10.03.2008
			//	$this->added_images[] = $image_name;
			}
			else
			{
				echo "[ ALBAMODA ] Database: Image [".$image_name."] is already in database <br />\n";
			}

			////
			// Albamoda does have videos as well, so lets improt them too:

			$pattern = "/so\.addVariable\(\"flv_file\", \"(.*\.flv)\"\);/";
			preg_match($pattern, $fc, $mt);
			$file_url = $mt[1];
			if (!empty($file_url))
			{
				$parts = explode('/', $file_url);
				$filename = $parts[count($parts) - 2];

				$video_download_result = $this->get_video(array(
					'source' => $file_url,
					'filename' => $filename.'.flv',
					'otto_import' => $import_obj
				));
				if ($video_download_result !== false)
				{
					echo "[ ALBAMODA ] Video: <a href=\"".$file_url."\">".$filename."</a><br />\n";
					$this->db_query("update otto_prod_img set video = '".addslashes(strip_tags($filename)).".flv' where pcode = '".$pcode."'");
				}
			}
		}
		return true;
	}

	function get_images_from_heine($arr)
	{
		$pcode = $arr['pcode'];
		$import_obj = $arr['import_obj'];
		$product_page_urls = array();

		// no spaces in product code ! --dragut
		$pcode = str_replace(" ", "", $pcode);

		$img_baseurl = "http://image01.otto.de/pool/format_hv_ea_1/";

		$url = "http://www.heine.de/is-bin/INTERSHOP.enfinity/WFS/Heine-HeineDe-Site/de_DE/-/EUR/ViewProductSearch-Search;sid=FPzpHV5oa6GkHRTr4Hn03ws4Wg19UWgvT-9O9HYx0YFE1ZEVxF5O9HYxlOi83Q==?query=$pcode&host=www.heine.de#lmPromo=la,1,hk,sh_home,fl,sh_home_header_suchen";
		echo "[ HEINE ] Loading <a href=\"$url\">page</a> content ... ";
		flush();

		$fc = $this->file_get_contents($url);
		echo "[ok]<br />\n";
		flush();

		$imgs = array();
		if (preg_match_all("/.*\.SKU = \"".substr($pcode, 0, 6).".*mainImages\.add\(\"(.*)\"\).*;$/Um", $fc, $mt))
		{
			$imgs = array_unique($mt[1]);
			foreach ($imgs as $k => $v)
			{
				$imgs[$k] = $img_baseurl.$v;
			}

		}
		else
		{
			// there might be case where there isn't mainImage set for this variation, then it seems, that it will use some style mainImage
			// and it seems, that the correct image can be optained from the line which will match to the following regexp, but it feels so fragile:( --dragut@22.09.2009
			if(preg_match_all("/^style.*AKL.*style\.mainImages\.add\(\"(.*\.jpg\")\)/Um", $fc, $mt))
			{
				echo "[ HEINE ] There is no image for this variation in heine.de, so I try to be smart here and take the image from style <br />\n";
				$imgs[] = $img_baseurl.$mt[1][0];
			}
			else
			{

				echo "[ HEINE ] For some reason it wasn't possible to get picture from the page found <br />\n";
			}
		}

		// if there are no images despite all my effort, then just quit:
		if (empty($imgs))
		{
			return false;
		}

		// if some pictures were found, then display them with urls:
		echo "[ HEINE ] Found product images:<br />\n";
		foreach ($imgs as $img)
		{
			echo "[ HEINE ] * <a href=\"".$img."\">".$img."</a><br />\n";
		}
		$product_page_urls[] = $url;
		echo "[ok]<br />\n";
		flush();

		return $imgs;
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

	//
	// source - image url
	// format - images format (1 - for big image, 2 for thumbnail)
	//- target_folder - server folder where to download images
	// filename - the new filename, if empty, then original filename is used provided by image parameter
	// debug - if set to true, then print out what is happening during download process (boolean)
	// otto_import - otto_import object instance (aw object)
	// overwrite - boolean, indictes if the image file should be overwritten or not
	function get_image($arr)
	{
		$debug = $arr['debug'];
		$overwrite = ( $arr['overwrite'] === true ) ? true : false;

		if (empty($arr['filename']))
		{
			$filename = basename($arr['source'], '.jpg');
		}
		else
		{
			$filename = basename($arr['filename'], '.jpg');
		}

		$folder = $this->get_file_location(array(
			'filename' => $filename,
			'otto_import' => $arr['otto_import']
		));
		if ($folder)
		{
			// new image file
			$new_file = $folder.'/'.$filename.'_'.$arr['format'].'.jpg';
			if ($overwrite || !file_exists($new_file) || filesize($new_file) == 0)
			{
				$this->copy_file(array(
					'source' => $arr['source'],
					'target' => $new_file
				));
			}
			if (filesize($new_file) > 0)
			{
				return true;
			}
		}
		return false;
	}

	////
	// source - url or filesystem path where to get the video (string)
	// otto_import - otto_import object's instance (aw object)
	function get_video($arr)
	{
		if (!empty($arr['filename']))
		{
			$filename = $arr['filename'];
		}
		else
		{
			$filename = basename($arr['source']);
		}

		if (empty($filename) || empty($arr['otto_import']))
		{
			return false;
		}

		$folder = $this->get_file_location(array(
			'filename' => $filename,
			'otto_import' => $arr['otto_import']
		));

		$new_file = $folder.'/'.$filename;

		if (!file_exists($new_file) || $arr['overwrite'] === true)
		{
			$result = $this->copy_file(array(
				'source' => $arr['source'],
				'target' => $new_file
			));
		}
		return $result;
	}

	////
	// filename - (string) filename to ask location for
	// create - (boolean) if the location doesn't exist, then create it, othervise the function returns false
	// otto_import (aw object) otto import object instance
	function get_file_location($arr)
	{
		$filename = $arr['filename'];

		$folder = $arr['otto_import']->prop('images_folder').'/'.$filename{0};

		if (!is_dir($folder))
		{
			mkdir($folder);
		}
		$folder .= '/'.$filename{1};
		if (!is_dir($folder))
		{
			mkdir($folder);
		}
		if (!is_writable($folder))
		{
			return false;
		}
		return $folder;
	}

	////
	// source - url or path where to get the media file (string)
	// target - full path in filesystem where to save the file (string)
	function copy_file($arr)
	{
		$f = fopen($arr['source'], 'rb');
		if ($f)
		{
			while (!feof($f))
			{
				$content .= fread($f, 1024);
			}
			fclose($f);

			$f = fopen($arr['target'], 'wb');
			if ($f)
			{
				fwrite($f, $content);
				fclose($f);
			}

			return true;
		}

		return false;
	}

	function get_file_name($imnr)
	{
		return $imnr{0}.'/'.$imnr{1}.'/'.$imnr;
	}

	function _get_availability_import_link($arr)
	{
		$arr['prop']['value'] = html::href(array(
			'caption' => t('Impordi'),
			'url' => $this->mk_my_orb('do_products_amounts_import', array('id' => $arr['obj_inst']->id()))
		));
	}

	/**
		@attrib name=do_products_amounts_import nologin=1
		@param id required type=int
	**/
	function do_products_amounts_import($arr)
	{
		$mail_msg = aw_ini_get('baseurl')." import l2ks k2ima: ".date("d.m.Y H:i:s");
		mail('rain.viigipuu@automatweb.com', $mail_msg, $mail_msg);

		$log = '';

		ini_set("memory_limit", "2048M");
		aw_set_exec_time(AW_LONG_PROCESS);

		if ($this->can('view', $arr['id']))
		{
			$o = new object($arr['id']);
		}
		else
		{
			exit('OTTO import object is not accessible');
		}

		$ftp_start_time = microtime(true);

		// Get the file from FTP:
		$ftp = new ftp();
		$ftp->verbose = false;
		$connection = $ftp->connect(array(
			'host' => $o->prop('availability_ftp_host'),
			'user' => $o->prop('availability_ftp_user'),
			'pass' => $o->prop('availability_ftp_password')
		));

		switch ($connection)
		{
			case 1:
				echo "[ ERROR ] Connecting to ftp failed <br />\n";
				exit();
			case 2:
				echo "[ ERROR ] Login to ftp failed <br />\n";
				exit();
			default:
				echo " Successfully connected to ftp <br />\n";
		}

		$file_location = $o->prop('availability_ftp_file_location');
		echo "Get file from ftp (file location: ".$file_location.")... ";
		flush();
		$file_content = $ftp->get_file($file_location);
		$local_file = aw_ini_get('site_basedir').'/files/otto_import_availability.zip';
		$file_size = file_put_contents($local_file, $file_content);
		echo "( ".number_format(($file_size / 1024 / 1024), 2)." )";
		echo "[done]<br />\n";
		flush();

		echo "Unpacking the file ...";
		flush();


		if (extension_loaded("zip"))
		{
			$folder = aw_ini_get("site_basedir")."/files/";
			$zip = zip_open($local_file);
			while ($zip_entry = zip_read($zip))
			{
				zip_entry_open($zip, $zip_entry, "r");
				$fn = $folder."/".zip_entry_name($zip_entry);
				$fc = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				file_put_contents($fn, $fc);
			}
		}
		else
		{
			echo "[ FATAL ERROR ] There is no zip extension loaded in PHP, so it is not possible to unpack the file!";
			exit();
		}
		echo "[done]<br />\n";
		flush();

		$ftp_end_time = microtime(true);

		$log .= 'FTP time: '.($ftp_end_time - $ftp_start_time)."\n";

		echo "### START IMPORT ###\n";

		$source_file_load_start = microtime(true);
		// Start import:
		echo "Load lines ... ";
		$lines = file(aw_ini_get('site_basedir').'/files/ASTAEXP.TXT');
		echo "(".count($lines).") [done]\n";
		$source_file_load_end = microtime(true);

		$log .= 'source file load time: '.($source_file_load_end - $source_file_load_start)."\n";

		// tarnija seostamiseks
		echo "Searching for providers ... ";
		$comps = new object_list(array(
			'class_id' => CL_CRM_COMPANY,
			'name' => '%Saksa%',
			new obj_predicate_limit(1),
		));
		$comp = ($comps->count() > 0) ? $comps->begin() : false;
		echo ($comp === false) ? " [fail]\n" : $comp->name()." [done]\n";

		echo "Searching for warehouse ... ";
		$whs = new object_list(array(
			'class_id' => CL_SHOP_WAREHOUSE,
			'name' => '%Eesti%',
			new obj_predicate_limit(1),
		));
		$wh = $whs->count() > 0 ? $whs->begin() : false;
		echo ($wh === false) ? " [fail]\n" : $wh->name()." [done]\n";

		echo "Creating products look-up-table ... ";
		$prods = $this->db_query("
			select
				aw_shop_products.code as full_code,
				aw_shop_products.aw_oid as aw_oid,
				substring(aw_shop_products.short_code, 1, 6) as code
			from
				aw_shop_products left join objects on aw_shop_products.aw_oid = objects.oid
			where
				objects.status > 0
		");
		while ($row = $this->db_next())
		{
			$codes[$row['code']][] = $row;
		}
		echo "(".count($codes).") [done]\n";

		$total_lines = count($lines);

		$counter = 0;

		foreach ($lines as $line)
		{
			$fields = explode(';', $line);

			$line_process_start = microtime(true);

			if (array_key_exists($fields[0], $codes))
			{
				// The catch here is, that sometimes there are several products in aw which share the same first 6 characters of product code
				// but as the purveyance info should go to packaging object, then I take packagings from all the products found and update it
				// in this way, no matter which product is actually in use and visible for users, there should be always up-to-date purveyance 
				// info --dragut@28.09.2009
				$packagings = array();
				foreach ($codes[$fields[0]] as $data)
				{
					$prod = new object($data['aw_oid']);
					echo "product: ".$prod->name()." ( OID: ".$prod->id()." )\n";
					$packagings += $prod->get_packagings()->arr();
					flush();
				}
				flush();
				foreach ($packagings as $packaging)
				{
					//	do_products_amounts_import_handle_size() handles different formes of sizes a'la S(127), 41/2(37), 56
					$handled_code = $this->do_products_amounts_import_handle_size($packaging->prop('size'));
				//	echo "-- Going to compare '".$handled_code."' with '".((int)($fields[1]))."' (".$fields[1].") - packaging id: ".$packaging->id()."<br />\n";
					flush();
					if ($handled_code === ((int)$fields[1]))
					{
						echo "----".$packaging->prop('size')." -- ".$fields[1]." - ".((int)$fields[1])."/ ".$fields[2]."\n";
						flush();
						$purvs = new object_list(array(
							"class_id" => CL_SHOP_PRODUCT_PURVEYANCE,
							"packaging" => $packaging->id(),
							"lang_id" => array(),
							"site_id" => array(),
						));

						// XXX disable ACL
						aw_disable_acl();

						if ($purvs->count() > 0)
						{
							$purv = $purvs->begin();
							echo "-------- Existing purveyance oid: ".$purv->id()." \n";
						}
						else
						{
							echo "-------- NEW purv: ";
							$purv = new object();
							$purv->set_class_id(CL_SHOP_PRODUCT_PURVEYANCE);
							$purv->set_parent($packaging->id());
							$purv->save();
							$purv->connect(array(
								'to' => $packaging->id(),
								'type' => 'RELTYPE_PACKAGING'
							));
							$purv->set_prop('packaging', $packaging->id());

							// tarnija seostamine
							if ($comp !== false)
							{
								echo "------------ connect company ".$comp->name()."\n";
								$purv->connect(array(
									'to' => $comp->id(),
									'type' => 'RELTYPE_COMPANY'
								));
								$purv->set_prop('company', $comp->id());
							}
							
							// seostab lao ka 2ra:
							if ($wh !== false)
							{
								echo "------------ connect warehouse ".$wh->name()."\n";
								$purv->connect(array(
									'to' => $wh->id(),
									'type' => 'RELTYPE_WAREHOUSE'
								));
								$purv->set_prop('warehouse', $wh->id());
							}
							$purv->save();
						}
						//	It's better to handle the status as integer. The caption will be in template
						$purv->set_name($fields[2]);
						switch ($fields[2])
						{
							case 1:
								$purv->set_comment(t('Tarneaeg 3-4 n&auml;dalat'));
								break;
							case 2:
								$purv->set_comment(t('Tarneaeg pikem kui 4 n&auml;dalat'));
								break;
							case 3:
								$purv->set_comment(t('V&auml;ljam&uuml;&uuml;dud'));
								break;
						}
						$purv->set_prop('code', $fields[2]);
						$purv->save();

						// XXX restore ACL
						aw_restore_acl();
						flush();	
					}
				}
				$packagings = null;
				$counter++;
				echo " [".($counter)."/".$total_lines."][ ".number_format( ( $counter / ( $total_lines / 100 ) ), 2 )."% ] \n";

			}
			$fields = null;

			$line_process_end = microtime(true);
		}
		echo "### DONE ###\n";
		$mail_msg = aw_ini_get('baseurl')." import l6petas: ".date("d.m.Y H:i:s");
		mail('rain.viigipuu@automatweb.com', $mail_msg, $mail_msg);
		exit();
	}

	function do_products_amounts_import_handle_size($size)
	{
		// Because the spaces in sizes a causing some trouble, then instead of fixing the patterns, lets remove the spaces when comparing:
		$size = str_replace(' ', '', $size);
		/*
			American sizes must be decoded
			8XL = 924
			7XL = 923
			6XL = 922
			5XL = 921
			4XL = 910
			3XL = XXXL = 909
			XXL = 908
			XL = 907
			L = 906
			M = 905
			ML = 955
			S = 904
			XS = 903
			XXS = 902
			3XS = XXXS = 901 
		*/
		$expressions = array(
			'/^3XS ?(\([0-9\/]+\))*$/' => 901,
			'/^XXXS ?(\([0-9\/]+\))*$/' => 901,
			'/^XXS ?(\([0-9\/]\))*$/' => 902,
			'/^XS ?(\([0-9\/]+\))*$/' => 903,
			'/^S ?(\([0-9\/]+\))*$/' => 904,
			'/^ML ?(\([0-9\/]+\))*$/' => 955,
			'/^M ?(\([0-9\/]+\))*$/' => 905,
			'/^L ?(\([0-9\/]+\))*$/' => 906,
			'/^XL ?(\([0-9\/]+\))*$/' => 907,
			'/^XXL ?(\([0-9\/]+\))*$/' => 908,
			'/^XXXL ?(\([0-9\/]+\))*$/' => 909,
			'/^3XL ?(\([0-9\/]+\))*$/' => 909,
			'/^4XL ?(\([0-9\/]+\))*$/' => 910,
			'/^5XL ?(\([0-9\/]+\))*$/' => 921,
			'/^6XL ?(\([0-9\/]+\))*$/' => 922,
			'/^7XL ?(\([0-9\/]+\))*$/' => 923,
			'/^8XL ?(\([0-9\/]+\))*$/' => 924,
		);
		foreach($expressions as $expression => $value)
		{
			if(preg_match($expression, $size))
			{
				return $value;
			}
		}
		/*
			double sizes 40/42 use the starting 40 only
		*/
		if(preg_match('/^[0-9]+[\/]{1}[0-9]+$/', $size, $m))
		{
			return (int)substr($size, 0, strpos($size, "/"));
		}
		/*
			ring or shoe sizes 19,5 or 8,5 use 195 or 85 (without decimals)

			28.08.2009 -kaarel : The shitty part is that we have show sizes like this: 41/2(37)
			Tested cases:
			41/2(37)	->	45
			51/2(38		->	55
			6(39)		->	60
			61/2(40)	->	65
			6 1/2 (40)	->	65
			7(40		->	70
			71/2(4		->	75
			7 1/2 (4	->	75
		*/
		if(preg_match('/^[0-9 ]+(\/[0-9]+)? ?\([0-9]+/', $size))
		{
			return ((int)substr($size, 0, min(strpos($size, "/") -1, strpos($size, "("))))*10 + (strpos($size, "/") ? 5 : 0);
		}
		return (int)aw_math_calc::string2float($size);
		// !!! FOLLOWING CASES ARE NOT YET HANDLED !!!
		/*
			if quantity is a length so order in cm, max 999 = 10m,		// I can't understand this one! -kaarel 28.08.2009
		*/
	}

	public function _get_xml_file_link($arr)
	{
		$url = $this->mk_my_orb('get_products_xml', array('id' => $arr['obj_inst']->id()));
		$arr['prop']['value'] = html::href(array(
			'url' => $url,
			'caption' => $url
		));
	}

	public function _get_csv_files_list($arr)
	{
		/*
			I probably need to build here somekind of interface where it is possible to choose which files will be imported
		*/
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'csv_file',
			'caption' => t('CSV fail')
		));
		$t->define_field(array(
			'name' => 'xml_file',
			'caption' => t('XML fail')
		));
		$folder = $arr['obj_inst']->prop('csv_files_location');

		$files = glob($folder.'/*.xls');

		$page_files = array();
		foreach ($files as $file)
		{
			$filename = basename($file, '.xls');

			$parts = explode('-', $filename);
			if (count($parts) == 2)
			{
				$page = $parts[0];
				$page_nr = $parts[1];
				$page_files[$page][$page_nr] = $filename;
			}
		}

		foreach ($page_files as $page => $files)
		{
			$t->define_data(array(
				'csv_file' => $page .'(' . implode(',', array_keys($files)) . ')'
			));
		}
	}

        /**
                @attrib name=get_products_xml
        **/
	public function get_products_xml()
	{
		$xml_file_path = aw_ini_get('site_basedir').'/files/warehouse_import/products.xml';
		// This is for warehouse import to get the XML file which the warehouse import will be able to import 

		// TODO: I need a better way to have otto import object id here
		$otto_import_ol = new object_list(array(
			'class_id' => CL_OTTO_IMPORT
		));
		$o = $otto_import_ol->begin();

		// XXX
		$this->load_data_from_csv($o);

/*
// üks variant kuidas see toodete xml välja võiks näha
<products>
	<product>
		<name />
		<desc />
		<categories>
			<category />
			<category />
			<category />
		</categories>
		<colors>
			<color>
				<code />
				<color_name />
				<sizes>
					<size>
						<size_name />
						<price />
					</size>
				</sizes>
			</color>
		</colors>
	</product>
</products>

teine variant oleks teha xml selline, et vastaks aw objektidele (<packet><product></packaging>) jne.
Esimese puhul oleks ülesehitus vast loogilisem, aga siis peaks kuidagi konfitavaks tegema selle, et 
milliste parent tagide järgi packette/tooteid/pakeneid tekitatakse (või kas üldse tehakse)

<warehouse_data>
	<packet>
		<page /> 
		<nr />
		<name />
		<description />
		<categories>
			<category />
		</categories>
		<products>
			<page />
			<nr />
			<type />
			<color />
			<code />
			<product>
				<packagings>
					<page />
					<nr />
					<type />
					<size />
					<price />
				</packagings>
			</product>
		</product>
	</packet>
</warehouse_data>

!!! V6tan hetkel kasutusele selle teise variandi !!!

*/
		$oxml = new XMLWriter();
	//	$oxml->openMemory();
		$oxml->openURI($xml_file_path);
		$oxml->startDocument('1.0', 'utf-8');
		$oxml->startElement('warehouse_data');

		$prods = $this->db_fetch_array("select * from otto_imp_t_prod where lang_id = ".aw_global_get('lang_id')." order by pg,nr");
		foreach ($prods as $prod)
		{
			$oxml->startElement('packet');

			$oxml->writeElement('page', $prod['pg']);
			$oxml->writeElement('nr', $prod['nr']);

			$oxml->startElement('name');
			$oxml->writeCData($prod['title']);
			$oxml->endElement();

			$oxml->startElement('categories');
			foreach (explode(',', $prod['extrafld']) as $extrafld)
			{
				$oxml->writeElement('category', $extrafld);
			}
			$oxml->writeElement('category', $prod['pg']); // add the file/page code as category as well:
			$oxml->endElement();

			$oxml->startElement('description');
			$oxml->writeCData($prod['c']);
			$oxml->endElement();

			echo "- ".$prod['pg'].' -- '.$prod['nr'].' -- '.$prod['title']."<br />\n";
			$codes = $this->db_fetch_array("select * from otto_imp_t_codes where lang_id = ". aw_global_get("lang_id")." and pg = '". $prod["pg"]."' and nr = '".$prod["nr"]."' order by pg,nr,s_type,id" );
			$oxml->startElement('products');
	
			foreach (safe_array($codes) as $product_order => $code)
			{
				$orig_code = $code;

				$oxml->startElement('product');

				$oxml->writeElement('page', $code['pg']);

				$oxml->writeElement('nr', $code['nr']);

				$oxml->writeElement('order', ($product_order * 10)); // i use the array index as order value and it will be with step 10 --dragut

				$oxml->startElement('type');
				$oxml->writeCData($code['s_type']);
				$oxml->endElement();

				$oxml->startElement('color');
				$oxml->writeCData($code['color']);
				$oxml->endElement();

				$oxml->startElement('code');
				$oxml->writeCData($code['code']);
				$oxml->endElement();

				// here i have product code and now i should perform images search
				$imgs = $this->get_pictures(array(
					'pcode' => $code['code'],
					'import_obj' => $o,
					'start_time' => time(),
				));
				if (empty($imgs))
				{
					echo "[NO IMAGES FOUND FOR THIS PRODUCT]<br />\n";
				}
				$oxml->startElement('images');
				foreach (safe_array($imgs) as $img)
				{
					$oxml->startElement('image');
					$oxml->writeCData($img);
					$oxml->endElement();
				}
				$oxml->endElement();

				echo "---- ".$code['pg']." -- ".$code['nr']." -- ".$code['s_type']." -- ".$code['code']." -- ".$code['color']."<br />\n";
				$sizes = $this->db_fetch_array("select * from otto_imp_t_prices where lang_id = ".aw_global_get("lang_id")." and pg = '".$orig_code['pg']."' and nr = '".$orig_code['nr']."' and s_type = '".$orig_code['s_type']."' order by pg,nr,s_type,id");

				$counter = 0; // packaging order counter

				$oxml->startElement('packagings');

				foreach ($sizes as $size)
				{
					echo "-------- ".$size['pg']." -- ". $size['nr'] ." -- ".$size['s_type']." -- ".$size['price'].".- -- ".$size['size']." -- ".$size['unit']."<br />\n";
					$tmp = explode(',', $size['size']);
					foreach ($tmp as $packaging_order => $s)
					{
						$oxml->startElement('packaging');

						$oxml->writeElement('page', $size['pg']);

						$oxml->writeElement('nr', $size['nr']);

						$oxml->writeElement('order', ($counter * 10)); // packaging order counter

						$oxml->startElement('type');
						$oxml->writeCData($size['s_type']);
						$oxml->endElement();

						$oxml->writeElement('price', $size['price']);
						if (!empty($size['special_price']))
						{
							$oxml->writeElement('special_price', $size['special_price']);
						}
						
						$oxml->startElement('size');
						$oxml->writeCData($s);
						$oxml->endElement();

						$oxml->endElement();

						$counter++; // increase the packaging order counter

						echo "------------ ".$s."<br />\n";
					}
				}

				$oxml->endElement(); // packagings

				$oxml->endElement(); // product tag
			}
			$oxml->endElement(); // products tag

			$oxml->endElement(); // packet tag
		}

		$oxml->endElement(); // warehouse_data tag

		return $xml_file_path;
	}


	// for warehouse interface:
	public function get_warehouse_list()
	{
		return array(
			1 => array(
				'name' => t('OTTO Eesti ladu'),
				'info' => t('draiver')
			)
		);
	}

	public function get_pricelist_xml(){}
	public function get_prices_xml(){}
	public function get_dnotes_xml(){}
	public function get_amounts_xml($wh_id = null){}
	public function get_bills_xml($wh_id = null){}

	/**
		@attrib name=submit_add_cart nologin=1
		@comment I don't know if this method is still required
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

		// For bugs #250598, #263491
		//	Cancelled by bug #291652 -kaarel 16.12.2008
		//	And brought up again by bug #346805. Just a bit modified. -kaarel 16.06.2009
		// Discounts by site_id => sections
		$dc_siteid_sec = array(
			"276" => array(
				139072,		// Soodustooted /
				/*
				139076,		// Soodustooted / Naistele / Pluusid, s2rgid, topid
				139077,		// Soodustooted / Naistele / Jakid, kampsunid, pulloverid
				139078,		// Soodustooted / Naistele / Joped, mantlid
				139080,		// Soodustooted / Naistele / Pyksid
				139074,		// Soodustooted / Naistele / Seelikud, kleidid
				139075,		// Soodustooted / Naistele / Pesu, 88pesu, rannamood
				139082,		// Soodustooted / Naistele / Jalan6ud
				139085,		// Soodustooted / Meestele / Pluusid, s2rgid
				139089,		// Soodustooted / Meestele / Jakid, kampsunid
				139091,		// Soodustooted / Meestele / Pyksid
				139084,		// Soodustooted / Meestele / Pesu, 88pesu
				139094,		// Soodustooted / Lastele / R6ivad
				139095,		// Soodustooted / Lastele / Jalan6ud
				139097,		// Soodustooted / Aksessuaarid
				939720,		// Soodustooted / Kodusisustus / Voodipesu
				1158663,	// Soodustooted / Kodusisustus / K2ter2tikud
				139110,		// Soodustooted / Kodusisustus / Vannitoamatid
				689148,		// Soodustooted / Kodusisustus / Sulle abiks
				*/
			),
			"277" => array(
				186099
			),
		);
		$discount = 0;
		foreach($dc_siteid_sec as $site_id => $sections)
		{
			if(aw_ini_get("site_id") == $site_id)
			{
				foreach($sections as $section)
				{
					$ot = new object_tree(array(
						"class_id" => CL_MENU,
						"parent" => $section,
						"lang_id" => array(),
						"status" => array(),
					));
					$ids = $ot->ids();
					if(!in_array(aw_global_get("section"), $ids) && time() < mktime(0, 0, 0, 8, 1, 2009))
					{
						$discount = 25;
					}
				}
			}
		}
		if($discount > 0)
		{
			$item_obj = obj($arr["add_to_cart"]);
			$arr["new_price"] = (1 - $discount / 100) * $item_obj->prop("price");
		}

		// rewrite some vars that are hard to rewire in js and forward to shop order cart
		$vars = $arr;
		if ($arr["spid"])
		{
			$vars["order_data"] = array();
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["prod_id"] = $arr["prod_id"];
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["color"] = ($arr["order_data_color".$arr["spid"]] != "" ? $arr["order_data_color".$arr["spid"]] : "---");
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["size"] = $arr["size_name".$arr["spid"]];
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["url"] = $retval;
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["discount"] = $discount;

			$vars["add_to_cart"] = array();
			$vars["add_to_cart"][$arr["add_to_cart".$arr["spid"]]] = $arr["add_to_cart_count".$arr["spid"]];
		}
		else
		{
			$vars["order_data"] = array();
			$vars["order_data"][$arr["add_to_cart"]]["prod_id"] = $arr["prod_id"];
			$vars["order_data"][$arr["add_to_cart"]]["color"] = ($arr["order_data_color"] != "" ? $arr["order_data_color"] : "---");
			$vars["order_data"][$arr["add_to_cart"]]["size"] = $arr["size_name"];
			$vars["order_data"][$arr["add_to_cart"]]["new_price"] = $arr["new_price"];
			$vars["order_data"][$arr["add_to_cart"]]["url"] = $retval;
			$vars["order_data"][$arr["add_to_cart".$arr["spid"]]]["discount"] = $discount;

			$vars["add_to_cart"] = array();
			$vars["add_to_cart"][$arr["add_to_cart"]] = $arr["add_to_cart_count"];
		}
		$i = get_instance(CL_SHOP_ORDER_CART);
		$i->submit_add_cart($vars);

		return $retval;
	}
}

?>
