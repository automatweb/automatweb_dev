<?php
// $Header: /home/cvs/automatweb_dev/classes/menu.aw,v 2.51 2003/06/18 15:25:28 duke Exp $
// menu.aw - adding/editing/saving menus and related functions

/*
	// stuff that goes into the objects table
	@default table=objects

	//added by martin
	@property multi_doc_style type=checkbox value=1 ch_value=1 group=advanced field=meta method=serialize
	@caption Kasuta jumpboxi

	@property alias type=textbox group=general
	@caption Alias

	@property jrk type=textbox size=4 group=general
	@caption Jrk

	@property target type=checkbox group=general ch_value=1 search=1 table=menu
	@caption Uues aknas

	@property users_only type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption Ainult sisselogitud kasutajatele

	@property color type=colorpicker field=meta method=serialize group=advanced
	@caption Men�� v�rv
	
	@property color2 type=colorpicker field=meta method=serialize group=advanced
	@caption Men�� v�rv 2

	@property icon type=icon field=meta group=advanced
	@caption Ikoon

	@property sort_by_name type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption Sorteeri nime j�rgi

	@property aip_filename type=textbox field=meta method=serialize group=advanced
	@caption Failinimi
	
	@property periodic type=checkbox group=advanced ch_value=1
	@caption Perioodiline

	@property objtbl_conf type=objpicker clid=CL_OBJ_TABLE_CONF field=meta method=serialize group=advanced
	@caption Objektitabeli konf

	@property add_tree_conf type=objpicker clid=CL_ADD_TREE_CONF field=meta method=serialize group=advanced
	@caption Objekti lisamise puu konff

	@property cfgmanager type=objpicker clid=CL_CFGFORM subclass=CL_PSEUDO field=meta method=serialize group=advanced
	@caption Konfiguratsioonivorm
	
	@property show_lead type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption N�ita ainult leadi (kasutusel N�dalas)
	
	@property grkeywords type=select size=10 multiple=1 field=meta method=serialize group=keywords
	@caption AW M�rks�nad

	@property keywords type=textbox field=meta method=serialize group=keywords
	@caption META keywords

	@property description type=textbox field=meta method=serialize group=keywords
	@caption META description
	
	@property sections type=select multiple=1 size=20 field=meta method=serialize group=relations
	@caption Vennastamine

	@property images_from_menu type=relpicker reltype=RELTYPE_PICTURES_MENU group=presentation field=meta method=serialize
	@caption V&otilde;ta pildid men&uuml;&uuml; alt

	@property img_timing type=textbox size=3 field=meta method=serialize group=presentation
	@caption Viivitus piltide vahel (sek.)

	@property img_act type=imgupload field=meta method=serialize group=presentation
	@caption Aktiivse men�� pilt

	@property menu_images type=callback field=meta method=serialize callback=callback_get_menu_image group=presentation
	@caption Men�� pildid

	// and now stuff that goes into menu table
	@default table=menu

	@property sss type=select multiple=1 size=15 table=objects field=meta method=serialize group=relations
	@caption Men��d, mille alt viimased dokumendid v�etakse
	
	@property pers type=select multiple=1 size=15 table=objects field=meta method=serialize group=relations
	@caption Perioodid, mille alt dokumendid v�etakse
	
	@property all_pers type=checkbox ch_value=1 table=objects field=meta method=serialize group=relations
	@caption K&otilde;ikide perioodide alt
	
	@property docs_per_period type=textbox size=3 group=relations table=objects field=meta method=serialize
	@caption Dokumente perioodist

	@property seealso type=select multiple=1 size=15 group=relations
	@caption Vali men��d, mille all see men�� on "vaata lisaks" men��

	@property seealso_refs type=hidden method=serialize field=meta table=objects group=relations

	@property seealso_order type=textbox group=relations size=3 table=objects field=meta method=serialize
	@caption J�rjekorranumber (vaata lisaks)

	@property link type=textbox group=show
	@caption Men�� link

	@property type type=select group=general
	@caption Men�� t��p
	
	@property admin_feature type=select group=general table=menu field=admin_feature
	@caption Vali programm

	@property pmethod_properties type=callback callback=callback_get_pmethod_options group=general store=no
	@caption Avaliku meetodi seaded
	
	@property clickable type=checkbox group=advanced ch_value=1
	@caption Klikitav
	
	@property no_menus type=checkbox group=advanced ch_value=1
	@caption Ilma men��deta
	
	@property mid type=checkbox group=advanced ch_value=1
	@caption Paremal
	
	@property width type=textbox size=5 group=advanced
	@caption Laius
	
	@property is_shop type=checkbox group=advanced ch_value=1
	@caption Pood
	
	@property shop_parallel type=checkbox group=advanced ch_value=1
	@caption Kaubad sbs (pood)
	
	@property shop_ignoregoto type=checkbox group=advanced ch_value=1
	@caption Ignoreeri j�rgmist (pood)

	@property submenus_from_menu type=relpicker reltype=RELTYPE_SHOW_SUBFOLDERS_MENU group=advanced field=meta method=serialize table=objects
	@caption V&otilde;ta alammen&uuml;&uuml;d men&uuml;&uuml; alt

	@property show_layout type=relpicker reltype=RELTYPE_SHOW_AS_LAYOUT group=advanced field=meta method=serialize table=objects
	@caption Kasuta n&auml;itamiseks layouti

	@default group=show

	@property left_pane type=checkbox  ch_value=1 default=1
	@caption Vasak paan

	@property right_pane type=checkbox ch_value=1 default=1
	@caption Parem paan
	
	@property tpl_dir table=objects type=select field=meta method=serialize
	@caption Template set 
	
	@property tpl_view type=select
	@caption Template dokumendi n�itamiseks (pikk)
	
	@property tpl_lead type=select
	@caption Template dokumendi n�itamiseks (l�hike)

	@property hide_noact type=checkbox ch_value=1
	@caption Peida �ra, kui dokumente pole

	@property ndocs type=textbox size=3 group=relations
	@caption Mitu viimast dokumenti

	@property show_periods type=checkbox ch_value=1 group=show table=objects field=meta method=serialize
	@caption N�ita perioode

	@property show_period_count type=textbox size=4 group=show table=objects field=meta method=serialize
	@caption Mitu viimast perioodi

	@property export type=callback callback=callback_get_export_options group=import_export store=no
	@caption Eksport

	@classinfo relationmgr=yes
	@classinfo objtable=menu
	@classinfo objtable_index=id
	@classinfo corefields=name,comment,alias,status,jrk
	@classinfo syslog_type=ST_MENU

	@groupinfo general caption=�ldine default=1
	@groupinfo advanced caption=Spetsiaal
	@groupinfo keywords caption=V�tmes�nad
	@groupinfo relations caption=Seosed
	@groupinfo presentation caption=Presentatsioon
	@groupinfo show caption=N�itamine
	@groupinfo import_export caption=Eksport submit=no

	@tableinfo menu index=id master_table=objects master_index=oid
*/

