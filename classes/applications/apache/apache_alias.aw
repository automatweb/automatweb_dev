<?php
// $Header
// Apache alias management

/*

@classinfo relationmgr=yes no_status=1 no_comment=1 syslog_type=ST_APACHE_ALIAS
@default group=general 

@tableinfo apache_aliases master_index=brother_of master_table=objects index=id

@property alias type=textbox table=apache_aliases
@caption Alias

@property dir type=textbox table=apache_aliases
@caption Kataloog serveris

@groupinfo list caption="Nimekiri"
@default group=list

@property tbl type=table
@caption Tabel

@property list type=text
@caption Apache konfiguratsioon

*/
class apache_alias extends class_base
{
	function apache_alias()
	{
		$this->init(array(
			"tpldir" => "apache_alias",
			"clid" => CL_APACHE_ALIAS
		));
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "tbl":
				$this->gen_list($arr);
				break;

			case "list":
				$prop["value"] = $this->gen_conf();
				break;
		}
		return PROP_OK;
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "alias":
				if ($prop["value"] == "")
				{
					$prop["error"] = t("Alias ei saa olla tühi!");
					return PROP_ERROR;
				}
				$filt = array(
					"class_id" => CL_APACHE_ALIAS,
					"alias" => $prop["value"],
				);
				if (is_oid($arr["obj_inst"]->id()))
				{
					$filt["oid"] = new obj_predicate_not($arr["obj_inst"]->id());
				}
				$ol = new object_list($filt);
				if ($ol->count() > 0)
				{
					$prop["error"] = t("Sellise nimega alias on juba olemas!");
					return PROP_ERROR;
				}
				break;

			case "dir":
				if ($prop["value"] == "")
				{
					$prop["error"] = t("Kataloog ei saa olla tühi!!");
					return PROP_ERROR;
				}
				break;
		}

		return PROP_OK;
	}

	function _init_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "oid",
			"caption" => t("ID"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "alias",
			"caption" => t("Alias"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "dir",
			"caption" => t("Kataloog serveris"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y / H:i"
		));

		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Autor"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y / H:i"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center"
		));
	}

	function gen_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_tbl($t);

		$ol = new object_list(array(
			"class_id" => CL_APACHE_ALIAS,
			"lang_id" => array(),
		));
		$t->data_from_ol($ol, array("change_col" => "alias"));
	}

	function gen_conf($args = array())
	{
		$ol = new object_list(array(
			"class_id" => CL_APACHE_ALIAS,
			"lang_id" => array(),
		));
		$conf = "";
		foreach($ol->arr() as $o)
		{
			$conf .= "Alias /".$o->prop("alias")." ".$o->prop("dir")."\n";
		};
		$fp = @fopen($this->cfg["aliasfile"],"w");
		if (!$fp)
		{
			$conf = sprintf(t("Faili %s ei saanud kirjutamiseks avada! <br>-------------------<br><br>"), $this->cfg["aliasfile"]).$conf;
		}
		else
		{
			fputs($fp,$conf);
			fclose($fp);
		};
		return $conf;
	}

}

?>
