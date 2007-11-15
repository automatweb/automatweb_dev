<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/transport_type.aw,v 1.2 2007/11/15 16:57:05 markop Exp $
// transport_type.aw - Transportation type 
/*

@classinfo syslog_type=ST_TRANSPORT_TYPE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property provider type=relpicker reltype=RELTYPE_PROVIDER store=connect
@caption Transpordi osutaja

@property currency type=relpicker reltype=RELTYPE_CURRENCY multiple=1 field=meta method=serialize
@caption Valuutad

@property prices_tb type=toolbar no_caption=1 store=no
@caption Hindade toolbar

@property prices_table type=table store=no
@caption Hindade tabel

@reltype CURRENCY value=1 clid=CL_CURRENCY
@caption valuutad

@reltype PROVIDER value=2 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption Transpordi osutaja

@reltype PRICE value=3 clid=CL_PRICE
@caption Transpordi hind

*/

class transport_type extends class_base
{
	function transport_type()
	{
		$this->init(array(
			"tpldir" => "applications/crm/transport_type",
			"clid" => CL_TRANSPORT_TYPE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "prices_table":
				$this->_get_prices_table(&$arr);
				break;

			case "prices_tb":
				$this->_get_prices_tb(&$arr);
				break;
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "prices_table":
				$this->_save_prices_table($arr);
				break;
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//

	function _get_prices_tb($arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus hind"),
			"action" => "add_new_price",
			"confirm" => t("Lisan uue hinna?"),
		));
	}

	/**
		@attrib name=add_new_price all_args=1
	**/
	function add_new_price($arr)
	{
		$price_inst = get_instance(CL_PRICE);
		$id = $price_inst->add(array(
			"object" => $arr["id"],
			"class_id" => CL_TRANSPORT_TYPE,
		));
		$o = obj($arr["id"]);
		$o->connect(array(
			"to" => $id,
			"type" => RELTYPE_PRICE
		));
		
		return $arr["post_ru"];
	}

	function _get_prices_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$price_inst = get_instance(CL_PRICE);

		$t->define_field(array(
			"name" => "start",
			"caption" => t("Algus"),
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("L&otilde;pp"),
		));
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CURRENCY")) as $conn)
		{
			$curr = $conn->to();
			$t->define_field(array(
				"name" => $curr->id(),
				"caption" => $curr->name(),
			));
		}

		$ol = $price_inst->get_prices(array("object" => $arr["obj_inst"]->id()));
		foreach($ol->arr() as $price)
		{
			$prices = $price->meta("prices");
			$data = array();
			$data["start"] = html::date_select(array(
				"name" => "prices[".$price->id()."][date_from]",
				"value" => $price->prop("date_from"),
			));
			$data["end"] = html::date_select(array(
				"name" => "prices[".$price->id()."][date_to]",
				"value" => $price->prop("date_to"),
			));	
		
			foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CURRENCY")) as $conn)
			{
				$curr = $conn->to();
				$data[$curr->id()] = html::textbox(array(
					"name" => "prices[".$price->id()."][prices][".$curr->id()."]",
					"value" => $prices[$curr->id()],
					"size" => 5,
				));
			}
			$t->define_data($data);
		}
	}
	
	function _save_prices_table($arr)
	{
		$price_inst = get_instance(CL_PRICE);
		foreach($arr["request"]["prices"] as $id => $val)
		{
			$price_inst->change(array("id" => $id, "data" => $val));
		}
	}

}
?>
