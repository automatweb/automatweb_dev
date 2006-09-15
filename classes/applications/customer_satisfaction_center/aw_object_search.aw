<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/customer_satisfaction_center/aw_object_search.aw,v 1.5 2006/09/15 07:27:53 kristo Exp $
// aw_object_search.aw - AW Objektide otsing 
/*

@classinfo syslog_type=ST_AW_OBJECT_SEARCH no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=srch
@default store=no

	@property s_tb type=toolbar no_caption=1 

	@property s_name type=textbox  size=50
	@caption Nimi

	@property s_comment type=textbox  size=50
	@caption Kommentaar

	@property s_clid type=select multiple=1 size=10
	@caption T&uuml;&uuml;p

	@property s_oid type=textbox  size=50
	@caption OID

	@property s_creator type=textbox  size=50
	@caption Looja

	@property s_modifier type=textbox  size=50
	@caption Muutja

	@property s_status type=chooser
	@caption Aktiivsus

	@property s_alias type=textbox  size=50
	@caption Alias

	@property s_language type=chooser
	@caption Keel

	@property s_period type=select
	@caption Periood

	@property s_site_id type=select
	@caption Saidi ID

	@property s_find_bros type=checkbox ch_value=1
	@caption Leia vendi

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
			case "s_clid":
				$prop["options"] = get_class_picker();
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
			"action" => "delete_bms",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"confirm" => t("Oled kindel et soovid valitud objektid kustutada?")
		));
	}

	/**
		@attrib name=delete_bms
	**/
	function delete_bms($arr)
	{
		object_list::iterate_list($_GET["sel"], "delete");
		return $arr["post_ru"];
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
			$po = obj($o->parent());
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
		$filt = array("limit" => 200, "lang_id" => array(), "site_id" => array());
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

		$nf = array("s_status" => "status", "s_oid" => "oid", "s_site_id" => "site_id", "s_period" => "period");
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
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] != "srch")
		{
			return false;
		}
		return true;
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
}
?>
