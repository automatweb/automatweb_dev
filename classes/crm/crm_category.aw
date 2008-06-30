<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_category.aw,v 1.12 2008/06/30 10:25:48 kristo Exp $
// crm_category.aw - Kategooria 
/*

@tableinfo aw_account_balances master_index=oid master_table=objects index=aw_oid

@classinfo syslog_type=ST_CRM_CATEGORY relationmgr=yes maintainer=markop

@default table=objects
@default group=general

	@property jrk type=textbox size=5 table=objects field=jrk
	@caption J&auml;rjekord

	@property img_upload type=releditor reltype=RELTYPE_IMAGE props=file,file_show
	@caption Pilt

	@property extern_id type=hidden field=meta method=serialize 

	//@property jrk type=textbox size=4
	//@caption J&auml;rk

@groupinfo list caption="Nimekiri" submit=no
@default group=list

	@property list type=hidden store=no

	@property list_tb type=toolbar no_caption=1 store=no

	@property list_tbl type=table no_caption=1 store=no

@property balance type=hidden table=aw_account_balances field=aw_balance

@reltype IMAGE value=1 clid=CL_IMAGE
@caption Pilt

@reltype CATEGORY value=2 clid=CL_CRM_CATEGORY
@caption Alam kategooria

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

*/

class crm_category extends class_base
{
	function crm_category()
	{
		$this->init(array(
			"tpldir" => "crm/crm_category",
			"clid" => CL_CRM_CATEGORY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "list_tb":
				$t = &$prop["vcl_inst"];
				$t->add_search_button(array(
					"pn" => "list",
					"clid" => CL_CRM_PERSON,
					"multiple" => 1,
				));
				$t->add_button(array(
					"name" => "delete_rels_to",
					"tooltip" => t("Kustuta seosed"),
					"img" => "delete.gif",
					"action" => "delete_rels_to",
					"confirm" => t("Oled kindel, et soovid valitud seosed kustutada?"),
				));
				break;

			case "list_tbl":
				$this->_get_list_tbl($arr);
				break;
		};
		return $retval;
	}

	function _get_list_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		$conns = $arr["obj_inst"]->connections_to(array(
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_CATEGORY",		// RELTYPE_CATEGORY
		));
		foreach($conns as $conn)
		{
			$from = $conn->from();
			$t->define_data(array(
				"oid" => $from->id(),
				"name" => html::href(array(
					"caption" => $from->name(),
					"url" => $this->mk_my_orb("change", array("id" => $from->id(), "return_url" => get_ru()), CL_CRM_PERSON),
				)),
			));
		}
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "list":
				$ps = explode(",", $prop["value"]);
				foreach($ps as $p)
				{
					$c = new connection(array(
						"to" => $arr["obj_inst"]->id(),
						"from" => $p,
						"reltype" => 80,		// RELTYPE_CATEGORY
					));
					$c->save();
				}
				break;
		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($t == "aw_account_balances" && $f == "")
		{
			$this->db_query("CREATE TABLE $t (aw_oid int primary key, aw_balance double)");
			// also, create entries in the table for each existing object
			$this->db_query("SELECT oid FROM objects WHERE class_id IN (".CL_CRM_CATEGORY.",".CL_CRM_COMPANY.",".CL_PROJECT.",".CL_TASK.",".CL_CRM_PERSON.",".CL_BUDGETING_FUND.",".CL_SHOP_PRODUCT.",".CL_BUDGETING_ACCOUNT.")");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				$this->db_query("INSERT INTO $t(aw_oid, aw_balance) values($row[oid], 0)");
				$this->restore_handle();
			}
			return true;
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	/**
		@attrib name=delete_rels_to
	**/
	function delete_rels_to($arr)
	{
		foreach($arr["sel"] as $id)
		{
			$cs = connection::find(array(
				"to" => $arr["id"],
				"from" => $id,
				"reltype" => "RELTYPE_CATEGORY",
			));
			foreach($cs as $c_id)
			{
				$c = new connection($c_id);
				$c->delete();
			}
		}
		return $arr["post_ru"];
	}
}
?>
