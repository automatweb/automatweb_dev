<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/site_translation.aw,v 1.5 2003/09/24 12:49:56 kristo Exp $
// site_translation.aw - Saidi tõlge 
/*

@classinfo syslog_type=ST_SITE_TRANSLATION relatiomgr=yes

@default table=objects
@default group=general

@property utr_toolbar group=utr_day,utr_week,utr_month,utr_all type=toolbar  store=no no_caption=1
@caption TB

@property tr_toolbar group=tr_day,tr_week,tr_month,tr_all type=toolbar  store=no no_caption=1
@caption TB

@property inp_toolbar group=ipr_day,ipr_week,ipr_month,ipr_all type=toolbar  store=no no_caption=1
@caption TB

@property baselang type=text group=general store=no
@caption Baaskeel

@property targetlang type=text group=general store=no
@caption Sihtkeel

@property targetlang_all type=checkbox ch_value=1 group=general field=meta method=serialize
@caption Kas kasutada k&otilde;iki keeli sihtkeelteks

@property translated group=tr_day,tr_week,tr_month,tr_all type=text store=no no_caption=1
@caption Tõlgitud


@property untranslated group=utr_day,utr_week,utr_month,utr_all type=text store=no no_caption=1
@caption Tõlkimata

@property in_progress group=ipr_day,ipr_week,ipr_month,ipr_all type=text store=no no_caption=1
@caption T&ouml;&ouml;s


@groupinfo translated caption=Tõlgitud submit=no
@groupinfo untranslated caption=Tõlkimata submit=no
@groupinfo in_progress caption="T&ouml;&ouml;s" submit=no

@groupinfo utr_day caption="Viimane päev" parent=untranslated submit=no
@groupinfo utr_week caption="Viimane nädal" parent=untranslated submit=no
@groupinfo utr_month caption="Viimane kuu" parent=untranslated submit=no
@groupinfo utr_all caption="Kõik" parent=untranslated submit=no

@groupinfo ipr_day caption="Viimane päev" parent=in_progress submit=no
@groupinfo ipr_week caption="Viimane nädal" parent=in_progress submit=no
@groupinfo ipr_month caption="Viimane kuu" parent=in_progress submit=no
@groupinfo ipr_all caption="Kõik" parent=in_progress submit=no

@groupinfo tr_day caption="Viimane päev" parent=translated submit=no
@groupinfo tr_week caption="Viimane nädal" parent=translated submit=no
@groupinfo tr_month caption="Viimane kuu" parent=translated submit=no
@groupinfo tr_all caption="Kõik" parent=translated submit=no

@classinfo relationmgr=yes

*/

define("RELTYPE_CLASS",1);

class site_translation extends class_base
{
	function site_translation()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
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
				$data["value"] = $this->base_lang_code;
				break;

			case "targetlang":
				$data["value"] = $this->target_lang_code;
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
		$udat = $this->get_user();
		$ucfg = new object($udat["oid"]);
		$this->base_lang_id = $ucfg->meta("base_lang");
		$this->target_lang_id = $ucfg->meta("target_lang");
		$l = get_instance("languages");
		$langinfo = $l->get_list(array(
			"all_data" => true,
		));
		$this->base_lang_code = $langinfo[$this->base_lang_id]["acceptlang"];
		$this->target_lang_code = $langinfo[$this->target_lang_id]["acceptlang"];
		$this->_init_env_done = true;

		$this->clid = array();
		
		// now get a list of connections from this object

