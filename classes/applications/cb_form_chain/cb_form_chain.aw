<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/cb_form_chain/cb_form_chain.aw,v 1.11 2005/07/11 12:56:12 kristo Exp $
// cb_form_chain.aw - Vormiahel 
/*

@classinfo syslog_type=ST_CB_FORM_CHAIN relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta 
@default method=serialize

	@property confirm_sep_page type=checkbox ch_value=1
	@caption Kinnitusvaade enne saatmist

@default group=cfs_tbl

	@property cfs type=table no_caption=1

@default group=cfs_headers

	@property cfs_headers type=table no_caption=1

@default group=cfs_entry_tbl

	@property cfs_entry_tbl type=table no_caption=1

@default group=mail_settings_general

	@property mail_to type=textbox 
	@caption Kellele

	@property mail_to_form type=relpicker reltype=RELTYPE_CF
	@caption Vorm, milles on saaja aadress

	@property mail_to_prop type=select 
	@caption Element, milles on saaja aadress	

	@property mail_from_addr type=textbox
	@caption Kellelt (aadress)

	@property mail_from_name type=textbox
	@caption Kellelt (nimi)

	@property mail_subj type=textbox
	@caption Teema

@default group=mail_settings_confirm

	@property send_confirm_mail type=checkbox ch_value=1
	@caption Saada tellijale kinnitusmeil

	@property confirm_mail_subj type=textbox
	@caption Kinnitusmeili subjekt

	@property confirm_mail type=textarea rows=20 cols=50
	@caption Kinnitusmeili sisu

	@property confirm_mail_to_form type=relpicker reltype=RELTYPE_CF
	@caption Vorm, milles on saaja aadress

	@property confirm_mail_to_prop type=select 
	@caption Element, milles on saaja aadress	

@default group=entry_settings

	@property entry_folder type=relpicker reltype=RELTYPE_ENTRY_FOLDER
	@caption Andmete kataloog
	@comment Vaikimisi salvestatakse seadete objekti alla

	@property entry_name_form type=relpicker reltype=RELTYPE_CF
	@caption Andmete nime vorm

	@property entry_name_el type=select multiple=1
	@caption Andmete nime elemendid

@default group=entries_unc,entries_con

	@property entry_tb type=toolbar no_caption=1
	@caption Andmete toolbar

	@property entry_table type=table no_caption=1
	@caption Andmed

@groupinfo cfs caption="Vormid"
	@groupinfo cfs_tbl caption="Vormid" parent=cfs
	@groupinfo cfs_headers caption="Pealkirjad" parent=cfs
	@groupinfo cfs_entry_tbl caption="Andmete tabel" parent=cfs

@groupinfo mail_settings caption="Meiliseaded"
	@groupinfo mail_settings_general caption="Tellimuse meil" parent=mail_settings
	@groupinfo mail_settings_confirm caption="Kinnitusmeil" parent=mail_settings

@groupinfo entry_settings caption="Andmete seaded"

@groupinfo entries caption="Andmed"
	@groupinfo entries_unc caption="Kinnitamata" parent=entries submit=no
	@groupinfo entries_con caption="Kinnitatud" parent=entries submit=no


@reltype CF value=1 clid=CL_WEBFORM
@caption veebivorm

@reltype REP_CTR value=2 clid=CL_CFGCONTROLLER
@caption vormide kordamise kontroller

@reltype ENTRY_FOLDER value=3 clid=CL_MENU
@caption andmete kataloog

@reltype DEF_CTR value=4 clid=CL_CFGCONTROLLER
@caption default andmete kontroller

*/

