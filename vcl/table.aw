<?php
// $Header: /home/cvs/automatweb_dev/vcl/Attic/table.aw,v 2.14 2001/11/08 22:48:48 duke Exp $
global $PHP_SELF;
$js_table = "
function xnavi_alfa(char_to_look_for) {
	with(document.aw_table) {
		var loc = \"field=\" + field.options[field.selectedIndex].value +
			\"&lookfor=\" + char_to_look_for;
		window.location = \"$PHP_SELF?\" + loc;
	}
}
";

class aw_table
{
	
	// constructor
	function aw_table($data)
	{
		// väljad
		// prefix - kasutame sessioonimuutujate registreerimisel
		$this->set_attribs($data);
		$this->sortby = $data["sortby"];
		$this->imgurl = $data["imgurl"];
		$this->self   = $data["self"];
		$this->tbgcolor = $data["tbgcolor"];
		$this->header_attribs = array();

		if (strpos($this->self,"?") === false)
			$this->separator = "?";
		else
			$this->separator = "&";

		$this->lookfor = $data[lookfor];

		$this->up_arr = sprintf("<img src='%s' border='0'>",$this->imgurl . "/up.gif");
		$this->dn_arr = sprintf("<img src='%s' border='0'>",$this->imgurl . "/down.gif");

		// ridade värvid (och siis stiilid) muutuvad
		// siin defineerime nad
		$this->style1 = "#AAAAAA";
		$this->style2 = "#CCCCCC";

		// initsialiseerime muutujad
		$this->rowdefs = array();
		$this->data = array();
		$this->actions = array();
		$this->alpha = false;

		$this->title_attribs = array();

		// esimene kord andmeid sisestada?
		// seda on vaja selleks, et määrata default sort order.
		$this->first = true;
	}

	function set_attribs($args = array())
	{
		if ( $args["prefix"] )
		{
			$this->prefix = $args["prefix"];
		}
	}

	// see on alternatiiv. Soovitav on kasutada funktsiooni parse_xml_def
	function define_row($data) 
	{
		// $data peab sisaldama järgmist inffi:
		//	name - välja nimi data sees
		//	caption - välja pealkiri
		// 	sortable - on sorteeritav?
		$this->rowdefs[] = $data;
		// kui see on esimene definitsioon, siis votame
		// selle ka defaultiks, s.t. sorteeritakse esimesena
		// defineeritud kui teisiti pole määratud
		if ($this->first) 
		{
			$this->default_order = $data["name"];
			$this->first = false;
		};
	}

	function define_data($row) 
	{
		// sisestame andmed
		$this->data[] = $row;
		$this->last = sizeof($this->data);
	}

	function merge_data($row)
	{
		$this->data[$this->last-1]  = array_merge($this->data[$this->last-1],$row);
	}

	////
	// !Throws everything away, we need this when we want to use the same
	// table class on the same page for multiple tables
	function reset_data()
	{
		$this->data = array();
	}
	
	
	function define_action($row) 
	{
		// sisestame andmed
		$this->actions[] = $row;
	}

	// defineerib headeri
	function define_header($caption,$links = array())
	{
		$this->headerstring = $caption;
		$hlinks = array();
		// lauri muudetud -->
		if ($this->headerlinkclassid)
		{
			$hlcl=" class='".$this->headerlinkclassid."' ";
		};
		// <--
		while(list($k,$v) = each($links))
		{
						// lauri muudetud -->
			if ($k=="extra")
			{
				$this->headerextra = $v;
			} else
			if ($k=="extrasize")
			{
				$this->headerextrasize= $v;
			} else
			{
				$hlinks[] = sprintf("<a href='$k' $hlcl>$v</a>",$k,$v);
			};
			// <--
		};
		$this->headerlinks = join(" | ",$hlinks);
			
	}
	
