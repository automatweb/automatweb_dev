<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/site_translation.aw,v 1.15 2005/03/23 10:31:35 kristo Exp $
// site_translation.aw - Saidi tõlge 
/*

@classinfo syslog_type=ST_SITE_TRANSLATION relatiomgr=yes

@default table=objects
@default group=general

@property utr_toolbar group=utr_day,utr_week,utr_month,utr_all type=toolbar  store=no no_caption=1 editonly=1
@caption TB

@property tr_toolbar group=tr_day,tr_week,tr_month,tr_all type=toolbar  store=no no_caption=1 editonly=1
@caption TB

@property inp_toolbar group=ipr_day,ipr_week,ipr_month,ipr_all type=toolbar  store=no no_caption=1 editonly=1
@caption TB

@property baselang type=select field=meta method=serialize
@caption Baaskeel

@property targetlang type=select field=meta method=serialize
@caption Sihtkeel

@property targetlang_all type=checkbox ch_value=1 field=meta method=serialize
@caption Kas kasutada k&otilde;iki keeli sihtkeelteks

@property translated_all_langs type=checkbox ch_value=1 field=meta method=serialize
@caption Kas objekt ilmub T&otilde;lgitud tabi alla ainult siis kui on t&otilde;litud k&otilde;kidesse keeltesse

@property translated group=tr_day,tr_week,tr_month,tr_all type=text store=no no_caption=1
@caption Tõlgitud


@property untranslated group=utr_day,utr_week,utr_month,utr_all type=text store=no no_caption=1
@caption Tõlkimata

@property in_progress group=ipr_day,ipr_week,ipr_month,ipr_all type=text store=no no_caption=1
@caption T&ouml;&ouml;s


@groupinfo translated caption=Tõlgitud submit=no
@groupinfo untranslated caption=Tõlkimata submit=no
@groupinfo in_progress caption="T&ouml;&ouml;s" submit=no

@groupinfo utr_all caption="Kõik" parent=untranslated submit=no
@groupinfo utr_day caption="Viimane päev" parent=untranslated submit=no
@groupinfo utr_week caption="Viimane nädal" parent=untranslated submit=no
@groupinfo utr_month caption="Viimane kuu" parent=untranslated submit=no

@groupinfo ipr_all caption="Kõik" parent=in_progress submit=no
@groupinfo ipr_day caption="Viimane päev" parent=in_progress submit=no
@groupinfo ipr_week caption="Viimane nädal" parent=in_progress submit=no
@groupinfo ipr_month caption="Viimane kuu" parent=in_progress submit=no

@groupinfo tr_all caption="Kõik" parent=translated submit=no
@groupinfo tr_day caption="Viimane päev" parent=translated submit=no
@groupinfo tr_week caption="Viimane nädal" parent=translated submit=no
@groupinfo tr_month caption="Viimane kuu" parent=translated submit=no

@classinfo relationmgr=yes

@reltype CLASS value=1 clid=CL_OBJECT_TYPE
@caption klass

*/
class site_translation extends class_base
{
	function site_translation()
	{
		$this->init(array(
			"tpldir" => "translate/site_translation",
			"clid" => CL_SITE_TRANSLATION
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		$this->_init_env($args);
		switch($data["name"])
		{
			case "baselang":
				$l = get_instance("languages");
				$data["options"] = $l->get_list();
				$data["value"] = $this->base_lang_id;
				break;

			case "targetlang":
				$l = get_instance("languages");
				$data["options"] = $l->get_list();
				$data["value"] = $this->target_lang_id;
				break;
			
			case "translated":
				$data["value"] = $this->table_of_translated_stuff($args);
				break;
				
			case "untranslated":
				$data["value"] = $this->table_of_untranslated_stuff($args);
				break;

			case "in_progress":
				$data["value"] = $this->table_of_in_progress_stuff($args);
				break;

			case "utr_toolbar":
				$this->gen_utr_toolbar($args);
				break;

			case "tr_toolbar":
				$this->gen_tr_toolbar($args);
				break;
	
			case "inp_toolbar":
				$this->gen_inp_toolbar($args);
				break;
	

		};
		return $retval;
	}

	function _init_env($args)
	{
		if ($this->_init_env_done)
		{
			return false;
		};
		// get the current user and figure out the base and target languages
		$us = get_instance(CL_USER);
		$ucfg = new object($us->get_current_user());

		$tr_o = $args["obj_inst"];
		if ($tr_o->prop("baselang"))
		{
			$this->base_lang_id = $tr_o->prop("baselang");
		}
		else
		{
			$this->base_lang_id = $ucfg->meta("base_lang");
		}

		$l = get_instance("languages");
		$l->set_active($this->base_lang_id);

		if ($tr_o->prop("targetlang"))
		{
			$this->target_lang_id = $tr_o->prop("targetlang");
		}
		else
		{
			$this->target_lang_id = $ucfg->meta("target_lang");
		}
		$l = get_instance("languages");
		$langinfo = $l->get_list(array(
			"all_data" => true,
		));
		$this->base_lang_code = $langinfo[$this->base_lang_id]["acceptlang"];
		$this->target_lang_code = $langinfo[$this->target_lang_id]["acceptlang"];
		$this->_init_env_done = true;

		$this->clid = array();
		
		// now get a list of connections from this object

		if (empty($args["new"]))
		{
			$conns = $tr_o->connections_from(array(
				"type" => RELTYPE_CLASS,
			));
			foreach($conns as $conn)
			{
				// yeah, these are class type objects, so we really do need to get the type from the object, not the connection
				$o = $conn->to();
				$this->clid[$o->prop("type")] = $o->prop("type");
			};
		};

	}

	function table_of_untranslated_stuff($args)
	{
		switch($args["request"]["group"])
		{
			case "utr_month":
				$start = mktime(0,0,0,date("m"),date("d")-30,date("Y"));
				break;

			case "utr_week":
				$start = mktime(0,0,0,date("m"),date("d")-7,date("Y"));
				break;

			default:
				$start = false;
				break;

			case "utr_day":
				$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
				break;
		};

		$filter = array(
			"flags" => array(
				"mask" => OBJ_NEEDS_TRANSLATION,
				"flags" => OBJ_NEEDS_TRANSLATION
			),
			"class_id" => in_array($args["request"]["clid"],$this->clid) ? $args["request"]["clid"] : $this->clid,
			"lang_id" => $this->base_lang_id,
		);
		$thingies = new object_list($filter);

		$clist = aw_ini_get("classes");

		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic",
		));

		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Originaali nimi"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("Klass"),
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "base_lang",
			"caption" => t("Baaskeel"),
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "action",
			"caption" => t("T&otilde;lgi"),
			"align" => "center",
		));	

		$obj = $args["obj_inst"];

		$l = get_instance("languages");

		if ($obj->meta("targetlang_all"))
		{
			$langs = $l->get_list(array(
				"all_data" => true
			));
		}
		else
		{
			$langs = array($l->fetch($l->get_langid_for_code($this->target_lang_code)));
		}
		// untranslated stuff is the objects that are marked as "needs translation" and 
		// has no translation objects created in any language
		foreach($thingies->arr() as $item)
		{
			$lch = array();
			$g_modified = $item->modified();

			$co = $item->connections_from(array(
				"type" => RELTYPE_TRANSLATION
			));

			$co_lg = array();
			foreach($co as $c)
			{
				$co_lg[$c->prop("to.lang_id")] = $c;
				$g_modified = max($g_modified, $c->prop("to.modified"));
			}


			foreach($langs as $lid => $lg)
			{
				if ($item->lang() != $lg["acceptlang"] && (!isset($co_lg[$lid])))
				{
					// check if this language translation is not in the translated objects list
					$lch[] = html::href(array(
						"url" => $this->mk_my_orb("create",array("id" => $item->id(),"dstlang" => $lg["acceptlang"]),"object_translation"),
						"caption" => $lg["name"],
						"target" => "_blank"
					));
				}
			}

			if ($start && $g_modified < $start)
			{
				continue;
			}


			if (count($lch) < 1)
			{
				continue;
			}

			$clss = aw_ini_get("classes");
			$clfile = $clss[$item->class_id()]["file"];
			$t->define_data(array(
				"id" => $item->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $item->id()), $clfile),
					"caption" => $item->name(),
					"target" => "_blank"
				)),
				"class_id" => $clist[$item->class_id()]["name"],
				"action" => join(" | ", $lch),
				"base_lang" => $this->base_lang_code
			));
		};

		$t->sort_by();
		return $t->draw();
	}
	
	function table_of_translated_stuff($args)
	{
		switch($args["request"]["group"])
		{
			case "tr_month":
				$start = mktime(0,0,0,date("m"),date("d")-30,date("Y"));
				break;

			case "tr_week":
				$start = mktime(0,0,0,date("m"),date("d")-7,date("Y"));
				break;

			default:
				$start = false;
				break;

			case "tr_day":
				$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
				break;
		};

		$o = $args["obj_inst"];

		$filter = array(
			"flags" => array(
				"mask" => OBJ_NEEDS_TRANSLATION,
				"flags" => OBJ_NEEDS_TRANSLATION
			),
			"class_id" => in_array($args["request"]["clid"],$this->clid) ? $args["request"]["clid"] : $this->clid,
			"lang_id" => $this->base_lang_id,
		);
		$thingies = new object_list($filter);

		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic",
		));

		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Originaali nimi"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("Klass"),
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "base_lang",
			"caption" => t("Baaskeel"),
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "action",
			"caption" => t("Vaata t&otilde;lkeid"),
			"sortable" => 0,
			"align" => "center",
		));

		$l = get_instance("languages");
		$langs = $l->get_list(array(
			"all_data" => true,
			"key" => "acceptlang"
		));
		$langs_id = $l->get_list(array(
			"all_data" => true,
			"key" => "id"
		));

		foreach($thingies->arr() as $item)
		{
			$g_modified = $item->modified();

			$co = $item->connections_from(array(
				"type" => RELTYPE_TRANSLATION
			));
			$lch = array();
			foreach($co as $conn)
			{
				if (($conn->prop("to.lang_id") != $langs[$item->lang()]["id"]) && 
					(($conn->prop("to.flags") & OBJ_IS_TRANSLATED) == OBJ_IS_TRANSLATED))
				{
					$lch[$langs_id[$conn->prop("to.lang_id")]["acceptlang"]] = html::href(array(
						"url" => $this->mk_my_orb("create",array("id" => $item->id(),"dstlang" => $langs_id[$conn->prop("to.lang_id")]["acceptlang"]),"object_translation"),
						"caption" => $langs_id[$conn->prop("to.lang_id")]["name"],
						"target" => "_blank"
					));
					$g_modified = max($g_modified, $conn->prop("to.modified"));
				}
			}

			if ($start && $g_modified < $start)
			{
				continue;
			}

			ksort($lch);

			// now, don't show the object if it is not translated to any languages
			// if it is, then check if all languages are marked fully translated and the translation object requires it
			$show = true;

			if (count($lch) < 1)
			{
				$show = false;
			}
			if ($o->prop("translated_all_langs") && count($lch) != (count($langs)- 1))
			{
				$show = false;
			}

			if ($show)
			{
				$clss = aw_ini_get("classes");
				$clfile = $clss[$item->class_id()]["file"];
				$t->define_data(array(
					"id" => $item->id(),
					"name" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $item->id()), $clfile),
						"caption" => $item->name(),
						"target" => "_blank"
					)),
					"class_id" => $clss[$item->class_id()]["name"],
					"base_lang" => $this->base_lang_code,
					"action" => join(" | ", $lch),
				));
			}
		};

		$t->sort_by();
		return $t->draw();
	}

	function table_of_in_progress_stuff($args)
	{
		switch($args["request"]["group"])
		{
			case "ipr_month":
				$start = mktime(0,0,0,date("m"),date("d")-30,date("Y"));
				break;

			case "ipr_week":
				$start = mktime(0,0,0,date("m"),date("d")-7,date("Y"));
				break;

			case "default":
				$start = false;
				break;

			case "ipr_day":
				$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
				break;
		};

		$filter = array(
			"flags" => array(
				"mask" => OBJ_NEEDS_TRANSLATION, 
				"flags" => OBJ_NEEDS_TRANSLATION
			),
			"class_id" => in_array($args["request"]["clid"],$this->clid) ? $args["request"]["clid"] : $this->clid,
			"lang_id" => $this->base_lang_id,
		);
		$thingies = new object_list($filter);

		$o = $args["obj_inst"];

		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic",
		));

		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Originaali nimi"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("Klass"),
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "base_lang",
			"caption" => t("Baaskeel"),
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "action",
			"caption" => t("Vaata t&otilde;lkeid"),
			"sortable" => 0,
			"align" => "center",
		));

		$l = get_instance("languages");
		$langs = $l->get_list(array(
			"all_data" => true,
			"key" => "acceptlang"
		));
		$langs_id = $l->get_list(array(
			"all_data" => true,
			"key" => "id"
		));

		foreach($thingies->arr() as $item)
		{
			$g_modified = $item->modified();

			$co = $item->connections_from(array(
				"type" => RELTYPE_TRANSLATION
			));
			$lch = array();
			// show all objects that have some translation objects that are not marked completed
			$all_translated = count($conn) == (count($langs) - 1);

			foreach($co as $conn)
			{
				if (($conn->prop("to.flags") & OBJ_IS_TRANSLATED) != OBJ_IS_TRANSLATED)
				{
					$all_translated = false;
				}

				$show = true;
				if (!$o->prop("translated_all_langs"))
				{
					$show = (($conn->prop("to.lang_id") != $langs[$item->lang()]["id"]) && 
					(($conn->prop("to.flags") & OBJ_IS_TRANSLATED) != OBJ_IS_TRANSLATED));
				}
				else
				{
					// show it even if the translation is completed
					$show = $conn->prop("to.lang_id") != $langs[$item->lang()]["id"];
				}

				if ($show)
				{
					$lch[] = html::href(array(
						"url" => $this->mk_my_orb("create",array("id" => $item->id(),"dstlang" => $langs_id[$conn->prop("to.lang_id")]["acceptlang"]),"object_translation"),
						"caption" => $langs_id[$conn->prop("to.lang_id")]["name"],
						"target" => "_blank"
					));
				}
				$g_modified = max($g_modified, $conn->prop("to.modified"));
			}

			// modified is set to latest translation save
			if ($start && $g_modified < $start)
			{
				continue;
			}

			if ($o->prop("translated_all_langs"))
			{
				if ($all_translated)
				{
					continue;
				}
			}

			if (count($lch) < 1)
			{
				continue;
			}
	

			$clss = aw_ini_get("classes");
			$clfile = $clss[$item->class_id()]["file"];
			$t->define_data(array(
				"id" => $item->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $item->id()), $clfile),
					"caption" => $item->name(),
					"target" => "_blank"
				)),
				"class_id" => $clss[$item->class_id()]["name"],
				"base_lang" => $this->base_lang_code,
				"action" => join(" | ", $lch),
			));
		};

		$t->sort_by();
		return $t->draw();
	}

	function gen_utr_toolbar($arr)
	{
		// which links do I need on the toolbar?
		// 1- lisa grupp
		$toolbar = &$arr["prop"]["toolbar"];

		$toolbar->add_cdata(html::select(array(
			"name" => "clid",
			"options" => $this->_prep_clid_list(),
			"selected" => (int)$arr["request"]["clid"],
		)));

		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"url" => "#",
			"onClick" => "document.location.href=document.location.href+'&clid='+document.getElementById('clid').value;",
			"img" => "save.gif",
		));
	}

	function gen_inp_toolbar($arr)
	{
		// which links do I need on the toolbar?
		// 1- lisa grupp
		$toolbar = &$arr["prop"]["toolbar"];

		$toolbar->add_cdata(html::select(array(
			"name" => "clid",
			"options" => $this->_prep_clid_list(),
			"selected" => (int)$arr["request"]["clid"],
		)));

		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"url" => "#",
			"onClick" => "document.location.href=document.location.href+'&clid='+document.getElementById('clid').value;",
			"img" => "save.gif",
		));
	}
	
	function gen_tr_toolbar($arr)
	{
		// which links do I need on the toolbar?
		// 1- lisa grupp
		$toolbar = &$arr["prop"]["toolbar"];

		$toolbar->add_cdata(html::select(array(
			"name" => "clid",
			"options" => $this->_prep_clid_list(),
			"selected" => (int)$arr["request"]["clid"],
		)));
		
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"url" => "#",
			"onClick" => "document.location.href=document.location.href+'&clid='+document.getElementById('clid').value;",
			"img" => "save.gif",
		));
	}

	function _prep_clid_list()
	{
		$res = array("0" => "kõik");
		$cl_info = aw_ini_get("classes");
		foreach($this->clid as $item)
		{
			$res[$item] = $cl_info[$item]["name"];
		}
		return $res;
	}


	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