class cb_form_chain extends class_base
{
	function cb_form_chain()
	{
		$this->init(array(
			"tpldir" => "applications/cb_form_chain",
			"clid" => CL_CB_FORM_CHAIN
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "cfs":
				$this->_cfs($arr);
				break;

			case "cfs_headers":
				$this->_cfs_headers($arr);
				break;

			case "confirm_mail_to_prop":
				if (!$arr["obj_inst"]->prop("confirm_mail_to_form"))
				{
					return PROP_IGNORE;
				}

				$prop["options"] = $this->get_el_picker_from_wf(obj($arr["obj_inst"]->prop("confirm_mail_to_form")));
				break;

			case "mail_to_prop":
				if (!$arr["obj_inst"]->prop("mail_to_form"))
				{
					return PROP_IGNORE;
				}

				$prop["options"] = $this->get_el_picker_from_wf(obj($arr["obj_inst"]->prop("mail_to_form")));
				break;

			case "entry_name_el":
				if (!$arr["obj_inst"]->prop("entry_name_form"))
				{
					return PROP_IGNORE;
				}

				$prop["options"] = $this->get_el_picker_from_wf(obj($arr["obj_inst"]->prop("entry_name_form")));
				break;

			case "entry_tb";
				$this->_entry_tb($arr);
				break;

			case "entry_table":
				$this->_entry_table($arr);
				break;

			case "cfs_entry_tbl":
				$this->_cfs_entry_tbl($arr);
				break;
		};
		return $retval;
	}

	function get_el_picker_from_wf($wf)
	{
		$ot = $wf->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");

		$cf = get_instance(CL_CFGFORM);
		$props = $cf->get_props_from_ot(array(
			"ot" => $ot->id()
		));
		$ps = array("" => t("--vali--"));
		foreach($props as $pn => $pd)
		{
			$ps[$pn] = $pd["caption"];
		}
		return $ps;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "cfs":
				$arr["obj_inst"]->set_meta("d", $arr["request"]["d"]);
				break;

			case "cfs_headers":
				$arr["obj_inst"]->set_meta("cfs_headers", $arr["request"]["hdrs"]);
				break;

			case "cfs_entry_tbl":
				$arr["obj_inst"]->set_meta("entry_tbl", $arr["request"]["t"]);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _init_cfs_t(&$t)
	{
		$t->define_field(array(
			"name" => "form",
			"caption" => t("Vorm"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "page",
			"caption" => t("Lehek&uuml;lg"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "repeat",
			"caption" => t("Korduv"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "repeat_fix",
			"caption" => t("Fikseeritud ridade arv"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "el_table",
			"caption" => t("Elemendid tabelina"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "data_table",
			"caption" => t("Andmed tabelina"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "repeat_ctr",
			"caption" => t("Korduste kontroller"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "def_ctr",
			"caption" => t("Default andmete kontroller"),
			"align" => "center"
		));
	}

	function _cfs($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cfs_t($t);

		$rep_ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_REP_CTR")));
		$reps = array("" => "") + $rep_ol->names();

		$def_ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_DEF_CTR")));
		$defs = array("" => "") + $def_ol->names();

		$d = safe_array($arr["obj_inst"]->meta("d"));
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CF")) as $c)
		{
			$o = $c->to();
			$rc = "";
			if ($d[$o->id()]["repeat"] == 1)
			{
				$rc = html::select(array(
					"name" => "d[".$o->id()."][rep_ctr]",
					"options" => $reps,
					"selected" => $d[$o->id()]["rep_ctr"]
				));
			}
			$t->define_data(array(
				"form" => html::get_change_url($o->id(), array("return_url" => get_ru()), parse_obj_name($c->prop("to.name"))),
				"page" => html::textbox(array(
					"size" => 5,
					"name" => "d[".$o->id()."][page]",
					"value" => $d[$o->id()]["page"]
				)),
				"ord" => html::textbox(array(
					"size" => 5,
					"name" => "d[".$o->id()."][ord]",
					"value" => $d[$o->id()]["ord"]
				)),
				"repeat" => html::checkbox(array(
					"name" => "d[".$o->id()."][repeat]",
					"value" => 1,
					"checked" => $d[$o->id()]["repeat"] == 1 
				)),
				"repeat_fix" => html::checkbox(array(
					"name" => "d[".$o->id()."][repeat_fix]",
					"value" => 1,
					"checked" => $d[$o->id()]["repeat_fix"] == 1 
				)),
				"el_table" => html::checkbox(array(
					"name" => "d[".$o->id()."][el_table]",
					"value" => 1,
					"checked" => $d[$o->id()]["el_table"] == 1 
				)),
				"data_table" => html::checkbox(array(
					"name" => "d[".$o->id()."][data_table]",
					"value" => 1,
					"checked" => $d[$o->id()]["data_table"] == 1 
				)),
				"repeat_ctr" => $rc,
				"def_ctr" => html::select(array(
					"name" => "d[".$o->id()."][def_ctr]",
					"options" => $defs,
					"selected" => $d[$o->id()]["def_ctr"]
				))
			));
		}
		$t->set_sortable(false);
	}

	function _init_cfs_headers_t(&$t)
	{
		$t->define_field(array(
			"name" => "pg",
			"caption" => t("Lehek&uuml;lg"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "title",
			"caption" => t("Pealkiri"),
			"align" => "center"
		));
	}

	function _cfs_headers($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cfs_headers_t($t);

		$hdrs = safe_array($arr["obj_inst"]->meta("cfs_headers"));
		
		$pgs = $this->_get_page_list($arr["obj_inst"]);
		foreach($pgs as $pg)
		{
			$t->define_data(array(
				"pg" => $pg,
				"title" => html::textbox(array(
					"name" => "hdrs[$pg][name]",
					"value" => $hdrs[$pg]["name"]
				))
			));
		}
		$t->set_sortable(false);
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);

		if ($_GET["display"])
		{
			$i = get_instance(CL_CB_FORM_CHAIN_ENTRY);
			return $i->show(array(
				"id" => $_SESSION["cbfc_last_entry"]
			));
		}

		if ($_GET["do_confirm"] == 1)
		{
			return $this->_do_confirm_view($ob);
		}

		$page = $this->_get_page($ob);

		$forms = $this->_get_forms_for_page($ob, $page);

		$html = $this->_draw_forms($ob, $forms);
		unset($_SESSION["cbfc_errors"]);
		return $html;
	}

	function _get_page_list($o)
	{
		$d = safe_array($o->meta("d"));
		$pgs = array();
		foreach($d as $form => $dat)
		{
			$pgs[$dat["page"]] = $dat["page"];
		}

		asort($pgs);

		return $pgs;
	}

	function _get_page($o)
	{
		$pgs = $this->_get_page_list($o);

		if (!empty($_GET["cbfc_pg"]) && isset($pgs[$_GET["cbfc_pg"]]))
		{
			return $_GET["cbfc_pg"];
		}
		return reset($pgs);
	}

	function _get_forms_for_page($o, $page)
	{
		$d = safe_array($o->meta("d"));
		$forms = array();
		foreach($d as $form => $dat)
		{
			if ($dat["page"] == $page)
			{
				$fd = array(
					"form" => $form,
					"rep" => $dat["repeat"],
					"rep_cnt" => 1,
					"el_table" => $dat["el_table"],
					"data_table" => $dat["data_table"],
					"repeat_fix" => $dat["repeat_fix"]
				);
				if ($dat["repeat"] && is_oid($dat["rep_ctr"]) && $this->can("view", $dat["rep_ctr"]))
				{
					$ci = get_instance(CL_CFGCONTROLLER);
					$fd["rep_cnt"] = $ci->check_property($dat["rep_ctr"], $form, $form, $form, $form, $form);
				}

				if (is_oid($dat["def_ctr"]) && $this->can("view", $dat["def_ctr"]))
				{
					$fd["def_ctr"] = $dat["def_ctr"];
				}
				$forms[] = $fd;
			}
		}
		return $forms;
	}

	function _draw_forms($o, $forms)
	{
		$cf = get_instance(CL_CFGFORM);
		$this->read_template("show_form.tpl");

		$this->_draw_page_titles($o);

		foreach($forms as $form_dat)
		{
			$wf = obj($form_dat["form"]);
			$ot = $wf->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");

			$props = $cf->get_props_from_ot(array(
				"ot" => $ot->id()
			));
			$this->_apply_view_controllers($props, $wf, $i);

			$this->vars(array(
				"form_name" => $wf->name()
			));
			$html .= $this->parse("FORM_HEADER");

			if ($form_dat["data_table"])
			{
				$this->_display_entry_data_table($form_dat, $props, $wf, $o);

				if (($idx = $this->_can_show_edit_form($form_dat)))
				{
					$html .= $this->_html_from_props($form_dat, $props, $ot, $wf, $o, $idx > 0 ? $idx-1 : NULL);
				}
			}
			else
			{
				if ($form_dat["el_table"] == 1)
				{
					$html .= $this->_html_table_from_props($form_dat, $props, $ot, $wf, $o);
				}
				else
				{
					$html .= $this->_html_from_props($form_dat, $props, $ot, $wf, $o);
				}
			}
		}

		$this->vars(array(
			"form" => $html,
			"reforb" => $this->mk_reforb("submit_data", array("id" => $o->id(), "ret" => post_ru(), "cbfc_pg" => $this->_get_page($o), "edit_num" => $_GET["edit_num"])),
		));

		$this->_do_prev_next_pages($o);

		return $this->parse();
	}

	function _do_prev_next_pages($o)
	{
		$pgs = array_values($this->_get_page_list($o));
		$cur_pg = $this->_get_page($o);

		$np = false;
		for($i = 0; $i < count($pgs); $i++)
		{
			if ($pgs[$i+1] == $cur_pg)
			{
				$this->vars(array(
					"prev_link" => aw_url_change_var(array("cbfc_pg" => $pgs[$i], "do_confirm" => NULL, "display" => NULL, "edit_num" => NULL))
				));
				$this->vars(array(
					"PREV_PAGE" => $this->parse("PREV_PAGE")
				));
			}
			if ($pgs[$i-1] == $cur_pg)
			{
				$this->vars(array(
					"next_link" => aw_url_change_var(array("cbfc_pg" => $pgs[$i], "display" => NULL, "edit_num" => NULL))
				));
				$this->vars(array(
					"NEXT_PAGE" => $this->parse("NEXT_PAGE")
				));
				$np = true;
			}
		}
		
		$fd = $this->_get_forms_for_page($o, $cur_pg);
		$ed = max(1, $_GET["edit_num"]);
		if ($fd[0]["repeat_fix"] == 1 && $ed < $fd[0]["rep_cnt"])
		{
			$this->vars(array(
				"next_link" => aw_url_change_var(array("edit_num" => $ed+1))
			));
			$this->vars(array(
				"NEXT_PAGE" => $this->parse("NEXT_PAGE")
			));
			$np = true;
		}

		if (!$np)
		{
			if ($o->prop("confirm_sep_page") == 1)
			{
				$this->vars(array(
					"next_link" => aw_url_change_var(array("do_confirm" => 1, "display" => NULL, "edit_num" => NULL))
				));
				$this->vars(array(
					"NEXT_PAGE" => $this->parse("NEXT_PAGE"),
				));
			}
			else
			{
				$this->vars(array(
					"CONFIRM" => $this->parse("CONFIRM"),
				));
			}
		}
	}

	/**

		@attrib name=submit_data nologin="1"

	**/
	function submit_data($arr)
	{
		// save data to session during the form filling
		// then only when the user clicks confirm, save to objects

		// but we gots to check submit controllers here :(
		$ps = array();

		$ctr_i = get_instance("cfg/cfgcontroller");

		$errors = array();

		$_SESSION["no_cache"] = 1;

		foreach(safe_array($arr) as $k => $data)
		{
			if ($k{0} == "f" && $k{1} == "_")
			{
				// this is form entry
				list($tmp, $wf_id, $num) = explode("_", $k);

				$wf = obj($wf_id);
				if (!isset($ps[$wf_id]))
				{
					$wf_i = $wf->instance();
					$ps[$wf_id] = $wf_i->get_props_from_wf(array("id" => $wf_id));
				}

				foreach($ps[$wf_id] as $pn => $pd)
				{
					$ctr = safe_array($pd["controllers"]);
					if (count($ctr))
					{
						$ok = true;
						foreach($ctr as $ctr_id)
						{
							$pd["value"] = &$data[$pn];
							if ($ctr_i->check_property($ctr_id, 0, $pd, $arr, $data, $wf) != PROP_OK)
							{
								$ok = false;
								$co = obj($ctr_id);

								$errmsg = str_replace("%caption", $pd["caption"], $co->prop("errmsg"));

								$errors[$wf_id][$num][$pn] = $errmsg;
							}
						}
					}
				}

				$_SESSION["cbfc_data"][$wf_id][$num] = $data;
			}
		}

		if (count($errors))
		{
			$_SESSION["cbfc_errors"] = $errors;
			return $arr["ret"];
		}

		if ($arr["confirm"] != "")
		{
			return $this->submit_confirm($arr);
		}
		if ($arr["goto_next"] != "")
		{
			$fd = $this->_get_forms_for_page(obj($arr["id"]), $arr["cbfc_pg"]);
			$ed = max(1, $arr["edit_num"]);
			if ($fd[0]["repeat_fix"] == 1 && $ed < $fd[0]["rep_cnt"])
			{
				return aw_url_change_var("edit_num", $ed+1, $arr["ret"]);
			}

			$pgs = $this->_get_page_list(obj($arr["id"]));
			$prev = false;
			foreach($pgs as $pg)
			{
				if ($prev == $arr["cbfc_pg"])
				{
					return aw_url_change_var("edit_num", NULL, aw_url_change_var("cbfc_pg", $pg, $arr["ret"]));
				}
				$prev = $pg;
			}
			return aw_url_change_var("edit_num", NULL, aw_url_change_var("do_confirm", 1, $arr["ret"]));
		}
		else
		if ($arr["goto_prev"] != "")
		{
			$fd = $this->_get_forms_for_page(obj($arr["id"]), $arr["cbfc_pg"]);
			$ed = max(1, $arr["edit_num"]);
			if ($fd[0]["repeat_fix"] == 1 && $ed > 1)
			{
				return aw_url_change_var("edit_num", $ed-1, $arr["ret"]);
			}

			$pgs = $this->_get_page_list(obj($arr["id"]));
			$prev = false;
			foreach($pgs as $pg)
			{
				if ($pg == $arr["cbfc_pg"])
				{
					return aw_url_change_var("edit_num", NULL, aw_url_change_var("cbfc_pg", $prev, $arr["ret"]));
				}
				$prev = $pg;
			}
		}

		return $arr["ret"];
	}

	function _do_confirm_view($o)
	{
		$this->read_template("show_confirm.tpl");

		$form_str = "";

		// for each page
		$pgs = $this->_get_page_list($o);
		foreach($pgs as $pg)
		{
			// for each form on page
			$forms = $this->_get_forms_for_page($o, $pg);
			foreach($forms as $form_dat)
			{
				if ($form_dat["rep_cnt"] > 1)
				{
					$form_str .= $this->_display_data_table($o, $form_dat);
				}
				else
				{
					$form_str .= $this->_display_data($o, $form_dat);
				}
			}
		}

		$this->vars(array(
			"forms" => $form_str,
			"reforb" => $this->mk_reforb("submit_confirm", array("id" => $o->id(), "ret" => post_ru(), "cbfc_pg" => $this->_get_page($o))),
			"prev_link" => aw_url_change_var(array("display" => NULL, "do_confirm" =>  NULL))
		));

		return $this->parse();
	}

	/**

		@attrib name=submit_confirm nologin="1"

	**/
	function submit_confirm($arr)
	{
		$o = obj($arr["id"]);

		// save data from session to objects

		$_SESSION["no_cache"] = 1;
		
		// first, entry object
		$entry = obj();
		$entry->set_parent($this->_get_parent($o));
		$entry->set_class_id(CL_CB_FORM_CHAIN_ENTRY);
		$entry->set_name($this->_get_entry_name($o));
		$entry->save();

		// then for each form, data objects in entry object
		// for each page
		$pgs = $this->_get_page_list($o);
		foreach($pgs as $pg)
		{
			// for each form on page
			$forms = $this->_get_forms_for_page($o, $pg);
			foreach($forms as $form_dat)
			{
				$wf = obj($form_dat["form"]);

				for($i = 0; $i < $form_dat["rep_cnt"]; $i++)
				{
					$dat = $_SESSION["cbfc_data"][$form_dat["form"]][$i];

					if ($this->_is_empty($dat))
					{
						continue;
					}

					$this->_create_entry_data_obj($wf, $entry, $dat);
				}
			}
		}
		
		// send confirm and order mails
		$this->_send_order_mail($o, $entry);
		$this->_send_confirm_mail($o, $entry);

		unset($_SESSION["cbfc_data"]);

		$_SESSION["cbfc_last_entry"] = $entry->id();

		return aw_url_change_var(
			"cbfc_pg", 
			NULL, 
			aw_url_change_var(
				"do_confirm", 
				NULL, 
				aw_url_change_var(
					"display", 
					1, 
					$arr["ret"]
				)
			)
		);
	}

	function _get_entry_data_name($wf, $data)
	{
		$name = array();
		foreach(safe_array($wf->prop("obj_name")) as $p)
		{
			$name[] = $data[$p];
		}
		return join(" ", $name);
	}

	function _get_entry_name($o)
	{
		$name = array();

		$f = $o->prop("entry_name_form");
		if (!$f)
		{
			return "";
		}

		foreach(safe_array($o->prop("entry_name_el")) as $el)
		{
			$name[] = $_SESSION["cbfc_data"][$f][0][$el];
		}

		return join(" ", $name);
	}

	function _is_empty($arr)
	{
		foreach($arr as $k => $v)
		{
			if ($v != "")
			{
				return false;
			}
		}
		return true;
	}

	function _create_entry_data_obj($wf, $entry, $dat)
	{
		$o = obj();
		$o->set_class_id(CL_REGISTER_DATA);
		$o->set_parent($entry->id());

		// set cfgform_id and object type to meta
		$cf = $wf->get_first_obj_by_reltype("RELTYPE_CFGFORM");
		$o->set_meta("cfgform_id", $cf->id());

		$ot = $wf->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");
		$o->set_meta("object_type", $ot->id());
	
		$o->set_meta("webform_id", $wf->id());

		$o->set_name($this->_get_entry_data_name($wf, $dat));

		$props = $o->get_property_list();

		$metaf = array();
		foreach($dat as $k => $v)
		{
			if ($props[$k]["type"] == "date_select")
			{
				$v = date_edit::get_timestamp($v);
			}
			else
			if ($props[$k]["type"] == "text")
			{
				$metaf[$k] = $v;
			}
			$o->set_prop($k, $v);
		}
		$o->set_meta("metaf", $metaf);
		$o->save();

		$entry->connect(array(
			"to" => $o->id(),
			"reltype" => "RELTYPE_ENTRY"
		));
	}

	function _send_order_mail($o, $entry)
	{
		if (!$o->prop("mail_to"))
		{
			return;
		}

		$i = $entry->instance();
		$html = $i->show(array(
			"id" => $entry->id()
		));

		$to_arr = array();
		if ($o->prop("mail_to") != "")
		{
			$to_arr = explode(",", $o->prop("mail_to"));
		}

		if ($o->prop("mail_to_form"))
		{
			foreach($entry->connections_from(array("type" => "RELTYPE_ENTRY")) as $c)
			{
				$do = $c->to();
				if ($do->meta("webform_id") == $o->prop("mail_to_form"))
				{
					break;
				}
				$do = NULL;
			}

			if ($do)
			{
				$d_props = $do->get_property_list();
				$to_prop = $d_props[$o->prop("mail_to_prop")];
				if ($to_prop["type"] == "classificator")
				{
					$v = $do->prop($to_prop["name"]);
					if (is_oid($v) && $this->can("view", $v))
					{
						$v = obj($v);
						if ($v->comment() != "")
						{
							$to_arr[] = $v->comment();
						}
					}
				}
				else
				{
					$to_arr[] = $entry->prop_str($to_prop["name"]);
				}
			}
		}


		$mailer = get_instance("protocols/mail/aw_mail");
		foreach($to_arr as $to)
		{
			$mailer->clean();
			$mailer->create_message(array(
				"froma" => $o->prop("mail_from_addr"),
				"fromn" => $o->prop("mail_from_name"),
				"subject" => $o->prop("mail_subj"),
				"to" => $to,
				"body" => "see on html kiri",
			));
			$mailer->htmlbodyattach(array(
				"data" => $html,
			));
			$mailer->gen_mail();
		}
	}

	function _send_confirm_mail($o, $entry)
	{
		if (!$o->prop("send_confirm_mail"))
		{
			return;
		}

		$form = $o->prop("confirm_mail_to_form");
		if (!is_oid($form) || !$this->can("view", $form))
		{
			return;
		}
		
		foreach($entry->connections_from(array("type" => "RELTYPE_ENTRY")) as $c)
		{
			$d = $c->to();
			if ($d->meta("webform_id") == $form)
			{
				break;
			}
			$d = false;
		}

		if (!$d)
		{
			return;
		}

		$from = $o->prop("mail_from_addr");
		if ($o->prop("mail_from_name") != "")
		{
			$from = $o->prop("mail_from_name")." <$from>";
		}

		$d_props = $d->get_property_list();
		$to_prop = $d_props[$o->prop("confirm_mail_to_prop")];
		if ($to_prop["type"] == "classificator")
		{
			$v = $d->prop($to_prop["name"]);
			if (is_oid($v) && $this->can("view", $v))
			{
				$v = obj($v);
				$to = $v->comment();
			}
		}
		else
		{
			$to = $d->prop_str($to_prop["name"]);
		}
		if ($to != "")
		{
			send_mail(
				$to,	// to
				$o->prop("confirm_mail_subj"), // subj 
				$o->prop("confirm_mail"), // msg
				"From: $from\n" // headers
			);
		}
	}

	function _display_data_table($o, $fd)
	{
		// make table via component
		classload("vcl/table");
		$t = new aw_table(array("layout" => "generic"));

		$wf = get_instance(CL_WEBFORM);
		$props = $wf->get_props_from_wf(array(
			"id" => $fd["form"]
		));

		foreach($props as $pn => $pd)
		{
			$t->define_field(array(
				"name" => $pn,
				"caption" => $pd["caption"],
				"align" => "center"
			));
		}
		// go over all datas
		for($i = 0; $i < $fd["rep_cnt"]; $i++)
		{
			$inf = $_SESSION["cbfc_data"][$fd["form"]][$i];
			if (!$this->_is_empty($inf))
			{
				$row = array();
				foreach($props as $pn => $pd)
				{
					$row[$pn] = $this->_value_from_data($pd, $inf[$pn]);
				}
				$t->define_data($row);
			}
		}

		$ret = $t->draw();
		return $ret;
	}

	function _display_data($o, $fd)
	{
		$wf = get_instance(CL_WEBFORM);
		$props = $wf->get_props_from_wf(array(
			"id" => $fd["form"]
		));
		$this->_apply_view_controllers($props, obj($fd["form"]), 0);
		
		$inf = $_SESSION["cbfc_data"][$fd["form"]][0];

		foreach($props as $pn => $pd)
		{
			if ($props[$pn]["type"] == "date_select" && $inf[$pn] == 0)
			{
				continue;
			}
			$val = $this->_value_from_data($pd,$inf[$pn]);

			$this->vars(array(
				"caption" => $pd["caption"],
				"value" => $val == "" ? "&nbsp;" : $val
			));
	
			$ret .= $this->parse("PROPERTY");
		}

		$this->vars(array(
			"PROPERTY" => $ret
		));
		return $this->parse("FORM");
	}

	function _entry_tb($arr)
	{
		$tb =& $arr["prop"]["toolbar"];

		if ($arr["request"]["group"] != "entries_con")
		{
			$tb->add_button(array(
				"name" => "confirm",
				"img" => "save.gif",
				"tooltip" => t("Kinnita"),
				"action" => "confirm_entries"
			));
		}

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"confirm" => t("Oled kindel et soovid sisestusi kustutada?"),
			"action" => "delete_entries"
		));
	}

	function _init_entry_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y / H:i"
		));

		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _entry_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_entry_table($t);

		$ol = new object_list(array(
			"parent" => $this->_get_parent($arr["obj_inst"]),
			"class_id" => CL_CB_FORM_CHAIN_ENTRY,
			"confirmed" => $arr["request"]["group"] == "entries_con" ? 1 : new obj_predicate_not(1)
		));
		$t->data_from_ol($ol, array("change_col" => "name"));
		$t->set_default_sortby("created");
		$t->set_default_sorder("desc");
	}

	/**

		@attrib name=confirm_entries

	**/
	function confirm_entries($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array(
				"oid" => $arr["sel"]
			));
			$ol->set_prop("confirmed", 1);
		}
		return $arr["post_ru"];
	}

	/**

		@attrib name=delete_entries

	**/
	function delete_entries($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array(
				"oid" => $arr["sel"]
			));
			$ol->delete();
		}
		return $arr["post_ru"];
	}

