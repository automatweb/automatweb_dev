<?php

class keyword_selector extends class_base
{
	function keyword_selector()
	{
		$this->init("vcl/keyword_selector");
	}

	function init_vcl_property($arr)
	{
		$tp = $arr["prop"];
		$tp["type"] = "text";
		
		$content = $this->_draw_existing_kws($arr)."<br><br><br>";
		$content .= $this->_draw_alphabet($arr);

		$tp["value"] = $content;
		return array($tp["name"] => $tp);
	}

	function callback_mod_reforb($arr, $r)
	{
		$arr["kw_sel_filt"] = $r["kw_sel_filt"];
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["kw_sel_filt"] = $arr["request"]["kw_sel_filt"];
	}

	function process_vcl_property($arr)
	{
		$data = safe_array($arr["request"]["kw_sel_".$arr["prop"]["name"]]);
		$filt = $arr["request"]["kw_sel_filt"];
		if (empty($filt))
		{
			$filt = "A";
		}
		$cf_p = array("to.class_id" => CL_KEYWORD, "type" => "RELTYPE_KEYWORD");
		if ($filt != "_all")
		{
			$cf_p["to.name"] = $filt."%";
		}

		$conns = $arr["obj_inst"]->connections_from($cf_p);
		// go over and delete all that do not exist in submit
		foreach($conns as $con_id => $con)
		{
			if (!isset($data[$con->prop("to")]))
			{
				$con->delete();
			}
		}

		// add new
		foreach($data as $kwid => $one)
		{
			$arr["obj_inst"]->connect(array("to" => $kwid));
		}

		if (count($arr["obj_inst"]->connections_from(array("to.class_id" => CL_KEYWORD, "type" => "RELTYPE_KEYWORD"))))
		{
			$arr["obj_inst"]->set_meta("has_kwd_rels", 1);
		}
		else
		{
			$arr["obj_inst"]->set_meta("has_kwd_rels", 0);
		}
	}

	function _init_kw_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "right"
		));

		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "name_2",
			"caption" => t("Nimi"),
			"align" => "right"
		));

		$t->define_field(array(
			"name" => "sel_2",
			"caption" => t("Vali"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "name_3",
			"caption" => t("Nimi"),
			"align" => "right"
		));

		$t->define_field(array(
			"name" => "sel_3",
			"caption" => t("Vali"),
			"align" => "center"
		));
	}

	function _draw_alphabet($arr)
	{
		classload("vcl/table");
		$t = new aw_table();
		$this->_init_kw_t($t);

		$filt = array(
			"class_id" => CL_KEYWORD
		);
		if (empty($arr["request"]["kw_sel_filt"]))
		{
			$arr["request"]["kw_sel_filt"] = "_all";
		}
		if ($arr["request"]["kw_sel_filt"] != "_all")
		{
			$filt["name"] = $arr["request"]["kw_sel_filt"]."%";
		}
		$filt["sort_by"] = "objects.name";
		$ol = new object_list($filt);
		$used_kws = new object_list($arr["obj_inst"]->connections_from(array("to.class_id" => CL_KEYWORD, "type" => "RELTYPE_KEYWORD")));
		$used_kws = $this->make_keys($used_kws->ids());
		$data = array_values($ol->arr());
		$rows = count($data) / 3;

		$cnt = 0;
		for($i = 0; $i < $rows; $i++)
		{
			$kw1 = $data[$cnt++];
			$rowd[$i] = array(
				"name" => html::obj_change_url($kw1),
				"sel" => html::checkbox(array(
					"name" => "kw_sel_".$arr["prop"]["name"]."[".$kw1->id()."]",
					"value" => 1,
					"checked" => isset($used_kws[$kw1->id()])
				))
			);
		}
		for($i = 0; $i < $rows; $i++)
		{
			$kw1 = $data[$cnt++];
			if (!$kw1)
			{
				continue;
			}
			$rowd[$i]["name_2"] = html::obj_change_url($kw1);
			$rowd[$i]["sel_2"] = html::checkbox(array(
				"name" => "kw_sel_".$arr["prop"]["name"]."[".$kw1->id()."]",
				"value" => 1,
				"checked" => isset($used_kws[$kw1->id()])
			));
		}
		for($i = 0; $i < $rows; $i++)
		{
			$kw1 = $data[$cnt++];
			if (!$kw1)
			{
				continue;
			}
			$rowd[$i]["name_3"] = html::obj_change_url($kw1);
			$rowd[$i]["sel_3"] = html::checkbox(array(
				"name" => "kw_sel_".$arr["prop"]["name"]."[".$kw1->id()."]",
				"value" => 1,
				"checked" => isset($used_kws[$kw1->id()])
			));
		}

		foreach($rowd as $row)
		{
			$t->define_data($row);
		}
		$t->set_header($this->_get_alpha_list($arr["request"]));
		return $t->draw();
	}

	function _get_alpha_list($r)
	{
		if (empty($r["kw_sel_filt"]))
		{
			$r["kw_sel_filt"] = "_all";
		}
		$list = array();
		for($i = ord('A'); $i <= ord('Z'); $i++)
		{
			if ($r["kw_sel_filt"] == chr($i))
			{
				$list[] = chr($i);
			}
			else
			{
				$list[] = html::href(array(
					"caption" => chr($i),
					"url" => aw_url_change_var("kw_sel_filt",chr($i))
				));
			}
		}


		if ($r["kw_sel_filt"] == "_all")
		{
			$list[] = t("K&otilde;ik");
		}
		else
		{
			$list[] = html::href(array(
				"caption" => t("K&otilde;ik"),
				"url" => aw_url_change_var("kw_sel_filt", "_all")	
			));
		}
		return join(" ", $list);
	}

	function _draw_existing_kws($arr)
	{
		$kws = new object_list($arr["obj_inst"]->connections_from(array("to.class_id" => CL_KEYWORD, "type" => $arr["prop"]["reltype"])));
		return t("Valitud:")." ".html::obj_change_url($kws->arr());
	}
}
?>