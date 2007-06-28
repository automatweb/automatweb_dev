<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_import.aw,v 1.48 2007/06/28 13:30:23 dragut Exp $
// otto_import.aw - Otto toodete import 
/*

@classinfo syslog_type=ST_OTTO_IMPORT relationmgr=yes no_status=1 no_comment=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property base_url type=textbox
@caption Toodete csv failide baasaadress

@property prod_folder type=relpicker reltype=RELTYPE_FOLDER
@caption Toodete kataloog

@property images_folder type=textbox
@caption Serveri kaust kuhu pildid salvestatakse

@property files_to_import type=text store=no
@caption Imporditavad failid

@property do_i type=checkbox ch_value=1
@caption Teosta import

@property do_pict_i type=checkbox ch_value=1
@caption Teosta piltide import

property restart_pict_i type=checkbox ch_value=1
caption Alusta piltide importi algusest

property restart_prod_i type=checkbox ch_value=1
caption Alusta toodete importi algusest

property just_update_prod_data type=checkbox ch_value=1
caption Uuenda ainult toote andmed

@property last_import_log type=text store=no
@caption Viimase impordi logi

@groupinfo imported_products caption="Imporditud tooted"

	@property imported_products_table type=table no_caption=1 group=imported_products
	@caption Imporditud toodete tabel

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

@groupinfo foldersa caption="Kataloogid / Kategooriad"

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

	groupinfo folders caption="Kataloogid (deprecated)" parent=foldersa

		property folders type=table store=no group=folders no_caption=1

		property inf_pages type=textarea rows=3 cols=40 group=folders field=meta method=serialize table=objects
		caption L&otilde;pmatus vaatega lehed

	groupinfo folderspri caption="Kataloogide m&auml;&auml;rangud (deprecated)" parent=foldersa

		property foldpri type=textarea rows=20 cols=20 table=objects field=meta method=serialize group=folderspri
		caption T&auml;htede prioriteedid

	groupinfo foldersnames caption="Kaustade nimed (deprecated)" parent=foldersa

		property foldernames type=table store=no group=foldersnames
		caption Kaustade nimed impordi jaoks

@groupinfo containers caption="Konteinerid"

	@property containers_toolbar type=toolbar group=containers no_caption=1
	@caption Konteinerite t&oouml;&ouml;riisariba

	@property containers_list type=table group=containers no_caption=1
	@caption Konteinerite nimekiri

	@property container_info type=table group=containers
	@caption Konteineri info

	@property container_rows type=table group=containers
	@caption Konteineri read

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

	@property del_prods_by_filename type=textbox field=meta method=serialize group=delete
	@caption Kustuta tooted vastavalt failikoodile

	@property del_prods_by_filename_info type=text store=no group=delete
	@caption Info

groupinfo post_import_fix caption="Parandused"

	property do_fixes type=checkbox ch_value=1 method=serialize field=meta group=post_import_fix
	caption Soorita parandus

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog


*/