	function _get_parent($o)
	{
		if (is_oid($o->prop("entry_folder")) && $this->can("view", $o->prop("entry_folder")))
		{
			return $o->prop("entry_folder");
		}
		return $o->id();
	}

	function _value_from_data($pd, $val)
	{
		if ($pd["type"] == "classificator")
		{
			if (is_array($val))
			{
				if (count($val))
				{
					$ol = new object_list(array("oid" => $val));
					$val = join(", ", $ol->names());
				}
				else
				{
					$val = "";
				}
			}
			if (is_oid($val) && $this->can("view", $val))
			{
				$tmp = obj($val);
				$val = $tmp->name();
			}	
		}
		if ($pd["type"] == "date_select")
		{
			$val = date("d.m.Y", date_edit::get_timestamp($val));
		}

		return $val;
	}

	function _apply_view_controllers(&$props, $wf, $i)
	{
		foreach($props as $k => $v)
		{
			if (is_array($v["view_controllers"]) && count($v["view_controllers"]))
			{
				$ci = get_instance(CL_CFG_VIEW_CONTROLLER);
				foreach($v["view_controllers"] as $ctr_id)
				{
					$cpv = $ci->check_property($v, $ctr_id, $_SESSION["cbfc_data"][$wf->id()][$i], $props[$k]);
					if ($cpv == PROP_IGNORE)
					{
						unset($props[$k]);
						continue;
					}
				}
			}
		}
	}

