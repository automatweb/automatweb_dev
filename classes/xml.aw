<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/xml.aw,v 2.6 2001/08/12 23:21:14 kristo Exp $
// xml.aw - generic class for handling data in xml format.
// at the moment (Apr 25, 2001) it can serialize PHP arrays to XML and vice versa
// now, I'm working on adding XML-RPC format support for this.
class xml {
	////
	// !Konstruktor
	function xml($args = array())
	{
		// konteiner tag
		$this->ctag = isset($args["ctag"]) ? $args["ctag"] : "xml";
		// xml versioon
		$this->xml_version = isset($args["xml_version"]) ? $args["xml_version"] : "1.0";
		// numbriliste elementide prefix arrayde serialiseerimise juures
		$this->num_prefix = isset($args["num_prefix"]) ? $args["num_prefix"] : "num_";
	}

	// lopetab xml definitsiooni (s.t. lisab versiooninumbri ning root tagi
	function _complete_definition($args = array())
	{
		$data = $args["data"];
		$retval = sprintf("<?xml version='%s'?>\n<%s>\n%s</%s>\n",$this->xml_version,$this->ctag,$data,$this->ctag);
		return $retval;
	}

	////
	// !Genereerib parameetrige pohjal tag-i
	function xml_gen_tag($args = array())
	{
		global $awt;
		$awt->start("xml::xml_gen_tag");
		$awt->count("xml::xml_gen_tag");

		// nende m‰rkide puhul kasutame tagi v‰‰rtuse esitamisel CDATA notatsiooni,
		// vastasel juhul lihtsalt v‰ljastame stringi
		$specials = array("<",">","\"");

		$tag = $args["tag"];       
		$value = $args["value"];
		$spacer = $args["spacer"];

		// tulem on soltuvalt spetsiaalm‰rkide olemasolust $value-s, kas
		//	<tag>
		//	<![CDATA[
		//	value
		//	]]>
		//	</tag>
		//
		//	vıi
		//
		// 	<tag>value</tag>
		//
		// kusjuures koikidele ridadele liidetakse ette $spacer

		reset($specials);
		$is_special = false;
		foreach($specials as $spec_char)
		{
			$pos = strpos($value,$spec_char);
			if ($pos === false)
			{
	
			}
			else
			{
				$is_special = true;
			};
		};

		$value = str_replace("&","&amp;",$value);
		if ($is_special)
		{
			$retval = $spacer . "<$tag>\n" . $spacer . "<![CDATA[" . $value . "]]>\n" . $spacer . "</$tag>\n";
		}
		else
		{
			$retval = $spacer . "<$tag>" . $value . "</$tag>" . "\n";
		};
		$awt->stop("xml::xml_gen_tag");
		return $retval;
	}

	////
	// !Serialiseerib array XML-i. Kutsub ennast rekursiivselt v‰lja
	// arg - array
	function xml_serialize($arg = array())
	{
		global $awt;
		$awt->start("xml::xml_serialize");
		$awt->count("xml::xml_serialize");

		$tmp = $this->_xml_serialize($arg);
		$r = $this->_complete_definition(array(
							"data" => $tmp,
						));
		$awt->stop("xml::xml_serialize");
		return $r;
	}

	////
	// !This one does the all the dirty job
	function _xml_serialize($arg = array(),$level = 0)
	{
		if (!is_array($arg))
		{
			return;
		};
		
		if (sizeof($arg) == 0)
		{
			return;
		};
	
		global $awt;
		$awt->start("xml::_xml_serialize");
		$awt->count("xml::_xml_serialize");

		$tmp = "";
		$realval = "";
	
		reset($arg);
		foreach($arg as $key => $val)
		{
			// kui $val on array, siis tˆˆtleme seda rekursiivselt,
			// muidu salvestame tagi siia
			$spacer = str_repeat("      ",$level);

			// numbrilised tagid pole paraku lubatud, seega liidame neile prefiksina "num_" ette
			if (gettype($key) == "integer")
			{
				$key = $this->num_prefix . $key;
			};
		
			if (is_array($val))
			{
				$level++;
				$realval .= sprintf("%s<%s>\n",$spacer,$key);
				$realval .= $this->_xml_serialize($val,$level);
				$realval .= sprintf("%s</%s>\n",$spacer,$key);
				$level--;
			}
			else
			{
				$realval .= $this->xml_gen_tag(array(
							"tag" => $key,
							"value" => $val,
							"spacer" => $spacer,
						));
			};
			$tmp = $realval;
		};
		$awt->stop("xml::_xml_serialize");
		return $tmp;
	}

	////
	// !Vıtab XML definitsiooni (mis peab olema korrektne), ning tagastab php array
	// source - xml
	function xml_unserialize($args = array())
	{
		global $awt;
		$awt->start("xml::xml_unserialize");
		$awt->count("xml::xml_unserialize");

		$source = $args["source"];
		$retval = array();
		$ckeys = array();
		
		$awt->start("xml::xml_unserialize::parsers");
		// parsimist enam kiiremaks ei saa, see toimub enivei PHP siseselt
		$parser = xml_parser_create();
		
		// keerame tag-ide suurt‰htedeks konvertimise maha
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);

		// kui tahad aru saada mida see funktsioon ja j‰rgnevad read teevad, siis debuukimise ajal
		xml_parse_into_struct($parser,$source,&$keys,&$values);
		// void siin teha midagi sellist: print_r($keys), print_r($values);

		// Good parser. Now go back where you came from
		xml_parser_free($parser);

		$awt->stop("xml::xml_unserialize::parsers");

		$awt->start("xml::xml_unserialize::php");

		$datablock = "";
		$ckeys = array();

		foreach($keys as $k1 => $v1)
		{
			if ( ($v1["type"] == "cdata") || ($v1["level"] < 2) )
			{
				continue;
			};
			$awt->start("xml::unserialize::datacycles");
			$tag = $v1["tag"];
			$awt->start("xml::unserialize::prefix_replace");
			
			$tag = preg_replace("/^" . $this->num_prefix . "/","",$tag);

			//if (strpos($tag,$this->num_prefix) == 0)
			//{
			//	$tag = str_replace($this->num_prefix,"",$tag);
			//};

			$awt->stop("xml::unserialize::prefix_replace");
		
			// kui lopetet tag, siis on meil v‰‰rtus k‰es, ja rohkem pole vaja midagi teha
			if ($v1["type"]	== "complete")
			{
				$awt->start("xml::unserialize::complete_tag");
				$awt->count("xml_complete_tags");
				$path1 = $path . "[\"" . $tag . "\"]";

				// value algusest ja lıpust liigne r‰ga maha
				$value = trim(isset($v1["value"]) ? $v1["value"] : "");

				// moodustame evali jaoks rea
				$value = str_replace("\\","\\\\",$value);
				$value = str_replace("\"","\\\"",$value);
				$datablock .= "\$retval" . $path1 . "=\"$value\";\n";
				$awt->stop("xml::unserialize::complete_tag");
			}
			elseif ($v1["type"] == "open")
			{
				array_push($ckeys,sprintf("[\"%s\"]",$tag));
				$path = join("",$ckeys);
			}
			elseif ($v1["type"] == "close")
			{
				$void = array_pop($ckeys);
				$path = join("",$ckeys);
			};
			// ¸lej‰‰nud tage ignoreeritakse
			$awt->stop("xml::unserialize::datacycles");
		}
		$awt->start("xml::unserialize::eval");
		eval($datablock);
		$awt->stop("xml::unserialize::eval");
		$awt->stop("xml::xml_unserialize::php");
		$awt->stop("xml::xml_unserialize");
		return $retval;
	}
};

?>
