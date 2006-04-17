<?php
// $Header: /home/cvs/automatweb_dev/classes/menu.aw,v 2.154 2006/04/17 10:13:21 kristo Exp $
// menu.aw - adding/editing/saving menus and related functions

/*
	// stuff that goes into the objects table
	@default table=objects

	@classinfo trans=1

	@groupinfo general_sub caption="Üldine" parent=general

		@property name type=textbox rel=1 trans=1 group=general_sub
		@caption Nimi
		@comment Objekti nimi

		@property comment type=textbox group=general_sub
		@caption Kommentaar
		@comment Vabas vormis tekst objekti kohta

		@property status type=status trans=1 default=1 group=general_sub
		@caption Aktiivne
		@comment Kas objekt on aktiivne

		@property alias type=textbox group=general_sub
		@caption Alias

		@property jrk type=textbox size=4 group=general_sub
		@caption Jrk

		@property pmethod_properties type=callback callback=callback_get_pmethod_options group=general_sub store=no
		@caption Avaliku meetodi seaded

		@property admin_feature type=select group=general_sub table=menu field=admin_feature
		@caption Vali programm


	@groupinfo advanced_settings caption="Süvaseaded" parent=general

		@property type type=select group=advanced_settings table=menu field=type
		@caption Menüü tüüp

		@property objtbl_conf type=relpicker reltype=RELTYPE_OBJ_TABLE_CONF field=meta method=serialize group=advanced_settings
		@caption Objektitabeli konf

		@property add_tree_conf type=relpicker reltype=RELTYPE_ADD_TREE_CONF field=meta method=serialize group=advanced_settings
		@caption Objekti lisamise puu konff

		@property cfgmanager type=relpicker reltype=RELTYPE_CFGFORM field=meta method=serialize group=advanced_settings
		@caption Konfiguratsioonivorm

	@groupinfo import_export caption=Eksport submit=no parent=general

		@property no_export type=checkbox group=import_export ch_value=1 field=meta method=serialize table=objects
		@caption &Auml;ra n&auml;ita ekspordis

		@property export type=callback callback=callback_get_export_options group=import_export store=no
		@caption Eksport


	@groupinfo users caption="Kasutajad" parent=general

		@property users_only type=checkbox field=meta method=serialize group=users ch_value=1
		@caption Ainult sisselogitud kasutajatele

@groupinfo show caption=Näitamine

	@groupinfo show_sub caption="Näitamine" parent=show


		@property link_behaviour type=chooser store=no multiple=1 group=show_sub
		@caption Lingi iseloom

		@property target type=checkbox group=show_sub ch_value=1 search=1 table=menu
		@caption Uues aknas

		@property clickable type=checkbox group=show_sub ch_value=1 default=1 table=menu
		@caption Klikitav


		@property link type=textbox group=show_sub table=menu
		@caption Menüü link


		@property show_restrictions type=chooser store=no multiple=1 group=show_sub
		@caption Näitamine

		@property frontpage type=checkbox table=objects field=meta method=serialize group=show_sub ch_value=1
		@caption Esilehel

		@property mid type=checkbox group=show_sub ch_value=1 table=menu
		@caption Paremal


		@property show_conditions type=chooser store=no multiple=1 group=show_sub
		@caption Tingimused

		@property hide_noact type=checkbox ch_value=1 group=show_sub table=menu
		@caption Peida ära, kui selle kausta all aktiivseid dokumente ei ole

		@property no_menus type=checkbox group=show_sub ch_value=1 table=menu
		@caption Ilma menüüdeta


		@property panes type=chooser store=no multiple=1 group=show_sub
		@caption Paanid

		@property left_pane type=checkbox  ch_value=1 default=1 group=show_sub table=menu
		@caption Vasak paan

		@property right_pane type=checkbox ch_value=1 default=1 group=show_sub table=menu
		@caption Parem paan


		@property width type=textbox size=5 group=show_sub table=menu
		@caption Laius

	@groupinfo doc_show caption="Dokumentide kuvamine" parent=show

		@property ndocs type=textbox size=3 group=doc_show table=menu
		@caption Mitu viimast dokumenti

		@property show_lead type=checkbox field=meta method=serialize group=doc_show ch_value=1
		@caption Näita ainult leadi

		@property sort_by_name type=checkbox field=meta method=serialize group=doc_show ch_value=1
		@caption Sorteeri nime järgi

	@groupinfo doc_ord  caption="Dokumentide järjestamine" parent=show

		@property sorter type=table group=doc_ord table=menu
		@caption Dokumentide järjestamine

		property sort_by type=select table=objects field=meta method=serialize group=doc_ord
		caption Dokumente j&auml;rjestatakse

		property sort_ord type=select table=objects field=meta method=serialize group=doc_ord

	@groupinfo ip caption="IP piirangud" parent=show

		@property ip type=table store=no group=ip no_caption=1

@groupinfo look caption="Välimus"

	@groupinfo look_sub caption="Välimus" parent=look

		@property color type=colorpicker field=meta method=serialize group=look_sub
		@caption Menüü värv

		@property color2 type=colorpicker field=meta method=serialize group=look_sub
		@caption Menüü värv 2

		@property icon type=icon field=meta method=serialize group=look_sub
		@caption Ikoon

		@property sel_icon type=relpicker reltype=RELTYPE_ICON table=objects field=meta method=serialize group=look_sub 
		@caption Vali ikoon

	@groupinfo templates caption=Kujunduspõhjad parent=look

		@property tpl_dir table=objects type=select field=meta method=serialize group=templates
		@caption Template set

		@property show_lead_template type=select field=meta method=serialize group=templates
		@caption Leadi template

		@property tpl_view type=select group=templates table=menu
		@caption Template dokumendi näitamiseks (pikk)

		@property tpl_lead type=select group=templates table=menu
		@caption Template dokumendi näitamiseks (lühike)

		@property show_layout type=relpicker reltype=RELTYPE_SHOW_AS_LAYOUT group=templates field=meta method=serialize table=objects
		@caption Kasuta n&auml;itamiseks layouti

	@groupinfo presentation caption=Pildid parent=look

		@property images_from_menu type=relpicker reltype=RELTYPE_PICTURES_MENU group=presentation field=meta method=serialize
		@caption V&otilde;ta pildid men&uuml;&uuml; alt

		@property img_timing type=textbox size=3 field=meta method=serialize group=presentation
		@caption Viivitus piltide vahel (sek.)

		@property imgrelmanager type=relmanager reltype=RELTYPE_IMAGE store=no group=presentation
		@caption Vali pilte

		@property img_act type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize group=presentation
		@caption Aktiivse menüü pilt

		@property menu_images type=table field=meta method=serialize group=presentation store=no
		@caption Menüü pildid

@groupinfo menus caption="Sisu seaded"

	@groupinfo menus_sub caption="Sisu seaded" parent=menus

		@property submenus_from_obj type=relpicker reltype=RELTYPE_SUBMENUS table=objects  field=meta method=serialize group=menus_sub
		@caption Alammen&uuml;&uuml;d objektist

		@property aip_filename type=textbox field=meta method=serialize group=menus_sub
		@caption Failinimi

		@property get_content_from type=relpicker reltype=RELTYPE_CONTENT_FROM field=meta method=serialize group=menus_sub
		@caption Sisu objektist

		@property submenus_from_menu type=relpicker reltype=RELTYPE_SHOW_SUBFOLDERS_MENU group=menus_sub table=objects field=meta method=serialize table=objects
		@caption V&otilde;ta alammen&uuml;&uuml;d men&uuml;&uuml; alt

		@property show_object_tree type=relpicker reltype=RELTYPE_OBJ_TREE group=menus_sub field=meta method=serialize table=objects
		@caption Kasuta alammen&uuml;&uuml;de n&auml;itamiseks objektide nimekirja

		@property use_target_audience type=checkbox ch_value=1 group=menus_sub field=meta method=serialize table=objects
		@caption Kasuta sihtr&uuml;hmap&otilde;hist kuvamist

		@property content_all_langs type=checkbox ch_value=1 group=menus_sub field=meta method=serialize table=objects
		@caption Sisu k&otilde;ikidest keeltest

		@property set_doc_content_type type=chooser group=menus_sub table=menu field=set_doc_content_type
		@caption M&auml;&auml;ra sisu t&uuml;&uuml;p

	@groupinfo advanced_ctx caption=Kontekst parent=menus

		@property has_ctx type=checkbox ch_value=1 table=objects field=meta method=serialize group=advanced_ctx
		@caption Kuva alamkaustu kontekstip&otilde;hiselt

		@property default_ctx type=select table=objects field=meta method=serialize group=advanced_ctx
		@caption Default kontekst

		@property ctx type=releditor reltype=RELTYPE_CTX field=meta method=serialize mode=manager props=name,status table_fields=name,status table_edit_fields=name,status group=advanced_ctx
		@caption Kontekstid

	@groupinfo relations caption="Vaata lisaks" parent=menus

		@property sa_manager type=relmanager reltype=RELTYPE_SEEALSO group=relations store=no
		@caption Seosehaldur

		@property seealso type=table group=relations store=no
		@caption Menüüd, mille all see menüü on "vaata lisaks" menüü
		@comment Nende menüüde lisamine ja eemaldamine käib läbi seostehalduri

		@property seealso_order type=textbox group=relations size=3 table=objects field=meta method=serialize
		@caption Järjekorranumber (vaata lisaks)

	@groupinfo brothers caption=Vennastamine parent=menus

		@property sections type=table store=no group=brothers
		@caption Vennad

	@groupinfo docs_from caption="Sisu asukoht" parent=menus

		@property sss type=table store=no group=docs_from
		@caption Menüüd, mille alt viimased dokumendid võetakse

	@groupinfo seealso_docs caption="Vaata lisaks dokumendid" parent=menus

		@property seealso_docs_t type=table group=seealso_docs no_caption=1 table=menu
		@caption Vaatalisaks dokumendid

	@groupinfo periods caption="Perioodid" parent=menus

		@property periodic type=checkbox group=periods ch_value=1
		@caption Perioodiline

		@property pers type=relpicker multiple=1 size=5 table=objects field=meta method=serialize group=periods reltype=RELTYPE_PERIOD
		@caption Perioodid, mille alt dokumendid võetakse

		@property all_pers type=checkbox ch_value=1 table=objects field=meta method=serialize group=periods
		@caption K&otilde;ikide perioodide alt

		@property docs_per_period type=textbox size=3 group=periods table=objects field=meta method=serialize
		@caption Dokumente perioodist

		@property show_periods type=checkbox ch_value=1 group=periods table=objects field=meta method=serialize
		@caption Näita perioode

		@property show_period_count type=textbox size=4 group=periods table=objects field=meta method=serialize
		@caption Mitu viimast perioodi

	@groupinfo keywords caption=Võtmesõnad parent=menus

		@property kw_tb type=toolbar no_caption=1 store=no group=keywords

		property grkeywords type=select size=10 multiple=1 field=meta method=serialize group=keywords
		caption AW Märksõnad

		@property grkeywords2 type=keyword_selector field=meta method=serialize group=keywords
		@caption AW Märksõnad

		@property keywords type=textbox field=meta method=serialize group=keywords
		@caption META keywords

		@property description type=textbox field=meta method=serialize group=keywords
		@caption META description


@groupinfo transl caption=T&otilde;lgi
@default group=transl
	
	@property transl type=callback callback=callback_get_transl
	@caption T&otilde;lgi

@groupinfo acl caption=&Otilde;igused
@default group=acl
	
	@property acl type=acl_manager store=no
	@caption &Otilde;igused


	@classinfo relationmgr=yes
	@classinfo objtable=menu
	@classinfo objtable_index=id
	@classinfo syslog_type=ST_MENU



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

	@reltype OBJ_TREE value=8 clid=CL_OBJECT_TREE,CL_OBJECT_TREEVIEW_V2
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

	@reltype SUBMENUS value=16 clid=CL_SHOP_ORDER_CENTER,CL_CRM_SECTION,CL_OBJECT_TREEVIEW_V2,CL_ABSTRACT_DATASOURCE,CL_CRM_COMPANY_WEBVIEW
	@caption alammen&uuml;&uuml;d objektist

	@reltype CONTENT_FROM value=17 clid=CL_PROJECT
    	@caption Sisu objektist

	@reltype SEEALSO_DOC value=18 clid=CL_DOCUMENT
    	@caption vaata lisaks dokument

	@reltype ICON value=19 clid=CL_IMAGE
    	@caption ikoon

	@reltype TIMING value=20 clid=CL_TIMING
	@caption Aeg

	@reltype CTX value=21 clid=CL_FOLDER_CONTEXT
	@caption Kontekst

	@reltype LANG_REL value=22 clid=CL_MENU
	@caption Keeleseos
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

		$this->trans_props = array(
			"name"
		);
	}

	/** Generate a form for adding or changing an object 
		
		@attrib name=new params=name all_args="1" is_public="1" caption="Lisa"
		
		@param parent optional type=int acl="add"
		@param period optional
		@param alias_to optional
		@param return_url optional
		@param reltype optional type=int

		
		@returns
		
		
		@comment
		id _always_ refers to the objects table. Always. If you want to load
		any other data, then you'll need to use other field name

	**/
	function new_change($args)
	{
		return $this->change($args);
	}

	/**  
		
		@attrib name=change params=name all_args="1" is_public="1" caption="Muuda"
		
		@param id optional type=int acl="edit"
		@param group optional
		@param period optional
		@param alias_to optional
		@param return_url optional
		
		@returns
		
		
		@comment

	**/
	function change($args = array())
	{
		return parent::change($args);
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$ob = $arr["obj_inst"];
		switch($data["name"])
		{
			case "set_doc_content_type":
				$ol = new object_list(array("class_id" => CL_DOCUMENT_CONTENT_TYPE, "lang_id" => array(), "site_id" => array()));
				$data["options"] = array("" => t("Vali t&uuml;hjaks")) + $ol->names();
				break;

			case "jrk":
				if ($arr["new"] && $this->can("view", $arr["request"]["ord_after"]))
				{
					$oa = obj($arr["request"]["ord_after"]);
					$mlp = new object_list(array(
						"class_id" => CL_MENU,
						"parent" => $oa->parent(),
						"sort_by" => "jrk"
					));
					foreach($mlp->arr() as $id => $menu)
					{
						if ($get_next)
						{
							$next_ord = $menu->ord();
							$get_next = false;
						}
						if ($id == $oa->id())
						{
							$get_next = true;
						}
					}
					if (!isset($next_ord))
					{
						$next_ord = $oa->ord() + 100;
					}
					$data["value"] = ($oa->ord() + $next_ord) / 2;
				}
				break;

			case "kw_tb":
				$this->kw_tb($arr);
				break;

			case "left_pane":
			case "right_pane":
				$retval = PROP_IGNORE;
				break;

			case "panes":
				$data["options"] = array(
					"left_pane" => t("Vasak"),
					"right_pane" => t("Parem"),
				);
				$data["value"]["left_pane"] = $ob->prop("left_pane");
				$data["value"]["right_pane"] = $ob->prop("right_pane");
				break;


			case "target":
			case "clickable":
				$retval = PROP_IGNORE;
				break;

			case "link_behaviour":
				$data["options"] = array(
					"target" => "Uues aknas",
					"clickable" => "Klikitav",
				);
				$data["value"]["target"] = $ob->prop("target");
				$data["value"]["clickable"] = $ob->prop("clickable");
				break;


			case "frontpage":
			case "mid":
				$retval = PROP_IGNORE;
				break;

			case "show_restrictions":
				$data["options"] = array(
					"frontpage" => "Esilehel",
					"mid" => "Paremal",
				);
				$data["value"]["frontpage"] = $ob->prop("frontpage");
				$data["value"]["mid"] = $ob->prop("mid");
				break;


			case "hide_noact":
			case "no_menus":
				$retval = PROP_IGNORE;
				break;

			case "show_conditions":
				$data["options"] = array(
					"hide_noact" => "Peida ära, kui selle kausta all aktiivseid dokumente ei ole",
					"no_menus" => "Ilma menüüdeta",
				);
				$data["value"]["hide_noact"] = $ob->prop("hide_noact");
				$data["value"]["no_menus"] = $ob->prop("no_menus");
				break;



			case "type":
				$m = get_instance("menuedit");
				$data["options"] = $m->get_type_sel();
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
				$data["options"] = array_merge(array("" => t("kasuta parenti valikut")),$template_sets);
				break;
			
			case "sections":
				$this->get_brother_table($arr);
				break;

			case "sss":
				$this->get_sss_table($arr);
				break;

			case "grkeywords":
				$kwds = get_instance(CL_KEYWORD);
				$data["options"] = $kwds->get_keyword_picker();
				$data["selected"] = $this->get_menu_keywords($ob->id());
				break;

			case "icon":
				$ext = $this->cfg['ext'];
				if (is_oid($ob->meta("sel_icon")) && $this->can("view", $ob->meta("sel_icon")))
				{
					$fi = get_instance(CL_IMAGE);
					$icon = html::img(array(
						"url" => $fi->get_url_by_id($ob->meta("sel_icon"))
					));
				}
				else
				if ($ob->prop("admin_feature"))
				{
					classload("core/icons");
					$icon = html::img(array(
						"url" => icons::get_feature_icon_url($ob->prop("admin_feature")),
					));
				}
				else
				{
					$icon = t("(no icon set)");
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

			case "sorter":
				// okey, how do I do this?
				// by default I show only one line ....
				// if something gets selected, then I'll add another..
				// salvestame metainfosse
				$sort_fields = new aw_array($arr["obj_inst"]->meta("sort_fields"));
				$sort_order = new aw_array($arr["obj_inst"]->meta("sort_order"));
				$t = &$data["vcl_inst"];
				$t->define_field(array(
					"name" => "fubar",
				));
				$t->define_field(array(
					"name" => "field",
					"caption" => t("Väli"),
				));
				$t->define_field(array(
					"name" => "order",
					"caption" => t("Järjekord"),
				));

				$fields = array(
					'' => "",
					'objects.jrk' => t("J&auml;rjekorra j&auml;rgi"),
					'objects.created' => t("Loomise kuup&auml;eva j&auml;rgi"),
					'objects.modified' => t("Muutmise kuup&auml;eva j&auml;rgi"),
					'documents.modified' => t("Dokumenti kirjutatud kuup&auml;eva j&auml;rgi"),
					'planner.start' => t("Kalendris valitud aja j&auml;rgi"),
					'objects.name' => t("Nime j&auml;rgi"),
				);

				$orders = array(
					'DESC' => t("Suurem (uuem) enne"),
					'ASC' => t("V&auml;iksem (vanem) enne"),
				);

				$idx = 1;

				$morder = $arr["obj_inst"]->meta("sort_order");

				foreach($sort_fields->get() as $key => $val)
				{
					$t->define_data(array(
						"fubar" => $idx,
						"field" => html::select(array(
							"name" => "sort_fields[$idx]",
							"options" => $fields,
							"value" => $val,
						)),

						"order" => html::select(array(
							"name" => "sort_order[$idx]",
							"options" => $orders,
							"value" => $morder[$key],
						)),
					));
					$idx++;
				}

				$t->define_data(array(
					"fubar" => $idx,
					"field" => html::select(array(
						"name" => "sort_fields[$idx]",
						"options" => $fields,
					)),
					"order" => html::select(array(
						"name" => "sort_order[$idx]",
						"options" => $orders,
					)),
				));
				$t->sort_by(array("field" => "fubar","sorder" => "desc"));
				break;

			case "sort_by":
				$data['options'] = array(
					'' => "",
					'objects.jrk' => t("J&auml;rjekorra j&auml;rgi"),
					'objects.created' => t("Loomise kuup&auml;eva j&auml;rgi"),
					'objects.modified' => t("Muutmise kuup&auml;eva j&auml;rgi"),
					'documents.modified' => t("Dokumenti kirjutatud kuup&auml;eva j&auml;rgi"),
					'planner.start' => t("Kalendris valitud aja j&auml;rgi"),
					'RAND()' => t("Random"),
				);
				break;

			case "sort_ord":
				$data['options'] = array(
					'DESC' => t("Suurem (uuem) enne"),
					'ASC' => t("V&auml;iksem (vanem) enne"),
				);
				break;

			case "seealso":
				$t = &$arr["prop"]["vcl_inst"];
				$t->define_field(array(
					"name" => "id",
					"caption" => t("OID"),
					"type" => "int",
					"talign" => "center",
				));

				$t->define_field(array(
					"name" => "name",
					"caption" => t("Nimi"),
				));

				$see_also_conns = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_SEEALSO",
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
					"caption" => t("IP Nimi"),
					"sortable" => 1,
					"align" => "center"
				));
				$t->define_field(array(
					"name" => "ip",
					"caption" => t("IP Aadress"),
					"sortable" => 1,
					"align" => "center"
				));
				$t->define_field(array(
					"name" => "allowed",
					"caption" => t("Lubatud"),
					"sortable" => 0,
					"align" => "center"
				));
				$t->define_field(array(
					"name" => "denied",
					"caption" => t("Keelatud"),
					"sortable" => 0,
					"align" => "center"
				));
				
				$allow = $ob->meta("ip_allow");
				$deny = $ob->meta("ip_deny");

				$conn = $ob->connections_from(array(
					"type" => "RELTYPE_IP",
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

			case "show_lead_template":
				if ($arr["obj_inst"]->prop("show_lead") == 0)
				{
					return PROP_IGNORE;
				}
				$ol = new object_list(array(
					"class_id" => CL_CONFIG_AW_DOCUMENT_TEMPLATE,
					"type" => 1 // lead
				));
				$data["options"] = array("" => "") +$ol->names();
				break;

			case "seealso_docs_t":
				$this->_do_seealso_docs_t($arr);
				break;

			case "default_ctx":
				// gather contexts from submenus
				$ol = new object_list(array(
					"class_id" => CL_MENU,
					"parent" => $arr["obj_inst"]->id(),
					"lang_id" => array(),
					"site_id" => array()
				));
				$opts = array("" => "");
				foreach($ol->arr() as $o)
				{
					foreach($o->connections_from(array("type" => "RELTYPE_CTX")) as $c)
					{
						$opts[$c->prop("to")] = $c->prop("to.name");
					}
				}
				$data["options"] = $opts;
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

		$imgrels = array(0 => t("Vali pilt.."));
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_IMAGE")) as $conn)
		{
			$imgrels[$conn->prop("to")] = $conn->prop("to.name");
		}

		for($i = 0; $i <  $cnt; $i++)
		{
			// image preview
			$url = "";
			$imi = get_instance(CL_IMAGE);
			if ($imdata[$i]["image_id"])
			{
				$url = $imi->get_url_by_id($imdata[$i]["image_id"]);
				if ($url)
				{
					$url =  html::img(array("url" => $url));
					$url .= " <br> ( ".html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $imdata[$i]["image_id"], "return_url" => get_ru()),"image"),
						"caption" => t("Muuda")
					))." ) ";
				}
			}

			$rel = html::select(array(
				"name" => "img[$i]",
				"options" => $imgrels,
				"selected" => $imdata[$i]["image_id"],
			));

			$t->define_data(array(
				"nr" => " $i",
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
				)),
				"up" => html::fileupload(array(
					"name" => "mimg_$i"
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
			"caption" => t("Pildi number"),
			"talign" => "center",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"talign" => "center",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "preview",
			"caption" => t("Eelvaade"),
			"talign" => "center",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "rel",
			"caption" => t("Vali Pilt"),
			"talign" => "center",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "up",
			"caption" => t("Uploadi Pilt"),
			"talign" => "center",
			"align" => "center",
		));