	function _html_from_props($form_dat, $props, $ot, $wf, $o, $num_to_show = NULL)
	{
		$i = 0;
		if ($num_to_show !== NULL)
		{
			$i = $num_to_show;
			$form_dat["rep_cnt"] = $i+1;
		}

		for(; $i < $form_dat["rep_cnt"]; $i++)
		{
			if ((!is_array($_SESSION["cbfc_data"][$wf->id()][$i])) && $form_dat["def_ctr"])
			{
				$ci = get_instance(CL_CFGCONTROLLER);
				$ci->check_property($form_dat["def_ctr"], $wf->id(), $_SESSION["cbfc_data"][$wf->id()][$i], $_REQUEST, $i, $o);
			}

			$nps = array();
			// insert values as well
			foreach($props as $k => $v)
			{
				if (($_err = $_SESSION["cbfc_errors"][$wf->id()][$i][$k]) != "")
				{
					$nps[$k."_err"] = array(
						"name" => $k."_err",
						"type" => "text",
						"no_caption" => 1,
						"value" => "<span class=\"cbfcerror\">".$_err."</span>",
						"store" => "no"
					);
				}
				if ($v["subtitle"] != 1)
				{
					$v["value"] = $_SESSION["cbfc_data"][$wf->id()][$i][$k];
				}
				else
				{
					$v["value"] = nl2br($v["value"]);
				}

				// if it is a text type property, insert a hidden element after the text so that the value gets submitted
				if ($v["type"] == "text")
				{
					$v["value"] .= html::hidden(array(
						"name" => "f_".$wf->id()."_".$i."[$k]",
						"value" => $v["value"]
					));
				}

				unset($v["subtitle"]);
				$nps[$k] = $v;
			}
			$props = $nps;

			$rd = get_instance(CL_REGISTER_DATA);
			$els = $rd->parse_properties(array(
				"properties" => $props,
				"name_prefix" => "f_".$wf->id()."_".$i,
				"object_type_id" => $ot->id()
			));

			$htmlc = get_instance("cfg/htmlclient");
			$htmlc->start_output();
			foreach($els as $pn => $pd)
			{
				$htmlc->add_property($pd);
			}
			$htmlc->finish_output();

			$html .= $htmlc->get_result(array(
				"raw_output" => 1
			));
		}
		return $html;
	}