	function set_header_attribs($args)
	{
		while(list($k,$v) = each($args))
		{
			$this->header_attribs[$k] = $v;
		};
		$this->header_att_string = join("&",map2("%s=%s",$this->header_attribs));
	}

	function add_query_string($string)
	{
		$this->header_att_string .= "&" . $string;
	}


	function sort_by($params) 
	{
		// määrame ära sorteeritava välja
		// paramteetrid: 
		//		field
		global $aw_tables,$awt; // see peaks olema array,
				   // kus on regitud erinevate tabelite andmed
		$awt->start("table::sort_by");
		reset($this->data);
		$newdata = array();
		$sess_field_key   = $this->prefix . "_sortby";
		$sess_field_order = $this->prefix . "_sorder";
		$before = $aw_tables[$sess_field_key];


		// määrame sorteerimisjärjekorra

		if (!$params["field"]) 
		{
			// kui sorteerimisjärjekorda polnud määratud, siis

			// before peaks sisaldama sessiooni sisse salvestatud
			// välja nime, mille järgi viimasel vaatamisel
			// sorteeriti
			if ($before) {
				$sortby = $before;
				$sorder = $aw_tables[$sess_field_order];
			} else {
			// ja kui sessioonis ka midagi pole, siis votame
			// defaulti
				$sortby = $this->default_order;
				$sorder = $this->default_odir;
			};
			// sorteerime kasvavas järjestuses
		} 
		else 
		if ($params["sorder"] == "")
		{
			// sorteerimisjärjekord anti ka kaasa, nyyd vaatame
			// kumba pidi sorteerida

			// kui enne ja nyyd on samad, siis peaks vist
			// sorteerimisjärjekorda muutma
			if ($params["field"] == $before) 
			{
				// vaatame nyyd sessiooni salvestatud sorteerimisjärjekorda,
				// ja pöörame selle ümber
				if ($aw_tables[$sess_field_order] == "asc") 
				{
					$sorder = "desc";
				}
				else 
				{
					$sorder = "asc";
				};
			} 
			else 
			{
				$sorder = "asc";
			};

			$sortby = $params["field"];
		}
		else
		{
			$sorder = $params["sorder"];
			$sortby = $params["field"];
		}

		$this->sortby = $sortby;
		$this->sorder = $sorder;
		$aw_tables[$sess_field_key] = $this->sortby;
		$aw_tables[$sess_field_order] = $sorder;
		session_register("aw_tables");

		// salvestame koik kasutatud keyd, et ei tekiks olukorda,
		// kus, ühesuguste väärtustega elemendid kaduma lähevad
	
		$usedkeys = array();	
		
		while(list($k,$v) = each($this->data)) {
			$key = $v[$sortby];
			if ($usedkeys[$key] > 0) {
				// seda keyd on juba kasutatud, lisame talle numbri
				$usedkeys[$key]++;
				$key .= $usedkeys[$key] + 1;
		        } else {
				$usedkeys[$key] = 1;	
				$key .= "1";
			};
			$newdata[$key] = $v;
		};

		// sorteerime andmed
		// uurime flagi v2lja
		if ($this->nfields[$this->sortby]) {
			$flag = SORT_NUMERIC;
		} else {
			$flag = SORT_REGULAR;
		};

		if ($sorder == "asc") 
		{
			if ($flag == SORT_NUMERIC)
			{
				ksort($newdata,$flag);
			}
			else
			{
//				uksort($newdata,create_function('$a,$b','return strcasecmp($a,$b);'));
				uksort($newdata,array($this,"__str_cmp"));
			}
		} 
		else 
		{
			if ($flag == SORT_NUMERIC)
			{
				krsort($newdata,$flag);
			}
			else
			{
//				uksort($newdata,create_function('$a,$b','return -strcasecmp($a,$b);'));
				uksort($newdata,array($this,"__reverse_str_cmp"));
			}
		};
		$this->data = $newdata;
		$awt->stop("table::sort_by");
	
	}

