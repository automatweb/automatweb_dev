<?php
include("const.aw");
include("admin_header.$ext");
classload("images","document");
if (!$docid && $oid)
	$docid = $oid;

$obj = $ob->fetch($docid);
if ($obj[period] > 0) {
	classload("periods");
	$periods = new db_periods($per_oid);
	$pdata = $periods->get($obj[period]);	
};
$img = new db_images;
$docs = new document;
$docs->tpl_init("automatweb/documents");

switch($type) {
	case "new":
		switch($class_id)
		{
			case CL_DOCUMENT:
				$docs->read_template("add.tpl");
				$par_data = $docs->get_object($parent);
				$section = $par_data[name];
				if ($period > 0) {
					$periods = new db_periods($per_oid);
					$pdata = $periods->get($period);
					$pername = $pdata[description];			
				} else {
					$period = 0;
					$pername = "staatiline";
				};
				$docs->vars(array("section" => $section,
							"period"  => $period,
							"parent"  => $parent,
							"pername" => $pername));
				$content = $docs->parse();
				$op = "bleh";
				break;
		}
		break;
	
	case "change":
		$docid=$oid;
		break;

	case "bro":
		$content = $docs->brother($docid);
		$op="bleh";
		break;
}

switch($op) {
	case "bleh":
		break;

	case "addalias":
		$al = $ob->fetch($alias);
		if ($al[class_id] == 8)	// form_entry
		{
			// we must let the user select whether he wants to view or edit the entry
			$content = $docs->select_alias($docid, $alias);
			$site_title = "<a href='pickobject.$ext?docid=$docid&parent=".$al[parent]."'>Tagasi</a> / Vali aliase t&uuml;&uuml;p";
			include("admin_footer.$ext");
			exit;
		} else {
			$docs->add_alias($docid,$alias);
			http_refresh(1,"documents.$ext?docid=$docid");
			exit;
		}
	
	case "save":
		switch($section) {
			case "header":
				break;
			case "footer":
				break;
			default:
				sysload("config");
				$config = new db_config;
                		$xml = $config->gen_xml_header();
                		$xml .= "<data>\n";
                		$data = $config->get_config("document");
				while(list(,$val) = each($data)) {
                        		$xml .= $config->gen_xml_tag("field",array("name" => $val[NAME],
                                       		                          "value" => $moreinfo[$val[NAME]]));

				};
                		$xml .= "</data>\n";
		
				$HTTP_POST_VARS[moreinfo] = $xml;	
				break;
		}
		if ($obj[class_id] == 1) {
			if (!$docs->exists($docid)) {
				$docs->create_doc($docid);
			};
		};
		$docs->save($HTTP_POST_VARS);
		#if ($back) {
		#	header("Refresh: 1;url=objects.aw?parent=$obj[parent]");
		#} else {
			header("Refresh: 1;url=documents.$ext?docid=$docid&section=$section");
		#};
		print "\n\n";
		exit;
	case "forms":
		print "<font color='red'>FormGen on installeerimata. Ei saa vorme lisada</a>";
	default:
		$grand = $ob->get_grand_parent($docid);
		$parent = $ob->fetch($obj[parent]);
		$document = $docs->fetch($docid);
		$doctext = $document;
		$struktuur = ($parent[oname]) ? $parent[oname] : "Struktuur";
		$struktuur .= " &gt; $document[oname]";
		// kui class_id on 1, siis jarelikult me muudame
		// mingi sektsiooni defaulte
		if ($obj[class_id] == 1) {
			$mcap = "Sektsiooni defaultid";
		} else {
			$mcap = "Dokumendid";
		};

		$mainmenu[] = "<a href='menuedit.$ext?parent=$obj[parent]&period=$document[period]'>$mcap</a>";
		$menu[1] = "<a href='documents.$ext?docid=$docid'>Muuda dokumenti</a>";
		$menu[3] = "<a href='documents.$ext?docid=$docid&mode=preview'>Eelvaade</a>";
		if ($mode == "preview") {
			global $HTTP_HOST;
			if (strpos($HTTP_HOST,"kroonika") > 0) {
				$content = $docs->show($docid,"","kroonika.tpl");	
			}
			else
			{
				$tpldir = $tpldirs[$HTTP_HOST];
				$template = $docs->get_long_template($docid);
				$content = $docs->gen_preview(array("docid" => $docid, "template" => $template,"leadonly" => false, "stripimg" => false));
			}
		} elseif ($mode == "lingid") {
			classload("link");
			$link = new db_links;
			$docs->read_template("links.tl");
			$alldocs = $ob->get_list();
			$alldocs[0] = "(viide väljapoole)";
			$alldocs_opt = $ob->option_list(0,$alldocs);
			$link->list_by_oid($docid);
			$c = "";
			while($row = $link->db_next()) {
				$docs->vars(array("caption" => $row[caption],
						 "url"     => $row[url],
						 "oid"     => $row[oid],
						 "dokument" => $ob->option_list($row[document],$alldocs)));
				$c .= $docs->parse("line");
					
			};
			$docs->vars(array("dokument" => $ob->option_list(0,$alldocs)));
			$docs->vars(array("line" => $c,
					  "docid" => $docid));
			$content = $docs->parse();
			
		} else {
			// MUUDA
			$o_dat = $docs->get_object($docid);
			$img->list_by_object($docid);
			$imglist = array();
			while($row = $img->db_next()) {
				$imglist[] = sprintf("<a href='images.$ext?parent=$docid&oid=$row[oid]&action=editpic'>#p%d#</a>",
							$row[idx]);
			};
			$aliases = $img->get_aliases_for($docid);
			$formlist = array();
			$linklist = array();
			$graphlist = array();
			$gallist = array();
			$fc = 0;
			$lc = 0; $gc = 0;
			reset($aliases);
			while(list(,$v) = each($aliases)) {
			  switch($v[type]) {
				  case "2":	// form
					  $fc++;
					  $formlist[] = sprintf("#f%d# <i>(Nimi: $v[name])</i>",$fc);
						break;
				  case "8": // form_entry
						$fc++;
				   	$m = unserialize($v[data]);
					  if ($m[type] == "change")
					   	$formlist[] = sprintf("#f%d# <i>(Nimi: $v[name],muuda sisestust)</i>",$fc);
					  else
     				  $formlist[] = sprintf("#f%d# <i>(Nimi: $v[name],kuva sisestus)</i>",$fc);
						break;
					case "21": // link
					    $lc++;
					    $linklist[] = "[<a href=\"#\" onClick=\"javascript:remote(0,300,400,'editlink.aw?target=$v[id]')\">#l".$lc."#</a> - $v[name]]";
						  break;
					case "28": // graafik
					    $gc++;
					    $graphlist[] = sprintf("#g%d# <i>(Nimi: $v[name])</i>",$gc);
						  break;
					case CL_GALLERY:
						$galc++;
						$gallist[] = sprintf("#y%d# <i>(Nimi: $v[name])</i>",$galc);
						break;
				
			};
			};
//			classload("tables");
//			$tbl = new tables();
			$tblist = array();
//			$tbl->list_by_object($docid);
//			while($row = $tbl->db_next()) {
//				$tblist[] = sprintf("<a href='tables.aw?id=$row[id]'>#t%d#</a>",$row[idx]);
//			};
			switch ($section) {
				case "header":
					$title .= " &gt; Header";
					break;
				case "footer":
					$title .= " &gt; Footer";
					break;
				default:
					// kala
			};
			sysload("config");	
			$config = new db_config;
			$data = $config->get_config("document");
			$doc = $docs->fetch($docid,$section);
//			$ochain = $docs->get_object_chain($docid);
/*			$tpl_edit = 1;
			if (is_array($ochain)) {
				while(list($k,$v) = each($ochain)) {
					if ($v[tpl_edit] > 1) {
						$tpl_edit = $v[tpl_edit];
					};
				};
		};*/
//			$tpl_edit = $docs->get_edit_
			$c = "";
		
			if ($section) {
				$docs->read_template("small.tpl");	
				$mainmenu[] = "<a href='documents.$ext?docid=$docid'>$document[title]</a>";
				$mainmenu[] = $section;
			} else { 
				$mainmenu[] = $document[title];
//				$q = "SELECT filename FROM template WHERE id = '$tpl_edit'";
//				$docs->db_query($q);
//				$row = $docs->db_fetch_row();
//				$docs->read_template($row[filename]);
				$docs->read_template($docs->get_edit_template($parent[oid]));
				while(list($k,$v) = each($data)) {
                       			 $docs->vars(array("mname"    => $v[NAME],
                        		                   "mcaption" => $v[CAPTION],
                               		 	           "x"        => $v[X],
                                	        	   "y"        => $v[Y],
                                        	   	   "mval"     => $mi[$v[NAME]][VALUE]));
                        		$tpl = ($v[Y] > 1) ? "textbox" : "textfield";
                        		$c .= $docs->parse("$tpl");
               		 	};

				$_loclist = $ob->get_list();
				if (is_array($_loclist)) {
					while(list($k,$v) = each($_loclist)) {
						if ($k != $docid) {
							$loclist[$k] = $v;
						};
					};
				};
				$floclist = $docs->option_list($obj[parent],$loclist);
			};
			$alilist = array();
			global $HTTP_HOST;
			if (strpos($HTTP_HOST,"kroonika")) {
				$docs->parse("citeblock");
				$docs->parse("allparemalblock");
			} else {
				$docs->ignore("citeblock");
				$docs->parse("allparemalblock");
			};
			$jrk = array("1" => "1",
				     "2" => "2",
				     "3" => "3",
				     "4" => "4",
				     "5"  => "5",
				     "6" => "6",
				     "7" => "7",
				     "8" => "8",
				     "9" => "9",
				     "10" => "10");
		        $docs->vars(array("title" => $doc[title],
				  "docid" => $docid,
				  "cite"  => trim($doc[cite]),
				  "backlink" => $struktuur,
				  "rubriik" => $obj[name],
				  "section" => $section,
				  "jrk1"  => $docs->picker($doc[jrk1],$jrk),
				  "jrk2"  => $docs->picker($doc[jrk2],$jrk),
				  "jrk3"  => $docs->picker($doc[jrk3],$jrk),
				  "allparemal" => ($doc[allparemal] == 1) ? "checked" : "",
				  "esilehel" => ($doc[esilehel] == 1) ? "checked" : "",
				  "showlead" => ($doc[showlead] == 1) ? "checked" : "",
				  "esilehel_uudis" => ($doc[esilehel_uudis] == 1) ? "checked" : "",
				  "yleval_paremal" => ($doc[yleval_paremal] == 1) ? "checked" : "",
				  "title_clickable" => ($doc[title_clickable] == 1) ? "checked" : "",
				  "esileht_yleval" => ($doc[esileht_yleval] == 1) ? "checked" : "",
				  "is_forum" => ($doc[is_forum] == 1) ? "checked" : "",
				  "lead_comments" => ($doc[lead_comments] == 1) ? "checked" : "",
				  "author"  => $doc[author],
				  "photos"  => $doc[photos],
				  "periood"  => ($obj[period] > 0) ? $pdata[description] : "staatiline",
				  "status"  => $docs->option_list($obj[status],array("2" => "Jah","1" => "Ei")),
				  "visible" => $docs->option_list($obj[visible],array("1" => "Jah","0" => "Ei")),
				  "imglist" => join("&nbsp;",$imglist),
				  "gallist" => join("&nbsp;",$gallist),
				  "tblist"  => join("&nbsp;",$tblist),
				  "alilist" => join("&nbsp;",$alilist),
			          "linklist" => join("&nbsp;",$linklist),
				  "formlist" => join("&nbsp;",$formlist),
				  "graphlist" => join("&nbsp;",$graphlist),
				  "cont"    => $c,
				  "loclist" => $floclist,
				  "keywords"  => $doc[keywords],
				  "lead"    => trim($doc[lead]),
				  "content" => trim($doc[content]),
					"channel"	=> trim($doc[channel]),
					"tm"			=> trim($doc[tm]),
					"subtitle"			=> trim($doc[subtitle]),
					"link_text" => trim($doc[link_text])));
			$docs->ignore(array("textfield","textbox"));
			$content = $docs->parse();
			$mainmenu[] = "   | <a href='list_docs.$ext?period=$document[period]'>Nimekiri</a>";
		};
};
include("admin_footer.$ext");
?>
