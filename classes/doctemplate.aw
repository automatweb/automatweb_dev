<?php
// doctemplate.aw - dokumenditemplatede genereerija
class doctemplate extends aw_template
{
	////
	// !konstruktor
	function doctemplate($id = 1)
	{
		$this->tpl_init("automatweb/tpledit");
		$this->db_init();
		$this->id = ($id) ? $id : 1;
	}

	////
	// !_get_static_parts - tagastab koik "staatilised" dokumenditemplate osad
	// näiteks lead, content, jne
	function _get_static_parts()
	{
		$this->read_template("documents/parts.tpl");
		// $this->names sisaldab nyyd koikide subtemplatede nimesid, mis sellest failist leiti
	}

	////
	// !get_template_def - loeb baasist info mingi template kohta
	function get_template_def()
	{
		$id = $this->id;
		# $id = $params["id"];
		$q = "SELECT * FROM doctemplates WHERE id = '$id'";
		$this->db_query($q);
		if (!($row = $this->db_next()))
		{
			$this->raise_error("Template $id is not defined",true);
		}
		else
		{
			// parsime selle arraysse.
			$parser = xml_parser_create();
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parse_into_struct($parser,$row[content],&$values,&$tags);
                	xml_parser_free($parser);
			$retval = $row[content];
			#$this->values = $values;
			#$this->tags = $tags;
		
			// koostame array nendest väljadest, mis templatedefinitsioonis kirjas olid 
			$usedfields = array();
			$this->names = array();	
			foreach($values as $key => $val)
			{
				if ($val["tag"] == "field")
				{
					$usedfields[] = $val[attributes];
					$this->names[$val[attributes][name]] = 1;

				};
			};
			$this->fields = $usedfields;
		};
		return $retval;
	}

	////
	// !gen_object_pool - genereerib objektide nimekirja, mida antud templates veel kasutatud pole
	//
	function gen_object_pool()
	{
		// loeme templatedefinitsiooni sisse	
		$tpldef = $this->get_template_def();
		$parts = $this->get_static_def();
		// $parts sisaldab nyyd array keydena koikide staatiliste elementide nimesid
		reset($parts);
		$this->read_template("pool.tpl");
		$content = "";
		foreach($parts as $key => $val)
		{
			if ($this->names[$key])
			{
				$checked = " disabled checked ";
			}
			else
			{
				$checked = "";
			};
			$this->vars(array(	"name" => $key,
						"id" => $key,
						"checked" => $checked));
			$content .= $this->parse("line");
		};
		$this->vars(array(	"line" => $content,
					"tpl"	=> $this->id));
		return $this->parse();
	}

	function register_tpl($params)
	{
		extract($params);
		$this->quote($name);
		$q = "INSERT INTO doctemplates (name) VALUES ('$name')";
		$this->db_query($q);
		$tpl = mysql_insert_id();
		return $tpl;
	}
		

	////
	// !get_static_def - loeb definitsiooni koigi staatiliste dokumendiväljade kohta.
	//	esimeses (viimases) lähenduses hoitakse seda XML-ina
	function get_static_def()
	{
		$file = $this->basedir . "/xml/templates/document.xml";
		if (!$xmldef = $this->get_file(array("file" => $file)))
		{
			$this->raise_error("file $file not found",1);
		};
		$values = array();
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parse_into_struct($parser,$xmldef,&$values,&$tags);
		reset($values);
		$parts = array();
		while(list($k,$v) = each($values))
		{
			switch($v[tag])
			{
				case "part":
					$name = $v[attributes][id];
					$content = $v[value];
					$parts[$name] = $content;
					$captions[$name] = $v[attributes][caption];
					break;
				case "element":
					$name = $v[attributes][id];
					$content = $v[value];
					$elements[$name] = $content;
					break;
				default:
					// do nothing
			};
		};
		$this->elements = $elements;
		$this->parts 	= $parts;
		$this->captions = $captions;
		return $parts;

	}	

