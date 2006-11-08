<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_prisma_browser.aw,v 1.1 2006/11/08 13:58:19 kristo Exp $
// mrp_prisma_browser.aw - Reusneri andmete sirvimine 
/*

@classinfo syslog_type=ST_MRP_PRISMA_BROWSER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@default group=hinnap

	@property s_cust_n type=textbox store=no
	@caption Kliendi nimi

	@property s_ord_num type=textbox store=no
	@caption Number

	@property s_date_from type=date_select store=no default=-1
	@caption Kuup&auml;ev alates

	@property s_date_to type=date_select store=no default=-1
	@caption Kuup&auml;ev kuni

	@property s_salesp_name type=textbox store=no
	@caption M&uuml;&uuml;gimehe nimi

	@property s_btn type=submit caption=Otsi store=no
	@caption Otsi

	@property s_res type=table no_caption=1 store=no

@groupinfo hinnap caption="Hinnapakkumised" submit_method=get
@groupinfo tellim caption="Tellimused" submit_method=get
*/

class mrp_prisma_browser extends class_base
{
	function mrp_prisma_browser()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_prisma_browser",
			"clid" => CL_MRP_PRISMA_BROWSER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "s_cust_n":
			case "s_ord_num":
			case "s_date_from":
			case "s_date_to":
			case "s_salesp_name":
				$prop["value"] = $arr["request"][$prop["name"]];
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
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _init_s_res(&$t)
	{
		$t->define_field(array(
			"name" => "nr",
			"caption" => t("hinnapakkumise number"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));

		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"sortable" => 1,
		/*	"type" => "time",
			"format" => "d.m.Y H:i",
Fdd
			"numeric" => 1*/
		));

		$t->define_field(array(
			"name" => "custn",
			"caption" => t("Kliendi nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "jobn",
			"caption" => t("T&ouml;&ouml; nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "salesman",
			"caption" => t("M&uuml;&uuml;gimees"),
			"align" => "center",
			"sortable" => 1
		));
	}

	function _get_s_res($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_s_res($t);

		$sr = array("1=1");

		$this->quote(&$arr["request"]);
		if ($arr["request"]["s_cust_n"] != "")
		{
			$sr[] = " cust.KliendiNimi LIKE '%".trim($arr["request"]["s_cust_n"])."%' ";
		}

		if ($arr["request"]["s_ord_num"] != "")
		{
			$sr[] = " h.`HINNAPAKKUMINE NR` = '".trim($arr["request"]["s_ord_num"])."' ";
		}

		$df = date_edit::get_timestamp($arr["request"]["s_date_from"]);
		if ($df > -1)
		{
			$sr[] = " h.`KUUPÄEV` >= FROM_UNIXTIME($df) ";
		}

		$dt = date_edit::get_timestamp($arr["request"]["s_date_to"]);
		if ($dt > -1)
		{
			$sr[] = " h.`KUUPÄEV` <= FROM_UNIXTIME($dt) ";
		}

		if ($arr["request"]["s_salesp_name"] != "")
		{
			$sr[] = " salesp.`MüügimeheNimi` LIKE '%".trim($arr["request"]["s_salesp_name"])."%' ";
		}

		if (count($sr) == 1)
		{
			return;
		}
		$sr = join(" AND ", $sr);
		$q ="
			SELECT h.`HINNAPAKKUMINE NR` as nr,
				h.`KUUPÄEV` as date,
				cust.KliendiNimi as custn,
				h.`TÖÖ NIMI` as jobn,
				salesp.`M\xfc\xfcgimeheNimi` as salesman 
			FROM hinnapakkumine h 
			LEFT JOIN kliendid cust ON cust.KliendiID = h.KliendiID
			LEFT JOIN muugimehed salesp ON salesp.`MüügimeheID` = h.`MüügimeheID`
			WHERE $sr
			order by h.`KUUP\xc4EV` desc
			LIMIT 300
		";
		//echo "q = $q <br>";

		$i = get_instance("mrp/mrp_prisma_import");
		$db = $i->_get_conn();

		$db->db_query($q);
		while($row = $db->db_next())
		{
			$row["nr"] = html::href(array(
				"url" => aw_url_change_var(array("group" => "view_hp", "hp_id" => $row["nr"])),
				"caption" => $row["nr"]
			));
			$t->define_data($row);
		}
		$t->set_default_sorder("desc");
		$t->set_default_sortby("nr");
	}
}
?>