	function _html_table_from_props($form_dat, $props, $ot, $wf, $o)
	{
		// header
		$h = "";
		foreach($props as $pn => $pd)
		{
			$this->vars(array(
				"caption" => $pd["caption"]
			));
			$h .= $this->parse("HEADER");
		}

		for($i = 0; $i < $form_dat["rep_cnt"]; $i++)
		{
			$prefix = "f_".$wf->id()."_".$i;
			$els = "";

			foreach($props as $k => $v)
			{
				$props[$k]["value"] = $_SESSION["cbfc_data"][$wf->id()][$i][$k];
			}

			if ($this->_has_errors($wf->id(), $i))
			{
				$forms .= $this->_display_table_errors($props, $wf->id(), $i);
			}

			$rd = get_instance(CL_REGISTER_DATA);
			$pels = $rd->parse_properties(array(
				"properties" => $props,
				"name_prefix" => "f_".$wf->id()."_".$i,
				"object_type_id" => $ot->id()
			));

			foreach($pels as $pn => $pd)
			{
				switch($pd["type"])
				{
					case "date_select":
						$el = html::date_select($pd);
						break;

					case "select":
						$el = html::select($pd);
						break;

					default:
						$pd["size"] = 20;
						$el = html::textbox($pd);
						break;
				}

				$this->vars(array(
					"element" => $el
				));
				$els .= $this->parse("ELEMENT");
			}

			$this->vars(array(
				"ELEMENT" => $els
			));
			$forms .= $this->parse("FORM");
		}

		$this->vars(array(
			"HEADER" => $h,
			"FORM" => $forms
		));

		return $this->parse("TABLE_FORM");
	}

