<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_style.aw,v 2.3 2001/06/18 21:13:27 kristo Exp $
// AW Style Engine.
class aw_style 
{
	var $tags;
	function aw_style()
	{
		$this->tags = array();
	}

	// loeb sisse XML formaadis stiilifaili. See kust data tuleb pole enam selle klassi
	// vaid calleri probleem
	function define_styles($data)
	{
		// that's the whole magic
		$parser = xml_parser_create();
		xml_parse_into_struct($parser,$data,&$values,&$tags);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parser_free($parser);

		foreach ($values as $element)
		{
			if ($element["tag"] == "TAG")
			{
				$id = $element["attributes"]["ID"];
				$this->tags[$id] = $element["value"];
			};
		};
	}

	function parse_text($text)
	{
		reset($this->tags);
		foreach ($this->tags as $tag => $val)
		{
			$find = sprintf("<%s>(.*)<\\/%s>",$tag,$tag);
			$val = trim($val);
			$text = preg_replace("/" . $find . "/isU",$val,$text);
		};
		return $text;
	}
};
?>
