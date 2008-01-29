<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/customer_satisfaction_center/aw_object_search.aw,v 1.19 2008/01/29 11:56:48 robert Exp $
// aw_object_search.aw - AW Objektide otsing 
/*

@classinfo syslog_type=ST_AW_OBJECT_SEARCH no_comment=1 no_status=1 prop_cb=1 maintainer=kristo

@default table=objects
@default group=srch
@default store=no

	@property s_tb type=toolbar no_caption=1 

	@layout ver_split type=hbox 

		@layout left_side type=vbox parent=ver_split closeable=1 area_caption=&Uuml;ldandmed

			@property s_name type=textbox  size=50 parent=left_side
			@caption Nimi

			@property s_comment type=textbox  size=50 parent=left_side
			@caption Kommentaar

			@property s_clid type=select multiple=1 size=10 parent=left_side
			@caption T&uuml;&uuml;p

			@property s_oid type=textbox  size=50 parent=left_side
			@caption OID

			@property s_parent_search type=text parent=left_side
			@caption Asukoht

			@property s_status type=chooser parent=left_side
			@caption Aktiivsus

			@property s_alias type=textbox  size=50 parent=left_side
			@caption Alias

			@property s_language type=chooser parent=left_side
			@caption Keel

			@property s_period type=select parent=left_side
			@caption Periood

			@property s_site_id type=select parent=left_side
			@caption Saidi ID

			@property s_find_bros type=checkbox ch_value=1 parent=left_side
			@caption Leia vendi

		@layout right_side type=vbox parent=ver_split

			@layout creamod type=vbox closeable=1 area_caption=Muutmine&nbsp;ja&nbsp;lisamine parent=right_side
	
				@property s_creator type=textbox  size=20 parent=creamod
				@caption Looja

				@property s_creator_from type=chooser parent=creamod default=0 orient=vertical
				@caption Otsida loojat
		
				@property s_crea_from type=datetime_select parent=creamod default=-1
				@caption Lisatud alates
			
				@property s_crea_to type=datetime_select parent=creamod default=-1
				@caption Lisatud kuni
			
				@property s_modifier type=textbox  size=20 parent=creamod
				@caption Muutja

				@property s_modifier_from type=chooser parent=creamod default=0 orient=vertical
				@caption Otsida muutjat

				@property s_mod_from type=datetime_select parent=creamod default=-1
				@caption Muudetud alates
			
				@property s_mod_to type=datetime_select parent=creamod default=-1
				@caption Muudetud kuni

			@layout keywords type=vbox closeable=1 area_caption=M&auml;rks&otilde;nad parent=right_side

				@property s_kws type=textbox parent=keywords 
				@caption M&auml;rks&otilde;nad

			@layout l_timing type=vbox closeable=1 area_caption=Ajaline&nbsp;aktiivsus parent=right_side

				@property s_tmg_activate_from type=datetime_select parent=l_timing default=-1
				@caption Aktiveeri alates
			
				@property s_tmg_activate_to type=datetime_select parent=l_timing default=-1
				@caption Aktiveeri kuni
			
				@property s_tmg_deactivate_from type=datetime_select parent=l_timing default=-1
				@caption Deaktiveeri alates
			
				@property s_tmg_deactivate_to type=datetime_select parent=l_timing default=-1
				@caption Deaktiveeri kuni
			


	@property s_sbt type=submit 
	@caption Otsi

	@property s_res type=table no_caption=1

@groupinfo srch caption="Otsing" submit_method=get save=no
*/