		if ($args["obj"]["oid"])
		{
			$obj = new object($args["obj"]["oid"]);
			$conns = $obj->connections_from(array(
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

			case "utr_all":
				$start = false;
				break;

			default:
				$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
				break;
		};
		if (!isset($args["request"]["clid"]))
		{
			$args["request"]["clid"] = CL_MENU;
		}

		$filter = array(
			"flags" => array(
				"mask" => OBJ_IS_TRANSLATED|OBJ_NEEDS_TRANSLATION,
				"flags" => OBJ_NEEDS_TRANSLATION
			),
			"class_id" => in_array($args["request"]["clid"],$this->clid) ? $args["request"]["clid"] : $this->clid,
			"lang_id" => $this->base_lang_id,
		);
		if ($start)
		{
			$filter["modified"] = ">=$start";
		}
		$thingies = new object_list($filter);

		$clist = aw_ini_get("classes");

		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic",
		));

		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => "Originaali nimi",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "class_id",
			"caption" => "Klass",
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "base_lang",
			"caption" => "Baaskeel",
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "action",
			"caption" => "T&otilde;lgi",
			"align" => "center",
		));	

		$obj = obj($args["obj"]["oid"]);
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
		foreach($thingies->arr() as $item)
		{
			$lch = array();
			foreach($langs as $lid => $lg)
			{
				if ($item->lang() != $lg["acceptlang"])
				{
					$lch[] = html::href(array(
						"url" => $this->mk_my_orb("create",array("id" => $item->id(),"dstlang" => $lg["acceptlang"]),"object_translation"),
						"caption" => $lg["name"],
						"target" => "_blank"
					));
				}
			}

			$clfile = $this->cfg["classes"][$item->class_id()]["file"];
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

			case "tr_all":
				$start = false;
				break;

			default:
				$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
				break;
		};

		if (!isset($args["request"]["clid"]))
		{
			$args["request"]["clid"] = CL_MENU;
		}

		$filter = array(
			"flags" => array(
				"mask" => OBJ_IS_TRANSLATED|OBJ_NEEDS_TRANSLATION,
				"flags" => OBJ_NEEDS_TRANSLATION|OBJ_IS_TRANSLATED
			),
			"class_id" => in_array($args["request"]["clid"],$this->clid) ? $args["request"]["clid"] : $this->clid,
			"lang_id" => $this->base_lang_id,
		);
		if ($start)
		{
			$filter["modified"] = ">=$start";
		}
		$thingies = new object_list($filter);

		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic",
		));

		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => "Originaali nimi",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "class_id",
			"caption" => "Klass",
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "base_lang",
			"caption" => "Baaskeel",
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "action",
			"caption" => "Vaata t&otilde;lkeid",
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
			$co = $item->connections_from(array(
				"type" => RELTYPE_TRANSLATION
			));
			$lch = array();
			foreach($co as $conn)
			{
				if ($conn->prop("to.lang_id") != $langs[$item->lang()]["id"])
				{
					$lch[] = html::href(array(
						"url" => $this->mk_my_orb("create",array("id" => $item->id(),"dstlang" => $langs_id[$conn->prop("to.lang_id")]["acceptlang"]),"object_translation"),
						"caption" => $langs_id[$conn->prop("to.lang_id")]["name"],
						"target" => "_blank"
					));
				}
			}

			$clfile = $this->cfg["classes"][$item->class_id()]["file"];
			$t->define_data(array(
				"id" => $item->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $item->id()), $clfile),
					"caption" => $item->name(),
					"target" => "_blank"
				)),
				"class_id" => $item->class_id(),
				"base_lang" => $this->base_lang_code,
				"action" => join(" | ", $lch),
			));
		};

		return $t->draw();
	}

	function table_of_in_progress_stuff($args)
	{
		switch($args["request"]["group"])
		{
			case "tr_month":
				$start = mktime(0,0,0,date("m"),date("d")-30,date("Y"));
				break;

			case "tr_week":
				$start = mktime(0,0,0,date("m"),date("d")-7,date("Y"));
				break;

			case "tr_all":
				$start = false;
				break;

			default:
				$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
				break;
		};

		if (!isset($args["request"]["clid"]))
		{
			$args["request"]["clid"] = CL_MENU;
		}

		$filter = array(
			"flags" => array(
				"mask" => OBJ_IS_TRANSLATED|OBJ_NEEDS_TRANSLATION, 
				"flags" => OBJ_NEEDS_TRANSLATION
			),
			"class_id" => in_array($args["request"]["clid"],$this->clid) ? $args["request"]["clid"] : $this->clid,
			"lang_id" => $this->base_lang_id,
		);
		if ($start)
		{
			$filter["modified"] = ">=$start";
		}
		$thingies = new object_list($filter);

		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic",
		));

		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => "Originaali nimi",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "class_id",
			"caption" => "Klass",
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "base_lang",
			"caption" => "Baaskeel",
			"sortable" => 1,
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "action",
			"caption" => "Vaata t&otilde;lkeid",
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
			$co = $item->connections_from(array(
				"type" => RELTYPE_TRANSLATION
			));
			$lch = array();
			foreach($co as $conn)
			{
				if ($conn->prop("to.lang_id") != $langs[$item->lang()]["id"])
				{
					$lch[] = html::href(array(
						"url" => $this->mk_my_orb("create",array("id" => $item->id(),"dstlang" => $langs_id[$conn->prop("to.lang_id")]["acceptlang"]),"object_translation"),
						"caption" => $langs_id[$conn->prop("to.lang_id")]["name"],
						"target" => "_blank"
					));
				}
			}

			$clfile = $this->cfg["classes"][$item->class_id()]["file"];
			$t->define_data(array(
				"id" => $item->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $item->id()), $clfile),
					"caption" => $item->name(),
					"target" => "_blank"
				)),
				"class_id" => $item->class_id(),
				"base_lang" => $this->base_lang_code,
				"action" => join(" | ", $lch),
			));
		};

		return $t->draw();
	}

	function gen_utr_toolbar($arr)
	{
		$id = $arr["obj"]["oid"];
		if ($id)
		{
			// which links do I need on the toolbar?
			// 1- lisa grupp
			$toolbar = &$arr["prop"]["toolbar"];

			if (!isset($arr["request"]["clid"]))
			{
				$arr["request"]["clid"] = CL_MENU;
			}

			$toolbar->add_cdata(html::select(array(
				"name" => "clid",
				"options" => $this->_prep_clid_list(),
				"selected" => (int)$arr["request"]["clid"],
			)));

			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "#",
				"onClick" => "document.location.href=document.location.href+'&clid='+document.getElementById('clid').value;",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
		};
	}

	function gen_inp_toolbar($arr)
	{
		$id = $arr["obj"]["oid"];
		if ($id)
		{
			// which links do I need on the toolbar?
			// 1- lisa grupp
			$toolbar = &$arr["prop"]["toolbar"];

			if (!isset($arr["request"]["clid"]))
			{
				$arr["request"]["clid"] = CL_MENU;
			}

			$toolbar->add_cdata(html::select(array(
				"name" => "clid",
				"options" => $this->_prep_clid_list(),
				"selected" => (int)$arr["request"]["clid"],
			)));

			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "#",
				"onClick" => "document.location.href=document.location.href+'&clid='+document.getElementById('clid').value;",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
		};
	}
	
	function gen_tr_toolbar($arr)
	{
		$id = $arr["obj"]["oid"];
		if ($id)
		{
			// which links do I need on the toolbar?
			// 1- lisa grupp
			$toolbar = &$arr["prop"]["toolbar"];

			if (!isset($arr["request"]["clid"]))
			{
				$arr["request"]["clid"] = CL_MENU;
			}
			
			$toolbar->add_cdata(html::select(array(
				"name" => "clid",
				"options" => $this->_prep_clid_list(),
				"selected" => (int)$arr["request"]["clid"],
			)));
                        
			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "#",
				"onClick" => "document.location.href=document.location.href+'&clid='+document.getElementById('clid').value;",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
		}
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

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_CLASS => "klass",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
		switch($args["reltype"])
		{
			case RELTYPE_CLASS:
				$retval = array(CL_OBJECT_TYPE);
				break;
		};
		return $retval;
	}
}
?>
