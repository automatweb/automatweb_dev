<?php
// $Header: /home/cvs/automatweb_dev/classes/menu.aw,v 2.9 2002/11/15 18:24:15 duke Exp $
// right now this class manages only the functios related to adding menu aliases
// to documents and tables. But I think that all functions dealing with a single
// menu should be moved here.

/*
	// stuff that goes into the objects table
	@default table=objects

	@property users_only type=checkbox field=meta method=serialize group=advanced
	@caption Ainult sisselogitud kasutajatele

	@property color type=colorpicker field=meta method=serialize group=advanced
	@caption Menüü värv

	@property icon type=icon field=icon group=advanced
	@caption Ikoon

	@property sort_by_name type=checkbox field=meta method=serialize group=advanced
	@caption Sorteeri nime järgi

	@property aip_filename type=textbox field=meta method=serialize group=advanced
	@caption Failinimi

	@property objtbl_conf type=objpicker clid=CL_OBJ_TABLE_CONF field=meta method=serialize group=advanced
	@caption Objektitabeli konff

	@property add_tree_conf type=objpicker clid=CL_ADD_TREE_CONF field=meta method=serialize group=advanced
	@caption Objekti lisamise puu konff

	@property cfgmanager type=objpicker clid=CL_CFGMANAGER field=meta method=serialize group=advanced
	@caption Konfiguratsioonihaldur
	
	@property show_lead type=checkbox field=meta method=serialize group=advanced
	@caption Näita ainult leadi (kasutusel Nädalas)
	
	@property grkeywords type=select size=10 multiple=1 field=meta method=serialize group=keywords
	@caption AW Märksõnad

	@property keywords type=textbox field=meta method=serialize group=keywords
	@caption META keywords

	@property description type=textbox field=meta method=serialize group=keywords
	@caption META description
	
	@property sections type=select multiple=1 field=meta size=20 method=serialize group=relations
	@caption Vennastamine

	@property img_timing type=textbox size=3 field=meta method=serialize group=presentation
	@caption Viivitus piltide vahel (sek.)

	@property img_act type=imgupload field=meta method=serialize group=presentation
	@caption Aktiivse menüü pilt

	@property menu_images type=array field=meta method=serialize getter=callback_get_menu_image group=presentation
	@caption Menüü pildid

	// and now stuff that goes into menu table
	@default table=menu

	@property sss type=select multiple=1 size=15 method=serialize group=relations
	@caption Menüüd, mille alt viimased dokumendid võetakse
	
	@property pers type=select multiple=1 size=15 method=serialize group=relations
	@caption Perioodid, mille alt dokumendid võetakse

	@property seealso type=select multiple=1 size=15 group=relations
	@caption Vali menüüd, mille all see menüü on "vaata lisaks" menüü

	@property seealso_refs type=hidden method=serialize field=meta table=objects group=relations

	@property seealso_order type=textbox group=relations size=3 table=objects field=meta method=serialize
	@caption Järjekorranumber (vaata lisaks)

	@property link type=textbox group=show
	@caption Menüü link

	@property type type=select group=advanced
	@caption Menüü tüüp
	
	@property admin_feature type=select group=advanced
	@caption Vali programm
	
	@property pclass type=select table=objects field=meta method=serialize group=advanced
	@caption Vali meetod
	
	@property pm_url_admin type=checkbox table=objects field=meta method=serialize group=advanced
	@caption Meetod viitab adminni

	@property pm_url_menus type=checkbox table=objects field=meta method=serialize group=advanced
	@caption Meetodi väljundi kuvamisel menüüde näitamine

	@property clickable type=checkbox group=advanced
	@caption Klikitav
	
	@property no_menus type=checkbox group=advanced
	@caption Ilma menüüdeta

	@property target type=checkbox group=general
	@caption Uues aknas

	@property mid type=checkbox group=advanced
	@caption Paremal
	
	@property width type=textbox size=5 group=advanced
	@caption Laius
	
	@property is_shop type=checkbox group=advanced
	@caption Pood
	
	@property shop_parallel type=checkbox group=advanced
	@caption Kaubad sbs (pood)
	
	@property shop_ignoregoto type=checkbox group=advanced
	@caption Ignoreeri järgmist (pood)

	@default group=show

	@property left_pane type=checkbox 
	@caption Vasak paan

	@property right_pane type=checkbox
	@caption Parem paan
	
	@property tpl_dir table=objects type=select field=meta method=serialize
	@caption Template set 
	
	@property tpl_edit type=select 
	@caption Template muutmiseks
	
	@property tpl_view type=select
	@caption Template näitamiseks (pikk)
	
	@property tpl_lead type=select
	@caption Template näitamiseks (lühike)
	
	@property hide_noact type=checkbox
	@caption Peida ära, kui dokumente pole
	
	@property ndocs type=textbox size=3
	@caption Mitu viimast dokumenti

	@classinfo relationmgr=yes
	@classinfo objtable=menu
	@classinfo objtable_index=id
*/
class menu extends aw_template
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
		static $i = 0;
		// each line consists of multiple elements
		// and this is where we create them
		if ($i < $this->cfg["num_menu_images"])
		{
			$tmp = array();
			$node = array();
			// do something
			$node["caption"] = "Pilt #" . ($i + 1);
			$node["items"] = array();

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

			$i++;
		}
		else
		{
			$node = false;
		};
		return $node;
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
		};
                return $retval;
	}

	////
	// !Displays the form for adding a new menu alias
	function change_alias($args = array())
	{
		extract($args);
		$this->read_template("change_alias.tpl");
		if ($id)
		{
			$obj = $this->get_object($id);
			$this->dequote($obj);
			$title = "Muuda menüü linki";
		}
		else
		{
			$obj = array();
			$title = "Lisa menüü link";
		};
		$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / $title");
		$dbo = get_instance("objects");
		$olist = $dbo->get_list();

		$this->vars(array(
			"menu" => $this->picker($obj["last"],$olist),
			"reforb" => $this->mk_reforb("submit_alias",array("id" => $id,"parent" => $parent, "return_url" => $return_url, "alias_to" => $alias_to)),
		));
		return $this->parse();
	}

	function submit_alias($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$target = $this->get_object($menu);
			$name = $target["name"];
			$comment = $target["comment"];
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"last" => $menu,
			));
		}
		else
		{
			$id = $this->create_menu_alias($args);
		};

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}
		return $this->mk_my_orb("change_alias",array("id" => $id,"return_url" => urlencode($return_url)));
	}

	function create_menu_alias($args = array())
	{
		extract($args);
		$target = $this->get_object($menu);
		$name = $target["name"];
		$comment = $target["comment"];
		$id = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"comment" => $comment,
			"class_id" => CL_MENU_ALIAS,
			"last" => $menu,
		));
		$this->add_alias($parent,$id);
		return $id;
	}

	
	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->mcaliases))
		{
			$this->mcaliases = $this->get_aliases(array(
				"oid" => $oid,
				"type" => CL_PSEUDO,
			));
		};
		$f = $this->mcaliases[$matches[3] - 1];
		if (!$f["target"])
		{
			return "";
		}
		$target = $f;
		return sprintf("<a href='".$this->cfg["baseurl"]."/%d'>%s</a>",$target["oid"],$target["name"]);
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

};
?>
