<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/xml_import.aw,v 2.16 2003/06/10 15:04:32 kristo Exp $
/*
        @default table=objects
        @default group=general
        @property datasource type=objpicker clid=CL_DATASOURCE field=meta method=serialize
        @caption XML datasource
                                                                                                                            
        @property import_function type=select field=meta method=serialize
        @caption Impordifunktsioon
                                                                                                                            
        @property run_import type=text editonly=1 store=no
        @caption Käivita import
                                                                                                                            
*/
class xml_import extends class_base
{

	function xml_import($args = array())
	{
		$this->init(array(
                        "tpldir" => "xml_import",
                        "clid" => CL_XML_IMPORT,
                ));

		$this->methods = array(
			"import_tudengid" => "import_tudengid",
			"import_struktuurid" => "import_struktuurid",
			"import_tootajad" => "import_tootajad",
			"import_oppekava" => "import_oppekava",
			"import_oppeasted" => "import_oppeasted",
			"import_oppevormid" => "import_oppevormid",
		);
		set_time_limit(90);
	}

	function get_property($args)
	{
                $data = &$args["prop"];
                switch($data["name"])
                {
                        case "import_function":
                                $data["options"] = $this->methods;
                                break;
                                                                                                                            
                        case "run_import":
                                classload("html");
                                $id = $args["obj"]["oid"];
                                if ($id)
                                {
                                        $url = $this->mk_my_orb("invoke",array("id" => $id),"xml_import",0,1);
                                        $data["value"] = html::href(array("url" => $url,"caption" => "Käivita import","target" => "_blank"));
                                };
                                break;
                                                                                                                            
                                                                                                                            
                                                                                                                            
                };
        }

