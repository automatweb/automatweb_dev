<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/cb_form_chain/cb_form_chain_entry.aw,v 1.3 2005/06/13 08:44:13 kristo Exp $
// cb_form_chain_entry.aw - Vormiahela sisestus 
/*

@classinfo syslog_type=ST_CB_FORM_CHAIN_ENTRY relationmgr=yes no_comment=1 no_status=1

@tableinfo aw_cb_form_chain_entries index=aw_id

@default group=general
@default table=aw_cb_form_chain_entries

	@property confirmed type=checkbox ch_value=1 
	@caption Kinnitatud

@default group=data

	@property data type=releditor reltype=RELTYPE_ENTRY mode=manager props=name

@groupinfo data caption="Andmed"

@reltype ENTRY value=1 clid=CL_REGISTER_DATA
@caption andmed
*/

class cb_form_chain_entry extends class_base
{
	function cb_form_chain_entry()
	{
		$this->init(array(
			"tpldir" => "applications/cb_form_chain",
			"clid" => CL_CB_FORM_CHAIN_ENTRY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "data":
				$prop["direct_links"] = 1;
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

	function show($arr)
	{
		$o = obj($arr["id"]);

		$this->read_template("show.tpl");

		$form_str = "";

		// make a list of form -> entry, then display either table or form
		$f2d = array();
		foreach($o->connections_from(array("type" => "RELTYPE_ENTRY")) as $c)
		{
			$d = $c->to();

			$f2d[$d->meta("webform_id")][] = $d;
		}
		
		foreach($f2d as $wf_id => $entries)
		{
			if (count($entries) > 1)
			{
				$form_str .= $this->_display_data_table($o, $wf_id, $entries);
			}
			else
			{
				$form_str .= $this->_display_data($o, $wf_id, reset($entries));
			}
		}

		$this->vars(array(
			"forms" => $form_str,
		));

		return $this->parse();
	}

	function _display_data_table($o, $wf_id, $entries)
	{
		// make table via component
		classload("vcl/table");
		$t = new aw_table(array("layout" => "generic"));

		$wf = get_instance(CL_WEBFORM);
		$props = $wf->get_props_from_wf(array(
			"id" => $wf_id
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
		foreach($entries as $entry)
		{
			$row = array();
			foreach($props as $pn => $pd)
			{
				if ($pd["type"] == "date_select")
				{
					$row[$pn] = date("d.m.Y", $entry->prop($pn));
				}
				else
				{
					$row[$pn] = $entry->prop_str($pn);
				}
			}
			$t->define_data($row);
		}

		$ret = $t->draw();
		return $ret;
	}

	function _display_data($o, $wf_id, $d)
	{
		$wf = get_instance(CL_WEBFORM);
		$props = $wf->get_props_from_wf(array(
			"id" => $wf_id
		));
		
		foreach($props as $pn => $pd)
		{
			if ($pd["type"] == "date_select")
			{
				if ($d->prop($pn) == 0)
				{
					continue;
				}
				$val = date("d.m.Y", $d->prop($pn));
			}
			else
			{
				$val = $d->prop_str($pn);
			}

			if ($val == "")
			{
				continue;
			}

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
}
?>