	function __str_cmp($a,$b)
	{
		$_a = preg_replace("/<a (.*)>(.*)<\/a>/","\\2",$a);
		$_b = preg_replace("/<a (.*)>(.*)<\/a>/","\\2",$b);
		return strcasecmp($_a,$_b);
	}

	function __reverse_str_cmp($a,$b)
	{
		$_a = preg_replace("/<a (.*)>(.*)<\/a>/","\\2",$a);
		$_b = preg_replace("/<a (.*)>(.*)<\/a>/","\\2",$b);
		return -strcasecmp($_a,$_b);
	}

	function draw() 
	{
		// väljastab tabeli
		if (!is_array($this->rowdefs)) 
		{
			print "Don't know what to do";
			return;
		};

		global $awt;
		$awt->start("table::draw");

/*		if (sizeof($this->data) == 0)
		{
			return "Andmed puuduvad";
		};*/

		global $PHP_SELF;
		$tbl = "";
		reset($this->rowdefs);

		$js_table = "function xnavi_alfa(char_to_look_for) {	with(document.aw_table) {	var loc = \"field=\" + field.options[field.selectedIndex].value +	\"&lookfor=\" + char_to_look_for;	window.location = \"$PHP_SELF?\" + loc;	}}";

		// loome javascripti funktsioonid
		$tbl .= $this->opentag(array("name"     => "script",
					     "language" => "Javascript"));
		$tbl .= $js_table;

		$tbl .= $this->closetag(array("name"  => "script"));
		if ($this->alpha || $this->seachable)		// no form if we don't need it
			$tbl .= $this->opentag(array("name"   => "form",
						     "id"     => "aw_table",
						     "method" => "post"));

		if (is_array($this->sfields) && (sizeof($this->sfields) > 0)) {
			$tbl .= $this->opentag(array("name" => "select",
						     "id"   => "field"));
			while(list($k,$v) = each($this->sfields)) {
				$tbl .= sprintf("<option value='%s'>%s</option>\n",$k,$v);
			};
			$tbl .= $this->closetag(array("name" => "select"));

			$tbl .= $this->tag(array("name"  => "input",
					         "id"    => "lookfor",
						 "value" => ($this->lookfor) ? $this->lookfor : "",
						 "type" => "text",
					         "size" => "30"));

			$tbl .= $this->tag(array("name" => "input",
						 "type" => "submit",
						 "value" => "Apply"));
						

		};
		// kas tähestiku ka joonistame?
		if ($this->alpha) {
			$al = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N",
			            "O","P","Q","R","S","T","U","V","W","X","Y","Z");
			$tbl .= $this->opentag(array("name" => "table",
						     "border" => 0,
						     "cellspacing" => 1,
						     "cellpadding" => 2));
			$tbl .= $this->opentag(array("name" => "tr"));
			while(list(,$c) = each($al)) {
				$tbl .= $this->opentag(array("name" => "td",
							     "classid" => $this->style_alfa));
				$fname = $this->prefix . "_table";
				$tbl .= sprintf("<a href=\"javascript:xnavi_alfa('$c')\">%s</a>",$c);
				$tbl .= $this->closetag(array("name" => "td"));
			};
			$tbl .= $this->closetag(array("name" => "tr"));
			$tbl .= $this->closetag(array("name" => "table"));
		};

		// moodustame välimise raami alguse
		if (is_array($this->frameattribs))
		{
			$tmp = $this->frameattribs;
			$tmp["name"] = "table";
			$tbl .= $this->opentag($tmp);
			$tbl .= $this->opentag(array("name" => "tr"));
			$tbl .= $this->opentag(array("name" => "td","bgcolor" => $this->framebgcolor));
		};
		// moodustame tabeli alguse
		if (is_array($this->tableattribs))
		{
			$tmp = $this->tableattribs;
			$tmp[name] = "table";
			$tbl .= $this->opentag($tmp);
		}