	function _get_titles($o)	
	{
		$hdrs = safe_array($o->meta("cfs_headers"));
		$ret = array();
		foreach($hdrs as $pg => $i)
		{
			$ret[$pg] = $i["name"];
		}
		return $ret;
	}
	
	function _draw_page_titles($o)
	{
		$titles = $this->_get_titles($o);
		$page = $this->_get_page($o);

		$ts = array();
		foreach($titles as $pg => $title)
		{
			$this->vars(array(
				"title" => $title
			));

			if ($pg == $page)
			{
				$ts[] = $this->parse("TITLE_SEL");
			}
			else
			{
				$ts[] = $this->parse("TITLE");
			}
		}

		$this->vars(array(
			"TITLE" => join($this->parse("TITLE_SEP"), $ts),
			"TITLE_SEL" => "",
			"TITLE_SEP" => ""
		));
	}

	function _display_table_errors($pels, $wf_id, $i)
	{
		$els = "";
		foreach($pels as $pn => $pd)
		{
			$this->vars(array(
				"element" => "<font color=\"red\">".$_SESSION["cbfc_errors"][$wf_id][$i][$pn]."</font>"
			));
			$els .= $this->parse("ELEMENT");
		}
		$this->vars(array(
			"ELEMENT" => $els
		));
		return $this->parse("FORM");
	}

