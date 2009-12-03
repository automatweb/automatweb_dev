<?php

/*
@classinfo maintainer=voldemar
*/

class objpicker extends core implements vcl_interface
{
	/**
		@attrib params=name api=1

		@param name required type=string
			String to indetify the object picker

		@param oid required type=int
			The object's ID the picker picks objects for

		@param property required type=string
			The property's name that picker picks objects for

		@param clid optional type=array
			Class id-s of objects to be picked from. Default is empty array, meaning any class object can be picked. If not specified, options must be defined or mode 'autocomplete'

		@param no_sel optional type=bool default=false

		@param no_edit optional type=bool default=false

		@param delete_button optional type=bool default=false

		@param options optional type=array default=array()
			Options to be displayed in the picker select box. Array(oid => caption).

		@param mode optional type=string default=''
			Values: 'autocomplete' Default is NULL

		@param buttonspos optional type=string default=''
			Position for buttons. Values: right, bottom. Default: right

		@returns string
			The HTML of the object picker.
	**/
	public function create($args)
	{
	}

	public function init_vcl_property($args)
	{
		$prop = $args["prop"];
		$name = $prop["name"];

		if (is_oid($args["obj_inst"]->prop($name)))
		{
			$o = new object($args["obj_inst"]->prop($name));
			$value = $o->prop_xml("name");
			$data_element = html::hidden(array("name" => $name, "value" => $o->id()));
		}
		else
		{
			$value = "";
			$data_element = html::hidden(array("name" => $name, "value" => ""));
		}

		if (empty($args["view"]))
		{
			$input_element = html::textbox(array("name" => "{$name}__autocompleteTextbox", "value" => $value));
			$clids = is_array($prop["clid"]) ? implode(",", $prop["clid"]) : $prop["clid"];

			load_javascript("bsnAutosuggest.js");
			$name_options_url = $this->mk_my_orb("get_options", array("clids" => $clids), "objpicker");
			$autocomplete_js = <<<SCRIPT
<script type="text/javascript">
// OBJPICKER {$name} ELEMENT AUTOCOMPLETE
(function(){
var optionsUrl = "{$name_options_url}&";
var options1 = {
	script: optionsUrl,
	varname: "typed_text",
	minchars: 2,
	timeout: 10000,
	delay: 200,
	json: true,
	shownoresults: false,
	callback: function(obj){ $("input[name='{$name}']").attr("value", obj.id) }
};
var nameAS = new AutoSuggest('{$name}__autocompleteTextbox', options1);
})()
// END AUTOCOMPLETE
</script>
SCRIPT;

			$visible_element = $input_element . $autocomplete_js;
		}
		else
		{
			$visible_element = $value;
		}

		$prop["value"] = $visible_element . $data_element;
		return array($name => $prop);
	}

	public function process_vcl_property(&$args)
	{
		$name = $args["prop"]["name"];
		// $args["obj_inst"]->set_prop($name, $args["prop"]["value"]);
	}

	/** Outputs autocomplete options matching object name search string $typed_text in bsnAutosuggest format json
		@attrib name=get_options
		@param clids required type=string
		@param typed_text optional type=string
	**/
	function get_options($args)
	{
		$choices = array("results" => array());

		$clids = explode(",", $args["clids"]);
		$classes_valid = true;
		foreach ($clids as $key => $clid)
		{
			if (!defined($clid))
			{
				$classes_valid = false;
			}
			else
			{
				$clids[$key] = constant($clid);
			}
		}

		if ($classes_valid)
		{
			$typed_text = $args["typed_text"];
			$limit = 20;
			$list = new object_list(array(
				"class_id" => $clids,
				"name" => "{$typed_text}%",
				"site_id" => array(),
				"lang_id" => array(),
				new obj_predicate_limit($limit)
			));

			if ($list->count() > 0)
			{
				$results = array();
				$o = $list->begin();
				do
				{
					$value = $o->prop_xml("name");
					$info = "";
					$results[] = array("id" => $o->id(), "value" => iconv("iso-8859-4", "UTF-8", $value), "info" => $info);
				}
				while ($o = $list->next());
				$choices["results"] = $results;
			}
		}

		ob_start("ob_gzhandler");
		header("Content-Type: application/json");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Pragma: no-cache"); // HTTP/1.0
		// header ("Content-type: text/javascript; charset: UTF-8");
		// header("Expires: ".gmdate("D, d M Y H:i:s", time()+43200)." GMT");
		exit(json_encode($choices));
	}
}

/** Generic objpicker error **/
class awex_vcl_objpicker extends awex_vcl {}

/** Argument type error indicator **/
class awex_vcl_objpicker_arg extends awex_vcl {}


?>