		if ($this->headerstring)
		{
			// lauri muudetud
			$colspan = sizeof($this->rowdefs) + sizeof($this->actions)-(int)$this->headerextrasize;
			$tbl .= $this->opentag(array("name" => "tr"));
			$tbl .= $this->opentag(array("name" => "td","colspan" => $colspan,"classid" => $this->titlestyle));
			$tbl .= "<strong>" . $this->headerstring . ": ";
			$tbl .= $this->headerlinks;
			$tbl .= "</strong>";
			$tbl .= $this->closetag(array("name" => "td"));
			// lauri muudetud
			$tbl .= $this->headerextra;
			$tbl .= $this->closetag(array("name" => "tr"));
		}
		
		$tbl .= $this->opentag(array("name" => "tr"));

		// moodustame headeri
		while(list($k,$v) = each($this->rowdefs)) {
			$ta = array("name" => "td");
	
			// määrame ära headeri stiili
			if ($v["sortable"]) {
			
				// kui on sorteeritud selle välja järgi
				if ($v["name"] == $this->sortby) {
				
					// peab tegema workaroundi, sest class on reserved word,
					// samas on seda vaja kasutada tabeli cellile stiili andmiseks
					$ta["classid"] = $this->header_sorted;
				} else {
					$ta["classid"] = $this->header_sortable;
				};
			} else {
				// ei ole sorteeritav
				$ta["classid"] = $this->header_normal;
			};
			if ($v["talign"]) {
				$ta["align"] = $v["talign"];
			};

			if ($v["tvalign"]) {
				$ta["valign"] = $v["tvalign"];
			};

			if ($this->tbgcolor) {
				$ta["bgcolor"] = $this->tbgcolor;
			};

			if ($v["nowrap"])
				$ta["nowrap"] = "";
				
			$tbl .= $this->opentag($ta);

			// kui on sorteeritav, siis kuvame lingina
			if ($v["sortable"]) {
				
				// vaikimis näitame allanoolt:
				$sufix = $this->dn_arr;

				// kui on sorteeritud selle välja järgi ja kahanevas järjekorras,
				// siis näitame ülesnoolt
				if (($v["name"] == $this->sortby) && ($this->sorder == "desc")) {
					$sufix = $this->up_arr;
				};
				if ($this->sorder == "desc")
				{
					$so = "asc";
				}
				else
				{
					$so = "desc";
				}

				$tbl .= "<b><a href='$PHP_SELF?".$this->header_att_string."&"."sortby=$v[name]&sort_order=$so'>$v[caption] $sufix</a></b>";
			} else {
				$tbl .= $v["caption"];
			};
			$tbl .= $this->closetag(array("name" => "td"));
		};

		// kui actionid on defineeritud, siis joonistame nende jaoks vajaliku headeri
		if (is_array($this->actions) && (sizeof($this->actions) > 0)) {
			$tbl .= $this->opentag(array("name"    => "td",
						     "align"   => "center",
						     "classid" => $this->header_normal,
						     "colspan" => sizeof($this->actions)));
			$tbl .= "Tegevused";
			$tbl .= $this->closetag(array("name" => "td"));
		};

		// header kinni
		$tbl .= $this->closetag(array("name" => "tr"));

