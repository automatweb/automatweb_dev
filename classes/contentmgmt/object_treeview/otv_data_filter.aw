<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/otv_data_filter.aw,v 1.1 2005/03/08 13:29:57 kristo Exp $
// otv_data_filter.aw - Andmeallika andmete muundaja 
/*

@classinfo syslog_type=ST_OTV_DATA_FILTER relationmgr=yes

@default table=objects
@default group=general

@default group=str_replace

	@property str_replace type=table no_caption=1

@default group=char_replace

	@property char_replace type=table no_caption=1

@groupinfo str_replace caption="Teksti asendused"
@groupinfo char_replace caption="T&auml;htede asendused"

*/

class otv_data_filter extends class_base
{
	function otv_data_filter()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/object_treeview/otv_data_filter",
			"clid" => CL_OTV_DATA_FILTER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "str_replace":
				$this->_str_replace($arr);
				break;

			case "char_replace":
				$this->_char_replace($arr);
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
			case "str_replace":
				$this->_save_str_replace($arr);
				break;

			case "char_replace":
				$this->_save_char_replace($arr);
				break;
		}
		return $retval;
	}	

	function _init_str_replace_t(&$t)
	{
		$t->define_field(array(
			"name" => "from",
			"caption" => "Mis asendada",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "to",
			"caption" => "Millega asendada",
			"align" => "center"
		));
	}

	function _str_replace($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_str_replace_t($t);

		$replaces = safe_array($arr["obj_inst"]->meta("str_replace"));
		$replaces[] = array();
		foreach($replaces as $idx => $row)
		{
			$t->define_data(array(
				"from" => html::textbox(
					"name" => "replaces[$idx][from]",
					"value" => htmlspecialchars($replaces[$idx]["from"])
				)),
				"to" => html::textbox(
					"name" => "replaces[$idx][to]",
					"value" => htmlspecialchars($replaces[$idx]["to"])
				)),
			));
		}

		$t->set_sortable(false);
	}

	function _save_str_replace($arr)
	{
		$sr = array();
		forach(safe_array($arr["request"]["replace"]) as $row)
		{
			if ($row["from"] != "" && $row["to"] != "")
			{
				$sr[] = $row;
			}
		}
		$arr["obj_inst"]->set_meta("str_replace", $sr);
	}

	function _init_char_replace_t(&$t)
	{
		$t->define_field(array(
			"name" => "from",
			"caption" => "Mis asendada",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "to",
			"caption" => "Millega asendada",
			"align" => "center"
		));
	}

	function _char_replace($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_char_replace_t($t);

		$replaces = safe_array($arr["obj_inst"]->meta("char_replace"));
		$replaces[] = array();
		foreach($replaces as $idx => $row)
		{
			$t->define_data(array(
				"from" => html::textbox(
					"name" => "replaces[$idx][from]",
					"value" => htmlspecialchars($replaces[$idx]["from"])
				)),
				"to" => html::textbox(
					"name" => "replaces[$idx][to]",
					"value" => htmlspecialchars($replaces[$idx]["to"])
				)),
			));
		}

		$t->set_sortable(false);
	}

	function _save_char_replace($arr)
	{
		$sr = array();
		forach(safe_array($arr["request"]["replace"]) as $row)
		{
			if ($row["from"] != "" && $row["to"] != "")
			{
				$sr[] = $row;
			}
		}
		$arr["obj_inst"]->set_meta("char_replace", $sr);
	}

	function callback_mod_reforb($arr)
	{
		$arr["return_url"] = post_ru();
	}

	/** transforms the data given according to the rules

		@attrib api=1

		@comment
	
			$o - transformer object to use
			$data - data to transform
	**/
	function transform($o, &$data)
	{
		
	}
}
?>