	function set_property($args = array())
        {
		$retval = PROP_OK;
                if ($args["prop"]["name"] == "run_import")
                {
                        $retval = PROP_IGNORE;
                };
                return $retval;
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
		$method = $obj["meta"]["import_function"];
		$ds = get_instance("datasource");
		$src_data = $ds->retrieve(array("id" => $obj["meta"]["datasource"]));
		print "Got " . strlen($src_data) . " bytes of data<br>";
		flush();
		if (strlen($src_data) < 100)
		{
			print "Didn't got enough data from the datasource<br>";
			exit;
		};
		/*
		print "<pre>";
		print htmlspecialchars($src_data);
		print "</pre>";
		*/
		print "Invoking import function<br>";
		flush();
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
		if (xml_get_error_code($parser))
		{
			$this->bitch_and_die($parser,$contents);
		};
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
		if (xml_get_error_code($parser))
		{
			$this->bitch_and_die($parser,$contents);
		};
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_struktuurid";
		$this->db_query($q);
		$lastlevel = 0;
		$ylem_list = array();
		$ylem_ilist = array();
		$str_level = 1;
		$counter = 0;
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
					$str_level--;
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
					$aadress = $this->convert_unicode($attr["aadress"]);
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
						

					$jrknimetus = sprintf("%02d%s",$jrk,$nimetus);
					//if ($str_level >= 3)
					if ($str_level > 3)
					{
						$t3_ylem = $t3taseme_ylem_id;
						print "t3<br>";
					}
					else
					{
						$t3_ylem = $real_ylem_id;
						print "real_ylem<br>";
					};

					if ($str_level < 4)
					{
						$t3sort = $id . $str_level . sprintf("%02d",$jrk) . $nimetus;
					}
					else
					{
						$t3sort = $t3taseme_id . $str_level . sprintf("%02d",$jrk) . $nimetus;
					};
						
					$q = "INSERT INTO ut_struktuurid (id,kood,nimetus,aadress,email,veeb,telefon,faks,osakond,ylem_id,ylemyksus,jrk,jrknimetus,3taseme_ylem_id,3taseme_sort)
							VALUES('$id','$kood','$nimetus','$aadress','$email','$veeb','$telefon','$faks','$osakond','$real_ylem_id','$real_ylem_name','$jrk','$jrknimetus','$t3_ylem','$t3sort')";
					$counter++;
					if ($str_level == 3)
					{
						$t3taseme_ylem_id = $real_ylem_id;
						$t3taseme_id = $id;
					};
					print "id = $id, kood = $kood, nimetus = $nimetus, aadress = $aadress, email=$email, veeb = $veeb, telefon = $telefon, faks = $faks<br>";
					print "osakond = $osakond, real_ylem_id = $real_ylem_id, real_ylem_name = $real_ylem_name<br>";
					print "jrk = $jrk, jrknimetus = $jrknimetus, t3_ylem = $t3_ylem, t3_sort = $t3_sort<br>";
					#print $q;
					print "<h1>$str_level</h1>";
					
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
					$str_level++;
					array_push($ylem_list,$ylem_name);
					array_push($ylem_ilist,$yid);
				};
			} 
		}
		print "<h1>$counter</h1>";
	}
	
	function import_tootajad($args = array())
	{
		$contents = $args["source"];
		//$contents = join("",file("/home/duke/tootajad.xml"));
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		print "<pre>";
		print_r(htmlspecialchars($contents));
		print "</pre>";
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		if (xml_get_error_code($parser))
		{
			$this->bitch_and_die($parser,$contents);
		};
	
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_tootajad";
		$this->db_query($q);
		$q = "DELETE FROM ut_ametid";
		$this->db_query($q);
		$q = "DELETE FROM tootajad_view";
		$this->db_query($q);
		foreach($values as $token)
		{
			if ( ($token["tag"] == "tootaja") && ($token["type"] == "open") )
			{
				$t_attr = $token["attributes"];
				// lisame uue töötaja baasi
				$this->quote($t_attr);
				// collect the data for later use
				$enimi = $this->convert_unicode($t_attr["enimi"]);
				$pnimi = $this->convert_unicode($t_attr["pnimi"]);
				$tid = $t_attr["id"];
				$veeb = $t_attr["veeb"];
				$ruum = $t_attr["ruum"];
				$email = $t_attr["email"];
				$markus = $this->convert_unicode($t_attr["markus"]);
				$mobiil = $t_attr["mobiil"];
				$sisetel = $t_attr["sisetel"];
				$pritel = $t_attr["pritel"];
				$tootajad_view = array();
			}

			if ( ($token["tag"] == "amet") && ($token["type"] == "complete") )
			{
				$attr = $token["attributes"];
				$this->quote($attr);
				$nimi = $this->convert_unicode($attr["nimi"]);
				$ysid = $attr["ysid"];
				$eriala = $this->convert_unicode($attr["eriala"]);
				$markus = $this->convert_unicode($attr["markus"]);
				$koht = $this->convert_unicode($attr["koht"]);

				$koht = preg_replace("/\s$/","&nbsp;",$koht);
				$eriala = preg_replace("/\s$/","&nbsp;",$eriala);

				/*
				Lisaks tuleb koormuse import ümber teha selliselt, et kui koormus on 1,
				 siis jäetakse koormus_view lahter tühjaks. kui koormus on
				midagi muud, kui 1, siis kirjutatakse sama väärtus nii tulpa koormus kui
				koormus_view, koormus_view lahtrisse lisatakse veel ka
				tühik ja täht "k".
				*/
				$koormus = (float)$attr["koormus"];
				if ($koormus == 1)
				{
					$koormus_view = "";
				}
				else
				{
					$koormus_view = " " . $koormus . " k";
				};
				$st_id = $attr["struktuur"] . $tid;
				$q = "INSERT INTO ut_ametid (struktuur_id,nimi,koormus,jrk,markus,tootaja_id,eriala,tel,koht,koormus_view,ysid,st_id)
					VALUES ('$attr[struktuur]','$nimi','$attr[koormus]','$attr[jrk]',
						'$markus','$tid','$eriala','$attr[tel]','$koht','$koormus_view','$ysid','$st_id')";
				print $q;
				$this->db_query($q);
				$tootajad_view[$attr[struktuur]][] = array(
					"tootaja_id" => $tid,
					"info" => $eriala . $nimi . $koormus_view,
					"tel" => $attr["tel"],
					"ruum" => $koht,
					"ysid" => $ysid,
					"jrk" => $attr["jrk"],
					"struktuur_id" => $attr["struktuur"],
				);
					
			}

			if ( ($token["tag"] == "kraad") && ($token["type"] == "complete") )
			{
				$attr = $token["attributes"];
				$_haru = $this->convert_unicode($attr["haru"]);
				$_kraad = $this->convert_unicode($attr["kraad"]);
				if ($_haru)
				{
					$kraad[] = "$_kraad ($_haru)";
				}
				else
				{
					$kraad[] = "$_kraad";
				};

			}

			if ( ($token["tag"] == "tootaja") && ($token["type"] == "close") )
			{
				$q = "SELECT * FROM ut_tootajad WHERE id = '$tid'";
				$this->db_query($q);
				$row = $this->db_next();
				$row = false;
				if (is_array($kraad))
				{
					$realkraad = join(", ",$kraad);
				}
				else
				{
					$realkraad = "";
				};
				if (!$row)
				{
					$q = "INSERT INTO ut_tootajad (id,enimi,pnimi,email,veeb,ruum,markus,mobiil,sisetel,pritel,kraad) 
						VALUES ('$tid','$enimi','$pnimi','$email','$veeb','$ruum','$markus','$mobiil','$sisetel','$pritel','$realkraad')";
					print $q;
					print "<br>";
					$this->db_query($q);
				};
				$kraad = array();

				if (is_array($tootajad_view))
				{
					foreach($tootajad_view as $str_id => $items)
					{
							if (sizeof($items) == 1)
							{
								// just write it out
								$fieldnames = join(",",array_keys($items[0]));
								$fieldvalues = join(",",map("'%s'",array_values($items[0])));
								$q = "INSERT INTO tootajad_view ($fieldnames) VALUES ($fieldvalues)";
								$this->db_query($q);
								print $q;
								print "<br>";
								flush();
							}
							else
							if (sizeof($items) > 1)
							{
								usort($items, create_function('$a,$b','if ($a["jrk"] > $b["jrk"]) return 1; if ($a["jrk"] < $b["jrk"]) return -1; return 0;'));
								$tmp = $items[0];
								$info = array();
								array_walk($items,create_function('$val,$key,$info','$info[] = $val["info"];'),&$info);
								$tmp["info"] = join(", ",$info);
								$fieldnames = join(",",array_keys($tmp));
								$fieldvalues = join(",",map("'%s'",array_values($tmp)));
								$q = "INSERT INTO tootajad_view ($fieldnames) VALUES ($fieldvalues)";
								$this->db_query($q);
								print $q;
								print "<br>";
								flush();
							}
					};
				}
			}
			print "<bR>";

		}
		// kahjuks ma ei saa mysql-ist kätte ainult neid, millel count=0, niet ma teen
		// tsükli
		$q = "SELECT ut_struktuurid.id,nimetus,COUNT(nimi) AS cnt FROM ut_struktuurid
			LEFT OUTER JOIN ut_ametid ON (ut_struktuurid.id = ut_ametid.struktuur_id)
			GROUP BY ut_struktuurid.id ORDER BY cnt";
		$this->db_query($q);
		$queries = array();
		while($row = $this->db_next())
		{
			if ($row["cnt"] == 0)
			{
				$q = "INSERT INTO ut_ametid (struktuur_id,st_id) 
					VALUES ('$row[id]','$row[id]')";
				$queries[] = $q;
			};
		}
		foreach($queries as $query)
		{
			$this->db_query($query);
		};
		print "all done!<br>";

	}

	function convert_unicode($source)
	{
		// utf8_decode doesn't work here
		$retval = str_replace(chr(0xC3). chr(0xB5),"õ",$source);
		$retval = str_replace(chr(0xC3). chr(0xBC),"ü",$retval);
		$retval = str_replace(chr(0xC3). chr(0xB6),"ö",$retval);
		$retval = str_replace(chr(0xC3). chr(0xA4),"ä",$retval);
		$retval = str_replace(chr(0xC3). chr(0x96),"Ö",$retval);
		$retval = str_replace(chr(0xC3). chr(0x95),"Õ",$retval);
		$retval = str_replace(chr(0xC3). chr(0xB4),"õ",$retval);
		$retval = str_replace(chr(0xC3). chr(0x84),"Ä",$retval);
		$retval = str_replace(chr(0xC3). chr(0x9C),"Ü",$retval);
		$retval = str_replace(chr(0xC5). chr(0xA0),"&#0352;",$retval);
		$retval = str_replace(chr(0xC5). chr(0xA1),"&#0353;",$retval);
		$retval = str_replace(chr(0xC5). chr(0xBD),"&#381;",$retval);
		$retval = str_replace(chr(0xC5). chr(0xBE),"&#382;",$retval);
		
		return $retval;
	}
	
	function import_oppekava($args = array())
	{
		$contents = $args["source"];
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$contents,&$values,&$tags);
		if (xml_get_error_code($parser))
		{
			$this->bitch_and_die($parser,$contents);
		};

		print "<pre>";
		print_r(htmlspecialchars($contents));
		print "</pre>";
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_oppekavad";
		$this->db_query($q);
		$oppekava_url = $oppeaasta_url = "";
		$aasta_urls = array();

		foreach($values as $key => $val)
		{
			if ( ($val["tag"] == "oppekava_url") && ($val["type"] == "complete") )
			{
				$oppekava_url = $val["value"];
			};
			if ( ($val["tag"] == "oppeaasta_url") && ($val["type"] == "complete") )
			{
				$oppeaasta_url = $val["value"];
			};
			if ( ($val["tag"] == "aasta") && ($val["type"] == "complete"))
			{
				$aasta = $val["value"];
				$aasta_id = $val["attributes"]["id"];
				$aasta_url = str_replace("[oppekava_id]",$id,$oppeaasta_url);
				$aasta_url = str_replace("[oppeaasta_id]",$aasta_id,$aasta_url);
				$aasta_url = str_replace("[oppeaasta]",$aasta,$aasta_url);
				$aasta_urls[$aasta] = "<a href='$aasta_url'>$aasta a</a>";
			};
			if ( ($val["tag"] == "oppekava")  && ( ($val["type"] == "complete") || ($val["type"] == "open")) )
			//if ( ($val["tag"] == "oppekava")  && ($val["type"] == "open") )
			{
				$aasta_urls = array();
				$attr = $val["attributes"];		
				$nimetus = $this->convert_unicode($attr["nimetus"]);
				$nimetus_en = $attr["nimetus_en"];
				$oppeaste = $attr["oppeaste"];
				$id = $attr["id"];
				$kood = $attr["kood"];
			};

			if ( ($val["tag"] == "oppekava") && ( ($val["type"] == "complete") || ($val["type"] == "close") ))
			{
				if (strlen($nimetus) == 0)
				{
					$attr = $val["attributes"];		
					$nimetus = $this->convert_unicode($attr["nimetus"]);
					$nimetus_en = $attr["nimetus_en"];
					$oppeaste = $attr["oppeaste"];
					$id = $attr["id"];
					$kood = $attr["kood"];
				};
				$this->quote($nimetus);
				$this->quote($nimetus_en);
				$kava_url = str_replace("[oppekava_id]",$id,$oppekava_url);
				$this->quote($kava_url);
				ksort($aasta_urls);
				$aasta_url_str = join(", ",$aasta_urls);
				$this->quote($aasta_url_str);
				$q = "INSERT INTO ut_oppekavad (id,kood,nimetus,nimetus_en,oppekava_url,oppeaasta_url,oppeaste)
					VALUES('$id','$kood','$nimetus','$nimetus_en','$kava_url','$aasta_url_str','$oppeaste')";				
				print $q;
				print "<br>";
				$nimetus = $nimetus_en = $oppeaste = $id = $kood = "";
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
		if (xml_get_error_code($parser))
		{
			$this->bitch_and_die($parser,$contents);
		};
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
		if (xml_get_error_code($parser))
		{
			$this->bitch_and_die($parser,$contents);
		};
		// R.I.P. parser
		xml_parser_free($parser);
		$q = "DELETE FROM ut_oppevormid";
		$this->db_query($q);
		foreach($values as $key => $val)
		{
			if ( ($val["tag"] == "oppevormid")  && ($val["type"] == "complete") )
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

	function bitch_and_die(&$parser,&$contents)
	{
		$err = xml_error_string(xml_get_error_code($parser));
		print "Viga lähteandmetes<br>"; 
		print "<font color='red'><strong>$err</strong></font><br>";
		$b_idx = xml_get_current_byte_index($parser);
		$frag = substr($contents,$b_idx - 100, 200);
		$pref = htmlspecialchars(substr($frag,0,100));
		$suf = htmlspecialchars(substr($frag,101));
		$offender = htmlspecialchars(substr($frag,100,1));
		print "Tekstifragment: <pre>" .  $pref . "<font color='red'><strong> ---&gt;&gt;$offender&lt;&lt;---</strong></font>$suf" . "</pre>";
		die();
	}
}
?>
