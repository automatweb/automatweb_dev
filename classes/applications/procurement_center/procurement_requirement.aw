<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_requirement.aw,v 1.2 2006/05/18 11:19:09 kristo Exp $
// procurement_requirement.aw - N&otilde;ue 
/*

@classinfo syslog_type=ST_PROCUREMENT_REQUIREMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo procuremnent_requirements index=aw_oid master_index=brother_of master_table=objects

@default table=objects
@default group=general

	@property desc type=textarea rows=20 cols=50 table=procuremnent_requirements field=aw_desc
	@caption Kirjeldus

@default group=offers

	@property offer_t type=table store=no no_caption=1

@default group=comments

	@property comments type=comments 


@groupinfo offers caption="Pakkumised"
@groupinfo comments caption="Kommentaarid"
*/

class procurement_requirement extends class_base
{
	function procurement_requirement()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_requirement",
			"clid" => CL_PROCUREMENT_REQUIREMENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "offer_t":
				$this->_offer_t($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "offer_t":
				$arr["obj_inst"]->set_meta("assessments", $arr["request"]["ass"]);
				$arr["obj_inst"]->set_meta("suitable", $arr["request"]["suitable"]);
				$arr["obj_inst"]->set_meta("nonsuitable", $arr["request"]["nonsuitable"]);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "" && $t == "procuremnent_requirements")
		{
			$this->db_query("CREATE TABLE procuremnent_requirements (aw_oid int primary key, aw_desc text)");
			return true;
		}
	}

	function _init_offer_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "solution",
			"caption" => t("Lahendus"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "offerer",
			"caption" => t("Pakkuja"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "default",
			"caption" => t("Eelistatud"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "assessment",
			"caption" => t("Hinne"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "suitable",
			"caption" => t("Sobiv"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "nonsuitable",
			"caption" => t("Tagasi l&uuml;katud"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "tti",
			"caption" => t("Tundide arv"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "readyness",
			"caption" => t("Valmidus"),
			"align" => "center"
		));
	}

	function _offer_t($arr)
	{	
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_offer_t($t);

		$data = $arr["obj_inst"]->meta("defaults");
		$ass = $arr["obj_inst"]->meta("assessments");
		$suitable = $arr["obj_inst"]->meta("suitable");
		$nonsuitable = $arr["obj_inst"]->meta("nonsuitable");
		$default = $data[$arr["obj_inst"]->id()];
		$ol = new object_list(array(
			"class_id" => CL_PROCUREMENT_REQUIREMENT_SOLUTION,
			"parent" => $arr["obj_inst"]->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		$po = get_instance(CL_PROCUREMENT_REQUIREMENT_SOLUTION);
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => $o->name(),
				"solution" => $o->prop("solution"),
				"offerer" => html::obj_change_url($o->prop("offerer_co"))." ".html::obj_change_url($o->prop("offerer_p")),
				"default" => ($default == $o->id() ? t("X") : t("")),
				"assessment" => html::textbox(array(
					"name" => "ass[".$o->id()."]",
					"value" => $ass[$o->id()],
					"size" => 5
				)),
				"suitable" => html::checkbox(array(
					"name" => "suitable[".$o->id()."]",
					"value" => 1,
					"checked" => $suitable[$o->id()]
				)),
				"nonsuitable" => html::checkbox(array(
					"name" => "nonsuitable[".$o->id()."]",
					"value" => 1,
					"checked" => $nonsuitable[$o->id()]
				)),
				"price" => number_format($o->prop("price"), 2),
				"tti" => $o->prop("time_to_install"),
				"readyness" => $po->readyness_states[$o->prop("readyness")],
				"oid" => $o->id()
			));
			$ass_sum += $ass[$o->id()];
			$price_sum += $o->prop("price");
			$tti_sum += $o->prop("time_to_install");
			$ass_cnt++;
		}
		
		$t->set_sortable(false);

		$t->define_data(array(
			"name" => t("<b>Keskmine:</b>"),
			"assessment" => number_format($ass_sum / $ass_cnt, 2),
			"price" => number_format($price_sum / $ass_cnt, 2),
			"tti" => number_format($tti_sum / $ass_cnt, 2),
		));
		
	}
}
?>
