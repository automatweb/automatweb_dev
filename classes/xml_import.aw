<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/xml_import.aw,v 2.6 2002/07/24 16:12:58 duke Exp $
class xml_import extends aw_template
{

	function xml_import($args = array())
	{
		$this->init("xml_import");
		$this->methods = array(
			"import_tudengid" => "import_tudengid",
			"import_struktuurid" => "import_struktuurid",
			"import_tootajad" => "import_tootajad",
			"import_oppekava" => "import_oppekava",
			"import_oppeasted" => "import_oppeasted",
			"import_oppevormid" => "import_oppevormid",
		);
	}

	////
	// !Displays form for adding new or modifying an existing XML import objekt
	function change($args = array())
	{
		extract($args);
		$this->read_template("change.tpl");
		$datasources = array();
		$this->get_objects_by_class(array(
				"class" => CL_DATASOURCE,
		));

		while($row = $this->db_next())
		{
			$datasources[$row["oid"]] = $row["name"];
		}

		if ($parent)
		{
			$caption = "Lisa uus XML import objekt";
			$prnt = $parent;
			$chn = "";
			$meta = array();
		}
		else
		{
			$caption = "Muuda XML import objekti";
			$obj = $this->get_object($id);
			$meta = $obj["meta"];
			$this->vars(array(
				"rep_link" => $this->mk_my_orb("repeaters",array("id" => $id)),
			));
			$chn = $this->parse("change");
			$prnt = $obj["parent"];
		};

		$this->mk_path($prnt,$caption);
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("id" => $id,"parent" => $parent)),
			"name" => $obj["name"],
			"change" => $chn,
			"datasources" => $this->picker($meta["datasource"],$datasources),
			"run_import" => $this->mk_my_orb("invoke",array("id" => $id),"xml_import",0,1),
			"import_functions" => $this->picker($meta["import_function"],$this->methods),
		));
		return $this->parse();
	}

	////
	// !Adds new or submits changes to an existing XML import objekt
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($parent)
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_XML_IMPORT,
				"name" => $name,
				"status" => 2,
			));
			$this->_log("xml_import","Lisas XML import objekti nimega '$name'",$id);
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
			));
			$this->_log("xml_import","Muutis XML import objekti nimega '$name'",$id);
		};

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"datasource" => $datasource,
				"import_function" => $import_function,
			),
		));

		return $this->mk_my_orb("change",array("id" => $id));

	}

	////
	// !Wrapper to display the repeater editing interface inside this classes frame
	function repeaters($args = array())
	{
		extract($args);
		classload("cal_event");
                $ce = new cal_event();
                $html = $ce->repeaters(array(
                        "id" => $id,
                        "cycle" => $cycle,
			"hide_menubar" => true,
                ));
		$this->read_template("repeaters.tpl");
		$this->vars(array(
			"ch_link" => $this->mk_my_orb("change",array("id" => $id)),
			"repeaters" => $html,
		));
		$obj = $this->get_object($id);
		$this->mk_path($obj["parent"],"Muuda XML import objekti");
		return $this->parse();



	}

	function invoke($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		if (not($obj))
		{
			return false;
		};

		if ($obj["class_id"] != CL_XML_IMPORT)
		{
			return false;
		};

		print "Retrieving data:<br>";
		flush();
		// retrieve data
		$ds = get_instance("datasource");
		$src_data = $ds->retrieve(array("id" => $obj["meta"]["datasource"]));
		print "Got " . strlen($src_data) . " bytes of data<br>";
		flush();
		if (strlen($src_data) < 100)
		{
			print "Didn't got enough data from the datasource<br>";
			exit;
		};
		print "<pre>";
		print htmlspecialchars($src_data);
		print "</pre>";
		print "Invoking import function<br>";
		flush();
		$method = $obj["meta"]["import_function"];
		$this->$method(array("source" => $src_data));
		print "Finished!!!<bR>";
		flush();
		exit;
	}

	function import_tudengid($args = array())
	{
		//o$contents = join("",file("/home/duke/tudengid.xml"));
		$contents = $args["source"];
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_tudengid";
		$this->db_query($q);
		foreach($values as $key => $val)
		{
			if ( ($val["tag"]  == "tudeng") && $val["type"] == "complete" )
			{
				$attr = $val["attributes"];		
				$enimi = $this->convert_unicode($attr["enimi"]);
				$pnimi = $this->convert_unicode($attr["pnimi"]);
				$id = $attr["id"];
				$struktuur = $attr["struktuur"];
				$oppekava = $attr["oppekava"];
				$oppeaste = $attr["oppeaste"];
				$oppevorm = $attr["oppevorm"];
				$oppekava = $this->convert_unicode($oppekava);
				$oppeaste = $this->convert_unicode($oppeaste);
				$oppevorm = $this->convert_unicode($oppevorm);
				$nimi = $enimi . " " . $pnimi;
				$aasta = $attr["aasta"];

				$this->quote($nimi);
				$this->quote($enimi);
				$this->quote($pnimi);
				$q = "INSERT INTO ut_tudengid (id,enimi,pnimi,struktuur,oppekava,oppeaste,oppevorm,aasta,nimi)
					VALUES('$id','$enimi','$pnimi','$struktuur','$oppekava','$oppeaste','$oppevorm','$aasta','$nimi')";
				print $q;
				print "<br>";
				$this->db_query($q);
			};


		}
	}
	
	function import_struktuurid($args = array())
	{
		$contents = $args["source"];
		//$contents = join("",file("/home/duke/struktuurid.xml"));
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_struktuurid";
		$this->db_query($q);
		$lastlevel = 0;
		$ylem_list = array();
		$ylem_ilist = array();
		foreach($values as $key => $val)
		{
			/*
			print "lastlevel = $lastlevel<br>";
			print "<pre>";
			print_r($val);
			print "</pre>";
			*/

			if ($val["tag"] == "struktuur")
			{
					
				if ($val["type"]  ==  "open")
				{

					if ($val["level"] == 2)
					{
						$osakond = $this->convert_unicode($val["attributes"]["nimetus"]);
						$ylem_id = $val["attributes"]["id"];
						$attr = $val["attributes"];		
						$ylem_name = $this->convert_unicode($attr["nimetus"]);
						$this->quote($osakond);
					};
				};

				if ( ($val["type"] == "close"))
				{
					/*
						print  "popping<br>";
						print "<pre>";
						print_r($val);
						print "</pre>";
					print "<b>popping</b><br>";
						*/
					array_pop($ylem_list);
					array_pop($ylem_ilist);
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
					$jrk = $attr["jrk"];

					/*
					print "<pre>";
					print_r($ylem_list);
					print  "</pre>";
					*/
					$real_ylem_name = $ylem_list[sizeof($ylem_list) - 1];
					$real_ylem_id = $ylem_ilist[sizeof($ylem_list) - 1];

					/*if (preg_match("/vastutusala/",$nimetus))
					{
						$real_ylem_id = -1;
					};
					*/
				
					if (not($real_ylem_id))
					{
						if (preg_match("/teaduskond/",$nimetus))
						{
							$real_ylem_id = 0;
						}
						else
						{
							$real_ylem_id = -1;
						};
					};
						

					$q = "INSERT INTO ut_struktuurid (id,kood,nimetus,aadress,email,veeb,telefon,faks,osakond,ylem_id,ylemyksus,jrk)
							VALUES('$id','$kood','$nimetus','$aadress','$email','$veeb','$telefon','$faks','$osakond','$real_ylem_id','$real_ylem_name','$jrk')";
					print $q;
					$this->db_query($q);
					print "<br>";
					$lastlevel = $val["level"];
				};

				if ($val["type"]  ==  "open")
				{
					$attr = $val["attributes"];		
					$ylem_name = $this->convert_unicode($attr["nimetus"]);
					$yid = $val["attributes"]["id"];
					/*
					print "<b>pushing</b><br>";
					*/
					array_push($ylem_list,$ylem_name);
					array_push($ylem_ilist,$yid);
				};
			} 
		}
	}
	
	function import_tootajad($args = array())
	{
		$contents = $args["source"];
		//$contents = join("",file("/home/duke/tootajad.xml"));
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_tootajad";
		$this->db_query($q);
		$q = "DELETE FROM ut_ametid";
		$this->db_query($q);
		foreach($values as $token)
		{
			if ( ($token["tag"] == "tootaja") && ($token["type"] == "open") )
			{
				$t_attr = $token["attributes"];
				// lisame uue töötaja baasi
				$this->quote($t_attr);
				$enimi = $this->convert_unicode($t_attr["enimi"]);
				$pnimi = $this->convert_unicode($t_attr["pnimi"]);
				$q = "INSERT INTO ut_tootajad (id,enimi,pnimi,veeb,ruum,markus) 
					VALUES ('$t_attr[id]','$enimi','$pnimi','$t_attr[veeb]','$t_attr[ruum]','$t_attr[markus]')";
				print $q;
				$this->db_query($q);
			}

			if ( ($token["tag"] == "amet") && ($token["type"] == "complete") )
			{
				$attr = $token["attributes"];
				$this->quote($attr);
				$nimi = $this->convert_unicode($attr["nimi"]);
				$q = "INSERT INTO ut_ametid (struktuur_id,nimi,koormus,jrk,markus,tootaja_id)
					VALUES ('$attr[struktuur]','$nimi','$attr[koormus]','$attr[jrk]',
						'$attr[markus]','$t_attr[id]')";
				print $q;
				$this->db_query($q);
			}
			print "<bR>";

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
		$retval = str_replace(chr(0xC5). chr(0xA0),"&Scaron;",$retval);
		$retval = str_replace(chr(0xC5). chr(0xA1),"&scaron;",$retval);
		$retval = str_replace(chr(0xC5). chr(0xBD),"&#381;",$retval);
		$retval = str_replace(chr(0xC5). chr(0xBE),"&#382;",$retval);
		
		return $retval;
	}
	
	function import_oppekava($args = array())
	{
		$contents = $args["source"];
		//$contents = join("",file("/home/duke/oppekavad.xml"));
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_oppekavad";
		$this->db_query($q);
		foreach($values as $key => $val)
		{
			if ( ($val["tag"] == "oppekava")  && ($val["type"] == "complete") )
			{
				$attr = $val["attributes"];		
				$nimetus = $this->convert_unicode($attr["nimetus"]);
				$id = $attr["id"];
				$kood = $attr["kood"];
				$this->quote($nimetus);
				$q = "INSERT INTO ut_oppekavad (id,kood,nimetus)
					VALUES('$id','$kood','$nimetus')";
				print $q;
				print "<br>";
				$this->db_query($q);
			};



		}
	}
	
	function import_oppeasted($args = array())
	{
		$contents = $args["source"];
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_oppeasted";
		$this->db_query($q);
		foreach($values as $key => $val)
		{
			if ( ($val["tag"] == "oppeaste")  && ($val["type"] == "complete") )
			{
				$attr = $val["attributes"];		
				$nimetus = $this->convert_unicode($attr["nimetus"]);
				$id = $attr["id"];
				$jrk = $attr["jrk"];
				$this->quote($nimetus);
				$q = "INSERT INTO ut_oppeasted (id,nimetus,jrk)
					VALUES('$id','$nimetus','$jrk')";
				print $q;
				print "<br>";
				$this->db_query($q);
			};



		}
	}
	
	function import_oppevormid($args = array())
	{
		$contents = $args["source"];
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_oppevormid";
		$this->db_query($q);
		foreach($values as $key => $val)
		{
			if ( ($val["tag"] == "oppevorm")  && ($val["type"] == "complete") )
			{
				$attr = $val["attributes"];		
				$nimetus = $this->convert_unicode($attr["nimetus"]);
				$id = $attr["id"];
				$jrk = $attr["jrk"];
				$this->quote($nimetus);
				$q = "INSERT INTO ut_oppevormid (id,nimetus,jrk)
					VALUES('$id','$nimetus','$jrk')";
				print $q;
				print "<br>";
				$this->db_query($q);
			};



		}
	}


}
?>