	function _has_errors($wf_id, $i)
	{
		foreach($_SESSION["cbfc_errors"][$wf_id][$i] as $k => $v)
		{
			if ($v != "")
			{
				return true;
			}
		}
		return false;
	}

	function _display_entry_data_table($form_dat, $props, $wf, $o)
	{
		$dat = safe_array($o->meta("entry_tbl"));

		$nprops = array();
		// for all entries for this form
		$row = "";
		for($i = 0; $i < $form_dat["rep_cnt"]; $i++)
		{
			// show row
			$col = "";
			foreach($props as $pn => $pd)
			{
				if ($dat[$wf->id()][$pn]["show"] == 1)
				{
					$this->vars(array(
						"content" => $this->_value_from_data($pd, $_SESSION["cbfc_data"][$wf->id()][$i][$pn])
					));
					$col .= $this->parse("DT_COL");
					$nprops[$pn] = $pd;
				}
			}

			$this->vars(array(
				"content" => html::href(array(
					"url" => aw_url_change_var("edit_num", $i+1),
					"caption" => t("Muuda")
				))
			));
			$col .= $this->parse("DT_COL");

			$this->vars(array(
				"DT_COL" => $col
			));
			$row .= $this->parse("DT_ROW");
		}
		

		$this->vars(array(
			"DT_HEADER" => $this->_get_data_table_header($nprops),
			"DT_ROW" => $row
		));

		$this->vars(array(
			"DATA_TABLE" => $this->parse("DATA_TABLE")
		));
	}

