<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/cb_form_chain/cb_form_chain_entry.aw,v 1.15 2006/04/24 06:44:12 kristo Exp $
// cb_form_chain_entry.aw - Vormiahela sisestus
/*

@classinfo syslog_type=ST_CB_FORM_CHAIN_ENTRY relationmgr=yes no_comment=1 no_status=1

@tableinfo aw_cb_form_chain_entries index=aw_id

@default group=general
@default table=aw_cb_form_chain_entries

	@property confirmed type=checkbox ch_value=1
	@caption Kinnitatud

	@property cb_form_id type=hidden
	@caption Vormiahela id

@default group=data

	@property data type=releditor reltype=RELTYPE_ENTRY mode=manager props=name no_caption=1

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

	/**
		@attrib name=show_pdf
		@param id required type=int acl=view
	**/
	function show_pdf($arr)
	{
		$html = $this->show($arr);
		$c = get_instance("core/converters/html2pdf");
		header("Content-type: application/pdf");
		die($c->convert(array("source" => $html)));
	}

	/**
		@attrib name=show
		@param id required type=int acl=view
	**/
	function show($arr)
	{
		$o = obj($arr["id"]);

		$d = array();
		if (is_oid($o->prop("cb_form_id")) && $this->can("view", $o->prop("cb_form_id")))
		{
			$form = obj($o->prop("cb_form_id"));
			$di = safe_array($form->meta("d"));
		}

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
			if (!$wf_id)
			{
				continue;
			}
			if (count($entries) > 1)
			{
				$form_str .= $this->_display_data_table($o, $wf_id, $entries, $di);
			}
			else
			{
				$form_str .= $this->_display_data($o, $wf_id, reset($entries));
			}
		}
		$this->vars(array(
			"forms" => $form_str,
			"gen_ctr_res" => $_SESSION['gen_ctr_res_value'],
		));

		return $this->parse();
	}

	function _display_data_table($o, $wf_id, $entries, $d = array())
	{
		// make table via component
		classload("vcl/table");
		$t = new aw_table(array("layout" => "generic"));

		$wf = get_instance(CL_WEBFORM);
		$props = $wf->get_props_from_wf(array(
			"id" => $wf_id
		));

		if ($d[$wf_id]["data_table_confirm_vert"] == 1)
		{
			$t->define_field(array(
				"name" => "capt",
				"caption" => t(""),
				"align" => "center"
			));
			foreach($entries as $idx => $entry)
			{
				$t->define_field(array(
					"name" => "e".$idx,
					"caption" => t(""),
					"align" => "center"
				));
			}

			// go over all datas
			foreach($props as $pn => $pd)
			{
				$row = array("capt" => $pd["caption"]);
				foreach($entries as $idx => $entry)
				{
					$metaf = $entry->meta("metaf");
					if ($pd["type"] == "date_select")
					{
						$row["e".$idx] = date("d.m.Y", $entry->prop($pn));
					}
					else
					if ($pd["type"] == "text")
					{
						$row["e".$idx] = $metaf[$pn];
					}
					else
					{
						if ($pd["type"] == "classificator" && $pd["store"] == "connect")
						{
							$ol = new object_list($entry->connections_from(array("type" => $pd["reltype"])));
							$row["e".$idx] = join("<br>", $ol->names());
						}
						else
						{
							$row["e".$idx] = $entry->prop_str($pn);
						}
					}
				}
				$t->define_data($row);
			}

		}
		else
		{
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
				$metaf = $entry->meta("metaf");
				foreach($props as $pn => $pd)
				{
					if ($pd["type"] == "date_select")
					{
						$row[$pn] = date("d.m.Y", $entry->prop($pn));
					}
					else
					if ($pd["type"] == "text")
					{
						$row[$pn] = $metaf[$pn];
					}
					else
					{
						if ($pd["type"] == "classificator" && $pd["store"] == "connect")
						{
							$ol = new object_list($entry->connections_from(array("type" => $pd["reltype"])));
							$row[$pn] = join("<br>", $ol->names());
						}
						else
						{
							$row[$pn] = $entry->prop_str($pn);
						}
					}
				}
				$t->define_data($row);
			}
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

		$metaf = $d->meta("metaf");
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
			if ($pd["type"] == "text")
			{
				$val = $metaf[$pn];
			}
			else
			{
				if ($pd["type"] == "classificator" && $pd["store"] == "connect")
				{
					$ol = new object_list($d->connections_from(array("type" => $pd["reltype"])));
					$val = join("<br>", $ol->names());
				}
				else
				{
					$val = $d->prop_str($pn);
				}
			}
			if ($pd["type"] == "releditor")
			{
				// file upload, get conn file and
				$o = $d->get_first_obj_by_reltype($pd["reltype"]);
				if ($o)
				{
					$file_i = get_instance(CL_FILE);
					$val = html::href(array(
						"url" => $file_i->get_url($o->id(), $o->name()),
						"caption" => $o->name()
					));
				}
			}
			if ($val == "")
			{
			//	continue;
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

	function do_db_upgrade($t, $f)
	{
		switch($f)
		{
			case "data":
				$this->db_query("ALTER TABLE $t ADD $f int");
				return true;
		}
	}
}
?>
