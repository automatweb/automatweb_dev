<?php
// $Header: /home/cvs/automatweb_dev/classes/menu.aw,v 2.83 2002/12/24 15:24:44 kristo Exp $
// menu.aw - adding/editing/saving menus and related functions

/*
	// stuff that goes into the objects table
	@default table=objects

	@classinfo trans=1

	@property alias type=textbox group=general
	@caption Alias

	@property jrk type=textbox size=4 group=general
	@caption Jrk

	@property target type=checkbox group=general ch_value=1 search=1 table=menu
	@caption Uues aknas

	@property users_only type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption Ainult sisselogitud kasutajatele

	@property color type=colorpicker field=meta method=serialize group=advanced
	@caption Menüü värv
	
	@property color2 type=colorpicker field=meta method=serialize group=advanced
	@caption Menüü värv 2

	@property icon type=icon field=meta method=serialize group=advanced
	@caption Ikoon

	@property sort_by_name type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption Sorteeri nime järgi

	@property aip_filename type=textbox field=meta method=serialize group=advanced
	@caption Failinimi
	
	@property periodic type=checkbox group=advanced ch_value=1
	@caption Perioodiline

	@property objtbl_conf type=relpicker reltype=RELTYPE_OBJ_TABLE_CONF field=meta method=serialize group=advanced
	@caption Objektitabeli konf

	@property add_tree_conf type=relpicker reltype=RELTYPE_ADD_TREE_CONF field=meta method=serialize group=advanced
	@caption Objekti lisamise puu konff

	@property cfgmanager type=relpicker reltype=RELTYPE_CFGFORM field=meta method=serialize group=advanced
	@caption Konfiguratsioonivorm
	
	@property show_lead type=checkbox field=meta method=serialize group=advanced ch_value=1
	@caption Näita ainult leadi
	
	@property grkeywords type=select size=10 multiple=1 field=meta method=serialize group=keywords
	@caption AW Märksõnad

	@property keywords type=textbox field=meta method=serialize group=keywords
	@caption META keywords

	@property description type=textbox field=meta method=serialize group=keywords
	@caption META description
	
	@property sections type=table store=no group=brothers
	@caption Vennad

	@property images_from_menu type=relpicker reltype=RELTYPE_PICTURES_MENU group=presentation field=meta method=serialize
	@caption V&otilde;ta pildid men&uuml;&uuml; alt

	@property img_timing type=textbox size=3 field=meta method=serialize group=presentation
	@caption Viivitus piltide vahel (sek.)

	@property img_act type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize group=presentation
	@caption Aktiivse menüü pilt

	@property menu_images type=table field=meta method=serialize group=presentation store=no
	@caption Menüü pildid

	// and now stuff that goes into menu table
	@default table=menu

	@property sss type=table store=no group=docs_from
	@caption Menüüd, mille alt viimased dokumendid võetakse
	
	@property pers type=relpicker multiple=1 size=5 table=objects field=meta method=serialize group=docs_from reltype=RELTYPE_PERIOD
	@caption Perioodid, mille alt dokumendid võetakse
	
	@property all_pers type=checkbox ch_value=1 table=objects field=meta method=serialize group=docs_from
	@caption K&otilde;ikide perioodide alt
	
	@property docs_per_period type=textbox size=3 group=docs_from table=objects field=meta method=serialize
	@caption Dokumente perioodist

	@property seealso type=table group=relations store=no
	@caption Menüüd, mille all see menüü on "vaata lisaks" menüü
	@comment Nende menüüde lisamine ja eemaldamine käib läbi seostehalduri

	@property seealso_order type=textbox group=relations size=3 table=objects field=meta method=serialize
	@caption Järjekorranumber (vaata lisaks)

	@property link type=textbox group=show
	@caption Menüü link

	@property type type=select group=general table=menu field=type
	@caption Menüü tüüp
	
	@property admin_feature type=select group=general table=menu field=admin_feature
	@caption Vali programm

	@property pmethod_properties type=callback callback=callback_get_pmethod_options group=general store=no
	@caption Avaliku meetodi seaded
	
	@property clickable type=checkbox group=advanced ch_value=1 default=1
	@caption Klikitav
	
	@property no_export type=checkbox group=advanced ch_value=1 field=meta method=serialize table=objects
	@caption &Auml;ra n&auml;ita ekspordis
	
	@property no_menus type=checkbox group=advanced ch_value=1
	@caption Ilma menüüdeta
	
	@property mid type=checkbox group=advanced ch_value=1
	@caption Paremal
	
	@property width type=textbox size=5 group=advanced
	@caption Laius
	
	@property submenus_from_menu type=relpicker reltype=RELTYPE_SHOW_SUBFOLDERS_MENU group=advanced field=meta method=serialize table=objects
	@caption V&otilde;ta alammen&uuml;&uuml;d men&uuml;&uuml; alt

	@property show_layout type=relpicker reltype=RELTYPE_SHOW_AS_LAYOUT group=advanced field=meta method=serialize table=objects
	@caption Kasuta n&auml;itamiseks layouti

	@property show_object_tree type=relpicker reltype=RELTYPE_OBJ_TREE group=advanced field=meta method=serialize table=objects
	@caption Kasuta alammen&uuml;&uuml;de n&auml;itamiseks objektide nimekirja

	@default group=show

	@property left_pane type=checkbox  ch_value=1 default=1
	@caption Vasak paan

	@property right_pane type=checkbox ch_value=1 default=1
	@caption Parem paan
	
	@property tpl_dir table=objects type=select field=meta method=serialize
	@caption Template set 
	
	@property tpl_view type=select
	@caption Template dokumendi näitamiseks (pikk)
	
	@property tpl_lead type=select
	@caption Template dokumendi näitamiseks (lühike)

	@property hide_noact type=checkbox ch_value=1
	@caption Peida ära, kui dokumente pole

	@property ndocs type=textbox size=3 group=docs_from
	@caption Mitu viimast dokumenti

	@property show_periods type=checkbox ch_value=1 group=show table=objects field=meta method=serialize
	@caption Näita perioode

	@property show_period_count type=textbox size=4 group=show table=objects field=meta method=serialize
	@caption Mitu viimast perioodi

	@property export type=callback callback=callback_get_export_options group=import_export store=no
	@caption Eksport

	@property sort_by type=select table=objects field=meta method=serialize group=show
	@caption Dokumente j&auml;rjestatakse

	@property sort_ord type=select table=objects field=meta method=serialize group=show

	@property ip type=table store=no group=ip no_caption=1

	@classinfo relationmgr=yes
	@classinfo objtable=menu
	@classinfo objtable_index=id
	@classinfo syslog_type=ST_MENU

	@groupinfo general caption=Üldine default=1
	@groupinfo advanced caption=Spetsiaal
	@groupinfo keywords caption=Võtmesõnad
	@groupinfo menus caption=Kaustad
	@groupinfo relations caption="Vaata lisaks" parent=menus
	@groupinfo brothers caption=Vennastamine parent=menus
	@groupinfo docs_from caption="Sisu asukoht" parent=menus
	@groupinfo presentation caption=Pildid
	@groupinfo show caption=Näitamine
	@groupinfo import_export caption=Eksport submit=no
	@groupinfo ip caption="IP Aadressid"

	@tableinfo menu index=id master_table=objects master_index=oid

	@reltype PICTURES_MENU value=1 clid=CL_MENU
	@caption võta pildid menüült

	@reltype SHOW_SUBFOLDERS_MENU value=2 clid=CL_MENU
	@caption võta alamkasutad menüült

	@reltype SHOW_AS_CALENDAR value=3 clid=CL_PLANNER
	@caption võta objekte kalendrist

	@reltype SHOW_AS_LAYOUT value=4 clid=CL_LAYOUT
	@caption kasuta saidi näitamisel layouti

	@reltype SEEALSO value=5 clid=CL_MENU
	@caption vaata lisaks

	@reltype IP value=6 clid=CL_IPADDRESS
	@caption IP aadress ligipääsu piiramiseks

	@reltype ACL_GROUP value=7 clid=CL_GROUP
	@caption Kasutajagrupp

	@reltype OBJ_TREE value=8 clid=CL_OBJECT_TREE
	@caption objektide nimekiri

	@reltype DOCS_FROM_MENU value=9 clid=CL_MENU
	@caption v&otilde;ta dokumente men&uuml;&uuml; alt

	@reltype PERIOD value=10 clid=CL_PERIOD
	@caption v&otilde;ta dokumente perioodi alt

	@reltype OBJ_TABLE_CONF value=11 clid=CL_OBJ_TABLE_CONF
	@caption objektitabeli konfiguratsioon

	@reltype ADD_TREE_CONF value=12 clid=CL_ADD_TREE_CONF
	@caption lisamise puu konfiguratsioon

	@reltype CFGFORM value=13 clid=CL_CFGFORM
	@caption konfiguratsioonivorm

	@reltype IMAGE value=14 clid=CL_IMAGE
	@caption pilt
*/