class otto_import extends class_base
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
			case "last_import_log":
				if ($_GET['dragut']){
					$this->clean_up_otto_prod_img_table();
					die();
				}
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
					"caption" => t("T&uuml;hjenda soodustoodete tabel ( aktiivse keele alt: ".aw_global_get('lang_id')." )"),
					"url" => $this->mk_my_orb("clear_discount_products", array(
						"id" => $arr['obj_inst']->id(),
						"lang_id" => aw_global_get('lang_id')
					)),
				));

				$prop['value'] .= ' ### ';
				$prop['value'] .= html::href(array(
					"caption" => t("T&uuml;hjenda soodustoodete tabel ( olenemata keelest! )"),
					"url" => $this->mk_my_orb("clear_discount_products", array(
						"id" => $arr['obj_inst']->id(),
					)),
				));
				break;

			case "discount_products_count":
				$all_products_count = $this->db_fetch_field("select count(*) as count from bp_discount_products", "count");
				$products_count = $this->db_fetch_field("select count(*) as count from bp_discount_products where lang_id=".aw_global_get('lang_id'), "count");
			//	$prop['value'] = $products_count." / ".$all_products_count;
				$prop['value'] = 'Aktiivse keele all ('.aw_global_get('lang_id').'): <strong>'.$products_count.'</strong>';
				$prop['value'] .= '<br />';
				$prop['value'] .= 'K&otilde;ik kokku (olenemata keelest): <strong>'.$all_products_count.'</strong>';
				break;

			case "foldernames":
				$this->_foldernames($arr);
				break;
			case "del_prods_by_filename_info":
				$prop['value'] = t("Failikood moodustub failinimest j&auml;rgmiselt: faili nimi: EST.TT010, selle faili kood: T010. Toodete otsimisel arvestatakse aktiivse keele ja saidi id-ga.<br /> ");
				$prop['value'] .= t("Failikoodist v&otilde;ib kirjutada ka ainult alguse v&otilde;i l&otilde;pu, puuduvat osa t&auml;histab sel juhul '%' m&auml;rk <br />");
				$prop['value'] .= t("N&auml;iteks k&otilde;ik 'G'-ga algavad t&auml;hised oleks: 'G%'. K&otilde;ik 'H0' algusega oleks 'H0%'. '%' m&auml;rgi v&otilde;ib ka &auml;ra j&auml;tta, sel juhul otsitakse t&auml;pselt selle j&auml;rgi, mis tekstikastis on. ")."<br />";

				$prop['value'] .= t("Peale salvestamist kuvatakse teile tekstikastis olevale stringile vastavad lehed, mille alusel hakatakse tooteid kustutama. Muutes stringi tekstikastis ja uuesti salvastades, saate veenduda, et &otilde;igete t&auml;histe j&rgi hakatakse tooteid otsima.")."<br />";
				$prop['value'] .= t("Selleks, et tooted kustutataks, m&auml;rkige &auml;ra l&otilde;ppu tekkiv m&auml;rkeruut ja salvestage. Peale seda kustutakse k&auml;ik tooted ja sellega seonduvad hinnad/suurused, mille juures on vastavalt tekstikasti sisestatud otsingustringile vastav t&auml;his (lehe nimetus).")."<br />";
				$prop['value'] .= "<strong>".t("Tooted ja nendega seonduv info kustutatakse s&uuml;steemist l&otilde;plikult!!!")."</strong><br />";
			

				$tmp = $arr['obj_inst']->prop('del_prods_by_filename');
				$lang_id = $arr['obj_inst']->lang_id();
				if ( !empty( $tmp ) )
				{
					$prop['value'] .= t('Kustutan j&auml;rgnevate t&auml;histega failides olevad tooted: ').'<br />';
					
					$this->db_query("
						select
							distinct(user18) as pg
						from
							aw_shop_products
							left join objects on objects.brother_of = aw_shop_products.aw_oid
						where
							aw_shop_products.user18 like '$tmp' AND
							objects.lang_id = $lang_id AND 
							objects.status > 0
						
					");
					$show_confirm = false;
					while ($row = $this->db_next())
					{
						$show_confirm = true;
						$prop['value'] .= '- '.$row['pg'].' <br />';
					}
					if ($show_confirm)
					{
						$prop['value'] .= '<span style="color: red;">'.t('Kustuta?').'</span>';
						$prop['value'] .= html::checkbox(array(
							'name' => 'confirm_del_prods_by_filename',
							'value' => 1
						));
					}
				}
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

			case "del_prods_by_filename":

				if ($arr['request']['confirm_del_prods_by_filename'] == 1 && !empty($arr['request']['del_prods_by_filename']))
				{
					$this->_do_del_prods(array(), $arr['request']['del_prods_by_filename']);
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

	function callback_mod_reforb($arr)
	{
		if ( $_GET['container_id'] && ($arr['group'] == 'containers') )
		{
			$arr['container_id'] = (int)$_GET['container_id'];
		}
	}
	
	function callback_mod_retval($arr)
	{
		if ( isset($arr['request']['container_id']) )
		{
			$arr['args']['container_id'] = $arr['request']['container_id'];
		}
		
		////
		// category data filter
		if ( isset($arr['request']['data_filter']['aw_folder_id']) )
		{
			$arr['args']['filter_aw_folder_id'] = $arr['request']['data_filter']['aw_folder_id'];
		}
		if ( isset($arr['request']['data_filter']['category']) )
		{
			$arr['args']['filter_category'] = $arr['request']['data_filter']['category'];
		}

		////
		// bubble filter 
		if ( isset($arr['request']['bubble_filter']['category']) )
		{
			$arr['args']['bubble_filter']['category'] = $arr['request']['bubble_filter']['category'];
		}
		if ( isset($arr['request']['bubble_filter']['image_url']) )
		{
			$arr['args']['bubble_filter']['image_url'] = $arr['request']['bubble_filter']['image_url'];
		}
		if ( isset($arr['request']['bubble_filter']['title']) )
		{
			$arr['args']['bubble_filter']['title'] = $arr['request']['bubble_filter']['title'];
		}

		////
		// firm filter
		if ( isset($arr['request']['firm_filter']['category']) )
		{
			$arr['args']['firm_filter']['category'] = $arr['request']['firm_filter']['category'];
		}
		if ( isset($arr['request']['firm_filter']['image_url']) )
		{
			$arr['args']['firm_filter']['image_url'] = $arr['request']['firm_filter']['image_url'];
		}
		if ( isset($arr['request']['firm_filter']['title']) )
		{
			$arr['args']['firm_filter']['title'] = $arr['request']['firm_filter']['title'];
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
/*
			if ($arr['obj_inst']->prop("restart_pict_i"))
			{
				echo "[ Piltide import algusest ]<br>\n";
				$this->restart_pictures_import = true;
			}
*/
			$arr["obj_inst"]->set_prop("do_i", 0);
			$arr["obj_inst"]->set_prop("do_pict_i", 0);
		//	$arr["obj_inst"]->set_prop("restart_pict_i", 0);
			
			$this->do_prod_import($arr["obj_inst"]);
		}

	}

	function _get_imported_products_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'oid',
			'caption' => t('OID')
		));
		$t->define_field(array(
			'name' => 'data',
			'caption' => t('Andmed')
		));
		$t->define_field(array(
			'name' => 'images',
			'caption' => t('Pildid'),
		));

		$products = safe_array($_SESSION['otto_import_product_data']);

		foreach ($products as $prod_id)
		{
			$prod_obj = new object($prod_id);

			$data_str = '<strong>'.$prod_obj->name().'</strong><br />';
			$data_str .= $prod_obj->prop('userta2');
			$data_str .= '<br />Min hind: '.$prod_obj->prop('user14').'.- | Max hind: '.$prod_obj->prop('user15').'.-';
			$data_str .= '<br />Kategooriad: '.$prod_obj->prop('user11');
			$data_str .= '<br />Leht: '.$prod_obj->prop('user18');
			$data_str .= '<br />Tootekoodid: '.$prod_obj->prop('user6');

			$images = explode(',', $prod_obj->prop('user3'));
		
			$images_str = '';
			foreach ($images as $image)
			{
				$images_str .= html::img(array(
					'url' => aw_ini_get('baseurl').'/vv_product_images/'.$image{0}.'/'.$image{1}.'/'.$image.'_2.jpg',
					'width' => '100',
					'alt' => $image,
					'title' => $image,
				)).html::checkbox(array(
					'name' => 'images['.$prod_id.']['.$image.']',
					'value' => 1,
					'caption' => t('Kustuta?')
				)).'||';
			}


			$t->define_data(array(
				'oid' => html::href(array(
					'url' => $this->mk_my_orb('change', array(
						'id' => $prod_id,
						'return_url' => post_ru(),
					), CL_SHOP_PRODUCT),
					'caption' => $prod_id
				)),
				'data' => $data_str,
				'images' => $images_str
			));
		}
		
		return PROP_OK;
	}

	function _set_imported_products_table($arr)
	{
		foreach (safe_array($arr['request']['images']) as $prod_id => $images)
		{
			$prod = new object($prod_id);
			$existing_images = explode(',', $prod->prop('user3'));
			foreach ($images as $image => $nr)
			{
				$key = array_search($image, $existing_images);
				if ($key !== false)
				{
					unset($existing_images[$key]);
				}
			}
			$prod->set_prop('user3', implode(',', $existing_images));
			$prod->save();
		}
	}

	function _get_containers_toolbar($arr)
	{
		$containers = $arr['obj_inst']->meta('containers');
		$new_key = max( array_keys( $containers ) ) + 1;

		$toolbar = &$arr['prop']['vcl_inst'];
		$toolbar->add_button(array(
			"name" => "new",
			"tooltip" => t('Uus konteiner'),
			"url" => $this->mk_my_orb('change', array(
				'id' => $arr['obj_inst']->id(),
				'group' => 'containers',
				'container_id' => $new_key
			)),
			"img" => "new.gif",
		));
		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "list",
			"tooltip" => t('Konteinerite nimekiri'),
			"url" => $this->mk_my_orb('change', array(
				'id' => $arr['obj_inst']->id(),
				'group' => 'containers',
			)),
			"img" => "iother_promo_box.gif",
		));

		return PROP_OK;
	}

	function _get_containers_list($arr)
	{
		if ( isset($arr['request']['container_id']) )
		{
			return PROP_IGNORE;
		}

		$table = &$arr['prop']['vcl_inst'];
		$table->set_sortable(false);

		$table->define_field(array(
			'name' => 'id',
			'caption' => t('ID'),
			'align' => 'center',
			'width' => '10%'
		));
		$table->define_field(array(
			'name' => 'order',
			'caption' => t('J&auml;rjekord'),
			'align' => 'center',
			'width' => '10%'
		));
		$table->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi'),
			'align' => 'center'
		));
		$table->define_field(array(
			'name' => 'delete',
			'caption' => t('Kustuta'),
			'width' => '10%',
			'align' => 'center'
		));

		$saved_containers = $arr['obj_inst']->meta('containers');
		foreach (safe_array($saved_containers) as $container_key => $container) 
		{
			$table->define_data(array(
				'id' => $container_key." ",
				'order' => html::textbox(array(
					'name' => 'container_order['.$container_key.']',
					'value' => $container['order'],
					'size' => 5
				)),
				'name' => html::href(array(
					'url' => $this->mk_my_orb('change', array(
						'id' => $arr['obj_inst']->id(),
						'group' => 'containers',
						'container_id' => $container_key
					)),
					'caption' => $container['name']
				)),
				'delete' => html::checkbox(array(
					'name' => 'delete_container['.$container_key.']',
					'value' => 1
				)),
			));

		}
	}

	function _set_containers_list($arr)
	{
		if (!empty($arr['request']['container_id']))
		{
			return PROP_OK;
		}
		$containers = $arr['obj_inst']->meta('containers');

		$delete_containers = safe_array($arr['request']['delete_container']);
		$containers_order = safe_array($arr['request']['container_order']);
		foreach ( safe_array($containers) as $id => $container)
		{
			if (array_key_exists($id, $delete_containers))
			{
				unset($containers[$id]);
			}
			else
			{
				$containers[$id]['order'] = $containers_order[$id];
			}
			
		}
		$arr['obj_inst']->set_meta('containers', $containers);
		return PROP_OK;
	}

	function _get_container_info($arr)
	{
		if (!isset($arr['request']['container_id']))
		{
			return PROP_IGNORE;	
		}
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi/J&auml;rjekord')
		));
		$t->define_field(array(
			'name' => 'categories',
			'caption' => t('Kategooriad')
		));
		$t->define_field(array(
			'name' => 'all_categories',
			'caption' => t('N&auml;ita k&otilde;igis kategooriates'),
			'align' => 'center'
		));

		$container_id = (int)$arr['request']['container_id'];
		if (empty($container_id)) {}

		$saved_containers = $arr['obj_inst']->meta('containers');
		$name = $saved_containers[$container_id]['name'];
		$order = $saved_containers[$container_id]['order'];
		$categories = $saved_containers[$container_id]['categories'];
		$all_categories = $saved_containers[$container_id]['all_categories'];

		$t->define_data(array(
			'name' => t('Nimi: ').html::textbox(array(
				'name' => 'container[name]',
				'value' => $name,
				'size' => 20
			)).
			'<br />'
			.t('J&auml;rjekord: ').html::textbox(array(
				'name' => 'container[order]',
				'value' => $order,
				'size' => 20
			)).
			html::hidden(array(
				'name' => 'container[id]',
				'value' => $container_id
			)),
			'categories' => html::textarea(array(
				'name' => 'container[categories]',
				'value' => implode(',', $categories)
			)),
			'all_categories' => html::checkbox(array(
				'name' => 'container[all_categories]',
				'value' => 1,
				'checked' => ($all_categories) ? true : false
			))
		));

		return PROP_OK;
	}

	function _set_container_info($arr)
	{
		if ( !isset($arr['request']['container']) )
		{
			return PROP_OK;
		}

		$saved_containers = $arr['obj_inst']->meta('containers');
		$containers_lut = $arr['obj_inst']->meta('containers_lut');
		$data = $arr['request']['container'];

		$container = $saved_containers[$data['id']];
		$container['name'] = $data['name'];
		$container['categories'] = explode(',', $data['categories']);
		$container['all_categories'] = $data['all_categories'];
		$container['order'] = $data['order'];

		// clean up the container data from the lut
		foreach ( $containers_lut['by_cat'] as $key => $value )
		{
			if ( array_key_exists($data['id'], $value) || !empty($container['all_categories']) )
			{
				unset($containers_lut['by_cat'][$key][$data['id']]);
				if ( empty($containers_lut['by_cat'][$key]) )
				{
					unset( $containers_lut['by_cat'][$key] );
				}
			}
		}
		unset($containers_lut['all_cat'][$data['id']]);

		// put back the container into the lut where needed
		if ( empty($container['all_categories']) )
		{
			foreach ( $container['categories'] as $cat )
			{
				$containers_lut['by_cat'][$cat][$data['id']] = $data['id'];
			}
		}
		else
		{
			$containers_lut['all_cat'][$data['id']] = $data['id'];
		}

		$valid_rows = array();
		foreach (safe_array($data['rows']) as $row)
		{
			if ( !isset($row['delete']) )
			{
				foreach ($row as $row_value)
				{
					if ( !empty($row_value) ) 
					{
						$valid_rows[] = $row;
						break;
					}
				}
			}
		}
		$container['rows'] = $valid_rows;

		$saved_containers[$data['id']] = $container;

		$arr['obj_inst']->set_meta('containers', $saved_containers);
		$arr['obj_inst']->set_meta('containers_lut', $containers_lut);
		return PROP_OK;
	}

	function _get_container_rows($arr)
	{
		$id = $arr['request']['container_id'];
		if (!isset($id))
		{
			return PROP_IGNORE;
		}

		$t  = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'img_url',
			'caption' => t('Pildi URL')
		));
		$t->define_field(array(
			'name' => 'link_text',
			'caption' => t('Lingi tekst')
		));
		$t->define_field(array(
			'name' => 'link_url',
			'caption' => t('Lingi URL')
		));
		$t->define_field(array(
			'name' => 'no_line_breaks',
			'caption' => t('Ilma reavahetusteta'),
			'align' => 'center'
		));
		$t->define_field(array(
			'name' => 'delete',
			'caption' => t('Kustuta'),
			'align' => 'center'
		));

		$saved_containers = $arr['obj_inst']->meta('containers');
		$container = $saved_containers[$id];

		$counter = 1;
		foreach (safe_array($container['rows']) as $row_key => $row_value)
		{
			$t->define_data(array(
				'img_url' => html::textbox(array(
					'name' => 'container[rows]['.$counter.'][img_url]',
					'value' => $row_value['img_url']
				)),
				'link_text' => html::textbox(array(
					'name' => 'container[rows]['.$counter.'][link_text]',
					'value' => $row_value['link_text']
				)),
				'link_url' => html::textbox(array(
					'name' => 'container[rows]['.$counter.'][link_url]',
					'value' => $row_value['link_url']
				)),
				'no_line_breaks' => html::checkbox(array(
					'name' => 'container[rows]['.$counter.'][no_line_breaks]',
					'value' => 1,
					'checked' => ($row_value['no_line_breaks']) ? true : false
				)),
				'delete' => html::checkbox(array(
					'name' => 'container[rows]['.$counter.'][delete]',
					'value' => 1,
				)),

			));
			$counter++;
		}

		$t->define_data(array(
			'img_url' => html::textbox(array(
				'name' => 'container[rows]['.$counter.'][img_url]',
			)),
			'link_text' => html::textbox(array(
				'name' => 'container[rows]['.$counter.'][link_text]',
			)),
			'link_url' => html::textbox(array(
				'name' => 'container[rows]['.$counter.'][link_url]',
			)),
			'no_line_breaks' => html::checkbox(array(
				'name' => 'container[rows]['.$counter.'][no_line_breaks]',
				'value' => 1
			)),
			'delete' => ''
		));

		return PROP_OK;
	}

	function get_product_codes($o)
	{
		$data = array();
		if (!is_object($o))
		{
			return array();
		}

		foreach(explode("\n", $o->prop("fnames")) as $fname)
		{
			if (trim($fname) == "" )
			{
				continue;
			}

			$fld_url = $o->prop("base_url")."/".trim($fname)."-2.xls";
			if (!$this->is_csv($fld_url))
			{
				continue;
			}
			echo "from url ".$fld_url." read: <br><br />\n";
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
		return $data;
	}

	/**

		@attrib name=pictimp

		@comment Otto pictures import

	**/
	function pictimp($arr,$fix_missing = false)
	{
		$GLOBALS["no_cache_clear"] = 1;
		$this->added_images = array();
		set_time_limit(0);
		$import_obj = $arr;

		echo "-----------[ start of picture import ]------------------<br>";
		flush();

		$data = $this->get_product_codes($arr);
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

		foreach ($data as $pcode) 
		{

			$pcode = str_replace(" ", "", $pcode);

			if ($pcode == "")
			{
				continue;
			}
			echo "process pcode $pcode <br>\n";
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

				$url = "http://www.otto.de/is-bin/INTERSHOP.enfinity/WFS/Otto-OttoDe-Site/de_DE/-/EUR/OV_ViewSearch-SearchStart;sid=mDuGagg9T0iHakspt6yqShOR_0e4OZ2Xs5qs8J39FNYYHvjet0FaQJmF?ls=0&Orengelet.sortPipelet.sortResultSetSize=15&SearchDetail=one&stype=N&Query_Text=".$pcode;

				echo "Loading <a href=\"$url\">page</a> content <br>\n";
				flush();
				$html = $this->file_get_contents($url);
				echo "Page content loaded, parsing ...<br>";
				flush();
				// image is http://image01.otto.de:80/pool/OttoDe/de_DE/images/formatb/[number].jpg

				if (strpos($html,"Leider konnten wir im gesamten OTTO") !== false)
				{ 
					// read from baur.de
					echo "Can't find an product for <b>$pcode</b> from otto.de, so searching from baur.de<br>\n";

					$this->read_img_from_baur(array(
						'pcode' => $pcode,
						'import_obj' => $import_obj
					));
				}
				else
				{
				//	echo "for product $pcode found multiple images! <br>\n";
				//	flush();

					$o_html = $html;


					preg_match_all("/<a id=\"silkhref\" href=\"Javascript:document\.location\.href='(.*)'\+urlParameter\"/imsU", $html, $mt, PREG_PATTERN_ORDER);
					$urld = array();
					// echo (dbg::dump($mt));
					foreach($mt[1] as $url)
					{
						$url = $url."&SearchDetail=one&stype=N&Orengelet.sortPipelet.sortResultSetSize=15&Orengelet.SimCategorize4OttoMsPipelet.Similarity_Parameter=&Orengelet.sortPipelet.sortCursorPosition=0&Query_Text=".$pcode;

						$urld[$url] = $url;
					}

					foreach($urld as $url)
					{
						echo "Searching pictures from <a href=\"$url\">url</a> <br>\n";
						$html = $this->file_get_contents($url);

						if (!preg_match_all("/Javascript:setImage\('(.*)\.jpg', '(\d+)'\)/imsU", $html, $mt, PREG_PATTERN_ORDER))
						{
							echo "-- setImage javascripti j2rgi pilti ei leitud<br>\n";
							if (!preg_match_all("/<img id=\"mainimage\" src=\"(.*)\.jpg\"/imsU", $html, $mt, PREG_PATTERN_ORDER))
							{
								echo "-- id=mainimage j2rgi pilti ei leitud .... <br>\n";
								// if we can't find image from the product view, then this should try to search the image from the other sites:
								echo "Let's try to search from the baur.de page: <br />\n";
								$this->read_img_from_baur(array(
									'pcode' => $pcode,
									'import_obj' => $import_obj
								));
								break;
							}
						}

						$f_imnr = NULL;


						// we need that connecting picture:
						$connection_image = '';
						$pattern = "/<img src=\"http:\/\/image01\.otto\.de:80\/pool\/formatd\/(.*)\.jpg\"/imsU";
						if (preg_match($pattern, $html, $matches ))
						{
							$connection_image = $matches[1];
							if (!empty($connection_image))
							{
								$image_ok = $this->download_image(array(
									'image' => 'http://image01.otto.de:80/pool/formatb/'.$connection_image.'.jpg',
									'format' => 2,
									'target_folder' => $import_obj->prop('images_folder')
								));
							}
						}

						// ach! if only single image then no js!!!
						if (count($mt[1]) == 0)
						{
							echo "ach! if only single image then no js!!!";

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
								if (strpos($img, 'leer.gif') !== false)
								{
								//	echo "-- $img <br>\n";
									echo "-- tundub, et sellele variandile pilti ei ole <br>\n";
									continue;
								}
								$imnr = basename($img, ".jpg");
								echo $imnr."<br>\n";
							//	$t_imnr = $this->get_image_from_product($pcode, $imnr);
								echo "image from product $pcode : ($t_imnr)<br />";
								$q = "SELECT pcode FROM otto_prod_img WHERE imnr = '$imnr' AND nr = '".$mt[2][$idx]."' AND pcode = '$pcode'";
								$t_imnr = $this->db_fetch_field($q, "pcode");

								if (!$f_imnr)
								{
									$f_imnr = $t_imnr.".jpg";
								}

								if (!$t_imnr)
								{
									echo "-- insert new image $imnr <br>\n";
									flush();

									$image_ok = $this->download_image(array(
										'image' => 'http://image01.otto.de:80/pool/formatb/'.$imnr.'.jpg',
										'format' => 2,
										'target_folder' => $import_obj->prop('images_folder')
									));
									if ($image_ok)
									{
										// download the big version of the image too:
										$this->download_image(array(
											'image' => 'http://image01.otto.de:80/pool/formata/'.$imnr.'.jpg',
											'format' => 1,
											'target_folder' => $import_obj->prop('images_folder')
										));

									//	$this->add_image_to_product($pcode, $imnr);
									}
									
									
									// here i probably have to download the image too
								
									$q = ("
										INSERT INTO 
											otto_prod_img(pcode, nr,imnr, server_id, conn_img) 
											values('$pcode','".$mt[2][$idx]."','$imnr', 1, '$connection_image')
									");
									//echo "q = $q <br>";
									$this->db_query($q);
									$this->added_images[] = $mt[2][$idx];
								}
								else
								{
									$this->db_query("
										update
											otto_prod_img
										set
											conn_img = '".$connection_image."'
										where
											imnr = '".$imnr."' and
											nr = '".$mt[2][$idx]."' and
											pcode = '".$pcode."'
									");
									echo "-- image $imnr for product $pcode is already in db<br />\n";
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
			'import_obj' => $arr['import_obj'],
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
						$this->not_found_products[$params['pcode']] = $params['pcode'];
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
						$this->not_found_products[$params['pcode']] = $params['pcode'];
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
		$import_obj = $arr['import_obj'];

		/*
			Poola bp saidist ei ole vaja pilte enam otsima minna, vaid need on juba olemas
			ja seosed piltide ja toodete vahel on defineeritud seosetabelis mille saab ftp-st.
		*/
		$f = file('/www/bp.ee.struktuur.ee/public/vv_bp_pl_img/linkage.txt');
		$f = array_unique($f);
		foreach ($f as $line)
		{
			$items = explode(';', $line);
			if ($items[0] == $pcode)
			{
				echo $pcode ." - ". $line."<br>\n";
				$mask = $items[2];
				$filename = basename($items[1], '.jpg');
				for ( $i = 0; $i < strlen($mask); $i++ )
				{
					if ($mask{$i} == 1)
					{ 
						$image_ok = $this->download_image(array(
							'image' => 'http://www.bonprix.ee/vv_bp_pl_img/'.$i.'/'.$filename.'_160.jpg',
							'format' => 2,
							'target_folder' => $import_obj->prop('images_folder'),
							'filename' => $filename.'_var'.$i
						));
						if ($image_ok)
						{
							// download the big version of the image too:
							$this->download_image(array(
								'image' => 'http://www.bonprix.ee/vv_bp_pl_img/'.$i.'/'.$filename.'_600.jpg',
								'format' => 1,
								'target_folder' => $import_obj->prop('images_folder'),
								'filename' => $filename.'_var'.$i
							));
						}
						$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '".$ilename."_var".$i."' AND nr = '$i' AND pcode = '$pcode'", "pcode");
						echo "---- Otsin baasist pilti [".$filename."_var".$i."] numbriga [$i] ja tootekoodiga [$pcode] <br>";
						if (!$imnr)
						{
							echo "------ image not found, insert new image $im <br>\n";
							flush();

							$q = ("
								INSERT INTO 
									otto_prod_img(pcode, nr,imnr, server_id, mod_time) 
									values('$pcode','$i','".$filename."_var".$i."', 7, $start_time)
								");
								//echo "q = $q <br>";
								$this->db_query($q);
								$this->added_images[] = $filename."_var".$i;
						}
						else
						{
							echo "------ found image, update mod_time to $start_time (".date("d.m.Y H:m:s", $start_time).")<br>\n";
							$this->db_query("UPDATE otto_prod_img SET mod_time=$start_time WHERE imnr = '$im' AND nr = '$num' AND pcode = '$pcode'");	
						}
					}
				}
				
			}
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
				$image_ok = $this->download_image(array(
					'image' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$first_im_var.'/'.$first_im_name.'.jpg',
					'format' => 2,
					'target_folder' => $import_obj->prop('images_folder'),
					'filename' => $first_im_name.'_var'.$first_im_var
				));
				if ($image_ok)
				{
					// download the big version of the image too:
					$this->download_image(array(
						'image' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$first_im_var.'/'.$first_im_name.'.jpg',
						'format' => 1,
						'target_folder' => $import_obj->prop('images_folder'),
						'filename' => $first_im_name.'_var'.$first_im_var
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
				$image_ok = $this->download_image(array(
					'image' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$nr.'/'.$r_i.'.jpg',
					'format' => 2,
					'target_folder' => $import_obj->prop('images_folder'),
					'filename' => $im
				));
				if ($image_ok)
				{
					// download the big version of the image too:
					$this->download_image(array(
						'image' => 'http://image01.otto.de/bonprixbilder/shopposiklein/7er/gross/var'.$nr.'/'.$r_i.'.jpg',
						'format' => 1,
						'target_folder' => $import_obj->prop('images_folder'),
						'filename' => $im
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

	function do_prod_import($o)
	{
		$GLOBALS["no_cache_clear"] = 1;
		
		// unset the products list which was imported last time:
		unset($_SESSION['otto_import_product_data']);

		if ($this->doing_pict_i)
		{
			$this->pictimp($o);
		}

		$this->import_product_objects($o);

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

	// takes otto_import obj. instance as parameter
	function import_product_objects($o)
	{
		set_time_limit(0);
		$otto_import_lang_id = $o->lang_id();
		$not_found_products_by_page = array();

		echo "START UPDATE CSV DB<br>";

		echo "<b>[!!]</b> start reading data from csv files <b>[!!]</b><br>\n";

		$this->import_data_from_csv($o); // reading data from csv file into temporary db tables

		echo "<br><b>[!!]</b>  end reading data from the csv files <b>[!!]</b><br><br>\n";


		// This should clean up the otto_prod_to_code_lut table from the those product object ids which are deleted from the system:
		$this->clean_up_products_to_code_lut();


		// the new structure is like this:
		// product - the row from the first file, contains name, desc, pictures info
		// 	packaging - for every row in the third file, contains info about price, size, and colors
		//			and product codes with comma separated list

		// all products (previosuly packages) - first file content
		$products = $this->db_fetch_array("select * from otto_imp_t_prod where lang_id = ".aw_global_get('lang_id'));
		
		foreach ($products as $product)
		{
			$product_data = array();

			echo "<b>product: ".$product['title']."</b><br>\n";

			// niisiis, kuidas ma teada saan kas sellisele tootele on juba objekt olemas ...
			// ilmselt peab tootekoodide järgi vaatama, et kas selline tootekood on lut-is olemas

			// get all product codes and colors:
			$product_codes = array();
			$colors = array();
			$q = "select * from otto_imp_t_codes where pg='".$product['pg']."' and nr='".$product['nr']."' and lang_id = ".aw_global_get('lang_id');
			$product_codes_data = $this->db_fetch_array($q);
			foreach ($product_codes_data as $value)
			{
				$product_codes[$value['code']] = $value['code'];
				$colors[] = $value['color'];
				
				// add some information into otto_prod_img table (refactor: see tuleks liigutada piltide impordi juurde imo):
				echo "UPDATE otto_prod_img table, set page code and product number in file: pcode = [".$value['code']."] page = [".$product['pg']."] nr = [".$product['nr']."]<br />\n";
				$this->db_query("UPDATE otto_prod_img SET p_pg='".$product['pg']."', p_nr='".$product['nr']."' WHERE pcode='".$value['code']."'");

			}

			echo "product codes: ".implode(',', $product_codes)."<br>\n";
			echo "v&auml;rvid: ".implode(',', $colors)."<br />\n";
			flush();

			if (empty($product_codes)){
				echo "<strong>[FATAL ERROR]</strong> No product codes found for this product so skipping it.  Check if the CSV files are containing proper data.<br />\n";
				flush();
				continue;
			}

			// using product codes lets search for existing product objects:
			$existing_products = $this->db_fetch_array("
				select 
					objects.status as status,
					otto_prod_to_code_lut.product_code as product_code,
					otto_prod_to_code_lut.product_id as product_id
				from 
					otto_prod_to_code_lut 
					left join objects on (objects.oid = otto_prod_to_code_lut.product_id)
				where 
					product_code in (".implode(",", map("'%s'", $product_codes)).") and 
					objects.status > 0 and
					objects.lang_id = ".aw_global_get('lang_id')."
			");
			$product_object_ids = array();
			foreach ($existing_products as $existing_product)
			{
				$product_object_ids[] = $existing_product['product_id'];
			}


			// lets delete the rows from the otto_prod_to_code_lut, which are related to that product by product codes:
			if (!empty($product_object_ids))
			{
				// clean up the otto_prod_to_code_lut database table, which connects product codes to product object id-s
				$this->db_query("delete from otto_prod_to_code_lut where product_id in (".implode(',', $product_object_ids).")");

				$product_object_id = reset($product_object_ids);
				$product_obj = new object($product_object_id);
			}
			else
			{
				$product_obj = obj();
				$product_obj->set_class_id(CL_SHOP_PRODUCT);
				$product_obj->set_parent($o->prop("prod_folder"));

				$product_obj->set_meta("cfgform_id", 599);
				$product_obj->set_meta("object_type", 1040);
				$product_obj->set_prop("item_type", 593);

				$product_obj->save();
				echo "created new product <br>\n";
			}

			$product_obj->set_name($product["title"]);
			$product_obj->set_prop("userta2", $product["c"]);
			$product_obj->set_prop("user18", $product["pg"]);
			$product_obj->set_prop("user11", $product["extrafld"]);
			$product_obj->set_prop("user19", $product["nr"]);
			
			// user6 - tootekoodid, komadega eraldatult
			$product_obj->set_prop('user6', implode(',', $product_codes));
			
			// user7 - värvid, komadega eraldatult
			$product_obj->set_prop('user7', implode(',', $colors));

		/*
			// deprecated
			$product_obj->set_prop("user16", $product["full_code"]);
			$product_obj->set_prop("user17", $product["color"]);
			$product_obj->set_prop("user20", $product["code"]);
		*/

			$product_obj->save();

			////
			// otto_prod_to_code_lut tabelisse tootekood <-> toote objekti id seosed:
			foreach ($product_codes as $product_code)
			{
				$this->db_query("
					insert into 
						otto_prod_to_code_lut 
					set 
						product_code = '".$product_code."',
						product_id = ".$product_obj->id().",
						color = '".$code_data['color']."'
				");

				// see siin on ajutine, selleks, et lingid vanadele objektidele t88le j22ks. 
				// paari kuu p2rast v6ib selle siit ilmselt 2ra koristada et ta vett ei segaks:
				// 22.03.2007 --dragut
				$this->db_query("
					update
						otto_tmp_obj_lut
					set
						new_oid = ".$product_obj->id()."
					where
						pcode = '".$product_code."' and
						lang_id = ".aw_global_get('lang_id')."
				");
			}

			////
			// categories
			////
			// ysnaag hakkab see asi siin siis nyyd niimoodi t88le, et on 1 tabel, kus on kirjas milliseid tooteid millise sektsiooni all n2idata on vaja
			// nothing more, nothing less
			// korjame need kategooriad toote juurest kokku k6igepealt:
			$categories = array($product['pg']);
			foreach (explode(',', $product['extrafld']) as $extrafld)
			{
				$categories[] = $extrafld;
			}
			$this->db_query("delete from otto_prod_to_section_lut where product=".$product_obj->id());
			$sections = $this->db_fetch_array("
				select 
					aw_folder
				from 
					otto_imp_t_aw_to_cat 
				where 
					category in (".implode(',', map("'%s'", $categories)).") and 
					lang_id = ".aw_global_get('lang_id')."
				group by 
					aw_folder
			");
			$product_sections = array();
			foreach ($sections as $section)
			{
				$this->db_query('insert into otto_prod_to_section_lut set 
					product='.$product_obj->id().', 
					section='.$section['aw_folder'].', 
					lang_id='.aw_global_get('lang_id').'
				');
				$product_sections[$section['aw_folder']] = $section['aw_folder'];
			}

			////
			// images
			////
			$images = $this->db_fetch_array("
				select 
					* 
				from 
					otto_prod_img 
				where 
					pcode in (".implode(',', map("'%s'", $product_codes)).") and 
					p_nr = '".$product['nr']."'
			");

			// here i need to check the flag, if the images should be updated on this object:

			if (!empty($images) && $product_obj->prop('userch2') != 1)
			{

				$images_arr = array();
				$connection_image = '';
				foreach ($images as $value)
				{
					$images_arr[$value['imnr']] = $value['imnr'];
					// siin v6ib ollanyyd see probleem, et vahel, m6nel tootel ei ole seda yhenduspilti
					// k6ikide tootekoodide kohta. ma arvan, et see on otto poolne black tegelt
					// nii et niikui leian selle yhenduspildi, siis aitab kyll:
					if (!empty($value['conn_img']) && empty($connection_image))
					{
						$connection_image = $value['conn_img'];	
					}
				}
				echo "selle toote koodidele leiti j&auml;rgmised pildid: ".implode(',', $images_arr)."<br />\n";
				$product_obj->set_prop('user3', implode(',', $images_arr));


				////
				// scanning which products should be visible via connection images and categories
				////
				// ysnaga, on vaja otsida teisi toote objekte nyyd, millel
				// oleks sama yhenduse pildi id ja millel oleks see n2itamise linnuke pysti
				if (!empty($connection_image))
				{
					echo "&uuml;hendav pilt: [".$connection_image."]<br />\n";
					$products_ol = new object_list(array(
						'class_id' => CL_SHOP_PRODUCT,
						'user2' => $connection_image,
						'status' => array(STAT_ACTIVE, STAT_NOTACTIVE),
					));
					
					$product_obj->set_prop('user2', $connection_image);
					if ($products_ol->count())
					{
						echo "leidsin veel objekte selle yhenduspildiga <br />\n";
					//	$products_ol->add($product_obj);

						$products_ol_ids = $products_ol->ids() + array($product_obj->id());
						$product_obj->set_prop('user4', implode(',', $product_ol_ids));
						$product_obj->set_prop('userch4', 1);
						echo "selle yhenduspildiga olevate toodete id-d: ".implode(',', $products_ol_ids)."<br />\n";

						$visible_product = false;
						foreach ($products_ol->arr() as $products_ol_item_id => $products_ol_item)
						{
							// nyyd on asi nii, et siin ma panen k6ik objektid listis mitte n2htavaks
							// n2htavaks objektiks selles pundis saab alati just 2sja imporditud toode

							// aga samal ajal ma peaks tsekkima ka sektsioone, et kui sektsioonid on erinevad
							// siis peaks ka toote n2htavaks panema.

							// ysnaga ma pean tegema query otto_prod_to_section_lut-i selle objekti id kohta
							$this->db_query("select section from otto_prod_to_section_lut where product = ".$products_ol_item_id);
							$product_ol_item_sections = array();
							while ($row = $this->db_next())
							{
								$product_ol_item_sections[$row['section']] = $row['section'];
							}
					/*
							arr('======================');
							arr($product_sections);
							arr('----------------------');
							arr($product_ol_item_sections);
							arr('======================');
					*/
							if (count($product_sections) > count($product_ol_item_sections))
							{
								$array_diff_res = array_diff($product_sections, $product_ol_item_sections);
							}
							else
							{
								$array_diff_res = array_diff($product_ol_item_sections, $product_sections);
							}
							if (!empty($array_diff_res))
							{
								echo "Seda toodet n&auml;idatakse erinevate sektsioonide all, nii, et m2rgin ka selle toote listis n2htavaks <br />\n";
								$products_ol_item->set_prop('userch4', 1);
							}
							else
							{
								$products_ol_item->set_prop('userch4', '');
							}

							$products_ol_item->set_prop('user4', implode(',', $products_ol_ids));
							$products_ol_item->save();
						}
						
					}
					else
					{
						echo "teisi selle yhenduspildiga tooteid ei leidnud, m&auml;rgin selle toote listis n&auml;idatavaks: [".$product_obj->id()."]<br />\n";
						$product_obj->set_prop('userch4', 1);
					}
				}
				else
				{
					echo "&uuml;hendav pilt puudub, m&auml;rgin selle toote listis n&auml;idatavaks [".$product_obj->id()."]<br />\n";
					$product_obj->set_prop('userch4', 1);
				}
			}

			$product_obj->save();


			////
			// prices/sizes
			////
			// get list of attached packaging objects
			$pkgs = array();
			$pak_sl = array();
			foreach($product_obj->connections_from(array("type" => "RELTYPE_PACKAGING")) as $c)
			{
				$t = $c->to();
				$pkgs[$t->prop('user6')][$t->prop("price")][$t->prop("user5")] = $t->id();
				$pak_sl[] = $t->id();
			}

			$found = array();

			$lowest = 10000000;

			// now, for each price, create packaging objects
			echo "---- [Iga hinna jaoks tekita packaging objekt]<br>\n";
/*
			echo "---- q = "."SELECT * FROM otto_imp_t_prices WHERE pg = '$product[pg]' AND nr = '$product[nr]' AND type IN ($typestr) <br>";
			$this->db_query("SELECT * FROM otto_imp_t_prices WHERE pg = '$product[pg]' AND nr = '$product[nr]' AND type IN ($typestr) ");
*/
		//	$this->db_query("SELECT * FROM otto_imp_t_prices WHERE pg = '$product[pg]' AND nr = '$product[nr]' ");
$this->db_query("
select 
	otto_imp_t_prices.pg as prices_pg,
	otto_imp_t_prices.nr as prices_nr,
	otto_imp_t_prices.type as prices_type,
	otto_imp_t_prices.size as size,
	otto_imp_t_prices.unit as unit,
	otto_imp_t_prices.price as price,
	otto_imp_t_prices.s_type as prices_s_type,
	
	otto_imp_t_codes.pg as codes_pg,
	otto_imp_t_codes.nr as codes_nr,
	otto_imp_t_codes.size as codes_size,
	otto_imp_t_codes.color as color,
	otto_imp_t_codes.code as code,
	otto_imp_t_codes.s_type as codes_s_type,
	otto_imp_t_codes.full_code as full_code
from 
	otto_imp_t_prices left join otto_imp_t_codes on (
		otto_imp_t_prices.nr = otto_imp_t_codes.nr and 
		otto_imp_t_prices.type = otto_imp_t_codes.size and 
		otto_imp_t_prices.pg = otto_imp_t_codes.pg
	)
where
	otto_imp_t_prices.pg = '".$product['pg']."' and
	otto_imp_t_codes.pg = '".$product['pg']."' and
	otto_imp_t_prices.nr = '".$product['nr']."' and
	otto_imp_t_codes.nr = '".$product['nr']."' and 
	otto_imp_t_prices.lang_id = ".aw_global_get('lang_id')."
order by prices_nr,code,price
");
			$rows = array();
			while ($row = $this->db_next())
			{
				echo "XXX ".$row['prices_nr']." - ".$row['code']." - ".$row['color']." ".$row['price']." - ".$row['size']." - ".$row['prices_s_type']."<br>\n";
				$rows[] = $row;
			}
// no kuskil siin tuleb sellest $code_lut-ist siis vajalikud värvif/tootekoodid välja võtta ja vastavate suuruste juurde kirja panna i guess
/*
			if (count($rows) == 0)
			{
				$tmp = $this->db_fetch_row("SELECT * FROM otto_imp_t_prices WHERE pg = '$product[pg]' AND nr = '$product[nr]' ");
				if ($tmp)
				{
					$rows[] = $tmp;
				}
			}
*/
			$sizes = false;
			$min_price = 0;
			$max_price = 0;
			foreach($rows as $row)
			{
				// gotta split the sizes and do one packaging for each
				$s_tmpc = explode(",", $row["size"]);
				//echo "got price row , s_tmpc = ".dbg::dump($s_tmpc)." <br>";
				$s_tmp = array();
				foreach($s_tmpc as $tmpcc)
				{
					// because the bloody csv files don't contain 100 106, that would mean 100,102,104,106, but they contain 100106
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
					$sizes = true;
					$row["size"] = $tmpcc;
					if (is_oid($pkgs[$row['code']][$row["price"]][$row["size"]]))
					{
						$pk = obj($pkgs[$row['code']][$row["price"]][$row["size"]]);
						echo "------ for prod ".$product_obj->name()." got (".$pk->id().") packaging ".$row["price"]." for type ".$product_code_data["s_type"]." <br>";
					}
					else
					{
						echo "------ for prod ".$product_obj->name()." got NEW packaging ".$row["price"]." for type ".$product_code_data["s_type"]." <br>";
						$pk = obj();
						$pk->set_class_id(CL_SHOP_PRODUCT_PACKAGING);
						$pk->set_parent($product_obj->id());
						$pk->save();

						$product_obj->connect(array(
							"to" => $pk->id(),
							"reltype" => 2 // RELTYPE_PACKAGING
						));
					}

					// i need to know min and max prices of the product:
					if ($max_price < $row['price'])
					{
						$max_price = $row['price'];
					}
					if ($min_price == 0 || $min_price > $row['price'])
					{
						$min_price = $row['price'];
					}

					$pk->set_parent($product_obj->id());
					$pk->set_prop("price", $row["price"]);
					$pk->set_prop("user5", $row["size"]);
					$pk->set_prop("user6", $row["code"]);
					$pk->set_prop("user7", $row["color"]);
					$pk->set_name($product_obj->name());
					$pk->save();

					$lowest = min($lowest, $row["price"]);

					$used[$pk->id()] = true;
					$first = false;
				}
			}
			$product_obj->set_prop('user14', $min_price);
			$product_obj->set_prop('user15', $max_price);
			$product_obj->save();
			foreach($pak_sl as $pak_sl_id)
			{
				if (!$used[$pak_sl_id] && $this->can('view', $pak_sl_id))
				{
					$product_obj->disconnect(array(
						"from" => $pak_sl_id
					));
					echo "disconnect from $pak_sl_id <br>";
				}
			}

			


			////
			// lets put the imported product id into the session, so i can show it after the import
			////
			$_SESSION['otto_import_product_data'][$product_obj->id()] = $product_obj->id();
		}

		echo "[!!] hear hear. prods done. Imporditi $items_done toodet [!!] <br>\n";


		////////////////
		// clear cache
		////////////////
		$cache = get_instance("cache");
 		$cache->file_clear_pt("menu_area_cache");
		$cache->file_clear_pt("storage_search");
		$cache->file_clear_pt("storage_object_data");
		$cache->file_clear_pt("html");
		$cache->file_clear_pt("acl");

		$fld = aw_ini_get("site_basedir")."/prod_cache";
		$cache->_get_cache_files($fld);
		echo 'about to delete '.count($cache->cache_files2).' files<br />';

		foreach(safe_array($cache->cache_files2) as $file)
		{
			unlink($file);
		}


/*
		// print out the conclusion
		if (!empty($not_found_products_by_page))
		{
			$discount_folders = $o->prop('discount_products_parents');
			if (!empty($discount_folders))
			{
				$this->db_query('
					select 
						product_code 
					from 
						otto_imp_t_prod_to_cat left join otto_imp_t_aw_to_cat on otto_imp_t_prod_to_cat.category = otto_imp_t_aw_to_cat.category 
					where 
						otto_imp_t_prod_to_cat.product_code in ('.implode(',', array_keys($this->not_found_products)).') and
						otto_imp_t_aw_to_cat.aw_folder in ('.$discount_folders.')
				');
				$cat_data = array();
				while ($row = $this->db_next())
				{
					$cat_data[] = $row['product_code'];
				}
			}
			if (!empty($cat_data))
			{
				
			}
		//	arr($cat_data);
			echo "<b>Kokkuv&otilde;te imporditud tootekoodidest, millele pilti ei &otilde;nnestunud leida</b><br />\n";
			foreach ($not_found_products_by_page as $page => $products)
			{
				echo "Page $page, products ".count($products)."<br />\n";
				foreach ($products as $product)
				{
					echo "-- Product ".$product[4]." / ".$product[5];
					if (array_search($product[4], $cat_data))
					{
						echo " [ soodus toode ]";
					}
					echo "<br />\n";
				}
			}
		}
*/
		die(t("all done! <br>"));
	}

	function import_data_from_csv($o)
	{
		$lang_id = aw_global_get('lang_id');
		$this->db_query("DELETE FROM otto_imp_t_prod WHERE lang_id=".$lang_id);
		$this->db_query("DELETE FROM otto_imp_t_codes WHERE lang_id=".$lang_id);
		$this->db_query("DELETE FROM otto_imp_t_prices WHERE lang_id=".$lang_id);

		$import_time = time();

		$fext = 'xls';

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
			flush();
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

				if (trim($row[2]) == "" && trim($row[1]) == "" && trim($row[3]) == "")
				{
					continue;
				}

				$this->quote(&$row);
				$row = $this->char_replacement($row);
				$row[2] = $this->conv($row[2]);

				$extrafld = trim($row[3]);
				$desc = $this->conv(trim($row[4]." ".$row[5]." ".$row[6]." ".$row[7]." ".$row[8]." ".$row[9]." ".$row[10]." ".$row[11]." ".$row[12]." ".$row[13]." ".$row[14]." ".$row[15]." ".$row[16]." ".$row[17]." ".$row[18]." ".$row[19]." ".$row[20]." ".$row[21]." ".$row[22]." ".$row[23]." ".$row[24]." ".$row[25]." ".$row[26]." ".$row[27]." ".$row[28]." ".$row[29]." ".$row[30]." ".$row[31]." ".$row[32]." ".$row[33]." ".$row[34]." ".$row[35]." ".$row[36]." ".$row[37]." ".$row[38]." ".$row[39]." ".$row[40]." ".$row[41]." ".$row[42]));

				$this->db_query("
					INSERT INTO otto_imp_t_prod(pg,nr,title,c,extrafld, lang_id)
					VALUES('$cur_pg','$row[1]','$row[2]','$desc','$extrafld', ".aw_global_get('lang_id').")
				");

				if ($row[2] == "")
				{
					echo "ERROR ON LINE $num title ".$row[2]." <br>";
					flush();
					$log[] = "VIGA real $num failis $fld_url nimi: ".$row[2];
				}
				$num++;

				echo "-- Lisasin toote numbriga [".$row[1]."], leht: [".$cur_pg."], extrafld/kategooria: [".$extrafld."],  nimi: [".$row[2]."]<br>\n";
				flush();

			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}

			echo "[ ...got $num titles from file $fld_url] <br><br>";
			flush();
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
			flush();
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
					INSERT INTO otto_imp_t_codes(pg,nr,size,color,code, full_code, set_f_img, lang_id)
					VALUES('$cur_pg','$row[1]','$row[2]','$color','$row[4]','$full_code', '$set_f_img', ".aw_global_get('lang_id').")
				");
				$num++;
				if (!$row[4])
				{
					echo "ERROR ON LINE $num code ".$row[4]." <br>";
					flush();
					$log[] = "VIGA real $num failis $fld_url kood: $row[4]";
				}

				// collect data for those product codes where no picture were found
				if (array_search($row[4], $this->not_found_products) !== false)
				{
					$not_found_products_by_page[$cur_pg][$row[4]] = $row;
					$prod_title = $this->db_fetch_field("select title from otto_imp_t_prod where pg='".$cur_pg."' and nr='".$row[1]."' and lang_id=".aw_global_get('lang_id'), "title");
					$not_found_products_by_page[$cur_pg][$row[4]][] = $prod_title;
					echo "X";
					
				}

				echo "-- Lisasin koodi numbriga $row[1], leht: [$cur_pg], tyyp: [$row[2]], v2rv: [$color], kood: [$row[4]], t2iskood: [$full_code], set_f_img: [$set_f_img]<br>\n";
				
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
			flush();
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
					INSERT INTO otto_imp_t_prices(pg,nr,type,size,unit,price, lang_id)
					VALUES('$cur_pg','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]', ".aw_global_get('lang_id').")
				");

				
				if (!$row[5])
				{
					echo "ERROR ON LINE $num price = $row[5] (orig = $orig)<br>".dbg::dump($orow);
					flush();
					$log[] = "VIGA real $num hind = $row[5]";

					for ($i = 0; $i < strlen($orig); $i++)
					{
						echo "at pos ".$i." cahar = ".ord($orig{$i})." v = ".$orig{$i}." <br>";
					}
				}
				$num++;

				echo "-- Lisasin hinna numbriga [$row[1]], leht: [$cur_pg], tyyp: [$row[2]], suurus: [$row[3]], yhik: [$row[4]], hind: [$row[5]]<br>\n";
				flush();
			}

			if ($tmpf)
			{
				@unlink($tmpf);
			}
			echo "[... got $num prices from file $fld_url ] <br>\n";
			$log[] = "lugesin failist $fld_url $num hinda";
			flush();
		}
		
	}

	function clean_up_products_to_code_lut()
	{
		$products = $this->db_fetch_array("
			SELECT
				otto_prod_to_code_lut.product_id as product_id
			FROM
				otto_prod_to_code_lut
				LEFT JOIN objects ON (objects.oid = otto_prod_to_code_lut.product_id)
			WHERE
				objects.status = 0 OR
				objects.status IS NULL
		");

		if (!empty($products))
		{
			$product_ids = array();
			foreach ($products as $value)
			{
				$product_ids[] = $value['product_id'];
			}
			$this->db_query("delete from otto_prod_to_code_lut where product_id in (".implode(',', $product_ids).")");
		}

		return true;
	}

	function clean_up_otto_prod_img_table()
	{
		$this->db_query("select * from otto_prod_img");
		$delete_images = array();
		while ($row = $this->db_next())
		{
			
			$imnr = $row['imnr'];
			$url = aw_ini_get('baseurl').'/vv_product_images/'.$imnr{0}.'/'.$imnr{1}.'/'.$imnr.'_2.jpg';
			$file = aw_ini_get('site_basedir').'/product_images/'.$imnr{0}.'/'.$imnr{1}.'/'.$imnr.'_2.jpg';
		//	if (getimagesize($url) !== false)
			if (is_readable($file))
			{
				echo "[".$imnr."] ".$file." [ok]<br />\n";
				flush();
			}
			else
			{
				echo "[".$imnr."] ".$file." [fail]<br />\n";
				$delete_images[$imnr] = $imnr;
				flush();
			}
		}
		arr($delete_images);
		arr(count($delete_images));
		if (!empty($delete_images))
		{
			$this->db_query("delete from otto_prod_img where imnr in (".implode(',', map("'%s'", $delete_images)).")");
		}
		echo "all done <br />\n";
		return true;
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
			chr(154), // shaa-enne oli 185
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

		// xxx debug by dragut
		if (false)
		{
			if (is_array($str))
			{
				$xxx = $str[2];
				for ($i = 0; $i < strlen($xxx); $i++)
				{
					arr('---'.$xxx{$i}.' ---- '.ord($xxx{$i}));
				}
			}
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

		// xxx debug by dragut
		if (false)
		{
			if (is_array($str))
			{
				$xxx = $str[2];
				for ($i = 0; $i < strlen($xxx); $i++)
				{
					arr('xxx'.$xxx{$i}.' ---- '.ord($xxx{$i}));
				}
			}
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

	// ??? --dragut
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
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'order',
			'caption' => t('J&auml;rjekord'),	
			'chgbgcolor' => 'line_color'
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
			'line_color' => 'lightblue'
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
			'caption' => t('Faili t&auml;ht'),
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'suffix',
			'caption' => t('Suffiks'),	
			'chgbgcolor' => 'line_color'
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
			'line_color' => 'lightblue'
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
			'align' => 'center',
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'aw_folder_id',
			'caption' => t('AW Kataloogi ID'),
			'align' => 'center',
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'categories',
			'caption' => t('Kategooriad'),
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'filter_button',
			'caption' => t('Filtreeri'),
			'chgbgcolor' => 'line_color'
		));
		
		$count = 1;
		$data = array();
		$aw_folder_ids = array();
		if (!empty($args['request']['filter_aw_folder_id']))
		{
			$aw_folder_ids[] = $args['request']['filter_aw_folder_id'];
		}
		if (!empty($args['request']['filter_category']))
		{
			$this->db_query("
				select
					aw_folder
				from
					otto_imp_t_aw_to_cat
				where 
					lang_id = ".aw_global_get('lang_id')." and
					category like '%".$args['request']['filter_category']."%' 
			");
			while ($row = $this->db_next())
			{
				$aw_folder_ids[] = $row['aw_folder'];
			}
		}

		if (!empty($aw_folder_ids))
		{
			$sql_params = " and aw_folder in (".implode(',', $aw_folder_ids).")";
		}

		$this->db_query("SELECT * FROM otto_imp_t_aw_to_cat WHERE lang_id=".aw_global_get('lang_id'). $sql_params);
		while ($row = $this->db_next())
		{
			if (!in_array($row['category'], $data[$row['aw_folder']]))
			{
				$data[$row['aw_folder']][] = $row['category'];
			}
		}

		$t->define_data(array(
			'jrk' => t('Filter'),
			'aw_folder_id' => html::textbox(array(
				'name' => 'data_filter[aw_folder_id]',
				'value' => (!empty($args['request']['filter_aw_folder_id'])) ? $args['request']['filter_aw_folder_id'] : '',
				'size' => '10'
			)),
			'categories' => html::textbox(array(
				'name' => 'data_filter[category]',
				'value' => (!empty($args['request']['filter_category'])) ? $args['request']['filter_category'] : '',
				'size' => '80',
			)),
			'filter_button' => html::submit(array(
				'name' => 'filter_categories',
				'value' => t('Filtreeri')
			)),
			'line_color' => 'green'

		));

		foreach ($data as $aw_folder => $categories)
		{

			$t->define_data(array(
				'jrk' => $count,
				'aw_folder_id' => html::textbox(array(
					'name' => 'data['.$aw_folder.'][aw_folder_id]',
					'value' => $aw_folder,
					'size' => '10'
				)),
				'categories' => html::textbox(array(
					'name' => 'data['.$aw_folder.'][categories]',
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
					'name' => 'new_data['.$count.'][aw_folder_id]',
					'value' => '',
					'size' => '10'
				)),
				'categories' => html::textbox(array(
					'name' => 'new_data['.$count.'][categories]',
					'value' => '',
					'size' => '80'
				)),
				'line_color' => 'lightblue'
			));
			$count++;
		}

		return PROP_OK;
	}

	function _set_categories($args)
	{
		if (!array_key_exists('filter_categories', $args['request']))
		{

			$aw_folder_ids = array_keys($args['request']['data']);

			if (!empty($aw_folder_ids))
			{
				$categories_by_section = array();
				$this->db_query('select * from otto_imp_t_aw_to_cat where lang_id = '.aw_global_get('lang_id').' and aw_folder in ('.implode(',', $aw_folder_ids).')');
				while ($row = $this->db_next())
				{
					$categories_by_section[$row['aw_folder']][] = $row['category'];
				}

				$this->db_query('delete from otto_imp_t_aw_to_cat where lang_id = '.aw_global_get('lang_id').' and aw_folder in ('.implode(',', $aw_folder_ids).')');
				foreach ($args['request']['data'] as $data)
				{
					$categories = explode(',', $data['categories']);
					$old_categories = $categories_by_section[$data['aw_folder_id']];
					// nyyd oleks vaja selline trikk teha, et kui kategooriate paigutus on muutunud, siis peaks
					// ka vastava kategooria tooted uute sektsioonide alla m22rama, v6i siis 2ra v6tta

					$added_categories = array_diff($categories, $old_categories);
					$deleted_categories = array_diff($old_categories, $categories);

					arr($added_categories);
					$tmp_added_categories = array();
					foreach ($added_categories as $key => $value)
					{
						if (!empty($value))
						{
							$tmp_added_categories[] = $value;
						}
					}
					$added_categories = $tmp_added_categories;

					if (!empty($added_categories))
					{
						echo "added categories <br /> \n";
						arr($added_categories);
						// mul on vaja k6iki nende kategooriatega tooteid

						$prod_ol = new object_list(array(
							'class_id' => CL_SHOP_PRODUCT,
							'user11' => $added_categories
						));
						$prod_ol_ids = $prod_ol->ids();
						if (!empty($prod_ol_ids))
						{
							// v6tame need tooted selle sektsiooni alt mis praegust aktiivne on
							$this->db_query("
								select 
									* 
								from 
									otto_prod_to_section_lut 
								where 
									section = ".$data['aw_folder_id']." and 
									product in (".implode(',', $prod_ol_ids).") and 
									lang_id = ".aw_global_get('lang_id')."
								");
							$tmp_prods = array();
							while ($row = $this->db_next())
							{
								$tmp_prods[$row['product']] = $row['section'];
							}
							arr($tmp_prods);
						}

						foreach ($prod_ol_ids as $prod_id)
						{
							if (!isset($tmp_prods[$prod_id]))
							{
								echo ">>> ".$prod_id." lisatakse sektsiooni ".$data['aw_folder_id']." alla <br /> \n";
								$this->db_query("
									insert into 
										otto_prod_to_section_lut 
									set
										product = ".$prod_id.",
										section = ".$data['aw_folder_id'].",
										lang_id = ".aw_global_get('lang_id')."
								");
							}
							else
							{
								echo "### ".$prod_id." n2idatakse juba sektsiooni ".$data['aw_folder_id']." all (ei tee midagi) <br />\n";
							}
						}
						
					}



					arr($deleted_categories);
					$tmp_deleted_categories = array();
					foreach ($deleted_categories as $key => $value)
					{
						if (!empty($value))
						{
							$tmp_deleted_categories[] = $value;
						}
					}
					$deleted_categories = $tmp_deleted_categories;

					if (!empty($deleted_categories))
					{
						echo "deleted categories <br /> \n";
						arr($deleted_categories);
						$prod_ol = new object_list(array(
							'class_id' => CL_SHOP_PRODUCT,
							'user11' => $deleted_categories
						));

						$prod_ol_ids = $prod_ol->ids();
						if (!empty($prod_ol_ids))
						{
							$this->db_query("
								select
									aw_oid, user11
								from
									aw_shop_products
								where
									aw_oid in (".implode(',', $prod_ol_ids).")
							");
							$prod_cats = array();
							while ($row = $this->db_next())
							{
								$prod_cats[$row['aw_oid']] = explode(',', $row['user11']);
							}

							foreach ($prod_ol_ids as $prod_id)
							{
								$tmp_arr_intersect = array_intersect($prod_cats[$prod_id], $categories);
								arr($tmp_arr_intersect);
								if (empty($tmp_arr_intersect))
								{
									echo "--- remove ".$prod_id." from section ".$data['aw_folder_id']." <br /> \n";
									$this->db_query("
										delete from
											otto_prod_to_section_lut 
										where 
											product = ".$prod_id." and 
											section = ".$data['aw_folder_id']." and
											lang_id = ".aw_global_get('lang_id')."
									");
								}
							}
						}
					}


					foreach ($categories as $category)
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
			}

			foreach ($args['request']['new_data'] as $data)
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
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'image_url',
			'caption' => t('Pildi aadress'),
			'chgbgcolor' => 'line_color'
		));

		$t->define_field(array(
			'name' => 'title',
			'caption' => t('Nimetus'),
			'chgbgcolor' => 'line_color'
		));

		$t->define_field(array(
			'name' => 'filter_button',
			'caption' => t('Filtreeri'),
			'chgbgcolor' => 'line_color'
		));

		$count = 0;
		$saved_data = $args['obj_inst']->meta('bubble_pictures');
		$show_data = array();

		$filter_bubble_category = $args['request']['bubble_filter']['category'];
		$filter_bubble_image_url = $args['request']['bubble_filter']['image_url'];
		$filter_bubble_title = $args['request']['bubble_filter']['title'];

		foreach (safe_array($saved_data) as $category => $data)
		{
			$show_data[$data['image_url']]['category'][] = $category;
			$show_data[$data['image_url']]['image_url'] = $data['image_url'];
			$show_data[$data['image_url']]['title'] = $data['title'];
		}

		$t->define_data(array(
			'category' => html::textbox(array(
				'name' => 'bubble_filter[category]',
				'value' => $filter_bubble_category
			)),
			'image_url' => html::textbox(array(
				'name' => 'bubble_filter[image_url]',
				'value' => $filter_bubble_image_url
			)),
			'title' => html::textbox(array(
				'name' => 'bubble_filter[title]',
				'value' => $filter_bubble_title
			)),
			'filter_button' => html::submit(array(
				'name' => 'filter_bubble_images',
				'value' => t('Filtreeri')
			)),
			'line_color' => 'green'
		));

		foreach ( $show_data as $data )
		{
			$add_line = false;
			$categories_str = implode(',', $data['category']);
			if (!empty($filter_bubble_category) || !empty($filter_bubble_image_url) || !empty($filter_bubble_title))
			{
				if (strpos($categories_str, $filter_bubble_category) !== false)
				{
					$add_line = true;
				}
				if (strpos($data['image_url'], $filter_bubble_image_url) !== false)
				{
					$add_line = true;
				}
				if (strpos($data['title'], $filter_bubble_title) !== false)
				{
					$add_line = true;
				}

			}
			else
			{
				$add_line = true;
			}

			if ($add_line)
			{

				$t->define_data(array(
					'category' => html::textbox(array(
						'name' => 'bubble_data['.$count.'][category]',
						'value' => $categories_str
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
			'line_color' => 'lightblue'
		));
		return PROP_OK;
	}

	function _set_bubble_pictures($args)
	{
		if (!array_key_exists('filter_bubble_images', $args['request']))
		{
			$valid_data = $args['obj_inst']->meta('bubble_pictures');
			foreach (safe_array($args['request']['bubble_data']) as $data)
			{
				if (!empty($data['category']) && !empty($data['image_url']))
				{
					$categories = explode(',', $data['category']);
					foreach ($categories as $category)
					{
						$valid_data[$category] = array(
							'image_url' => $data['image_url'], 
							'title' => $data['title']
						);
					}
				}
			}
			$args['obj_inst']->set_meta('bubble_pictures', $valid_data);
		}
			return PROP_OK;

	}

	function _get_firm_pictures($args)
	{
		$t = &$args['prop']['vcl_inst'];
		$t->set_sortable(false);		

		$t->define_field(array(
			'name' => 'category',
			'caption' => t('Kategooria'),
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'image_url',
			'caption' => t('Pildi aadress'),
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'title',
			'caption' => t('Nimetus'),
			'chgbgcolor' => 'line_color'
		));
		$t->define_field(array(
			'name' => 'filter_button',
			'caption' => t('Filtreeri'),
			'chgbgcolor' => 'line_color'
		));

		$count = 0;
		$saved_data = $args['obj_inst']->meta('firm_pictures');

		$filter_firm_category = $args['request']['firm_filter']['category'];
		$filter_firm_image_url = $args['request']['firm_filter']['image_url'];
		$filter_firm_title = $args['request']['firm_filter']['title'];

		$show_data = array();
		foreach (safe_array($saved_data) as $category => $data)
		{
			$key = (empty($data['image_url'])) ? $data['title'] : $data['image_url'];
			
			$show_data[$key]['category'][] = $category;
			$show_data[$key]['image_url'] = $data['image_url'];
			$show_data[$key]['title'] = $data['title'];
		}

		$t->define_data(array(
			'category' => html::textbox(array(
				'name' => 'firm_filter[category]',
				'value' => $filter_firm_category
			)),
			'image_url' => html::textbox(array(
				'name' => 'firm_filter[image_url]',
				'value' => $filter_firm_image_url
			)),
			'title' => html::textbox(array(
				'name' => 'firm_filter[title]',
				'value' => $filter_firm_title
			)),
			'filter_button' => html::submit(array(
				'name' => 'filter_firm_images',
				'value' => t('Filtreeri')
			)),
			'line_color' => 'green'
		));

		foreach ($show_data as $data)
		{
			$add_line = false;
			$categories_str = implode(',', $data['category']);
			if (!empty($filter_firm_category) || !empty($filter_firm_image_url) || !empty($filter_firm_title))
			{
				if (strpos($categories_str, $filter_firm_category) !== false)
				{
					$add_line = true;
				}
				if (strpos($data['image_url'], $filter_firm_image_url) !== false)
				{
					$add_line = true;
				}
				if (strpos($data['title'], $filter_firm_title) !== false)
				{
					$add_line = true;
				}

			}
			else
			{
				$add_line = true;
			}
			if ($add_line)
			{
				$t->define_data(array(
					'category' => html::textbox(array(
						'name' => 'firm_data['.$count.'][category]',
						'value' => $categories_str
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
		}

		for ($i = 0; $i < 5; $i++ )
		{	
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
				'line_color' => 'lightblue'
			));
			$count++;
		}
		return PROP_OK;
	}

	function _set_firm_pictures($args)
	{
		if (!array_key_exists('filter_firm_images', $args['request']))
		{
			$valid_data = $args['obj_inst']->meta('firm_pictures');
			foreach (safe_array($args['request']['firm_data']) as $data)
			{
				if (!empty($data['category']))
				{
					$categories = explode(',', $data['category']);
					foreach ($categories as $category)
					{
					//	$valid_data[$data['category']] = array(
						$valid_data[$category] = array(
							'image_url' => $data['image_url'],
							'title' => $data['title']
						);
					}
				}
			}
			$args['obj_inst']->set_meta('firm_pictures', $valid_data);
		}
		return PROP_OK;
	}

	function read_img_from_baur($arr)
	{
		$pcode = str_replace(" ", "", $arr['pcode']);
		$import_obj = $arr['import_obj'];

		$url = "http://www.baur.de/is-bin/INTERSHOP.enfinity/WFS/BaurDe/de_DE/-/EUR/BV_ParametricSearch-Progress;sid=9wziDKL5zmzox-N_94eyWWD0hj6lQBejDB2TPuW1?ls=0&_PipelineID=search_pipe_bbms&_QueryClass=MallSearch.V1&Servicelet.indexRetrieverPipelet.threshold=0.7&Orengelet.sortPipelet.sortResultSetSize=10&Query_Text=".$pcode."&Kategorie_Text=&x=23&y=13";

$url = "http://www.baur.de/is-bin/INTERSHOP.enfinity/WFS/Baur-BaurDe-Site/de_DE/-/EUR/BV_ParametricSearch-Progress;sid=9wziDKL5zmzox-N_94eyWWD0hj6lQBejDB2TPuW1?ls=0&_PipelineID=search_pipe_bbms&_QueryClass=MallSearch.V1&Servicelet.indexRetrieverPipelet.threshold=0.7&Orengelet.sortPipelet.sortResultSetSize=10&Query_Text=".$pcode."&Kategorie_Text=&x=23&y=13";
	//	arr($url);
		echo "[ BAUR ] Loading <a href=\"$url\">page</a> content ";
		$fc = $this->file_get_contents($url);
		echo " [ok]<br />\n";
//		if (strpos($fc, "leider keine Artikel gefunden") !== false)
		if ( (strpos($fc, "search/topcontent/noresult_slogan.gif") !== false) || (strpos($fc, "Entschuldigung,<br>diese Seite konnte nicht gefunden werden.") !== false) )
		{
			echo "[ BAUR ] Can't find a product for <b>$pcode</b> from baur.de, so searching from schwab<br>\n";
			return $this->read_img_from_schwab(array(
				'pcode' => $pcode,
				'import_obj' => $import_obj
			));
			
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
				$image_ok = $this->download_image(array(
					'image' => 'http://image01.otto.de/pool/BaurDe/de_DE/images/formatb/'.$pn.'.jpg',
					'format' => 2,
					'target_folder' => $import_obj->prop('images_folder')
				));
				if ($image_ok)
				{
					// download the big version of the image too:
					$this->download_image(array(
						'image' => 'http://image01.otto.de/pool/BaurDe/de_DE/images/formatb/'.$pn.'.jpg',
						'format' => 1,
						'target_folder' => $import_obj->prop('images_folder')
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

	/**

		@attrib name=swt

		@param pcode required

	**/
	function swt($arr)
	{
		return $this->pictimp(false,false);
	}

	function read_img_from_schwab($arr)
	{
		$pcode = $arr['pcode'];
		$import_obj = $arr['import_obj'];

		$url = "http://ww2.schwab.de/is-bin/INTERSHOP.enfinity/WFS/SchwabDe/de_DE/-/EUR/SV_ParametricSearch-Progress;sid=CUEKcPjDjXgLcLrISj06UONvQYLj_AIgPN2HQ_xO?_PipelineID=search_pipe_svms&_QueryClass=MallSearch.V1&ls=0&Orengelet.sortPipelet.sortCursorPosition=0&Orengelet.sortPipelet.sortResultSetSize=10&SearchDetail=one&Query_Text=".$pcode;
	//	arr($url);
		echo "[ SCHWAB ] Loading <a href=\"$url\">page</a> content ";
		$fc = $this->file_get_contents($url);
		echo "[ok]<br />\n";
		if (strpos($fc, "Sie suchen einen Artikel mit bestimmten Eigenschaften") !== false)
		{
			echo "[ SCHWAB ] can't find a product for <b>$pcode</b> from schwab.de, so searching from albamoda<br>\n";
			return $this->read_img_from_albamoda(array(
				'pcode' => $pcode,
				'import_obj' => $import_obj
			));
		}

		// match prod urls
		preg_match_all("/ProductRef=(.*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
		$pcs = array_unique($mt[1]);
	//	echo "[schwab] got pcs as ".dbg::dump($pcs)."\n";

		foreach($pcs as $prodref)
		{
			if ($prodref == "")
			{
				continue;
			}


//			$prod_url = "http://ww2.schwab.de/is-bin/INTERSHOP.enfinity/WFS/SchwabDe/de_DE/-/EUR/SV_DisplayProductInformation-ProductRef;sid=CUEKcPjDjXgLcLrISj06UONvQYLj_AIgPN2HQ_xO?ls=&ProductRef=".$prodref."&SearchDetail=1&aktPage=&Query_Text=371388&ArtikelID_Text=&Personen_Text=&PreisMin_Text=&PreisMax_Text=&Hersteller_Text=&Artikel_Text=&Stichwoerter_Text=&Artikel=&Hersteller=&Trend=";
			$prod_url = "http://ww2.schwab.de/is-bin/INTERSHOP.enfinity/WFS/Schwab-SchwabDe-Site/de_DE/-/EUR/SV_DisplayProductInformation-ProductRef;sid=Tk4bsAx5e3E7sEiXlcw0kBfV9c4JypIszkKxAg7F2xHxVkXqRnRWsaWbfmq0NJCohr5LSNgR?ProductRef=".$prodref."&ls=0&SearchDetail=1&SearchDetail=one&stype=&Orengelet.sortPipelet.sortResultSetSize=10&Orengelet.SimCategorize4OttoMsPipelet.Similarity_Parameter=&Orengelet.sortPipelet.sortCursorPosition=0&Query_Text=";
			echo "[ SCHWAB ] product <a href=\"$prod_url\">url</a>: <br />\n";
			$fc2 = $this->file_get_contents($prod_url);

			// get first image
			preg_match("/http:\/\/image01\.otto\.de:80\/pool\/formatb\/(\d+).jpg/imsU", $fc2, $mt);
			$first_im = $mt[1];

			$image_ok = $this->download_image(array(
				'image' => 'http://image01.otto.de/pool/formatb/'.$first_im.'.jpg',
				'format' => 2,
				'target_folder' => $import_obj->prop('images_folder')
			));
			if ($image_ok)
			{
				// download the big version of the image too:
				$this->download_image(array(
					'image' => 'http://image01.otto.de/pool/formata/'.$first_im.'.jpg',
					'format' => 1,
					'target_folder' => $import_obj->prop('images_folder')
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
					//echo "q = $q <br>";
					$this->db_query($q);
					$this->added_images[] = $im;
				}
			}
		}		
	}

	function read_img_from_albamoda($arr)
	{
		$pcode = $arr['pcode'];
		$import_obj = $arr['import_obj'];

		$url = "http://suche.albamoda.de/servlet/SearchServlet?clientId=AlbaModa-AlbaModaDe-Site&query=".$pcode."&resultsPerPage=120&category=&color=&manufacturer=&minPrice=&maxPrice=&prodDetailUrl=http%3A//www.albamoda.de/is-bin/INTERSHOP.enfinity/WFS/AlbaModa-AlbaModaDe-Site/de_DE/-/EUR/AM_ViewProduct-ProductRef%3Bsid%3DYxKYQ1BufUk5QxZUapu1Y0vC4a2r_3Im9-K6C8SemFURf8RYYg66C8SeC-oUEg%3D%3D%3Fls%3D%26ProductRef%3D%253CSKU%253E%2540AlbaModa-AlbaModaDe%26SearchBack%3D-1%26SearchDetail%3Dtrue";
	//	arr($url);
		echo "[ ALBAMODA ] Loading <a href=\"$url\">page</a> content ";
		$fc = $this->file_get_contents($url);
		echo "[ok]<br />\n";
		if (strpos($fc, "Es wurden leider keine Artikel") !== false)
		{
			echo "[ ALBAMODA ] can't find a product for <b>$pcode</b> from albamoda.de, so searching from heine<br>\n";
			return $this->read_img_from_heine(array(
				'pcode' => $pcode,
				'import_obj' => $import_obj
			));
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

			$image_ok = $this->download_image(array(
				'image' => 'http://image01.otto.de:80/pool/AlbaModaDe/de_DE/images/albamoda_formatb/'.$first_im.'.jpg',
				'format' => 2,
				'target_folder' => $import_obj->prop('images_folder')
			));
			if ($image_ok)
			{
				// download the big version of the image too:
				$this->download_image(array(
					'image' => 'http://image01.otto.de:80/pool/AlbaModaDe/de_DE/images/albamoda_formata//'.$first_im.'.jpg',
					'format' => 1,
					'target_folder' => $import_obj->prop('images_folder')
				));
			}

			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
			if (!$imnr)
			{
				echo "[ ALBAMODA ] insert new image $first_im <br>\n";
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

	function read_img_from_heine($arr)
	{
		$pcode = $arr['pcode'];
		$import_obj = $arr['import_obj'];

		// no spaces in product code ! --dragut
		$pcode = str_replace(" ", "", $pcode);
		$url = "http://search.heine.de/Heine/Search.ff?query=".$pcode;

		echo "[ HEINE ] Loading <a href=\"$url\">page</a> content ... ";
		$fc = $this->file_get_contents($url);
		echo "[ok]<br />\n";

		if (strpos($fc, "Diesen Artikel haben wir in unserem Online-Shop nicht gefunden.") !== false)
		{
			echo "[ HEINE ] Can't find product for code $pcode<br>";
			echo "NO IMAGE FOUND FOR PCODE $pcode <br>\n";
			$this->not_found_products[$pcode] = $pcode;
/*
			return $this->read_img_from_baur(array(
				'pcode' => $pcode,
				'import_obj' => $import_obj
			));
*/
			flush();
			return;
		}

	// xxx
		$fc2 = $fc;

		$patterns = array(
			"/bild\[bildZahl\+\+\]=\"(\d+).jpg\";/imsU",

		);

		// connection image ...
		$connection_image = '';
		if (preg_match("/ImageBundle = (\d+).jpg/", $fc, $mt))
		{
			$connection_image = $mt[1];
		}

		foreach ($patterns as $pattern)
		{
			if (preg_match($pattern, $fc2, $mt))
			{
				break;
			}
		}

		$first_im = $mt[1];

		$image_ok = $this->download_image(array(
			'image' => 'http://image01.otto.de/pool/format_hv_ds_b/'.$first_im.'.jpg',
			'format' => 2,
			'target_folder' => $import_obj->prop('images_folder')
		));
		if ($image_ok)
		{
			// download the big version of the image too:
			$this->download_image(array(
				'image' => 'http://image01.otto.de/pool/format_hv_ds_a/'.$first_im.'.jpg',
				'format' => 1,
				'target_folder' => $import_obj->prop('images_folder')
			));
		}

		$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
		if (!$imnr)
		{
			echo "[ HEINE ] insert new image $first_im <br>\n";
			flush();

			$q = ("
				INSERT INTO 
					otto_prod_img(pcode, nr,imnr, server_id, conn_img) 
					values('$pcode','1','$first_im', 5, '$connection_image')
			");

			$this->db_query($q);
			$this->added_images[] = $first_im;
		}
		else
		{
			$this->db_query("
				update
					otto_prod_img
				set
					conn_img = '".$connection_image."'
				where
					imnr = '".$first_im."' and
					pcode = '".$pcode."'
			");
			echo "[ HEINE ] image ". $first_im ." for product ". $pcode ." is already in database<br />\n";
		}

	// xxx

/*

		// get prods
		preg_match_all("/ProductRef=([^\"].*)&/imsU", $fc, $mt, PREG_PATTERN_ORDER);
		$pcs = array_unique($mt[1]);
		foreach($pcs as $prodref)
		{
			if ($prodref == "")
			{
				continue;
			}
			$prod_url = "http://www.neu.heine.de/is-bin/INTERSHOP.enfinity/WFS/HeineDe/de_DE/-/EUR/SH_ViewProduct-ProductRef;sid=YtPBfo9Zn47Dfs1V6VzvXpT13mqu32H0mc0eO27a?ProductRef=".$prodref."&Source=Search";
			echo "[ HEINE ] Searching images from this page: <a href=\"".$prod_url."\">prod url</a><br />\n";
			$fc2 = $this->file_get_contents($prod_url);
			if (strpos($fc2, "Ihrer Anforderung sind technische Probleme aufgetreten.") !== false)
			{
				$fc2 = $fc;
			}

			$patterns = array(
			//	"/http:\/\/image01\.otto\.de:80\/pool\/HeineDe\/de_DE\/images\/format_hv_ds_a\/(\d+).jpg/imsU",
			//	"/http:\/\/image01.otto.de\/pool\/images\/format_hv_ds_a\/(\d+).jpg/imsU",
			//	"/http:\/\/image01.otto.de:80\/pool\/images\/format_hv_ds_a\/(\d+).jpg/imsU",
				"/bild\[bildZahl\+\+\]=\"(\d+).jpg\";/imsU",

			);

			foreach ($patterns as $pattern)
			{
				if (preg_match($pattern, $fc2, $mt))
				{
					break;
				}
			}

			$first_im = $mt[1];

			$image_ok = $this->download_image(array(
				'image' => 'http://image01.otto.de/pool/format_hv_ds_b/'.$first_im.'.jpg',
				'format' => 2,
				'target_folder' => $import_obj->prop('images_folder')
			));
			if ($image_ok)
			{
				// download the big version of the image too:
				$this->download_image(array(
					'image' => 'http://image01.otto.de/pool/format_hv_ds_a/'.$first_im.'.jpg',
					'format' => 1,
					'target_folder' => $import_obj->prop('images_folder')
				));
			}

			$imnr = $this->db_fetch_field("SELECT pcode FROM otto_prod_img WHERE imnr = '$first_im' AND nr = '1' AND pcode = '$pcode'", "pcode");
			if (!$imnr)
			{
				echo "[ HEINE ] insert new image $first_im <br>\n";
				flush();

				$q = ("
					INSERT INTO 
						otto_prod_img(pcode, nr,imnr, server_id) 
						values('$pcode','1','$first_im', 5)
				");

				$this->db_query($q);
				$this->added_images[] = $first_im;
			}
			else
			{
				echo "[ HEINE ] image ". $first_im ." for product ". $pcode ." is already in database<br />\n";
			}


		}
*/

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

	function _do_del_prods($prods, $page_pattern = "")
	{
		set_time_limit(0);

		// lets convert this stuff to direct sql	
		$sql_params = "
			objects.status > 0 AND
			objects.site_id = ".aw_ini_get('site_id')." AND
			objects.lang_id = ".aw_global_get('lang_id')."
		";
		if (!empty($prods))
		{
			$product_codes_str = implode(',', map("'%s'", $prods ));
			$sql_params .= " AND user20 IN ($product_codes_str)";
		}
		if (!empty($page_pattern))
		{
			$sql_params .= " AND user18 LIKE '$page_pattern'";
		}
		
		$this->db_query("
			select 
				*
			from 
				aw_shop_products
				left join objects on objects.brother_of = aw_shop_products.aw_oid
			where
				$sql_params 
		");

	
		echo "Leidsin tooted: <br />\n";
		flush();
		$found_any_products = false;
		$product_ids = array();
		while ($row = $this->db_next())
		{
			$found_any_products = true;
			$product_ids[$row['oid']] = $row['oid'];
			echo $row['oid']." -- ".$row['name']." -- ".$row['user20']."<br />\n";
			flush();
		}

		if ($found_any_products === false)
		{
			echo "Tooteid ei leitud:<br>\n";
			arr($prods);
			arr($page_pattern);
			return;
		}
		
		$product_ids_str = implode(',', $product_ids);
		$this->db_query("
			select
				target 
			from
				aliases
			where
				source in($product_ids_str)
		");
		
		while ($row = $this->db_next())
		{
			$packaging_ids[$row['target']] = $row['target'];
		}
		$packaging_ids_str = implode(',', $packaging_ids);

		// now i will find the packets too
		$this->db_query("
			select
				objects.oid as id
			from
				aliases
				left join objects on objects.oid = aliases.source
			where
				objects.class_id = ".CL_SHOP_PACKET." AND
				aliases.reltype = 1 AND
				aliases.target IN ($product_ids_str)
		");
		while ($row = $this->db_next())
		{
			$packet_ids[$row['id']] = $row['id'];	
		}
		

		/**
			DELETING
				-- products (colors)
				-- packagings (prices/sizes)
		**/
		if (!empty($product_ids_str))
		{
			$this->db_query("delete from objects where oid in ($product_ids_str)");
			$this->db_query("delete from aw_shop_products where aw_oid in ($product_ids_str)");
			$this->db_query("delete from aliases where source in ($product_ids_str)");
			echo "Kustutasin <strong>".count($product_ids)."</strong> toodet (v&auml;rvid)<br />\n";
		}
		if (!empty($packaging_ids_str))
		{
			$this->db_query("delete from objects where oid in ($packaging_ids_str)");
			$this->db_query("delete from aw_shop_packaging where id in ($packaging_ids_str)");
			$this->db_query("delete from aliases where source in ($packaging_ids_str)");
			echo "Kustutasin <strong>".count($packaging_ids)."</strong> pakendit (suurused/hinnad)<br />\n";

		}

		/**
			PACKETS SCANNING:
		**/
		$packets_to_del = array();
		foreach (safe_array($packet_ids) as $packet_id)
		{
			$conns = $this->db_fetch_array("
				select
					*
				from
					aliases
				where 
					source = $packet_id AND
					reltype = 1
					
			");
			if (empty($conns))
			{
				$packets_to_del[$packet_id] = $packet_id;
			}
			
		}
		
		
		if (!empty($packets_to_del))
		{
			echo "Paketid mis l&auml;hevad kustutamisele: <br /> \n";
			arr($packets_to_del);
			$packets_to_del_str = implode(',', $packets_to_del);
			$this->db_query("delete from objects where oid in ($packets_to_del_str)");
			$this->db_query("delete from aw_shop_packets where aw_oid in ($packets_to_del_str)");
			$this->db_query("delete from aliases where source in ($packets_to_del_str)");
			echo "Kustutasin <strong>".count($packets_to_del_str)."</strong> paketti (tooteid (v&auml;rve) koondav obj)<br />\n";
	
		}
		flush();

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
				$prods_data = array();
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

					// ok, i'm sick of this thing, they always bounce this
					// thing to me when there is new price bigger than old price
					// so what the hell, lets solve this problem then!
					// if new price is bigger than old price, then switch them:
					$fields[5] = (int)$fields[5];
					$fields[6] = (int)$fields[6];
					if ($fields[5] < $fields[6])
					{
						$old_price = $fields[6];
						$new_price = $fields[5];
					}
					else
					{
						$old_price = $fields[5];
						$new_price = $fields[6];
					}

					$sql = "insert into bp_discount_products set ";
					$sql .= "prom='".trim($fields[0])."',";
					$sql .= "product_code='".trim($fields[1])."',";
					$sql .= "name='".trim($fields[2])."',";
					$sql .= "size='".trim($fields[3])."',";
					$sql .= "amount=".(int)$fields[4].",";
					$sql .= "old_price=".$old_price.",";
					$sql .= "new_price=".$new_price.",";
					$sql .= "category='".trim($fields[7])."',";
					$sql .= "lang_id=".aw_global_get('lang_id')." ;";

					$this->db_query($sql);

					if ((int)$fields[4] > 0)
					{
						$prods_data[$fields[1]] = array(
							'new_price' => (int)$fields[6],
							'amount' => (int)$fields[4]
						);
					}

				}
				$discount_products_parents = $object->prop('discount_products_parents');

				$visible_discount_products = array();
				$this->db_query("select product_id from otto_prod_to_code_lut where product_code in (".implode(',', map("'%s'", array_keys($prods_data))).")");
				while ($row = $this->db_next())
				{
					$visible_discount_products[$row['product_id']] = $row['product_id'];
				}

				$this->db_query("select product from otto_prod_to_section_lut where section in (".$discount_products_parents.") and lang_id = ".aw_global_get('lang_id')."");
				while ($row = $this->db_next())
				{
					if ($this->can('view', $row['product']))
					{
						$prod_obj = new object($row['product']);
						if (isset($visible_discount_products[$row['product']]))
						{
							if ($prod_obj->prop('userch3') == 1)
							{
								$prod_obj->set_prop('userch3', 0);
								$prod_obj->save();
							}
						}
						else
						{
							if ($prod_obj->prop('userch3') != 1)
							{
								$prod_obj->set_prop('userch3', 1);
								$prod_obj->save();
							}
						}
			
					}
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
		@param lang_id optional int
	**/
	function clear_discount_products($args)
	{
		$sql = "delete from bp_discount_products";
		if (!empty($args['lang_id']))
		{
			$sql .= ' where lang_id='.aw_global_get('lang_id');
		}
		$this->db_query($sql);

		return $this->mk_my_orb("change", array(
			"id" => $args['id'],
			"group" => "discount_products",
		));	
	}

	//
	// image - image url
	// format - images format (1 - for big image, 2 for thumbnail)
	// target_folder - server folder where to download images
	// filename - the new filename, if empty, then original filename is used provided by image parameter
	function download_image($arr)
	{
		echo "[ START DOWNLOADING IMAGE ]<br>\n";

		if (empty($arr['filename']))
		{
			$filename = basename($arr['image'], '.jpg');
		}
		else
		{
			$filename = $arr['filename'];
		}

		$folder = $arr['target_folder'].'/'.$filename{0};

		if (!is_dir($folder))
		{
			echo "-- creating directory ($folder) <br>\n";
			mkdir($folder);
		}
		$folder .= '/'.$filename{1};
		if (!is_dir($folder))
		{
			echo "-- creating directory ($folder) <br>\n";
			mkdir($folder);
		}

		// new image file name
		$new_filename = $folder.'/'.$filename.'_'.$arr['format'].'.jpg';
		if (file_exists($new_filename))
		{
			echo "[ END DOWNLOADING IMAGE -- pilt [ $new_filename ] juba olemas ]<br>\n";
			return true;
		}

		echo "-- reading image (".$arr['image'].") <br>\n";
		$f = fopen($arr['image'], 'rb');

		if ($f === false)
		{
			echo "[ END DOWNLOADING IMAGE -- pilti [ ".$arr['image']." ] ei suudetud lugeda ]<br>\n";
			return false;
		}

		while (!feof($f))
		{
			$content .= fread($f, 1024);
		}
		fclose($f);
		$filename = $folder."/".$filename."_".$arr["format"].".jpg";
		echo "-- writing image (".$filename.") <br>\n";
		if (chmod($filename, 0777) === true)
		{
			echo "-- &otilde;iguste muutmine failil ".$filename." &otilde;nnestus [ OK ] <br /> \n";
		}
		else
		{
			echo "-- &otilde;iguste muutmine failil ".$filename." eba&otilde;nnestus [ FAIL ] <br /> \n";
		}
		$f = fopen($filename, 'w');
		fwrite($f, $content);
		fclose($f);
		echo "[ END DOWNLOADING IMAGE -- pilt on alla laetud]<br />\n";
		return true;
	}

	function get_file_name($imnr)
	{
		return $imnr{0}.'/'.$imnr{1}.'/'.$imnr;
	}
	
	function add_image_to_product($pcode, $imnr)
	{
		// lets get the product obj:
		echo "[add image to product] <br>\n";
		$product_obj_id = $this->db_fetch_field("
			select
				*
			from
				otto_prod_to_code_lut
			where
				product_code = '".$pcode."'
		", "product_id");
		echo "-- got product obj id: $product_obj_id <br>\n";
		if ($this->can('view', $product_obj_id))
		{
			echo "-- product obj. is readable<br>\n";
			$product_obj = new object($product_obj_id);
			$images = explode(',', $product_obj->prop('user3'));
			$x = array_search($imnr, $images);
			if ($x !== false)
			{
				$images[$x] = $imnr;
			}
			else
			{
				$images[] = $imnr;
			}
			$product_obj->set_prop('user3', implode(',', $images));
			$product_obj->save();
			$this->db_query("delete from otto_prod_to_image_lut where product=".$product_obj->id()." and image='".$imnr."'");
			$this->db_query("insert into otto_prod_to_image_lut set product=".$product_obj->id().", image='".$imnr."'");
			echo "[/add image to product (saved) ]<br>\n";
			return true;
		}
		return false;
	}

	function get_image_from_product($pcode, $imnr)
	{
		echo "[ get_image_from_product_object]<br>\n";
		$product_obj_id = $this->db_fetch_field("
			select
				*
			from
				otto_prod_to_code_lut
			where
				product_code = '".$pcode."'
		", "product_id");
		echo "-- $product_obj_id <br>\n";
		if ($this->can('view', $product_obj_id))
		{
			echo "-- obj is readable <br>\n";
			$product_obj = new object($product_obj_id);
			echo "-- obj name is ".$product_obj->name()."<br>\n";
			$images = explode(',', $product_obj->prop('user3'));
			$x = array_search($imnr, $images);
			if ($x !== false)
			{
				echo "-- found image !!! <br>\n";
				echo "[/ get_image_from_product_object]<br>\n";
				return $images[$x];
			}
			else
			{
				echo "-- image not found !!! <br>\n";
				echo "[/ get_image_from_product_object]<br>\n";
				return false;
			}
		}
	}		

}

?>
