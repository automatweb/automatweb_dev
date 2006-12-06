<?php

class crm_company_cedit_impl extends core
{
	function crm_company_cedit_impl()
	{
		$this->init();
	}

	function _get_phone_tbl(&$t, $arr)
	{
		$pn = "phone_id";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "phone";
		}
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_PHONE",
		));
		$i = get_instance(CL_CRM_PHONE);
		$ptypes = $i->get_phone_types();
		foreach($conns as $conn)
		{
			$obj = $conn->to();
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => $obj->id(),
				"checked" => $arr["obj_inst"]->prop($pn) == $obj->id()?1:0,
			));
			$ch_url = aw_url_change_var("cedit_tbl_edit", $obj->id());

			if ($arr["request"]["cedit_tbl_edit"] == $obj->id())
			{
				$types = array();
				foreach($ptypes as $_type_id => $_type_val)
				{		
					$types[] = html::radiobutton(array(
						"name" => "cedit_phone[".$obj->id()."][type]",
						"checked" => $obj->prop("type") == $_type_id,
						"caption" => $_type_val,
						"value" => $_type_id
					));
				}
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"number" => html::textbox(array(
						"name" => "cedit_phone[".$obj->id()."][name]",
						"value" => $obj->name(),
						"size" => 15
					)),
					"type" => html::select(array(
						"name" => "cedit_phone[-1][type]",
						"options" => $ptypes
					)),	//join(" ", $types),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
			else
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"number" => $obj->name(),
					"type" => $ptypes[$obj->prop("type")],
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
		}
		if (!$arr["request"]["cedit_tbl_edit"])
		{
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => -1,
				"checked" => $this->can("view", $arr["obj_inst"]->prop($pn)) ? 0 : 1,
			));
			$types = array();
			foreach($ptypes as $_type_id => $_type_val)
			{		
				$types[] = html::radiobutton(array(
					"name" => "cedit_phone[-1][type]",
					"value" => $_type_id,
					"caption" => $_type_val
				));
			}
			$t->define_data(array(
				"choose" => $chooser,
				"number" => html::textbox(array(
					"name" => "cedit_phone[-1][name]",
					"value" => "",
					"size" => 15
				)),
				"type" => html::select(array(
					"name" => "cedit_phone[-1][type]",
					"options" => $ptypes
				)),//join(" ", $types),
				"change" => ""
			));
		}
		$t->set_sortable(false);
	}

	function _get_fax_tbl(&$t, $arr)
	{
		$pn = "telefax_id";
		$tp = "RELTYPE_TELEFAX";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "fax";
			$tp = "RELTYPE_FAX";
		}
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => $tp,
		));
		$i = get_instance(CL_CRM_PHONE);
		foreach($conns as $conn)
		{
			$obj = $conn->to();
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => $obj->id(),
				"checked" => $arr["obj_inst"]->prop($pn) == $obj->id()?1:0,
			));
			$ch_url = aw_url_change_var("cedit_tbl_edit_f", $obj->id());

			if ($arr["request"]["cedit_tbl_edit_f"] == $obj->id())
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"number" => html::textbox(array(
						"name" => "cedit_fax[".$obj->id()."][name]",
						"value" => $obj->name(),
						"size" => 15
					)),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
			else
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"number" => $obj->name(),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
		}
		if (!$arr["request"]["cedit_tbl_edit_f"])
		{
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => -1,
				"checked" => $this->can("view", $arr["obj_inst"]->prop($pn)) ? 0 : 1,
			));
			$t->define_data(array(
				"choose" => $chooser,
				"number" => html::textbox(array(
					"name" => "cedit_fax[-1][name]",
					"value" => "",
					"size" => 15
				)),
				"change" => ""
			));
		}
		$t->set_sortable(false);
	}

	function _set_cedit_phone_tbl($arr)
	{
		$pn = "phone_id";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "phone";
		}
		foreach(safe_array($arr["request"]["cedit_phone"]) as $id => $data)
		{
			if ($this->can("view", $id))
			{
				$o = obj($id);
				foreach($data as $k => $v)
				{
					$o->set_prop($k, $v);
				}
				$o->save();
			}
			else
			if ($id == -1)
			{
				$o = obj();
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_class_id(CL_CRM_PHONE);
				$o->set_name($data["name"]);
				$has = false;
				foreach($data as $k => $v)
				{
					if ($v != "" && $v != "work")
					{
						$has = true;
					}
					$o->set_prop($k, $v);
				}
				if ($has)
				{
					$o->save();
					$arr["obj_inst"]->connect(array(
						"to" => $o->id(),
						"type" => "RELTYPE_PHONE"
					));
					if ($arr["request"]["cedit"]["cedit_phone_tbl"] == -1)
					{
						$arr["obj_inst"]->set_prop($pn, $o->id());
					}
				}
			}
		}

		if ($this->can("view", $arr["request"]["cedit"]["cedit_phone_tbl"]))
		{
			$arr["obj_inst"]->set_prop($pn, $arr["request"]["cedit"]["cedit_phone_tbl"]);
		}
	}

	function _set_cedit_telefax_tbl($arr)
	{
		$pn = "telefax_id";
		$tp = "RELTYPE_TELEFAX";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "fax";
			$tp = "RELTYPE_FAX";
		}
		foreach(safe_array($arr["request"]["cedit_fax"]) as $id => $data)
		{
			if ($this->can("view", $id))
			{
				$o = obj($id);
				$o->set_name($data["name"]);
				$o->save();
			}
			else
			if ($id == -1)
			{
				$o = obj();
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_class_id(CL_CRM_PHONE);
				$o->set_name($data["name"]);
				$has = false;
				foreach($data as $k => $v)
				{
					if ($v != "")
					{
						$has = true;
					}
					$o->set_prop($k, $v);
				}
				if ($has)
				{
					$o->save();
					$arr["obj_inst"]->connect(array(
						"to" => $o->id(),
						"type" => $tp
					));
					if ($arr["request"]["cedit"]["cedit_telefax_tbl"] == -1)
					{
						$arr["obj_inst"]->set_prop($pn, $o->id());
					}
				}
			}
		}

		if ($this->can("view", $arr["request"]["cedit"]["cedit_telefax_tbl"]))
		{
			$arr["obj_inst"]->set_prop($pn, $arr["request"]["cedit"]["cedit_telefax_tbl"]);
		}
	}

	function _get_url_tbl(&$t, $arr)
	{
		$pn = "url_id";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "url";
		}
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_URL",
		));
		foreach($conns as $conn)
		{
			$obj = $conn->to();
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => $obj->id(),
				"checked" => $arr["obj_inst"]->prop($pn) == $obj->id()?1:0,
			));
			$ch_url = aw_url_change_var("cedit_tbl_edit_u", $obj->id());

			if ($arr["request"]["cedit_tbl_edit_u"] == $obj->id())
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"url" => html::textbox(array(
						"name" => "cedit_url[".$obj->id()."][url]",
						"value" => $obj->name(),
						"size" => 15
					)),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
			else
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"url" => $obj->name(),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
		}
		if (!$arr["request"]["cedit_tbl_edit_u"])
		{
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => -1,
				"checked" => $this->can("view", $arr["obj_inst"]->prop($pn)) ? 0 : 1,
			));
			$t->define_data(array(
				"choose" => $chooser,
				"url" => html::textbox(array(
					"name" => "cedit_url[-1][url]",
					"value" => "http://",
					"size" => 15
				)),
				"change" => ""
			));
		}
		$t->set_sortable(false);
	}

	function _set_cedit_url_tbl($arr)
	{
		$pn = "url_id";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "url";
		}
		foreach(safe_array($arr["request"]["cedit_url"]) as $id => $data)
		{
			if ($this->can("view", $id))
			{
				$o = obj($id);
				$o->set_name($data["url"]);
				$o->set_prop("url",$data["url"]);
				$o->save();
			}
			else
			if ($id == -1)
			{
				$o = obj();
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_class_id(CL_EXTLINK);
				$o->set_name($data["url"]);
				$has = false;
				foreach($data as $k => $v)
				{
					if ($v != "" && $v != "http://")
					{
						$has = true;
					}
					$o->set_prop($k, $v);
				}
				if ($has)
				{
					$o->save();
					$arr["obj_inst"]->connect(array(
						"to" => $o->id(),
						"type" => "RELTYPE_URL"
					));
					if ($arr["request"]["cedit"]["cedit_url_tbl"] == -1)
					{
						$arr["obj_inst"]->set_prop($pn, $o->id());
					}
				}
			}
		}

		if ($this->can("view", $arr["request"]["cedit"]["cedit_url_tbl"]))
		{
			$arr["obj_inst"]->set_prop($pn, $arr["request"]["cedit"]["cedit_url_tbl"]);
		}
	}

	function _get_email_tbl(&$t, $arr)
	{
		$pn = "email_id";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "email";
		}
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_EMAIL",
		));
		foreach($conns as $conn)
		{
			$obj = $conn->to();
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => $obj->id(),
				"checked" => $arr["obj_inst"]->prop($pn) == $obj->id()?1:0,
			));
			$ch_url = aw_url_change_var("cedit_tbl_edit_e", $obj->id());

			if ($arr["request"]["cedit_tbl_edit_e"] == $obj->id())
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"email" => html::textbox(array(
						"name" => "cedit_email[".$obj->id()."][email]",
						"value" => $obj->prop("mail"),
						"size" => 15
					)),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
			else
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"email" => $obj->prop("mail"),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
		}
		if (!$arr["request"]["cedit_tbl_edit_e"])
		{
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => -1,
				"checked" => $this->can("view", $arr["obj_inst"]->prop($pn)) ? 0 : 1,
			));
			$t->define_data(array(
				"choose" => $chooser,
				"email" => html::textbox(array(
					"name" => "cedit_email[-1][email]",
					"value" => "",
					"size" => 15
				)),
				"change" => ""
			));
		}
		$t->set_sortable(false);
	}

	function _set_cedit_email_tbl($arr)
	{
		$pn = "email_id";
		if ($arr["obj_inst"]->class_id() == CL_CRM_PERSON)
		{
			$pn = "email";
		}
		foreach(safe_array($arr["request"]["cedit_email"]) as $id => $data)
		{
			if ($this->can("view", $id))
			{
				$o = obj($id);
				$o->set_name($data["email"]);
				$o->set_prop("mail",$data["email"]);
				$o->save();
			}
			else
			if ($id == -1)
			{
				$o = obj();
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_class_id(CL_ML_MEMBER);
				$o->set_name($data["email"]);
				$has = false;
				foreach($data as $k => $v)
				{
					if ($v != "")
					{
						$has = true;
					}
					$o->set_prop("mail", $v);
				}
				if ($has)
				{
					$o->save();
					$arr["obj_inst"]->connect(array(
						"to" => $o->id(),
						"type" => "RELTYPE_EMAIL"
					));
					if ($arr["request"]["cedit"]["cedit_email_tbl"] == -1)
					{
						$arr["obj_inst"]->set_prop($pn, $o->id());
					}
				}
			}
		}

		if ($this->can("view", $arr["request"]["cedit"]["cedit_email_tbl"]))
		{
			$arr["obj_inst"]->set_prop($pn, $arr["request"]["cedit"]["cedit_email_tbl"]);
		}
	}


	function _get_acct_tbl(&$t, $arr)
	{
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_BANK_ACCOUNT",
		));
		$banks_ol = new object_list(array(
			"class_id" => CL_CRM_BANK,
			"lang_id" => array(),
			"site_id" => array()
		));
		$banks = array("" => t("--vali--")) + $banks_ol->names();
		foreach($conns as $conn)
		{
			$obj = $conn->to();
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => $obj->id(),
				"checked" => $arr["obj_inst"]->prop("aw_bank_account") == $obj->id()?1:0,
			));
			$ch_url = aw_url_change_var("cedit_tbl_edit_a", $obj->id());

			if ($arr["request"]["cedit_tbl_edit_a"] == $obj->id())
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"name" => html::textbox(array(
						"name" => "cedit_acct[".$obj->id()."][name]",
						"value" => $obj->name(),
						"size" => 15
					)),
					"account" => html::textbox(array(
						"name" => "cedit_acct[".$obj->id()."][acct_no]",
						"value" => $obj->prop("acct_no"),
						"size" => 15
					)),
					"bank" => html::select(array(
						"name" => "cedit_acct[".$obj->id()."][bank]",
						"value" => $obj->prop("bank"),
						"options" => $banks
					)),
					"office_code" => html::textbox(array(
						"name" => "cedit_acct[".$obj->id()."][sort_code]",
						"value" => $obj->prop("sort_code"),
						"size" => 15
					)),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
			else
			{
				$t->define_data(array(
					"sel" => $obj->id(),
					"choose" => $chooser,
					"name" => $obj->name(),
					"account" => $obj->prop("acct_no"),
					"bank" => $obj->prop("bank.name"),
					"office_code" => $obj->prop("sort_code"),
					"change" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $ch_url,
					)),
				));
			}
		}
		if (!$arr["request"]["cedit_tbl_edit_a"])
		{
			$chooser = html::radiobutton(array(
				"name" => "cedit[".$arr["prop"]["name"]."]",
				"value" => -1,
				"checked" => $this->can("view", $arr["obj_inst"]->prop("aw_bank_account")) ? 0 : 1,
			));
			$t->define_data(array(
				"choose" => $chooser,
				"name" => html::textbox(array(
					"name" => "cedit_acct[-1][name]",
					"value" => "",
					"size" => 15
				)),
				"account" => html::textbox(array(
					"name" => "cedit_acct[-1][acct_no]",
					"value" => "",
					"size" => 15
				)),
				"bank" => html::select(array(
					"name" => "cedit_acct[-1][bank]",
					"value" => "",
					"options" => $banks
				)),
				"office_code" => html::textbox(array(
					"name" => "cedit_acct[-1][sort_code]",
					"value" => "",
					"size" => 15
				)),
				"change" => ""
			));
		}
		$t->set_sortable(false);
	}

	function _set_cedit_bank_account_tbl($arr)
	{
		foreach(safe_array($arr["request"]["cedit_acct"]) as $id => $data)
		{
			if ($this->can("view", $id))
			{
				$o = obj($id);
				$o->set_name($data["name"]);
				foreach($data as $k => $v)
				{
					$o->set_prop($k, $v);
				}
				$o->save();
			}
			else
			if ($id == -1)
			{
				$o = obj();
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_class_id(CL_CRM_BANK_ACCOUNT);
				$o->set_name($data["name"]);
				$has = false;
				foreach($data as $k => $v)
				{
					if ($v != "")
					{
						$has = true;
					}
					$o->set_prop($k, $v);
				}
				if ($has)
				{
					$o->save();
					$arr["obj_inst"]->connect(array(
						"to" => $o->id(),
						"type" => "RELTYPE_BANK_ACCOUNT"
					));
					if ($arr["request"]["cedit"]["cedit_bank_account_tbl"] == -1)
					{
						$arr["obj_inst"]->set_prop("aw_bank_account", $o->id());
					}
				}
			}
		}

		if ($this->can("view", $arr["request"]["cedit"]["cedit_bank_account_tbl"]))
		{
			$arr["obj_inst"]->set_prop("aw_bank_account", $arr["request"]["cedit"]["cedit_bank_account_tbl"]);
		}
	}

	function init_cedit_tables($t, $fields)
	{
		$t->define_chooser(array(
			"name" => "select",
			"field" => "sel",
			"width" => "60",
		));
		foreach($fields as $name => $caption)
		{
			if($name == "choose")
			{
				$width = "10%";
			}
			elseif($name == "change")
			{
				$width = "60px";
			}
			$t->define_field(array(
				"name" => $name,
				"caption" => $caption,
				"width" => $width,
			));
		}
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"width" => "80",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "choose",
			"caption" => t("Vali &uuml;ks"),
			"width" => "60",
			"align" => "center",
		));
	}
}