	////
	// !show - genereerib template muutmisvormi
	function show($params = array())
	{
		// millist templatet vaatame
		$tpl = $this->id;

		// kuidas me teda näitame. type = undef on muutmisvorm, type = preview on eelvaade
		$type = ($params[type]) ? $params[type] : "";

		// valime template näitamiseks
		switch($type)
		{
			case "preview":
				$use_template = "preview.tpl";
				break;
			default:
				$use_template = "edit.tpl";
				break;
		};

		// loeme template sisse
		$this->read_template($use_template);

		// loeme templatedefinitsiooni sisse	
		$tpldef = $this->get_template_def();

		reset($this->fields);

		print "<pre>";
		print htmlspecialchars($tpldef);
		print "</pre>";

		// kui tahad aru saada, mida täpselt xml_parse_into_struct teeb, siis eemalda järgmise rea eest kommentaar
		// dump_struct($this->values);
		
		
		// loeme koik staatilised templateosad sisse
		// hmz. on seda ikka vaja?
		$parts = $this->get_static_def();

		
		$c = ""; $cnt = 0;
		// praegu: tsükkel üle kõigi staatiliste elementide
		// need, mis on template jaoks valitud, kuvatakse checkituna
		// ülejäänud niisama

		// peab olema:
		// tsükkel üle templateelementide
		// alguses kuvatakse koik elemendid

		// hmmm. ja lisamise funktsioone voiks olla 2
		// lisaks praegusele "Lisa dünaamiline"
		// tuleks juurde ka "Lisa staatiline"
		
	
		// tsükkel üle kõigi staatiliste templateosade
		#reset($this->parts);
		$c = "";
		while(list($k,$v) = each($this->fields))
		{
			print "$k = $v<br>";
			$val = $this->parts[$v[name]];
			switch ($type)
			{
				case "preview":
					if ($v[type] == "dynamic")
					{
						switch ($v[style])
						{
							default:
								// vaikimisi kasutame stiilina checkboxi
								$el_tpl = $this->elements["checkbox"];
								break;
						};
						// leiame koik selle menyy all olevad elemendid
						$elements = $this->get_objects_by_parent($v[section]);
						$c .= "<tr><td colspan='2'>\n";
						while(list($k,$v) = each($elements))
						{
							if ($v[name])
							{
								$c .= $this->localparse($el_tpl,array("caption" => $v[name]));
							};
						};
						$c .= "</td></tr>";
					}
					else
					{
						$_tpl_params = array(	"caption" => $v[caption],
									"content" => "");

						$this->vars(array(
									"content"	=> $this->localparse($val,$_tpl_params)
								));
						$c .= $this->parse("line");
					};
					break;

				default:
					$key = $cnt;
					if ($v[type] == "dynamic")
					{
						$keycap = $v[name] . "<i>(oid=" . $v[section] . ")</i>";
						$caption = $v[name];
						$style = $v[style];
						$dyn = 1;
						$subtpl = "dynamic";
					}
					else
					{
						$caption = $v[caption];
						$style = "";
						$dynamic = "";
						$keycap = $key;
						$subtpl = "line";
					};
					$this->vars(array(
							"keycap"	=> $keycap,
							"style"		=> $style,
							"name"		=> $v[name],
							"dyn"		=> $dynamic,
							"caption" 	=> $caption,
							"key"		=> $key,
							"jrk"		=> $cnt + 1));
					$c .= $this->parse($subtpl);
					break;
			};
			$cnt++;

		};

		$this->vars(array(	"line" => $c,
					"tpl" => $tpl,
					"action" => "savetemplate"));

		$retval = $this->parse();
		return $retval;

	}

	function gen_list()
	{
		$this->read_template("list.tpl");
		$q = "SELECT * FROM doctemplates ORDER BY id";
		$this->db_query($q);
		$c = "";
		while($row = $this->db_next())
		{
			$this->vars(array("id" => $row[id],
						"name" => $row[name]));
			$c .= $this->parse("line");
		};
		$this->vars(array("line" => $c));
		return $this->parse();
	}