class aw_object_search extends class_base
{
	function aw_object_search()
	{
		$this->init(array(
			"tpldir" => "applications/customer_satisfaction_center/aw_object_search",
			"clid" => CL_AW_OBJECT_SEARCH
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$prop["value"] = $arr["request"][$prop["name"]];
		switch($prop["name"])
		{
			case "s_parent_search":
				$v = html::textbox(array(
					"name" => "s_parent",
					"size" => 20,
					"value" => $arr["request"]["s_parent"],
				));
				$url = $this->mk_my_orb("do_search", array(
					"pn" => "s_parent",
					"multiple" => 1,
					"no_submit" => 1,
				),"popup_search");
				$url = "javascript:aw_popup_scroll(\"".$url."\",\"".t("Otsi")."\",550,500)";
				$v .= " ".html::href(array(
					"caption" => html::img(array(
						"url" => "images/icons/search.gif",
						"border" => 0
					)),
					"url" => $url
				));
				$prop["value"] = $v;
				break;
			case "s_creator_from":
			case "s_modifier_from":
				$prop["options"] = array(
					0 => t("Kasutajatest"),
					1 => t("Gruppidest"),
				);
				if(!strlen($prop["value"]))
				{
					$prop["value"] = 0;
				}
				break;
			case "s_crea_from":
			case "s_crea_to":
			case "s_mod_from":
			case "s_mod_to":
			case "s_tmg_activate_from":
			case "s_tmg_activate_to":
			case "s_tmg_deactivate_from":
			case "s_tmg_deactivate_to":
				if ($prop["value"] < 10)
				{
					$prop["value"] = -1;
				}
				break;

			case "s_clid":
				$odl = new object_data_list(
					array(
						"lang_id" => array(),
						"site_id" => array()
					),
					array(
						"" => array(new obj_sql_func(OBJ_SQL_UNIQUE, "clid", "class_id"))
					)
				);
				$cls = array();
				$cldata = aw_ini_get("classes");
				foreach($odl->arr() as $od)
				{
					if($cldata[$od["clid"]]["name"])
					{
						$cls[$od["clid"]] = html_entity_decode($cldata[$od["clid"]]["name"]);
					}
				}
				natsort($cls);
				$prop["options"] = $cls;
				break;

			case "s_status":
				$prop["options"] = array(
					"0" => t("K&otilde;ik"),
					"2" => t("Aktiivsed"),
					"1" => t("Deaktiivsed")
				);
				break;

			case "s_language":
				$lg = get_instance("languages");
				$prop["options"] = $lg->get_list(array("addempty" => true));
				break;

			case "s_period":
				$pr = get_instance(CL_PERIOD);
				$prop["options"] = $pr->period_list(aw_global_get("act_per_id"),false);
				if (count($prop["options"]) == 0)
				{
					return PROP_IGNORE;
				}
				$prop["options"] = array("" => t("K&otilde;ik")) + $prop["options"];
				break;

			case "s_site_id":
				$dat = $this->db_fetch_array("SELECT distinct(site_id) as site_id FROM objects");
				$sid = aw_ini_get("site_id");
				$sites = array("" => 0, $sid => $sid);
				$sl = get_instance("install/site_list");
				foreach($dat as $row)
				{
					$sites[$row["site_id"]] = $row["site_id"];
				}

				foreach($sites as $nsid)
				{
					if ($nsid != $sid)
					{
						if ($nsid == 0)
						{
							$sites[""] = t("Igalt poolt");
						}
						else
						{
							
							$sites[$nsid] = $sl->get_url_for_site($nsid);
						}
					}
					else
					{
						$sites[$nsid] = aw_ini_get("baseurl");
					}
				}
				$sites[""] = t("Igalt poolt");
				$prop["options"] = $sites;
				break;

			case "s_res":
				if (!$arr["request"]["MAX_FILE_SIZE"])
				{
					return PROP_IGNORE;
				}
				$this->_s_res($arr);
				break;

			case "s_tb":
				$this->_s_tb($arr);
				break;
		};
		return $retval;
	}

	function _s_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "delb",
			"url" => "#",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"onClick" => "check_delete()",
			"confirm" => t("Oled kindel et soovid valitud objektid kustutada?")
		));
		$tb->add_button(array(
			"name" => "cut",
			"action" => "cut",
			"img" => "cut.gif",
			"tooltip" => t("L&otilde;ika"),
		));
		$tb->add_button(array(
			"name" => "copy",
			"action" => "copy",
			"img" => "copy.gif",
			"tooltip" => t("Kopeeri"),
		));
	}

	/**
		@attrib name=delete_bms
	**/
	function delete_bms($arr)
	{
		object_list::iterate_list($_GET["sel"], "delete");
		die("<script>window.back();</script>");
	}

	function _init_s_res_t(&$t)
	{
		$t->define_field(array(
			"name" => "oid",
			"caption" => t("OID"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "icon",
			"caption" => t(""),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "lang",
			"caption" => t("Keel"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "oppnar",
			"caption" => t("Ava"),
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _s_res($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_s_res_t($t);
		$filt = $this->get_s_filt($arr);
		if (count($filt) == 1)
		{
			return;
		}
		$ol = new object_list($filt);
		classload("core/icons");
		$clss = aw_ini_get("classes");
		foreach($ol->arr() as $o)
		{
			if($o->class_id() == CL_USER)
			{
				$this->u_oids[] = $o->id();
			}
			if (!$this->can("view", $o->parent()))
			{
				$po = obj();
			}
			else
			{
				$po = obj($o->parent());
			}
			$t->define_data(array(
				"oid" => $o->id(),
				"icon" => icons::get_icon($o),
				"name" => html::get_change_url($o->id(), array(), parse_obj_name($o->name())),
				"lang" => $o->lang(),
				"class_id" => $clss[$o->class_id()]["name"],
				"location" => $po->name(),
				"created" => $o->created(),
				"createdby" => $o->createdby(),
				"modified" => $o->modified(),
				"modifiedby" => $o->modifiedby(),
				"oppnar" => html::href(array(
					"url" => $this->mk_my_orb("redir", array("parent" => $o->id()), CL_ADMIN_IF),
					"caption" => t("Ava")
				))
			));
		}
		$t->set_default_sortby("name");
	}

	function get_s_filt($arr)
	{
		$filt = array("limit" => 2000, "lang_id" => array(), "site_id" => array());
		$arrprops = array("s_name", "s_parent");
		foreach($arrprops as $arrprop)
		{
			if(strpos($arr["request"][$arrprop], ","))
			{
				$arr["request"][$arrprop] = explode(",", $arr["request"][$arrprop]);
				foreach($arr["request"][$arrprop] as $id => $val)
				{
					$arr["request"][$arrprop][$id] = "%".trim($val)."%";
				}
			}
		}
		$groupprops = array("creator", "modifier");
		foreach($groupprops as $gp)
		{
			if($arr["request"]["s_".$gp."_from"] && $arr["request"]["s_".$gp])
			{
				$ol = new object_list(array(
					"class_id" => CL_GROUP,
					"name" => "%".$arr["request"]["s_".$gp]."%",
				));
				$ppl = array();
				foreach($ol->arr() as $grp)
				{
					$conn = $grp->connections_from(array(
						"type" => "RELTYPE_MEMBER",
					));
					foreach($conn as $c)
					{
						$uo = obj($c->prop("to"));
						$uid = $uo->name();
						$ppl[] = $uid;
					}
				}
				$arr["request"]["s_".$gp] = $ppl;
			}
		}
		if($arr["request"]["s_parent"])
		{
			$filt["parent"] = $arr["request"]["s_parent"];
		}
		$props = array(
			"s_name" => "name", 
			"s_comment" => "comment", 
			"s_clid" => "class_id", 
			"s_creator" => "createdby",
			"s_modifier" => "modifiedby",
			"s_alias" => "alias",
		);
		foreach($props as $pn => $ofn)
		{
			if ($arr["request"][$pn] != "")
			{
				if (is_array($arr["request"][$pn]))
				{
					$filt[$ofn] = $arr["request"][$pn];
				}
				else
				{
					$filt[$ofn] = "%".$arr["request"][$pn]."%";
				}
			}
		}

		$nf = array("s_status" => "status", "s_oid" => "oid", "s_site_id" => "site_id", "s_period" => "period", "s_language" => "lang_id");
		foreach($nf as $pn => $ofn)
		{
			if ($arr["request"][$pn] > 0)
			{
				$filt[$ofn] = $arr["request"][$pn];
			}
		}

		if (!$arr["request"]["s_find_bros"])
		{
			$filt["brother_of"] = new obj_predicate_prop("id");
		}


		$c_from = date_edit::get_timestamp($arr["request"]["s_crea_from"]);
		$c_to = date_edit::get_timestamp($arr["request"]["s_crea_to"]);
		$m_from = date_edit::get_timestamp($arr["request"]["s_mod_from"]);
		$m_to = date_edit::get_timestamp($arr["request"]["s_mod_to"]);

		if ($c_from > 1 && $c_to > 1)
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $c_from, $c_to);
		}
		else
		if ($c_from > 1)
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $c_from);
		}
		else
		if ($c_to > 1)
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $c_to);
		}

		if ($m_from > 1 && $m_to > 1)
		{
			$filt["modified"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $m_from, $m_to);
		}
		else
		if ($m_from > 1)
		{
			$filt["modified"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $m_from);
		}
		else
		if ($m_to > 1)
		{
			$filt["modified"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $m_to);
		}

		if ($arr["request"]["s_kws"] != "")
		{
			$filt["CL_DOCUMENT.RELTYPE_KEYWORD.name"] = "%".$arr["request"]["s_kws"]."%";
		}

		$c_from = date_edit::get_timestamp($arr["request"]["s_tmg_activate_from"]);
		$c_to = date_edit::get_timestamp($arr["request"]["s_tmg_activate_to"]);
		$m_from = date_edit::get_timestamp($arr["request"]["s_tmg_deactivate_from"]);
		$m_to = date_edit::get_timestamp($arr["request"]["s_tmg_deactivate_to"]);

		if ($c_from > 1 && $c_to > 1)
		{
			$filt["CL_DOCUMENT.RELTYPE_TIMING.activate"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $c_from, $c_to);
		}
		else
		if ($c_from > 1)
		{
			$filt["CL_DOCUMENT.RELTYPE_TIMING.activate"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $c_from);
		}
		else
		if ($c_to > 1)
		{
			$filt["CL_DOCUMENT.RELTYPE_TIMING.activate"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $c_to);
		}

		if ($m_from > 1 && $m_to > 1)
		{
			$filt["CL_DOCUMENT.RELTYPE_TIMING.deactivate"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $m_from, $m_to);
		}
		else
		if ($m_from > 1)
		{
			$filt["CL_DOCUMENT.RELTYPE_TIMING.deactivate"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $m_from);
		}
		else
		if ($m_to > 1)
		{
			$filt["CL_DOCUMENT.RELTYPE_TIMING.deactivate"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $m_to);
		}

		return $filt;
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["return_url"] = $_GET["return_url"];
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] != "srch")
		{
			return false;
		}
		return true;
	}

	function callback_generate_scripts($arr)
	{
		$ret = "var oids = Array()".chr(13).chr(10);
		foreach($this->u_oids as $oid)
		{
			$ret .= "oids[".$oid."] = ".$oid.chr(13).chr(10);
		}
		$this->read_template("scripts.tpl");
		$ret .= $this->parse();
		return $ret;
	}

	function init_search()
	{
		$ol = new object_list(array(
			"class_id" => CL_AW_OBJECT_SEARCH,
			"lang_id" => array(),
			"site_id" => array(),
		));	
		if ($ol->count())
		{
			return $ol->begin();
		}
		$o = obj();
		$o->set_class_id(CL_AW_OBJECT_SEARCH);
		$o->set_name(t("AW Objektide otsing"));
		$o->set_parent(aw_ini_get("amenustart"));
		$o->save();
		return $o;
	}

	/**
		@attrib name=redir_search
		@param url optional
		@param s_name optional
		@param s_clid optional
		@param MAX_FILE_SIZE optional
	**/
	function redir_search($arr)
	{
		$so = $this->init_search();
		return html::get_change_url($so->id(), array("group" => "srch", "return_url" => $arr["url"], "s_name" => $arr["s_name"], "s_clid" => $arr["s_clid"], "MAX_FILE_SIZE" => $arr["MAX_FILE_SIZE"]));
	}

	/** cuts the selected objects 
		
		@attrib name=cut params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function cut($arr)
	{
		$i = get_instance(CL_ADMIN_IF);
		$i->if_cut($_GET);
		die("<script>window.back();</script>");
	}

	/** copies the selected objects 
		@attrib name=copy params=name default="0"
	**/
	function copy($arr)
	{
		$i = get_instance(CL_ADMIN_IF);
		return $i->if_copy($_GET);
		die("<script>window.back();</script>");
	}
}
?>