	function _get_data_table_header($props)
	{
		// show header
		$header = "";
		foreach($props as $pn => $pd)
		{
			$this->vars(array(
				"col_name" => $pd["caption"]
			));
			$header .= $this->parse("DT_HEADER");
		}

		$this->vars(array(
			"col_name" => t("Muuda")
		));
		$header .= $this->parse("DT_HEADER");

		return $header;
	}

	function _can_show_edit_form($form_dat)
	{
		return max($_GET["edit_num"], 1);
	}

	function _init_cfs_entry_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "prop",
			"caption" => t("Omadus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "in_tbl",
			"caption" => t("N&auml;ita tabelis"),
			"align" => "center"
		));
	}

	function _cfs_entry_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cfs_entry_tbl($t);

		$dat = safe_array($arr["obj_inst"]->meta("entry_tbl"));
		$d = safe_array($arr["obj_inst"]->meta("d"));

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CF")) as $c)
		{
			if ($d[$c->prop("to")]["data_table"] == 1)
			{
				$els = $this->get_el_picker_from_wf($c->to());
				foreach($els as $pn => $pc)
				{
					if ($pn != "")
					{
						$t->define_data(array(
							"prop" => $pc,
							"in_tbl" => html::checkbox(array(
								"name" => "t[".$c->prop("to")."][$pn][show]",
								"value" => 1,
								"checked" => ($dat[$c->prop("to")][$pn]["show"] == 1)
							))
						));
					}
				}
			}
		}

		$t->set_sortable(false);
	}
}
?>