	function add_form()
	{
		$this->read_template("add.tpl");
		return $this->parse();
	}


	function submit_template($data)
	{
		// mark : array : sisaldab checkboxide väärtusi (elik, millised templateosad valitud on)
		// jrk: array : millises järjekorras nad on
		// caption : array : pealkirjad
		// kui parameetrites on defineeritud "delete_marked" flag, siis voetakse vastavalt margitud väljad maha
		extract($data);
		// sorteerime järjekorranumbrite järgi.
		$tpldef = $this->get_template_def();
		asort($jrk);
		$xml = gen_xml_header();
		$xml .= "<template>\n";
		while(list($k,$v) = each($jrk))
		{
			if ($delete_marked && ($marked[$k]))
			{
				// see element oli märgitud kustutamiseks, seega jätame ta vahele
			}
			else
			{
				// dünaamiline element
				if ($dyn[$k])
				{
					$attribs = array(
								"type"		=> "dynamic",
								"section" 	=> $k,
								"style" 	=> $style[$k],
								"name" 		=> $caption[$k]);
				}
				else
				{
					$attribs = array(
								"type" 		=> "static",
								"name" 		=> $name[$k],
								"caption" 	=> $caption[$k]);
				};
				$xml .= gen_xml_tag("field",$attribs);
                	};
		};
                $xml .= "</template>\n";
		$this->quote($xml);
		$q = "UPDATE doctemplates
			SET content = '$xml'
			WHERE id = '$tpl'";
		$this->db_query($q);
	}

	// lisab info dünaamilise sektsiooni kohta
	function add_dynamic($params = array())
	{
		$chk = $params["chk"];
		$tpl = $params["tpl"];

		// koigepealt loeme vana template sisu failist sisse
		// loeme templatedefinitsiooni sisse	
		$tpldef = $this->get_template_def();

		$tags = $this->fields;

		// NB! selle idee järgi lisame me uue template alati loppu.

		//$tags[] = $newtag;

		reset($tags);
		
		$xml = gen_xml_header();
		$xml .= "<template>\n";

		while(list($k,$v) = each($tags))
		{
			$xml .= gen_xml_tag("field",$v);
		};
	
		if (is_array($chk))
		{
			$xml .= gen_xml_tag("container",array("caption" => " "),2);
			foreach($chk as $key => $val)
			{
				$params = array("id" => $key);
				$xml .= gen_xml_tag("element",$params);
			};
			$xml .= gen_xml_tag("container",array(),3);
		};
		#$newtag[type] = "dynamic";
		#$newtag[section] = $params[section];
		#$newtag[style] = $params[style];
		
		#$odata = $this->get_object($params[section]);
		#$newtag[name] = $odata[name];

		$xml .= "</template>";
		
		$this->quote($xml);
		$q = "UPDATE doctemplates
			SET content = '$xml'
			WHERE id = '$tpl'";
		$this->db_query($q);
			
	}

	function add_static($params = array())
	{
		$tpldef = $this->get_template_def();
		$parts = $this->get_static_def();
		$tags = $this->fields;
		reset($tags);

		$o2a = $params["chk"];
		$tpl = $params["tpl"];
		if (is_array($o2a))
		{
			foreach($o2a as $key => $val)
			{
				// lisame ainult siis, kui seda elementi juba eelnevalt olemas ei olnud
				if (!$this->names[$key])
				{
					$newtag[type] = "static";
					$newtag[name] = $key;
					$newtag[caption] = $key;
					$tags[] = $newtag;
				};
			};
		};
		reset($tags);
		$xml = gen_xml_header();
		$xml .= "<template>\n";
		while(list($k,$v) = each($tags))
		{
			$xml .= gen_xml_tag("field",$v);
		};

		$xml .= "</template>";
		
		$this->quote($xml);
		$q = "UPDATE doctemplates
			SET content = '$xml'
			WHERE id = '$tpl'";
		$this->db_query($q);
	}	
		
		

};
?>