		// koostame tabeli sisu
		if (is_array($this->data)) {
			reset($this->data);

			// tsükkel üle data, siia kuhugi peaks ka sorteerimine tulema
			$counter = 0; // kasutame ridadele erineva värvi andmiseks
			$cnt = 0;
			while(list($k,$v) = each($this->data)) {
				$cnt++;
				$counter++;

				// rida algab
				$tbl .= $this->opentag(array("name" => "tr"));
				reset($this->rowdefs);
				
				// tsükkel üle rowdefsi, et andmed oleksid oiges järjekorras
				while(list($k1,$v1) = each($this->rowdefs)) {

					// määrame ära staili
					if ($this->sortby == $v1[name]) {
						$style = (($counter % 2) == 0) ? $this->selected1 : $this->selected2;
						$bgcolor = ($counter % 2) ? $this->selbgcolor1 : $this->selbgcolor2;
					} else {
						$style = (($counter % 2) == 0) ? $this->style1 : $this->style2; 
						$bgcolor = ($counter % 2) ? $this->bgcolor1 : $this->bgcolor2;
					};
						
					// moodustame celli
					$cell_attribs = array("name"    => "td",
							      "classid" => $style,
							      "bgcolor" => $bgcolor);

					if ($this->actionrows)
					{
						$cell_attribs["rowspan"]=$this->actionrows;
					};
					
					// eri värvi cellide jaoks muutus
					if ($v1["chgbgcolor"] && $v[$v1["chgbgcolor"]])
					{
						$cell_attribs["style"]="background:".$v[$v1["chgbgcolor"]];
					};

					
					if ($v1["align"]) {
						$cell_attribs["align"] = $v1["align"];
					};

					if ($v1["valign"]) {
						$cell_attribs["valign"] = $v1["valign"];
					};

					if ($v1["nowrap"]) {
						$cell_attribs["nowrap"] = "";
					};

	
					if ($v["bgcolor"]) {
						$cell_attribs["bgcolor"] = $v["bgcolor"];
					};

					// this one overrides the definition given in the table header
					if ($v["style"]) {
						$cell_attribs["classid"] = $v["style"];
					};

					$tbl .= $this->opentag($cell_attribs);

					if ($v1["name"] == "rec") {
						$val = $cnt;
					} else {
						if ($v1["strformat"])
						{
							$format = localparse($v1["strformat"],$v);
							$val = sprintf($format,$v[$v1["name"]]);
						}
						else
						{
							$val = $v[$v1["name"]];	
						};
					};

					if ($v1["type"] == "time") {
						$val = date($v1["format"],$val);
					};

					if (!$val && $v1["type"]!="int") 
					{
						$val = "&nbsp;";
					};

					$tbl .= $val;
					$tbl .= $this->closetag(array("name" => "td"));
				};

				// joonistame actionid
				$actionridu=$this->actionrows?$this->actionrows:1;

				for ($arow=1;$arow<=$actionridu;$arow++)
				{
					// uutele actioni ridadele tuleb teha uus <tr>
					if ($arow>1)
					{
						$tbl.= $this->opentag(array("name"=>"tr"));
					};
					// joonistame actionid
					reset($this->actions);
					$style = (($counter % 2) == 0) ? $this->style1 : $this->style2; 
					while(list($ak,$av) = each($this->actions)) 
					{
						// joonista ainult need actionid, mis siia ritta kuuluvad
						if ($this->actionrows?( $arow==$av["row"] || ($arow==1 && !$av["row"]) ):1)
						{
							$tdtag=array("name"=>"td",
								"classid" => ($av["style"]) ? $av["style"] : $style,
								"align" => "center");

							$av["cspan"]?$tdtag["colspan"]=$av["cspan"]:"";
							$av["rspan"]?$tdtag["rowspan"]=$av["rspan"]:"";

							$tbl .= $this->opentag($tdtag);

							$tbl.=$av["remote"]?
								"<a href='javascript:remote(0,".$av["remote"].",\"$this->self?".$av["link"]."&id=".$v[$av["field"]].'");\'>'.$av["caption"]."</a>":
								"<a href='$this->self?" . $av["link"] . "&id=" . $v[$av["field"]] . "&" . $av[field] . "=" . $v[$av["field"]] . "'>$av[caption]</a>";

							$tbl .= $this->closetag(array("name" => "td"));
						};
					};

				// rida lopeb
				$tbl .= $this->closetag(array("name" => "tr"));
				};
			};
		};
		// sisu joonistamine lopeb
	