define("IP_ALLOWED", 1);
define("IP_DENIED", 2);

class menu extends class_base
{
	function menu($args = array())
	{
		$this->init(array(
			"tpldir" => "automatweb/menu",
			"clid" => CL_MENU,
		));
	}
	
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$ob = $arr["obj_inst"];
		switch($data["name"])
		{
			case "type":
				$m = get_instance("menuedit");
				$data["options"] = $m->get_type_sel();
				break;

			case "tpl_edit":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 0, "menu" => $ob->id()));
				break;
			
			case "tpl_lead":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 1, "menu" => $ob->id()));
				break;
			
			case "tpl_view":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 2, "menu" => $ob->id()));
				break;

			case "tpl_dir":
				$template_sets = aw_ini_get("menuedit.template_sets");
				$data["options"] = array_merge(array("" => "kasuta parenti valikut"),$template_sets);
				break;
			
			case "sections":
				$this->get_brother_table($arr);
				break;

			case "sss":
				$this->get_sss_table($arr);
				break;

			case "grkeywords":
				$kwds = get_instance("keywords");
				$data["options"] = $kwds->get_keyword_picker();
				$data["selected"] = $this->get_menu_keywords($ob->id());
				break;

			case "icon":
				$ext = $this->cfg['ext'];
				if ($ob->prop("icon_id"))
				{
					$icon = html::img(array(
						"url" => "${baseurl}/automatweb/icon.${ext}?id=".$ob->id(),
					));
				}
				else
				{
					$m = get_instance("menuedit");
					if ($ob->prop("admin_feature"))
					{
						classload("icons");
						$icon = html::img(array(
							"url" => icons::get_feature_icon_url($ob->prop("admin_feature")),
						));
					}
					else
					{
						$icon = "(no icon set)";
					};
				};
				$data["value"] = $icon;
				break;

			case "admin_feature":
				// only show the program selector, if the menu has the correct type
				if ($ob->prop("type") == MN_ADMIN1)
				{
					$data["options"] = $this->get_feature_sel();				
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;	

			case "sort_by":
				$data['options'] = array(
					'' => "",
					'objects.jrk' => "J&auml;rjekorra j&auml;rgi",
					'objects.created' => "Loomise kuup&auml;eva j&auml;rgi",
					'objects.modified' => "Muutmise kuup&auml;eva j&auml;rgi",
					'documents.modified' => "Dokumenti kirjutatud kuup&auml;eva j&auml;rgi"
				);
				break;

			case "sort_ord":
				$data['options'] = array(
					'DESC' => "Suurem (uuem) enne",
					'ASC' => "V&auml;iksem (vanem) enne",
				);
				break;

			case "seealso":
				$t = &$arr["prop"]["vcl_inst"];
				$t->define_field(array(
					"name" => "id",
					"caption" => "OID",
					"type" => "int",
					"talign" => "center",
				));

				$t->define_field(array(
					"name" => "name",
					"caption" => "Nimi",
				));

				$see_also_conns = $arr["obj_inst"]->connections_from(array(
					"type" => RELTYPE_SEEALSO,
				));

				foreach($see_also_conns as $conn)
				{
					$t->define_data(array(
						"id" => $conn->prop("to"),
						"name" => $conn->prop("to.name"),
					));
				};
				break;

			case "ip":
				$t = &$arr["prop"]["vcl_inst"];
				$t->define_field(array(
					"name" => "ip_name",
					"caption" => "IP Nimi",
					"sortable" => 1,
					"align" => "center"
				));
				$t->define_field(array(
					"name" => "ip",
					"caption" => "IP Aadress",
					"sortable" => 1,
					"align" => "center"
				));
				$t->define_field(array(
					"name" => "allowed",
					"caption" => "Lubatud",
					"sortable" => 0,
					"align" => "center"
				));
				$t->define_field(array(
					"name" => "denied",
					"caption" => "Keelatud",
					"sortable" => 0,
					"align" => "center"
				));
				
				$allow = $ob->meta("ip_allow");
				$deny = $ob->meta("ip_deny");

				$conn = $ob->connections_from(array(
					"type" => RELTYPE_IP
				));
				foreach($conn as $c)
				{
					$c_o = $c->to();
			
					$t->define_data(array(
						"ip_name" => $c_o->name(),
						"ip" => $c_o->prop("addr"),
						"allowed" => html::radiobutton(array(
							"name" => "ip[".$c_o->id()."]",
							"checked" => $allow[$c_o->id()] == 1,
							"value" => IP_ALLOWED
						)),
						"denied" => html::radiobutton(array(
							"name" => "ip[".$c_o->id()."]",
							"checked" => $deny[$c_o->id()] == 1,
							"value" => IP_DENIED
						))
					));
				}
				break;

			case "menu_images":
				$data["value"] = $this->_get_images_table($arr);
				break;
		};
		return $retval;
	}

	function _get_images_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_get_images_table_cols($t);
		
		$cnt = aw_ini_get("menu.num_menu_images");
		$imdata = $arr["obj_inst"]->meta("menu_images");

		$imgrels = array(0 => "Vali pilt..");
		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_IMAGE)) as $conn)
		{
			$imgrels[$conn->prop("to")] = $conn->prop("to.name");
		}

		for($i = 0; $i <  $cnt; $i++)
		{
			// image preview
			$url = "";
			$imi = get_instance("image");
			if ($imdata[$i]["image_id"])
			{
				$url = $imi->get_url_by_id($imdata[$i]["image_id"]);
				if ($url)
				{
					$url =  html::img(array("url" => $url));
					$url .= " <br> ( ".html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $imdata[$i]["image_id"]),"image"),
						"caption" => "Muuda"
					))." ) ";
				}
			}

			$rel = html::select(array(
				"name" => "img[$i]",
				"options" => $imgrels,
				"selected" => $imdata[$i]["image_id"],
			));

			$t->define_data(array(
				"nr" => sprintf("Pilt #%d",$i),
				"ord" => html::textbox(array(
					"name" => "img_ord[$i]",
					"value" => $imdata[$i]["ord"],
					"size" => 3
				)),
				"preview" => $url,
				"rel" => $rel,
				"del" => html::checkbox(array(
					"ch_value" => 1,
					"name" => "img_del[$i]"
				))
			));
		}
		$t->set_default_sortby("nr");
		$t->sort_by();
	}

	function _get_images_table_cols(&$t)
	{
		$t->define_field(array(
			"name" => "nr",
			"caption" => "NR",
			"talign" => "center",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "ord",
			"caption" => "J&auml;rjekord",
			"talign" => "center",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "preview",
			"caption" => "Eelvaade",
			"talign" => "center",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "rel",
			"caption" => "Vali Pilt",
			"talign" => "center",
			"align" => "center",
		));
/*		$t->define_field(array(
			"name" => "del",
			"caption" => "Kustuta",
			"talign" => "center",
			"align" => "center",
		));*/
	}

	function callback_get_export_options($arr = array())
	{
		$submenus = $this->get_menu_list(false,false,$arr["obj_inst"]->id());
		$nodes = array();
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

	function callback_get_pmethod_options($arr = array())
	{
		if ($arr["obj_inst"]->prop("type") != MN_PMETHOD)
		{
			return PROP_IGNORE;
		};

		$nodes = array();

		$nodes[] = array(
			"type" => "select",
			"name" => "pclass",
			"caption" => "Vali meetod",
			"options" => array(),
			"selected" => $arr["obj_inst"]->meta("pclass"),
			"options" => $this->get_pmethod_sel(),
		);
		
		$nodes[] = array(
			"type" => "checkbox",
			"name" => "pm_url_admin",
			"value" => 1,
			"caption" => "Meetod viitab adminni",
			"ch_value" => $arr["obj_inst"]->meta("pm_url_admin"),
		);
		
		$nodes[] = array(
			"type" => "checkbox",
			"name" => "pm_url_menus",
			"value" => 1,
			"caption" => "Meetodi väljundi kuvamisel näidatakse menüüsid",
			"ch_value" => $arr["obj_inst"]->meta("pm_url_menus"),
		);
				
		return $nodes;
	}
			
	function set_property($arr = array())
	{	
		$data = &$arr["prop"];
		$ob = $arr["obj_inst"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			// grkeywords just triggers an action, nothing should
			// be saved into the objects table
			case "grkeywords":
				if (!$ob->id())
				{
					$this->save_menu_keywords($data["value"],$ob->id());
				};
				$retval = PROP_IGNORE;
				break;

			case "icon":
				$retval = PROP_IGNORE;
				break;

			case "sections":
				$dar = new aw_array($arr["request"]["erase"]);
				foreach($dar->get() as $erase)
				{
					$e_o = obj($erase);
					$e_o->delete();
				}
				break;

			case "sss":
				$arr["obj_inst"]->set_meta("section_include_submenus",$arr["form_data"]["include_submenus"]);
				break;

			case "type":
				$form_data = &$arr["form_data"];
				if ($form_data["type"] != MN_ADMIN1)
				{
					$ob->set_prop("admin_feature",0);
				};
				if ($form_data["type"] != MN_PMETHOD)
				{
					$ob->set_meta("pclass","");
					$ob->set_meta("pm_url_admin","");
					$ob->set_meta("pm_url_menus","");
				};
				break;

			case "menu_images":
				if (!$this->menu_images_done)
				{
					$arr["obj_inst"]->set_meta("menu_images",$this->update_menu_images(array(
						"id" => $ob->id(),
						"img_del" => $arr["form_data"]["img_del"],
						"img_ord" => $arr["form_data"]["img_ord"],
						"img" => $arr["request"]["img"],
						"meta" => $arr["obj"]["meta"]
					)));
					$this->menu_images_done = 1;
				};
				break;


			case "pmethod_properties":
				$form_data = &$arr["form_data"];
				$ob->set_meta("pclass",$form_data["pclass"]);
				$ob->set_meta("pm_url_menus",$form_data["pm_url_menus"]);
				$ob->set_meta("pm_url_admin",$form_data["pm_url_admin"]);
				break;

			case "ip":
				$allow = array();
				$deny = array();

				$ar = new aw_array($arr["form_data"]["ip"]);
				foreach($ar->get() as $ipid => $ipv)
				{
					if ($ipv == IP_ALLOWED)
					{
						$allow[$ipid] = 1;
					}
					else
					if ($ipv == IP_DENIED)
					{
						$deny[$ipid] = 1;
					}
				}
				$arr["obj_inst"]->set_meta("ip_allow",$allow);
				$arr["obj_inst"]->set_meta("ip_deny",$deny);
				break;				
		};
		return $retval;
	}

	function update_menu_images($args = array())
	{
		extract($args);
		$num_menu_images = $this->cfg["num_menu_images"];
		$t = get_instance("image");

		$imgar = $meta["menu_images"];
		for ($i=0; $i < $num_menu_images; $i++)
		{
			if ($img_del[$i] == 1 || !$img[$i])
			{
				unset($imgar[$i]);
			}
			else
			{
				$imgar[$i]["image_id"] = $img[$i];
				$imgar[$i]["ord"] = $img_ord[$i];
			}
		}

		$timgar = array();
		$cnt = 0;
		for ($i=0; $i < $num_menu_images; $i++)
		{
			if ($imgar[$i]["id"] || $imgar[$i]["ord"] || $imgar[$i]["image_id"])
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


	function callback_post_save($arr)
	{
		$this->updmenus[] = (int)$arr["obj_inst"]->id();
		$m = get_instance("menuedit");
		$m->invalidate_menu_cache($this->updmenus);
	}

	function callback_pre_save($arr)
	{
		$form_data = &$arr["form_data"];
		if ($form_data["do_export"])
		{
			$menu_export = get_instance("export/menu_export");
			$menu_export->export_menus(array(
				"id" => $arr["obj_inst"]->id(),
				"ex_menus" => $form_data["ex_menus"],
				"allactive" => $form_data["allactive"],
				"ex_icons" => $form_data["ex_icons"],
			));
		};
	}

	function callback_gen_path($args = array())
	{
		// XXX: rewrite it to use some kind of global list of container objects
		// because, all this does it to put a clickable link on the YAH to see
		// the contents of the container
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
		$orb = get_instance("orb");
		return array("0" => "--vali--") + $orb->get_classes_by_interface(array("interface" => "public"));
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


	function callback_on_submit_relation_list($args)
	{
		$obj =& obj($args["id"]);
		$co = $obj->connections_from(array(
			"type" => RELTYPE_IP,
		));

		$_allow = $obj->meta("ip_allow");
		$_deny = $obj->meta("ip_deny");

		$lut = array();
		foreach($co as $c)
		{
			$lut[$c->prop("to")] = $c->prop("to");
		}

		$allow = array();
		$deny = array();
		foreach($allow as $ipa => $one)
		{
			if (isset($lut[$ipa]))
			{
				$allow[$ipa] = $one;
			}
		}
		foreach($deny as $ipa => $one)
		{
			if (isset($lut[$ipa]))
			{
				$deny[$ipa] = $one;
			}
		}

		$obj->set_meta("ip_allow", $allow);
		$obj->set_meta("ip_deny", $deny);
		$obj->save();
	}

	function get_brother_table($arr)
	{
		$obj = $arr["obj_inst"];

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
/*		$t->define_field(array(
			"name" => "check",
			"caption" => "kustuta",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));*/

		$ol = new object_list(array(
			"brother_of" => $obj->id()
		));

		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->id() == $obj->id())
			{
				continue;
			}
			$t->define_data(array(
				"id" => $o->id(),
				"name" => $o->path_str(array(
					"max_len" => 3
				)),
				"check" => html::checkbox(array(
					"name" => "erase[".$o->id()."]",
					"value" => $o->id(),
					"checked" => false,
				)),
			));
		}
	}

	function get_sss_table($arr)
	{
		$obj = $arr["obj_inst"];

		$section_include_submenus = $obj->meta("section_include_submenus");

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
		$t->define_field(array(
			"name" => "check",
			"caption" => "k.a. alammen&uuml;&uuml;d",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));

		$conns = $obj->connections_from(array(
			"type" => RELTYPE_DOCS_FROM_MENU
		));

		foreach($conns as $c)
		{
			$o = $c->to();

			$t->define_data(array(
				"id" => $o->id(),
				"name" => $o->path_str(array(
					"max_len" => 3
				)),
				"check" => html::checkbox(array(
					"name" => "include_submenus[".$o->id()."]",
					"value" => $o->id(),
					"checked" => $section_include_submenus[$o->id()],
				)),
			));
		}
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
				"type" => $this->clid,
			));
		};
		$f = $this->mcaliases[$matches[3] - 1];
		if (!$f["target"])
		{
			return "";
		}
		$target = $f;

		if (!$this->object_exists($target["oid"]))
		{
			return "";
		}

		$o = obj($target["oid"]);

		if ($o->prop("link") != "")
		{
			$link = $o->prop("link");
		}	
		else
		{
			$link = $this->cfg["baseurl"]."/".$target["oid"];
		}

		$ltarget = "";
		if ($o->prop("target"))
		{
			$ltarget = "target='_blank'";
		}

		if (aw_global_get("section") == $target["oid"])
		{
			$ret = sprintf("<a $ltarget class=\"sisutekst-sel\" href='$link'>%s</a>",$target["name"]);
		}
		else
		{
			$ret = sprintf("<a $ltarget class=\"sisutekst\" href='$link'>%s</a>",$target["name"]);
		}
		return $ret;
	}
};
?>