define("RELTYPE_PICTURES_MENU",1);
define("RELTYPE_SHOW_SUBFOLDERS_MENU",2);
define("RELTYPE_SHOW_AS_CALENDAR",3);
define("RELTYPE_SHOW_AS_LAYOUT",4);

class menu extends class_base
{
	function menu($args = array())
	{
		$this->init(array(
			"tpldir" => "automatweb/menu",
			"clid" => CL_PSEUDO,
		));
	}
	
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "type":
				$m = get_instance("menuedit");
				$data["options"] = $m->get_type_sel();
				break;

			case "tpl_edit":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 0, "menu" => $args["obj"]["oid"]));
				break;
			
			case "tpl_lead":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 1, "menu" => $args["obj"]["oid"]));
				break;
			
			case "tpl_view":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 2, "menu" => $args["obj"]["oid"]));
				break;

			case "tpl_dir":
				$template_sets = $this->cfg["template_sets"];
				$data["options"] = array_merge(array("" => "kasuta parenti valikut"),$template_sets);
				break;
			
			case "sections":
				$data["options"] = $this->get_menu_list(false,true);
				$data["selected"] = $this->get_brothers($args["obj"]["oid"]);
				break;

			case "sss":
				$data["options"] = $this->get_menu_list();
				break;

			case "pers":
				$dbp = get_instance("period",$this->cfg["per_oid"]);
				$data["options"] = $dbp->period_list(false);
				break;

			case "grkeywords":
				$kwds = get_instance("keywords");
				$data["options"] = $kwds->get_keyword_picker();
				$data["selected"] = $this->get_menu_keywords($args["obj"]["oid"]);
				break;

			case "seealso":
				// seealso asi on nyt nii. et esiteks on metadata[seealso_refs] - seal on
				// kirjas, mis menyyde all see menyy seealso item on
				// ja siis menu.seealso on nagu enne serializetud array menyydest mis
				// selle menyy all seealso on et n2itamisel kiirelt teada saax
				$sa = $args["obj"]["meta"]["seealso_refs"];
				$rsar = $sa[aw_global_get("lang_id")];
				$data["options"] = $this->get_menu_list();
				$data["value"] = $rsar;
				break;

			case "icon":
				$ext = $this->cfg["ext"];
				if ($args["objdata"]["icon_id"])
				{
					$icon = "<img src='$baseurl" . "/automatweb/icon.$ext" . "?id=" . $args[objdata][icon_id] . "'>";
				}
				else
				{
					$m = get_instance("menuedit");
					if ($args["objdata"]["admin_feature"])
					{
						$icon = "<img src='" . $m->get_feature_icon_url($args["objdata"]["admin_feature"]) . "'>";
					}
					else
					{
						$icon = "(no icon set)";
					};
				};
				$data["value"] = $icon;
				break;

			case "img_act":
				$data["value"] = $args["obj"]["meta"]["img_act_url"] != "" ? "<img src='".$args[obj][meta][img_act_url]."'>" : "";
				break;

			case "admin_feature":
				// only show the program selector, if the menu has the correct type
				if ($args["objdata"]["type"] == MN_ADMIN1)
				{
					$data["options"] = $this->get_feature_sel();				
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;	

		};
		return $retval;
	}

	////
	// !Callback for the edit form, returns the data for 
	// menu images (of which there can be 1..n)
	function callback_get_menu_image($args = array())
	{
		classload("image","html");
		$data = $args["prop"];
		// each line consists of multiple elements
		// and this is where we create them
		$nodes = array();
		for ($i = 0; $i < $this->cfg["num_menu_images"]; $i++)
		{
			$node = array();
			// do something
			$node["caption"] = "Pilt #" . ($i+1);
			$node["table"] = "objects";
			$node["name"] = "menu_images";
			$node["field"] = "meta";
			$node["method"] = "serialize";
			$node["items"] = array();
			$node["group"] = "presentation";
			
			$val = $data["value"][$i];

			// ord textbox
			$tmp = array(
				"type" => "textbox",
				"name" => "img_ord[$i]",
				"size" => 3,
				"value" => $val["ord"],
			);
			array_push($node["items"],$tmp);

			// delete checkbox
			$tmp = array(
				"type" => "checkbox",
				"name" => "img_del[$i]",
				"ch_value" => 1,
			);
			array_push($node["items"],$tmp);

			// file upload
			$tmp = array(
				"type" => "fileupload",
				"name" => "img" . $i,
			);
			array_push($node["items"],$tmp);

			// image preview
			$url = image::check_url($val["url"]);
			if ($url)
			{
				$url =  html::img(array("url" => $url));
				$url .= " ( ".html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $val["id"]),"image"),
					"caption" => "Muuda"
				))." ) ";
			}

			$tmp = array(
				"type" => "text",
				"value" => $url,
			);
			array_push($node["items"],$tmp);

			$nodes[] = $node;
		};
		return $nodes;
	}

	function callback_get_export_options($args = array())
	{
		$submenus = $this->get_menu_list(false,false,$args["obj"]["oid"]);
		$nodes = array();
		$tmp = array(
			"type" => "select",
			"multiple" => 1,
			"size" => 15,
			"name" => "ex_menus",
			"caption" => "Vali men��d",
			"options" => $submenus,
			// this selects all choices
			"selected" => array_flip($submenus),
		);
		$nodes[] = $tmp;
		$tmp = array(
			"type" => "checkbox",
			"name" => "allactive",
			"value" => 1,
			"caption" => "M�rgi k�ik men��d aktiivseks",
		);
		$nodes[] = $tmp;
		$tmp = array(
			"type" => "checkbox",
			"name" => "ex_icons",
			"value" => 1,
			"caption" => "Ekspordi ikoonid",
		);
		$nodes[] = $tmp;
		$tmp = array(
			"type" => "submit",
			"value" => "Ekspordi",
			"name" => "do_export",
		);
		$nodes[] = $tmp;
		return $nodes;
	}

	function callback_get_pmethod_options($args = array())
	{
		if ($args["objdata"]["type"] != MN_PMETHOD)
		{
			return PROP_IGNORE;
		};

		$nodes = array();

		$nodes[] = array(
			"type" => "select",
			"name" => "pclass",
			"caption" => "Vali meetod",
			"options" => array(),
			"selected" => $args["obj"]["meta"]["pclass"],
			"options" => $this->get_pmethod_sel(),
		);
		
		$nodes[] = array(
			"type" => "checkbox",
			"name" => "pm_url_admin",
			"value" => 1,
			"caption" => "Meetod viitab adminni",
			"ch_value" => $args["obj"]["meta"]["pm_url_admin"],
		);
		
		$nodes[] = array(
			"type" => "checkbox",
			"name" => "pm_url_menus",
			"value" => 1,
			"caption" => "Meetodi v�ljundi kuvamisel n�idatakse men��sid",
			"ch_value" => $args["obj"]["meta"]["pm_url_menus"],
		);
		
		return $nodes;
	}
			
	function set_property($args = array())
	{	
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			// grkeywords just triggers an action, nothing should
			// be saved into the objects table
			case "grkeywords":
				if (!empty($args["obj"]["oid"]))
				{
					$this->save_menu_keywords($data["value"],$args["obj"]["oid"]);
				};
				$retval = PROP_IGNORE;
				break;

			// seealso is not saved, it should be skipped.
			// update_seealso creates $this->seealso_refs (which is a hidden
			// element for simplicity) 
			case "seealso":
				if (!empty($args["obj"]["oid"]))
				{
					$this->update_seealso(array(
						"id" => $args["obj"]["oid"],
						"meta" => $args["obj"]["meta"],
						"seealso" => $args["form_data"]["seealso"],
						"seealso_order" => $args["form_data"]["seealso_order"],
					));
				};
				$retval = PROP_IGNORE;
				break;

			case "icon":
				$retval = PROP_IGNORE;
				break;

			// this puts the result of update_seealso into the save queue
			case "seealso_refs":
				$form_data = &$args["form_data"];
				$form_data["seealso_refs"] = $this->seealso_refs;
				break;

			case "sections":
				if (!empty($args["obj"]["oid"]))
				{
					$this->update_brothers(array(
						"id" => $args["obj"]["oid"],
						"menu" => array_merge($args["obj"],$args["objdata"]),
						"sections" => $args["form_data"]["sections"],
					));
				};
				break;

			case "type":
				$form_data = &$args["form_data"];
				if ($form_data["type"] != MN_ADMIN1)
				{
					$form_data["admin_feature"] = 0;
				};
				if ($form_data["type"] != MN_PMETHOD)
				{
					$metadata = &$args["metadata"];
					$metadata["pclass"] = "";
					$metadata["pm_url_admin"] = "";
					$metadata["pm_url_menus"] = "";
				};
				break;

			case "menu_images":
				// XXX: this should be rewritten to upload one image at a time
				if (!$this->menu_images_done)
				{
					$args["metadata"]["menu_images"] = $this->update_menu_images(array(
						"id" => $args["obj"]["oid"],
						"img_del" => $args["form_data"]["img_del"],
						"img_ord" => $args["form_data"]["img_ord"],
						"meta" => $args["obj"]["meta"],
					));
					$this->menu_images_done = 1;
				};
				break;

			case "pmethod_properties":
				$form_data = &$args["form_data"];
				$metadata = &$args["metadata"];
				$metadata["pclass"] = $form_data["pclass"];
				$metadata["pm_url_menus"] = $form_data["pm_url_menus"];
				$metadata["pm_url_admin"] = $form_data["pm_url_admin"];
				break;
				
		};
                return $retval;
	}


	////
	// !Updates seealso references
	// see k2ib siis nii et objekti metadata juures on kirjas et mis menyyde all see menyy
	// ja siis menu.seealso on serializetud array nendest, mis selle menyy all on seealso
	// niisis. k2ime k6ik praegused menyyd l2bi kus see menyy seealso on ja kui see on 2ra
	// v6etud, siis kustutame ja kui jrk muutunud siis muudame seda ja viskame nad arrayst v2lja
	function update_seealso($args = array())
	{
		extract($args);

		if (!is_array($seealso))
		{
			$seealso = array();
		}

		$lang_id = aw_global_get("lang_id");

		if (is_array($meta["seealso_refs"][$lang_id]))
		{
			foreach ($meta["seealso_refs"][$lang_id] as $mid)
			{
				if (!in_array($mid,$seealso))
				{
					// remove this one from the menus seealso list
					$m_sa = $this->db_fetch_field("SELECT seealso FROM menu WHERE id = $mid", "seealso");
					$m_sa_a = unserialize($m_sa);
					unset($m_sa_a[$lang_id][$id]);
					$m_sa = serialize($m_sa_a);
					$this->db_query("UPDATE menu SET seealso = '$m_sa' WHERE id = $mid");
				}
				else
				{
					if ($seealso_order != $meta["seealso_order"])
					{
						// kui jrk on muutunud siis tuleb see 2ra muuta
						$m_sa = $this->db_fetch_field("SELECT seealso FROM menu WHERE id = $mid", "seealso");
						$m_sa_a = unserialize($m_sa);
						$m_sa_a[$lang_id][$id] = $seealso_order;
						$m_sa = serialize($m_sa_a);
						$this->db_query("UPDATE menu SET seealso = '$m_sa' WHERE id = $mid");
					}
				}
			}
		}

		// nyt k2ime l2bi sisestet seealso array ja lisame need mis pole metadatas olemas juba
		$sas = $meta["seealso_refs"];
		unset($sas[$lang_id]);
		foreach($seealso as $m_said)
		{
				if (!isset($meta["seealso_refs"][$lang_id][$m_said]))
				{
						// tuleb lisada selle menyy juurde kirje
						$m_sa = $this->db_fetch_field("SELECT seealso FROM menu WHERE id = $m_said", "seealso");
						$m_sa_a = unserialize($m_sa);
						$m_sa_a[$lang_id][$id] = $seealso_order;
						$m_sa = serialize($m_sa_a);
						$this->db_query("UPDATE menu SET seealso = '$m_sa' WHERE id = $m_said");
				}
				$sas[$lang_id][$m_said] = $m_said;
		}

		$this->seealso_refs = $sas;
	}

	////
	// !Updates brothers of this menu
	function update_brothers($args = array())
	{
		extract($args);
		$sar = array(); $oidar = array();
		// leiame koik selle men�� vennad
		$menu = $this->get_menu($id);
		$q = "SELECT * FROM objects
			 WHERE brother_of = $id AND status != 0 AND class_id = " . CL_BROTHER;
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$sar[$row["parent"]] = $row["parent"];
			$oidar[$row["parent"]] = $row["oid"];
		}

		$not_changed = array();
		$added = array();

		$this->updmenus = array();

		if (is_array($sections))
		{
			$a = array();
			//reset($sections);
			//while (list(,$v) = each($sections))
			foreach($sections as $v)
			{
				if ($sar[$v])
				{
					$not_changed[$v] = $v;
				}
				else
				{
					$added[$v] = $v;
				}
				$a[$v]=$v;
			}
		}
		$deleted = array();
		//reset($sar);
		//while (list($oid,) = each($sar))
		foreach($sar as $oid => $val)
		{
			if (!$a[$oid])
			{
				$deleted[$oid] = $oid;
			}
		}

		//reset($deleted);
		//while (list($oid,) = each($deleted))
		foreach($deleted as $oid => $val)
		{
			$this->updmenus[] = $oid;
			$this->delete_object($oidar[$oid]);
		}

		while(list($oid,) = each($added))
		{
			if ($oid != $id)        // no recursing , please
			{
				$noid = $this->new_object(array(
					"parent" => $oid,
					"class_id" => CL_BROTHER,
					"status" => 2,
					"brother_of" => $id,
					"name" => $menu["name"],
					"comment" => $menu["comment"],
					"jrk" => 50,
				));

				$this->db_query("INSERT INTO menu(id,link,type,is_l3,is_copied,
							periodic,tpl_edit,tpl_view,tpl_lead,active_period,
							clickable,target,mid,data,hide_noact)
				VALUES($noid,'$menu[link]','$menu[type]','$menu[is_l3]','$menu[is_copied]','$menu[periodic]','$menu[tpl_edit]','$menu[tpl_view]','$menu[tpl_lead]','$menu[active_period]','$menu[clickable]','$menu[target]','$menu[mid]','$menu[data]','$menu[hide_noact]')");
				$this->updmenus[] = $noid;
			}
		}

		// updmenus is used to invalidate the menu cache for the objects
		// that have changed. So I need to invalidate all the brothers
		// and the current menu as well
	}

	function update_menu_images($args = array())
	{
		extract($args);
		$num_menu_images = $this->cfg["num_menu_images"];
		$t = get_instance("image");

		$imgar = $meta["menu_images"];
		for ($i=0; $i < $num_menu_images; $i++)
		{
			if ($img_del[$i] == 1)
			{
				unset($imgar[$i]);
			}
			else
			{
				$ar = $t->add_upload_image("img".$i, $id, $imgar[$i]["id"]);
				$imgar[$i]["id"] = $ar["id"];
				$imgar[$i]["url"] = $ar["url"];
				$imgar[$i]["ord"] = $img_ord[$i];
			}
		}

		$timgar = array();
		$cnt = 0;
		for ($i=0; $i < $num_menu_images; $i++)
		{
			if ($imgar[$i]["id"] || $imgar[$i]["ord"])
			{
				$timgar[$cnt++] = $imgar[$i];
			}
		}

		// now sort the image array
		usort($timgar,array($this,"_menu_img_cmp"));
		return $timgar;
	}

	function _menu_img_cmp($a,$b)
	{
		if ($a["ord"] == $b["ord"]) return 0;
		return ($a["ord"] < $b["ord"]) ? -1 : 1;
	}


	function callback_post_save($args = array())
	{
		$this->updmenus[] = (int)$args["id"];
		$m = get_instance("menuedit");
		$m->invalidate_menu_cache($this->updmenus);
	}

	function callback_pre_save($args)
	{
		$form_data = &$args["form_data"];
		if ($form_data["do_export"])
		{
			$this->export_menus(array(
				"id" => $form_data["id"],
				"ex_menus" => $form_data["ex_menus"],
				"allactive" => $form_data["allactive"],
				"ex_icons" => $form_data["ex_icons"],
			));
		};
		/*
		if (!$args["object"]["type"])
		{
			$args["objdata"]["type"] = MN_CONTENT;
		}
		*/
	}

	function callback_gen_path($args = array())
	{
		if ($args["id"])
		{
			$obj = $this->get_object($args["id"]);
			$link = $this->mk_my_orb("right_frame",array("parent" => $args["id"]),"admin_menus");
			$title = html::href(array(
				"url" => $link,
				"caption" => $obj["name"],
			));
			$title .= " / Muuda";
		}
		else
		{
			$title = "Lisa";
		};
		return $title;
	}

	////
	// !exports menu $id and all below it
	// if $ret_data is true, then the export arr is returned, not output
	function export_menus($arr)
	{
		extract($arr);

		if (!is_array($ex_menus))
		{
			return;
		}

		$i = get_instance("icons");
		$this->m = get_instance("menuedit");
		$this->m->get_feature_icon_url(0);	// warm up the cache

		$menus = array("0" => $id);

		// ok. now we gotta figure out which menus the user wants to export. 
		// he can select just the lower menus and assume that the upper onec come along with them.
		// biyaatch 

		// kay. so we cache the menus
		$this->m->db_listall();
		while ($row = $this->m->db_next())
		{
			$this->mar[$row["oid"]] = $row;
		}

		// this keeps all the menus that will be selected
		$sels = array();	
		// now we start going through the selected menus
		reset($ex_menus);
		while (list(,$eid) = each($ex_menus))
		{
			// and for each we run to the top of the hierarchy and also select all menus 
			// so we will gather a list of all the menus we need. groovy.
			
			$sels[$eid] = $eid;
			while ($eid != $id && $eid > 0)
			{
				$sels[$eid] = $eid;
				$eid = $this->mar[$eid]["parent"];
			}
		}

		// so now we have a complete list of menus to fetch.
		// so fetchemall
		reset($sels);
		while (list(,$eid) = each($sels))
		{
			$row = $this->mar[$eid];
			if ($allactive)
			{
				$row["status"] = 2;
			}
			flush();
			$this->append_exp_arr($row,&$menus,$ex_icons,$i);
		}

		if ($ret_data)
		{
			return $menus;
		}

		/// now all menus are in the array with all the other stuff, 
		// so now export it.
		header("Content-type: x-automatweb/menu-export");
		header("Content-Disposition: filename=awmenus.txt");
		echo serialize($menus);
		die();
	}

	function append_exp_arr($db, $menus,$ex_icons,&$i)
	{
		$ret = array();
		$ret["db"] = $db;
		if ($ex_icons)
		{
			$icon = -1;
			// admin_feature icon takes precedence over menu's icon. so include just that.
			if ($db["admin_feature"] > 0)
			{
				$icon = $this->m->pr_icons[$db["admin_feature"]]["id"];
				if ($icon)
				{
					$icon = $i->get($icon);
				}
			}
			else
			if ($db["icon_id"] > 0)
			{
				$icon = $i->get($db["icon_id"]);
			}
			$ret["icon"] = $icon;
		}
		$menus[$db["parent"]][] = $ret;
	}

	////
	// !tagastab array adminni featuuridest, mida sobib ette s88ta aw_template->picker funxioonile
	function get_feature_sel()
	{
		$ret = array("0" => "--vali--");
		reset($this->cfg["programs"]);
		while (list($id,$v) = each($this->cfg["programs"]))
		{
			// only show stuff with names
			if ($v["name"])
			{
				$ret[$id] = $v["name"];
			};
		}
		return $ret;
	}

	////
	// !Tagastab nimekirja avalikest meetodidest. Arvatavasti tuleb see anyway ymber kirjutada,
	// sest kui neid meetodeid saab olema palju, siis on neid sitt selectist valida
	function get_pmethod_sel()
	{
		$aw_orb = get_instance("aw_orb");
		return array("0" => "--vali--") + $aw_orb->get_classes_by_interface(array("interface" => "public"));
	}

	function get_menu_keywords($id)
	{
		$ret = array();
		$id = (int)$id;
		$this->db_query("SELECT * FROM keyword2menu WHERE menu_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["keyword_id"]] = $row["keyword_id"];
		}
		return $ret;
        }

	function save_menu_keywords($keywords,$id)
	{
		$old_kwds = $this->get_menu_keywords($id);
		if (is_array($keywords))
		{
			// check if the kwywords have actually changed - if not, we souldn't do this, as this
			// can be quite time-consuming
			$update = false;
			foreach($keywords as $koid)
			{
				if ($old_kwds[$koid] != $koid)
				{
					$update = true;
				}
			}

			if (count($old_kwds) != count($keywords))
			{
				$update = true;
			}

			if (!$update)
			{
				return;
			}
		}
		else
		{
			if (count($old_kwds) < 1)
			{
				return;
			}
		}
		$this->db_query("DELETE FROM keyword2menu WHERE menu_id = $id");
	
		if (is_array($keywords))
		{
			$has_kwd_rels = 1;
			foreach($keywords as $koid)
			{
				$this->db_query("INSERT INTO keyword2menu (menu_id,keyword_id) VALUES('$id','$koid')");
			}
		}
		else
		{
			$has_kwd_rels = 0;
		};

		$this->upd_object(array(
			"oid" => $id,
			"metadata" => array("has_kwd_rels" => $has_kwd_rels),
		));
	}

	function get_brothers($id)
	{
		$bsar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER);
		while ($arow = $this->db_next())
		{
			$bsar[$arow["parent"]] = $arow["parent"];
		}
		return $bsar;
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_PICTURES_MENU => "v&otilde;ta pildid men&uuml;&uuml;lt",
			RELTYPE_SHOW_SUBFOLDERS_MENU => "v�ta alamkaustad men&uuml;&uuml;lt",
			RELTYPE_SHOW_AS_CALENDAR => "v�ta objekte kalendrist",
			RELTYPE_SHOW_AS_LAYOUT => "kasuta saidi n&auml;itamisel layouti"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args["reltype"])
                {
			case RELTYPE_PICTURES_MENU:
			case RELTYPE_SHOW_SUBFOLDERS_MENU:
				$retval = array(CL_PSEUDO);
				break;
			case RELTYPE_SHOW_AS_CALENDAR:
				$retval = array(CL_PLANNER);
				break;
			case RELTYPE_SHOW_AS_LAYOUT:
				$retval = array(CL_LAYOUT);
				break;
		};
		return $retval;
	}
};
?>