/*		$t->define_field(array(
			"name" => "del",
			"caption" => t("Kustuta"),
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
			"caption" => t("Vali menüüd"),
			"options" => $submenus,
			// this selects all choices
			"selected" => array_flip($submenus),
		);
		$nodes[] = $tmp;
		$tmp = array(
			"type" => "checkbox",
			"name" => "allactive",
			"value" => 1,
			"caption" => t("Märgi kõik menüüd aktiivseks"),
		);
		$nodes[] = $tmp;
		$tmp = array(
			"type" => "checkbox",
			"name" => "ex_icons",
			"value" => 1,
			"caption" => t("Ekspordi ikoonid"),
		);
		$nodes[] = $tmp;
		$tmp = array(
			"type" => "submit",
			"value" => t("Ekspordi"),
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
			"caption" => t("Vali meetod"),
			"options" => array(),
			"selected" => $arr["obj_inst"]->meta("pclass"),
			"options" => $this->get_pmethod_sel(),
		);
		$pclass = $arr["obj_inst"]->meta("pclass");
		list($class_name, $tmp) = explode("/", $pclass);
		if($class_name == "method" || $class_name == "commune" || $class_name == "community")
		{
			$class_id = clid_for_name($class_name);
			$nodes[] = array(
				"type" => "select",
				"name" => "pobject",
				"caption" => t("Vali meetodiga seotud objekt"),
				"selected" => $arr["obj_inst"]->meta("pobject"),
				"options" => $this->get_pobjects($class_id),
			);
		}
		if($class_name == "commune" || $class_name == "community")
		{
			$nodes[] = array(
				"type" => "select",
				"name" => "pgroup",
				"caption" => t("Vali meetodiga seotud grupp"),
				"options" => $this->get_object_groups($class_id),
				"selected" => $arr["obj_inst"]->meta("pgroup"),
			);
		}
		$nodes[] = array(
			"type" => "checkbox",
			"name" => "pm_url_admin",
			"value" => 1,
			"caption" => t("Meetod viitab adminni"),
			"ch_value" => $arr["obj_inst"]->meta("pm_url_admin"),
		);
		
		$nodes[] = array(
			"type" => "checkbox",
			"name" => "pm_url_menus",
			"value" => 1,
			"caption" => t("Meetodi väljundi kuvamisel näidatakse menüüsid"),
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
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			// grkeywords just triggers an action, nothing should
			// be saved into the objects table
			case "grkeywords":
				if ($ob->id())
				{
					$this->save_menu_keywords($data["value"],$ob->id());
				};
				$retval = PROP_IGNORE;
				break;

			case "icon":
				$retval = PROP_IGNORE;
				break;
			
			case "left_pane":
			case "right_pane":
				$retval = PROP_IGNORE;
				break;

			case "panes":
				$ob->set_prop("left_pane",isset($data["value"]["left_pane"]) ? 1 : 0);
				$ob->set_prop("right_pane",isset($data["value"]["right_pane"]) ? 1 : 0);
				break;


			case "target":
			case "clickable":
				$retval = PROP_IGNORE;
				break;

			case "link_behaviour":
				$ob->set_prop("target",isset($data["value"]["target"]) ? 1 : 0);
				$ob->set_prop("clickable",isset($data["value"]["clickable"]) ? 1 : 0);
				break;


			case "frontpage":
			case "mid":
				$retval = PROP_IGNORE;
				break;

			case "show_restrictions":
				$ob->set_prop("frontpage",isset($data["value"]["frontpage"]) ? 1 : 0);
				$ob->set_prop("mid",isset($data["value"]["mid"]) ? 1 : 0);
				break;


			case "hide_noact":
			case "no_menus":
				$retval = PROP_IGNORE;
				break;

			case "show_conditions":
				$ob->set_prop("hide_noact",isset($data["value"]["hide_noact"]) ? 1 : 0);
				$ob->set_prop("no_menus",isset($data["value"]["no_menus"]) ? 1 : 0);
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
				$arr["obj_inst"]->set_meta("section_include_submenus",$arr["request"]["include_submenus"]);
				break;

			case "type":
				$request = &$arr["request"];
				if ($request["type"] != MN_ADMIN1)
				{
					$ob->set_prop("admin_feature",0);
				};
				if ($request["type"] != MN_PMETHOD)
				{
					$ob->set_meta("pclass","");
					$ob->set_meta("pobject", "");
					$ob->set_meta("pgroup", "");
					$ob->set_meta("pm_url_admin","");
					$ob->set_meta("pm_url_menus","");
				};
				break;

			case "menu_images":
				if (!$this->menu_images_done)
				{
					$arr["obj_inst"]->set_meta("menu_images",$this->update_menu_images(array(
						"id" => $ob->id(),
						"img_del" => $arr["request"]["img_del"],
						"img_ord" => $arr["request"]["img_ord"],
						"img" => $arr["request"]["img"],
						"meta" => $arr["obj_inst"]->meta(),
						"obj_inst" => $arr["obj_inst"]
					)));
					$this->menu_images_done = 1;
				};
				break;

			case "sorter":
				$request = &$arr["request"];
				$save_fields = array();
				$save_orders = array();
				$fields = new aw_array($request["sort_fields"]);
				$str = array();
				foreach($fields->get() as $key => $val)
				{
					if ($val)
					{
						$save_fields[] = $val;
						$str[] = $val . " " . $request["sort_order"][$key];
						$save_orders[] = $request["sort_order"][$key];
					};
				};
				$ob->set_meta("sort_fields",$save_fields);
				$ob->set_meta("sort_order",$save_orders);
				$ob->set_meta("sort_by",join(",",$str));
				$ob->set_meta("sort_ord","");
				break;

			case "pmethod_properties":
				$request = &$arr["request"];
				$ob->set_meta("pclass", $request["pclass"]);
				$ob->set_meta("pobject", $request["pobject"]);
				$ob->set_meta("pgroup", $request["pgroup"]);
				$ob->set_meta("pm_url_menus", $request["pm_url_menus"]);
				$ob->set_meta("pm_url_admin",$request["pm_url_admin"]);
				break;

			case "ip":
				$allow = array();
				$deny = array();

				$ar = new aw_array($arr["request"]["ip"]);
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

			case "alias":
				if ($data["value"] != "")
				{
					$filt = array(
						"class_id" => CL_MENU,
						"alias" => $data["value"],
						"site_id" => array(),
						"lang_id" => array()
					);
					if (aw_ini_get("menuedit.recursive_aliases") == 1)
					{
						$filt["parent"] = $arr["obj_inst"]->parent();
					}
					if (is_oid($arr["obj_inst"]->id()))
					{
						$filt["oid"] = new obj_predicate_not($arr["obj_inst"]->id());
					}
					$ol = new object_list($filt);
					if (count($ol->ids()))
					{
						$data["error"] = t("Selline alias on juba olemas!");
						return PROP_FATAL_ERROR;
					}
				}
				break;

			case "seealso_docs_t":
				$arr["obj_inst"]->set_meta("sad_opts", $arr["request"]["sad_opts"]);
				break;
		};
		return $retval;
	}

	function get_object_groups($class_name)
	{
		$cfg = get_instance("cfg/cfgutils");
		$cfg->load_class_properties(array("clid" => $class_name));
		$groups = $cfg->get_groupinfo();
		//arr($groups);
		$rval = array();
		foreach($groups as $key => $value)
		{
			$rval[$key] = $value["caption"] ? $value["caption"]." ($key)" : $key;
		}
		return $rval;
	}
	
	function get_pobjects($class_id)
	{
		$objects = new object_list(array(
			"class_id" => $class_id,
			"limit" => 100,
		));
		return array(0 => t("-- vali --")) + $objects->names();
	}
	
	function update_menu_images($args = array())
	{
		extract($args);
		$imgar = $meta["menu_images"];

		// rewire the uploaded images as connected and selected
		foreach(safe_array($_FILES) as $name => $upf)
		{
			if (substr($name, 0, 4) == "mimg" && is_uploaded_file($upf["tmp_name"]))
			{
				$nm = substr($name, 5);
				$im = get_instance(CL_IMAGE);
				$imd = $im->add_upload_image($name, $args["obj_inst"]->id(), $imgar[$nm]["image_id"]);
				$args["obj_inst"]->connect(array(
					"to" => $imd["id"],
					"type" => "RELTYPE_IMAGE"
				));
				$img[$nm] = $imd["id"];
			}
		}

		$num_menu_images = $this->cfg["num_menu_images"];
		$t = get_instance(CL_IMAGE);

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
		$request = &$arr["request"];
		if ($request["group"] == "import_export")
		{
			$menu_export = get_instance("export/menu_export");
			$menu_export->export_menus(array(
				"id" => $arr["obj_inst"]->id(),
				"ex_menus" => $request["ex_menus"],
				"allactive" => $request["allactive"],
				"ex_icons" => $request["ex_icons"],
			));
		};
	}

	////
	// !tagastab array adminni featuuridest, mida sobib ette s88ta aw_template->picker funxioonile
	function get_feature_sel()
	{
		$ret = array("0" => t("--vali--"));
		$prog = aw_ini_get("programs");
		reset($prog);
		while (list($id,$v) = each($prog))
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
		$orb = get_instance("core/orb/orb");
		return array("0" => t("--vali--")) + $orb->get_classes_by_interface(array("interface" => "public"));
	}

	function get_menu_keywords($id)
	{
		$m = obj($id);
		$kws = new object_list($m->connections_from(array("to.class_id" => CL_KEYWORD)));
		return $this->make_keys($kws->ids());

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

		$tmp = obj($id);
		$tmp->set_meta("has_kwd_rels",$has_kwd_rels);
		$tmp->save();
	}


	function callback_on_submit_relation_list($args)
	{
		$obj =& obj($args["id"]);
		$co = $obj->connections_from(array(
			"type" => "RELTYPE_IP",
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
			"caption" => t("ID"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"talign" => "center",
		));
/*		$t->define_field(array(
			"name" => "check",
			"caption" => t("kustuta"),
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
			"caption" => t("ID"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"talign" => "center",
		));
		$t->define_field(array(
			"name" => "check",
			"caption" => t("k.a. alammen&uuml;&uuml;d"),
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));

		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_DOCS_FROM_MENU",
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
		$f = $alias;
		if (!$f["target"])
		{
			return "";
		}
		$target = $f;

		if (!$this->can("view", $target["to"]))
		{
			return "";
		}

		$o = obj($target["to"]);

		if ($o->prop("link") != "")
		{
			$link = $o->prop("link");
		}	
		else
		{
			$link = $this->cfg["baseurl"]."/".$target["to"];
		}
												  
		$ltarget = "";
		if ($o->prop("target"))
		{
			$ltarget = "target='_blank'";
		}

		if (aw_global_get("section") == $target["to"])
		{
			$ret = sprintf("<a $ltarget class=\"sisutekst-sel\" href='$link'>%s</a>",$target["name"]);
		}
		else
		{
			$ret = sprintf("<a $ltarget class=\"sisutekst\" href='$link'>%s</a>",$target["name"]);
		}
		return $ret;
	}

	////
	// !this must set the content for subtemplates in main.tpl
	// params
	//	inst - instance to set variables to
	//	content_for - array of templates to get content for
	//	currently handles SEEALSO_DOCUMENT only
	function on_get_subtemplate_content($arr)
	{
		$str = array();
		$sect = obj(aw_global_get("section"));
		if ($sect->class_id() != CL_MENU)
		{
			$sect = obj($sect->parent());
		}
		$sad_opts = $sect->meta("sad_opts");
		foreach($sect->connections_from(array("type" => "RELTYPE_SEEALSO_DOC")) as $c)
		{
			$tpl = isset($sad_opts[$c->prop("to")]["tpl"]) ? $sad_opts[$c->prop("to")]["tpl"] : "SEEALSO_DOCUMENT";
			if ($c->prop("to.lang_id") == aw_global_get("lang_id"))
			{
				$str[$tpl][$c->prop("to")] = $c->prop("to.jrk");
			}
		}

		// also parents
		$pt = $sect->path();
		foreach($pt as $o)
		{
			if ($o->id() == $sect->id())
			{
				continue;
			}
			
			$sad_opts = $o->meta("sad_opts");
			foreach(safe_array($sad_opts) as $docid => $dat)
			{
				if ($dat["submenus"] == $docid)
				{
					$tpl = isset($dat["tpl"]) ? $dat["tpl"] : "SEEALSO_DOCUMENT";
					$doco = obj($docid);
					if ($doco->lang_id() == aw_global_get("lang_id"))
					{
						$str[$tpl][$docid] = $doco->ord();
					}
				}
			}
		}

		$tmp = array();
		foreach($str as $tpl => $dat)
		{
			asort($dat);

			foreach($dat as $did => $ord)
			{
				$d_tpl = "seealso_document.tpl";
				if (!empty($this->cfg["seealso_doc_tpl_names"][$tpl]))
				{
					$d_tpl = $this->cfg["seealso_doc_tpl_names"][$tpl];
				}
				$d = get_instance(CL_DOCUMENT);
				$tmp[$tpl] .= $d->gen_preview(array(
					"docid" => $did,
					"tpl" => $d_tpl
				));
			}
		}
		foreach($tmp as $tpl => $docs)
		{
			$arr["inst"]->vars(array(
				$tpl => $docs
			));
		}
	}

	function _init_seealso_docs_t(&$t)
	{
		$t->define_field(array(
			"name" => "doc",
			"caption" => t("Dokument"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "doc_subs",
			"caption" => t("ka alammen&uuml;&uuml;de juures"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "tpl",
			"caption" => t("Vali asukoht"),
			"align" => "center"
		));
	}

	function _do_seealso_docs_t($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_seealso_docs_t($t);

		$sad_opts = $arr["obj_inst"]->meta("sad_opts");
		$tpls = aw_ini_get("menu.seealso_doc_tpls");

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_SEEALSO_DOC")) as $c)
		{
			$cto = $c->prop("to");
			$t->define_data(array(
				"doc" => html::get_change_url($cto, array(), $c->prop("to.name")),
				"doc_subs" => html::checkbox(array(
					"name" => "sad_opts[".$cto."][submenus]",
					"value" => $cto,
					"checked" => $sad_opts[$cto]["submenus"]
				)),
				"tpl" => html::select(array(
					"name" => "sad_opts[".$cto."][tpl]",
					"options" => $tpls,
					"selected" => $sad_opts[$cto]["tpl"]
				))
			));
		}
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	function kw_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		
		$tb->add_button(array(
			"name" => "new_kw",
			"tooltip" => t("M&auml;rks&otilde;na"),
			"url" => html::get_new_url(CL_KEYWORD, $arr["obj_inst"]->id(), array("return_url" => get_ru())),
			"img" => "new.gif",
		));
	}

	/** toggles site editing display
		@attrib name=toggle_site_editing is_public=1 caption="N&auml;ita &auml;ra n&auml;ita saidi muutmise linke"
	**/
	function toggle_site_editing($arr)
	{
		$_SESSION["no_display_site_editing"] = !$_SESSION["no_display_site_editing"];
		return aw_ini_get("baseurl");
	}

	function do_db_upgrade($t, $f)
	{
		switch($f)
		{
			case "set_doc_content_type":
				$this->db_query("ALTER TABLE menu add set_doc_content_type int");
				return true;
				break;
		}
	}
};
?>
