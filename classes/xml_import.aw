<?php
class xml_import extends aw_template
{

	function xml_import($args = array())
	{
		$this->init("xml_import");
	}

	////
	// !Displays form for adding new or modifying an existing XML import objekt
	function change($args = array())
	{
		$this->read_template("change.tpl");
		if ($parent)
		{
			$caption = "Lisa uus XML import objekt";
			$prnt = $parent;
		}
		else
		{
			$caption = "Muuda XML import objekti";
			$obj = $this->get_object($id);
			$prnt = $obj["parent"];
		};

		$this->mk_path($prnt,$caption);
		return $this->parse();
	}

	////
	// !Adds new or submits changes to an existing XML import objekt
	function submit($args = array())
	{

	}

	function import_tudengid($args = array())
	{
		$contents = join("",file("/home/duke/tudengid.xml"));
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		foreach($values as $key => $val)
		{
			if ( $val["type"] == "complete" )
			{
				$attr = $val["attributes"];		
				$nimi = $attr["nimi"];
				$id = $attr["id"];
				$struktuur = $attr["struktuur"];
				$oppekava = $attr["oppekava"];
				$oppeaste = $attr["oppeaste"];
				$oppevorm = $attr["oppevorm"];
				$aasta = $attr["aasta"];
			};

			$this->quote($nimi);
			$q = "INSERT INTO ut_tudengid (id,nimi,struktuur,oppekava,oppeaste,oppevorm,aasta)
				VALUES('$id','$nimi','$struktuur','$oppekava','$oppeaste','$oppevorm','$aasta')";
			print $q;
			print "<br>";
			$this->db_query($q);


		}
	}
	
	function import_struktuurid($args = array())
	{
		$contents = join("",file("/home/duke/struktuurid.xml"));
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		foreach($values as $key => $val)
		{
			if ($val["tag"] == "struktuur")
			{
				if ($val["type"]  ==  "open")
				{
					if ($val["level"] == 2)
					{
						$osakond = $this->convert_unicode($val["attributes"]["nimetus"]);
						$this->quote($osakond);
					};
				};

				if ( ($val["type"] == "open") || ($val["type"]  == "complete") )
				{
					$attr = $val["attributes"];		
					$this->quote($attr);
					$nimetus = $this->convert_unicode($attr["nimetus"]);
					$id = $attr["id"];
					$kood = $attr["kood"];
					$aadress = $attr["aadress"];
					$email = $attr["email"];
					$veeb = $attr["veeb"];
					$telefon = $attr["telefon"];
					$faks = $attr["faks"];

					$q = "INSERT INTO ut_struktuurid (id,kood,nimetus,aadress,email,veeb,telefon,faks,osakond)
							VALUES('$id','$kood','$nimetus','$aadress','$email','$veeb','$telefon','$faks','$osakond')";
					print $q;
					$this->db_query($q);
					print "<br>";
				};
			};
		}
	}

	function convert_unicode($source)
	{
		$retval = str_replace(chr(0xC3). chr(0xB5),"õ",$source);
		$retval = str_replace(chr(0xC3). chr(0xBC),"ü",$retval);
		$retval = str_replace(chr(0xC3). chr(0xB6),"ö",$retval);
		$retval = str_replace(chr(0xC3). chr(0xA4),"ä",$retval);
		$retval = str_replace(chr(0xC3). chr(0x96),"Ö",$retval);
		$retval = str_replace(chr(0xC3). chr(0x95),"Õ",$retval);
		$retval = str_replace(chr(0xC3). chr(0x84),"Ä",$retval);
		$retval = str_replace(chr(0xC3). chr(0x9C),"Ü",$retval);
		
		return $retval;
	}
	
	function import_oppekava($args = array())
	{
		$contents = join("",file("/home/duke/oppekavad.xml"));
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		foreach($values as $key => $val)
		{
			if ( $val["type"] == "complete" )
			{
				$attr = $val["attributes"];		
				$nimetus = $attr["nimetus"];
				$id = $attr["id"];
				$kood = $attr["kood"];
			};

			$this->quote($nimetus);
			$q = "INSERT INTO ut_oppekavad (id,kood,nimetus)
				VALUES('$id','$kood','$nimetus')";
			print $q;
			print "<br>";
			$this->db_query($q);


		}
	}


}
?>