		// tabel kinni
		if (is_array($this->tableattribs))
			$tbl .= $this->closetag(array("name" => "table"));

		// raam kinni
		if (is_array($this->frameattribs))
		{
			$tbl .= $this->closetag(array("name" => "td"));
			$tbl .= $this->closetag(array("name" => "tr"));
			$tbl .= $this->closetag(array("name" => "table"));
		};
		// vorm kinni
		if ($this->alpha || $this->searchable)
			$tbl .= $this->closetag(array("name" => "form"));

		// tagastame selle käki
		$awt->stop("table::draw");
		return $tbl;
	}

	function _format_csv_field($d)
	{
		$new=strtr($d,array('"'=>'""'));
		if (!(strpos($d,';')===false) || $new != $d)
		{
			$new='"'.$new.'"';
		};
		return $new;
	}

	// tagastab csv andmed, kustuda välja draw asemel
	function get_csv_file()
	{
		$d=array();
		reset($this->rowdefs);
		$tbl="";
		if (is_array($this->rowdefs))
		while(list($k,$v) = each($this->rowdefs)) 
		{
				$tbl .= ($tbl?";":"").$this->_format_csv_field($v["caption"]);
		};
		$d[]=$tbl;

		
		// koostame tabeli sisu
		if (is_array($this->data)) 
		{
			reset($this->data);
			$cnt=0;
			while(list($k,$v) = each($this->data)) 
			{
				$tbl="";
				$cnt++;
				reset($this->rowdefs);
				if (is_array($this->rowdefs))
				while(list($k1,$v1) = each($this->rowdefs)) 
				{
					if ($v1["name"] == "rec") 
					{
						$val = $cnt;   
					} else 
					{
						if ($v1["strformat"])
						{
							$format = localparse($v1["strformat"],$v);
							$val = sprintf($format,$v[$v1["name"]]);
						}
						else
						{
							$val = $v[$v1["name"]];	
						};
					};

					if ($v1["type"] == "time")
					{
						$val = date($v1["format"],$val);
					};

					if (!$val && $v1["type"]!="int")
					{
						$val = "";
					};

					$tbl .= ($tbl?";":"").$this->_format_csv_field($val);
				};
				$d[]=$tbl;
			};
		};
		// sisu joonistamine lopeb
		return join("\r\n",$d);
	}

	// genereerib html tagi
	function tag($data) {

		if (!is_array($data)) {
			// kui anti vigased andmed, siis bail out
			return;
		};

		// eraldame nime ja atribuudid
		while(list($k,$v) = each($data)) {
			if ($k == "name") {
				$name = $v;
			} elseif ($k == "id") {
				$attribs[name] = $v;
			} else {
				$attribs[$k] = $v;
			};
		};

		// moodustame atribuutidest stringi
		$attr_list = "";
		if (is_array($attribs)) {
			reset($attribs);
			while(list($k,$v) = each($attribs)) {
				if ($k == "nowrap")
					$attr_list.= " $k ";
				else
					$attr_list .= " $k=\"$v\"";
			};
			// see on workaround, sest "class" on reserved ja seda
			// ei saa array indexina kasutada
			$attr_list = str_replace("classid","class",$attr_list);
		};

		// koostame tagi
		$retval = sprintf("<%s%s>\n",$name,$attr_list);

		// ja tagastame selle
		return $retval;
	}

	// alias eelmisele, monikord voiks selle kasutamine loetavusele kaasa aidata
	function opentag($data) {
		return $this->tag($data);
	}

	// sulgeb tag-i
	function closetag($data) {
		$retval = sprintf("\n</%s>\n",$data[name]);
		return $retval;
	}

	// loeb faili. Hiljem liigutame selle kuhugi baasklassi
	function get_file_contents($name,$bytes = 8192) {
		$fh = fopen($name,"r");
		$data = fread($fh,$bytes);
		fclose($fh);
		return $data;
	}

	// xml funktsioonid
	function _xml_start_element($parser,$name,$attrs) {
		$tmp = "";
		switch($name) {
			// paaritute ridade värv
			case "style1":
				$this->style1 = $attrs["value"];
				break;

			// paaris ridade värv
			case "style2":
				$this->style2 = $attrs["value"];
				break;


			// vaikimisi määratud sorteerimisjärjekord
			case "default_order":
				$this->default_order = $attrs["value"];
				$this->default_odir = $attrs["order"];
				break;

			// tabeli atribuudid
			case "tableattribs":
				$this->tableattribs = $attrs;
				break;
			
			// välimise tabeli atribuudid
			case "frameattribs":
				$this->frameattribs = $attrs;
				break;
			case "framebgcolor":
				$this->framebgcolor = $attrs["bgcolor"];
				break;

			case "titlebar":
				$this->titlestyle = $attrs["style"];
				// lauri muudetud
				$this->headerlinkclassid = $attrs["linkclass"];
				break;

			// tavalise (mittesorteeritava) headeri stiil
			case "header_normal":
				$this->header_normal = $attrs["value"];
				break;

			// sorteeritava headeri stiil
			case "header_sortable":
				$this->header_sortable = $attrs["value"];
				break;

			// stiil, mida kasutada parajasti sorteeritud välja headeri näitamiseks
			case "header_sorted":
				$this->header_sorted = $attrs["value"];
				break;

			// stiilid contenti kuvamiseks
			case "content_style1":
				$this->style1 = $attrs["value"];
				$this->bgcolor1 = $attrs["bgcolor"];
				break;

			case "content_style2":
				$this->style2 = $attrs["value"];
				$this->bgcolor2 = $attrs["bgcolor"];
				break;

			// stiilid millega kuvatakse sorteeritud välja sisu
			case "content_style1_selected":
				$this->selected1 = $attrs["value"];
				$this->selbgcolor1 = $attrs["bgcolor"];
				break;

			case "content_style2_selected":
				$this->selected2 = $attrs["value"];
				$this->selbgcolor2 = $attrs["bgcolor"];
				break;

			// stiil, millega joonistatakse tähestik
			case "style_alfa":
				$this->style_alfa = $attrs["value"];
				break;

			// actionid
			case "action":
				$this->actions[] = $attrs;
				break;

			// kas tähestikku ka näitame?
			case "alpha":
				$this->alpha = true;
				break;

			case "actionrows":
				$this->actionrows = $attrs["value"];
				break;

			// väljad
			case "field":
				while(list($k,$v) = each($attrs)) {
					$temp[$k] = $v;
				};

				$this->rowdefs[] = $temp;
				
				if ($attrs["searchable"]) {
					$this->sfields[$attrs["name"]] = $attrs["caption"];
					$this->searchable = true;
				};
				
				if ($attrs["numeric"]) {
					$this->nfields[$attrs["name"]] = 1;
				};
				
				break;

			default:
				// do nothing

		}; // end of switch
	}

	function define_field($args = array())
	{
		$this->rowdefs[] = $args;
		if ($args["numeric"])
		{
			$this->nfields[$args["name"]] = 1;
		};
	}

	function _xml_end_element($parser,$name) 
	{
		// actually, this is only a dummy function that does nothing
	}

	function parse_xml_def_string($xml_data)
	{
		$this->sfields = array();
		$xml_parser = xml_parser_create();
		xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
		xml_set_object($xml_parser,&$this);
	  xml_set_element_handler($xml_parser,"_xml_start_element","_xml_end_element");
		if (!xml_parse($xml_parser,$xml_data)) 
		{
			echo(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
        xml_get_current_line_number($xml_parser)));
    };
    return $this->data;
	}

	function parse_xml_def($file) 
	{
		$xml_data = $this->get_file_contents($file);
		return $this->parse_xml_def_string($xml_data);
  }
};
?>
