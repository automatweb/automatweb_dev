<?php
// $Header: /home/cvs/automatweb_dev/classes/menu.aw,v 2.27 2003/01/27 00:18:40 duke Exp $
// menu.aw - adding/editing/saving menus and related functions

/*
	// stuff that goes into the objects table
	@default table=objects

	@property users_only type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption Ainult sisselogitud kasutajatele

	@property color type=colorpicker field=meta method=serialize group=advanced
	@caption Menüü värv
	
	@property color2 type=colorpicker field=meta method=serialize group=advanced
	@caption Menüü värv 2

	@property icon type=icon field=meta group=advanced
	@caption Ikoon

	@property sort_by_name type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption Sorteeri nime järgi

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
	@caption Näita ainult leadi (kasutusel Nädalas)
	
	@property grkeywords type=select size=10 multiple=1 field=meta method=serialize group=keywords
	@caption AW Märksõnad

	@property keywords type=textbox field=meta method=serialize group=keywords
	@caption META keywords

	@property description type=textbox field=meta method=serialize group=keywords
	@caption META description
	
	@property sections type=select multiple=1 size=20 field=meta method=serialize group=relations 
	@caption Vennastamine

	@property img_timing type=textbox size=3 field=meta method=serialize group=presentation
	@caption Viivitus piltide vahel (sek.)

	@property img_act type=imgupload field=meta method=serialize group=presentation
	@caption Aktiivse menüü pilt

	@property menu_images type=generated field=meta method=serialize callback=callback_get_menu_image group=presentation
	@caption Menüü pildid

	// and now stuff that goes into menu table
	@default table=menu

	@property sss type=select multiple=1 size=15 table=objects field=meta method=serialize group=relations
	@caption Menüüd, mille alt viimased dokumendid võetakse
	
	@property pers type=select multiple=1 size=15 table=objects field=meta method=serialize group=relations
	@caption Perioodid, mille alt dokumendid võetakse

	@property seealso type=select multiple=1 size=15 group=relations
	@caption Vali menüüd, mille all see menüü on "vaata lisaks" menüü

	@property seealso_refs type=hidden method=serialize field=meta table=objects group=relations

	@property seealso_order type=textbox group=relations size=3 table=objects field=meta method=serialize
	@caption Järjekorranumber (vaata lisaks)

	@property link type=textbox group=show
	@caption Menüü link

	@property type type=select group=general
	@caption Menüü tüüp
	
	@property admin_feature type=select group=general
	@caption Vali programm
	
	@property pclass type=select table=objects field=meta method=serialize group=general
	@caption Vali meetod
	
	@property pm_url_admin type=checkbox table=objects field=meta method=serialize group=general ch_value=1
	@caption Meetod viitab adminni

	@property pm_url_menus type=checkbox table=objects field=meta method=serialize group=general ch_value=1
	@caption Meetodi väljundi kuvamisel menüüde näitamine

	@property clickable type=checkbox group=advanced ch_value=1
	@caption Klikitav
	
	@property no_menus type=checkbox group=advanced ch_value=1
	@caption Ilma menüüdeta
	
	@property target type=checkbox group=general ch_value=1 search=1
	@caption Uues aknas

	@property mid type=checkbox group=advanced ch_value=1
	@caption Paremal
	
	@property width type=textbox size=5 group=advanced
	@caption Laius
	
	@property is_shop type=checkbox group=advanced ch_value=1
	@caption Pood
	
	@property shop_parallel type=checkbox group=advanced ch_value=1
	@caption Kaubad sbs (pood)
	
	@property shop_ignoregoto type=checkbox group=advanced ch_value=1
	@caption Ignoreeri järgmist (pood)

	@default group=show

	@property left_pane type=checkbox  ch_value=1
	@caption Vasak paan

	@property right_pane type=checkbox ch_value=1
	@caption Parem paan
	
	@property tpl_dir table=objects type=select field=meta method=serialize
	@caption Template set 
	
	@property tpl_edit type=select 
	@caption Template dokumendi muutmiseks
	
	@property tpl_view type=select
	@caption Template dokumendi näitamiseks (pikk)
	
	@property tpl_lead type=select
	@caption Template dokumendi näitamiseks (lühike)

	@property tpl_edit_cfgform type=cfgform_picker clid=CL_DOCUMENT table=objects field=meta method=serialize
	@caption Konfivorm/template dokumendi muutmiseks
	
	@property hide_noact type=checkbox ch_value=1
	@caption Peida ära, kui dokumente pole
	
	@property ndocs type=textbox size=3 group=relations
	@caption Mitu viimast dokumenti

	@property export type=callback callback=callback_get_export_options group=import_export
	@caption Eksport

	@classinfo relationmgr=yes
	@classinfo objtable=menu
	@classinfo objtable_index=id
	@classinfo corefields=name,comment,alias,status,jrk

	@groupinfo general caption=Üldine default=1
	@groupinfo advanced caption=Spetsiaal
	@groupinfo keywords caption=Võtmesõnad
	@groupinfo relations caption=Seosed
	@groupinfo presentation caption=Presentatsioon
	@groupinfo show caption=Näitamine
	@groupinfo import_export caption=Import/Eksport submit=no

	@tableinfo menu index=id master_table=objects master_index=oid
*/
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
		$retval = true;
		switch($data["name"])
		{
			case "type":
				$m = get_instance("menuedit");
				$data["options"] = $m->get_type_sel();
				break;

			case "tpl_edit":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 0));
				break;
			
			case "tpl_lead":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 1));
				break;
			
			case "tpl_view":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 2));
				break;

			case "tpl_dir":
				$template_sets = $this->cfg["template_sets"];
				$data["options"] = array_merge(array("" => "kasuta parenti valikut"),$template_sets);
				break;
			
			case "sections":
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list(false,true);
				$m = get_instance("menuedit");
				$data["selected"] = $m->get_brothers($args["obj"]["oid"]);
				break;
			
			case "sss":
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list();
				break;

			case "pers":
				$dbp = get_instance("periods",$this->cfg["per_oid"]);
				$data["options"] = $dbp->period_list(false);
				break;

			case "grkeywords":
				$kwds = get_instance("keywords");
				$m = get_instance("menuedit");
				$data["options"] = $kwds->get_keyword_picker();
				$data["selected"] = $m->get_menu_keywords($args["obj"]["oid"]);
				break;

			case "seealso":
				// seealso asi on nyt nii. et esiteks on metadata[seealso_refs] - seal on
				// kirjas, mis menyyde all see menyy seealso item on
				// ja siis menu.seealso on nagu enne serializetud array menyydest mis
				// selle menyy all seealso on et n2itamisel kiirelt teada saax
				$sa = $args["obj"]["meta"]["seealso_refs"];
				$rsar = $sa[aw_global_get("lang_id")];
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list();
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
					if ($args[objdata]["admin_feature"])
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
				if ($args["objdata"]["type"] == MN_ADMIN1)
				{
					$m = get_instance("menuedit");
					$data["options"] = array_merge(array("0" => "--vali--"),$m->get_feature_sel());				
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;	

			case "pclass":
				if ($args["objdata"]["type"] == MN_PMETHOD)
				{
					$m = get_instance("menuedit");
					$data["options"] = $m->get_pmethod_sel();
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "pm_url_admin":
			case "pm_url_menus":
				if ($args["objdata"]["type"] != MN_PMETHOD)
				{
					$retval = PROP_IGNORE;
				}
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
			$tmp = array(
				"type" => "text",
				"value" => ($url) ? html::img(array("url" => $url)) : "",
			);
			array_push($node["items"],$tmp);

			$nodes[] = $node;
		};
		return $nodes;
	}

	function callback_get_export_options($args = array())
	{
		$submenus = $this->get_menu_list(false,false,$args["obj"]["oid"]);
		$tmp = array(
			"type" => "select",
			"multiple" => 1,
			"size" => 15,
			"name" => "ex_menus",
			"caption" => "Vali menüüd",
			"options" => $submenus,
			// this selects all choices
			"selected" => array_flip($submenus),
		);
		$nodes[] = $tmp;
		$tmp = array(
			"type" => "checkbox",
			"name" => "allactive",
			"value" => 1,
			"caption" => "Märgi kõik menüüd aktiivseks",
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

	function set_property($args = array())
	{	
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			// grkeywords just triggers an action, nothing should
			// be saved into the objects table
			case "grkeywords":
				$m = get_instance("menuedit");
				$m->save_menu_keywords($data["value"],$args["obj"]["oid"]);
				$retval = PROP_IGNORE;
				break;

			// seealso is not saved, it should be skipped.
			// update_seealso creates $this->seealso_refs (which is a hidden
			// element for simplicity) 
			case "seealso":
				$this->update_seealso(array(
					"id" => $args["obj"]["oid"],
					"meta" => $args["obj"]["meta"],
					"seealso" => $args["form_data"]["seealso"],
					"seealso_order" => $args["form_data"]["seealso_order"],
				));
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
				$this->update_brothers(array(
					"id" => $args["obj"]["oid"],
					"menu" => array_merge($args["obj"],$args["objdata"]),
					"sections" => $args["form_data"]["sections"],
				));

			case "type":
				$form_data = &$args["form_data"];
				if ($form_data["type"] != MN_ADMIN1)
				{
					$form_data["admin_feature"] = 0;
				};
				if ($form_data["type"] != MN_PMETHOD)
				{
					$form_data["pclass"] = "";
					$form_data["pm_url_admin"] = "";
					$form_data["pm_url_menus"] = "";
				};

			case "menu_images":
				$form_data = &$args["form_data"];
				// XXX: this should be rewritten to upload one image at a time
				if (!$this->menu_images_done)
				{
					$form_data["menu_images"] = $this->update_menu_images(array(
						"id" => $args["obj"]["oid"],
						"img_del" => $args["form_data"]["img_del"],
						"img_ord" => $args["form_data"]["img_ord"],
						"meta" => $args["obj"]["meta"],
					));
					$this->menu_images_done = 1;
				};
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
		// leiame koik selle menüü vennad
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
			reset($sections);
			$a = array();
			while (list(,$v) = each($sections))
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
		reset($sar);
		while (list($oid,) = each($sar))
		{
			if (!$a[$oid])
			{
				$deleted[$oid] = $oid;
			}
		}

		reset($deleted);
		while (list($oid,) = each($deleted))
		{
			$this->updmenus[] = $oid;
			$this->delete_object($oidar[$oid]);
		}
		reset($added);

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
			if ($imgar[$i]["id"])
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
		return ($a["ord"] > $b["ord"]) ? -1 : 1;
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
			$link = $this->mk_my_orb("right_frame",array("parent" => $args["id"]),"menuedit");
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
			$this->append_exp_arr($row,&$menus,$ex_icons,$i);
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
};
?>
