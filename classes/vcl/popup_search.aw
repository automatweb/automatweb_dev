<?php

class popup_search extends aw_template
{
	function popup_search()
	{
		$this->init("popup_search");
	}

	function init_vcl_property($arr)
	{
		$options = array();
		$name = "popup_search[".$arr["property"]["name"]."]";
		if (is_array($arr["obj_inst"]->meta($name)))
		{
			$options +=  $arr["obj_inst"]->meta($name);
		}

		if (count($options) > 0)
		{
			$ol = new object_list(array(
				"oid" => $options
			));
			$options = $ol->names();
		}

		$tmp = $arr["property"];

		$tmp["type"] = "text";
		$url = $this->mk_my_orb("do_search", array(
			"id" => $arr["obj_inst"]->id(),
			"pn" => $tmp["name"],
			"clid" => constant($tmp["clid"])
		));

		$tmp["value"] = html::select(array(
			"name" => $arr["property"]["name"],
			"options" => array("" => "--Vali--") + $options,
			"selected" => $arr["obj_inst"]->prop($arr["property"]["name"])
		)).html::href(array(
			"url" => "javascript:aw_popup_scroll(\"$url\",\"Otsing\",550,500)",
			"caption" => "Otsi"
		));

		return array(
			$arr["property"]["name"] => $tmp,
		);
	}

	function process_vcl_property($arr)
	{
	}

	/**
		
		@attrib name=do_search

		@param id required type=int acl=view
		@param pn required 
		@param clid required type=int
		@param s optional

	**/
	function do_search($arr)
	{
		$form_html = $this->_get_form($arr);

		$res_html = $this->_get_results($arr);

		return $form_html."<br>".$res_html;
	}

	function _get_form($arr)
	{
		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();

		$htmlc->add_property(array(
			"name" => "s[name]",
			"type" => "textbox",
			"value" => $arr["s"]["name"],
			"caption" => "Nimi"
		));

		$htmlc->add_property(array(
			"name" => "s[comment]",
			"type" => "textbox",
			"value" => $arr["s"]["comment"],
			"caption" => "Kommentaar"
		));

		$htmlc->add_property(array(
			"name" => "s[oid]",
			"type" => "textbox",
			"value" => $arr["s"]["oid"],
			"caption" => "OID"
		));

		$htmlc->add_property(array(
			"name" => "s[submit]",
			"type" => "submit",
			"value" => "Otsi",
			"caption" => "Otsi"
		));

		$htmlc->finish_output(array(
			"action" => "do_search",
			"method" => "GET",
			"data" => array(
				"id" => $arr["id"],
				"pn" => $arr["pn"],
				"clid" => $arr["clid"],
				"orb_class" => "popup_search",
				"reforb" => 0
			)
		));

		$html = $htmlc->get_result();

		return $html;
	}

	function _get_results($arr)
	{
		$this->read_template("table.tpl");

		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));

		$t->define_field(array(
			"name" => "oid",
			"caption" => "OID",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "name",
			"sortable" => 1,
			"caption" => "Nimi"
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"sortable" => 1,
			"caption" => "Muutja"
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"sortable" => 1,
			"format" => "d.m.Y H:i",
			"type" => "time"
		));
		$t->define_field(array(
			"name" => "sel",
			"caption" => "Vali"
		));
		$t->set_default_sortby("name");

		$filter = array(
			"class_id" => $arr["clid"],
		);

		$awa = new aw_array($arr["s"]);
		foreach($awa->get() as $k => $v)
		{
			if ($v != "")
			{
				$filter[$k] = "%".$v."%";
			}
		}

		if (count($filter) > 1)
		{
			$ol = new object_list($filter);
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				$mod = $o->modifiedby();
				$t->define_data(array(
					"oid" => $o->id(),
					"name" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
						"caption" => $o->path_str()
					)),
					"modifiedby" => $mod->name(),
					"modified" => $o->modified(),
					"sel" => html::checkbox(array(
						"name" => "sel[]",
						"value" => $o->id()
					))
				));
			}
		}

		$t->sort_by();
		$this->vars(array(
			"table" => $t->draw(),
			"reforb" => $this->mk_reforb("final_submit", array(
				"id" => $arr["id"],
				"pn" => $arr["pn"],
				"clid" => $arr["clid"]
			))
		));

		return $this->parse();
	}

	/**

		@attrib name=final_submit all_args="1" 

	**/
	function final_submit($arr)
	{
		// available options are in metadata, selected option value of the property
		$o = obj($arr["id"]);
		$o->set_meta("popup_search[".$arr["pn"]."]", $this->make_keys($arr["sel"]));
		if (is_array($arr["sel"]) && count($arr["sel"]) == 1)
		{
			$o->set_prop($arr["pn"], $arr["sel"][0]);
		}
		$o->save();
		die("
			<html><body><script language='javascript'>window.opener.location.reload();window.close();</script></body></html>
		");
	}
}
?>