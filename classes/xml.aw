<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/xml.aw,v 2.4 2001/07/04 23:01:55 kristo Exp $
// xml.aw - generic class for handling data in xml format.
// at the moment (Apr 25, 2001) it can serialize PHP arrays to XML and vice versa
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
		return $retval;
	}

	////
	// !Serialiseerib array XML-i. Kutsub ennast rekursiivselt v‰lja
	// arg - array
	function xml_serialize($arg = array())
	{
		$tmp = $this->_xml_serialize($arg);
		return $this->_complete_definition(array(
							"data" => $tmp,
						));
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
		return $tmp;
	}

	////
	// !Vıtab XML definitsiooni (mis peab olema korrektne), ning tagastab php array
	// source - xml
	function xml_unserialize($args = array())
	{
		$source = $args["source"];
		$retval = array();
		$ckeys = array();
		
		$parser = xml_parser_create();
		
		// keerame tag-ide suurt‰htedeks konvertimise maha
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
	
		// kui tahad aru saada mida see funktsioon ja j‰rgnevad read teevad, siis debuukimise ajal
		xml_parse_into_struct($parser,$source,&$keys,&$values);
		// void siin teha midagi sellist: print_r($keys), print_r($values);

		// Good parser. Now go back where you came from
		xml_parser_free($parser);

		foreach($keys as $k1 => $v1)
		{
			// level1 on root tag, seega, meid huvitavad tagid algavad levelist 2
			if ($v1["level"] >= 2)
			{
				$tag = $v1["tag"];
				if (strpos($tag,$this->num_prefix) == 0)
				{
					// puudus. See asendab koik num_prefixid tagis.
					// Aga ma usun, et see pole takistus
					$tag = str_replace($this->num_prefix,"",$tag);
				};
		
				// kui lopetet tag, siis on meil v‰‰rtus k‰es, ja rohkem pole vaja midagi teha
				if ($v1["type"]	== "complete")
				{
					// arvutame path-i v‰lja
					// kui $ckeys on array("yx","kax","kolm"), siis $pathi v‰‰rtuseks saab ["yx"]["kax"]["kolm"]
					reset($ckeys);
					$path = "";
					if (sizeof($ckeys) > 0)
					{
						while(list(,$cval) = each($ckeys))
						{
							$path .= sprintf("[\"%s\"]",$cval);
						};
					};
					// lopuks liidame sinna otsa hetkel k‰siloleva tagi
					$path .= "[\"" . $tag . "\"]";

					// value algusest ja lıpust liigne r‰ga maha
					$value = trim(isset($v1["value"]) ? $v1["value"] : "");

					// moodustame evali jaoks rea
					$value = str_replace("\\","\\\\",$value);
					$value = str_replace("\"","\\\"",$value);
					$line = "\$retval" . $path . "=\"$value\";";

					// and here we go. It might be ugly, but at the moment I don't care
					eval($line); 
				}
				elseif ($v1["type"] == "open")
				{
					array_push($ckeys,$tag);
				}
				elseif ($v1["type"] == "close")
				{
					$void = array_pop($ckeys);
				};
				// ¸lej‰‰nud tage ignoreeritakse
			};
		}
		return $retval;
	}
};

?>
