<?php
include("const.aw");
include("admin_header.$ext");
classload("document","periods");
$docs = new document;
$docs->tpl_init("automatweb/documents");
switch($action) {
	case "all":
                $field = ($field) ? $field : "oid";
                $lookfor = "";
                session_unregister("dlookfor");
                session_unregister("dfield");
                header("Location: $PHP_SELF?field=$field");
                break;
	case "activate":
		$docs->set_status($docid,2);
		header("Location: $PHP_SELF");
		exit;
	case "deactivate":
		$docs->set_status($docid,0);
		header("Location: $PHP_SELF");		
		exit;
	case "show":
                $docs->set_visibility($docid,1);
                header("Location: $PHP_SELF");
                exit;
        case "hide":
                $docs->set_visibility($docid,0);
                header("Location: $PHP_SELF");
                exit;
	case "adddoc":
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
		break;
	case "editdoc":
		// me registreerime dokumendi menyyeditoris 
		// uue alamsektsioonina.
		if ($period) {
			$data["class_id"] = CL_PERIODIC_SECTION;
			$data[period] = $period;
		} else {
			$data["class_id"] = CL_DOCUMENT;
		};
		$data[name] = $name;
		$data[parent] = $parent;
		$o_data = $ob->get_object($parent);
		$ob->period = $data[period];
		$lid = $ob->new_object($data);
		$ob->upd_object(array("oid" => $lid, "brother_of" => $lid));	// dokument on enda vend ka
		// me peame selle dokumendi ka menyys registreerima
		if ($period) {
			$q = "INSERT INTO menu (id,type,periodic) VALUES ('$lid','99','1')";
		} else {
			$q = "INSERT INTO menu (id,type) VALUES ('$lid','99')";
		};
		$defaults = $docs->fetch($parent);
		$flist = array();
		$vlist = array();
		while(list($k,$v) = each($docs->knownfields)) 
		{
			$flist[] = $v;
			if ($v == "title") {
				$defaults[$v] = ($name) ? $name : "";
			}
			else
			if ($v == "title_clickable")
			{
				$defaults[$v] = 1;
			}
			$vlist[] = "'" . $defaults[$v] . "'";
		};
//		$docs->db_query($q);
		if (is_array($flist) && (sizeof($flist) > 0)) {
			$part1 = "," . join(",",$flist);
			$part2 = "," . join(",",$vlist);
		} else {
			$part1 = "";
			$part2 = "";
		};
		$q = "INSERT INTO documents (docid $part1) VALUES ('$lid' $part2)";
		$docs->db_query($q);
		header("Location: documents.$ext?docid=$lid");
		print "\n\n";
		exit;
	case "delete":
		$obj = $docs->get_object($remove);
		$dd = $docs->fetch($remove);
		$docs->delete($remove);
		header("Location: menuedit.$ext?parent=$obj[parent]&period=$dd[period]");
		print "\n\n";
		exit;
	default:
		$menu[] = "<a href='$PHP_SELF?action=adddoc'>Lisa uus..</a>";
		$menu[] = "<a href='$PHP_SELF?action=all'>Näita koiki</a>";
		if ($field) 
		{
			$dfield = $field;
      session_register("dfield");
    } 
		else 
		{
			$field = ($dfield) ? $dfield : "oid";
    };
    $dorderby = $field;
    if ($lookfor) 
		{
			$dlookfor = $lookfor;
      session_register("dlookfor");
    } 
		else 
		{
			$lookfor = ($dlookfor) ? $dlookfor : "";
    };

		// siin on list menyydest
		$doclist = $ob->get_list();

		if (!$dorderby) 
			$dorderby = ($sess_dorderby) ? $sess_dorderby : "docid";

		$sess_dorderby = $dorderby;
    $sess_dsorder = $dsorder;
    session_register("sess_dorderby");
    session_register("sess_dsorder");

		if (preg_match("/doclist/i",$HTTP_REFERER)) 
		{
			if ($dorderby != $dordered) 
			{
				// kui järjekord enne ja nüüd ei ühti, siis kindlasti ASC
        $dsorder = "asc";
      } 
			elseif ($dorderby == $dordered) 
			{
				if ($dsorder == "asc") 
				{
					$dsorder = "desc";
        } 
				else 
				{
					$dsorder = "asc";
        };
      };
    } 
		else 
		{
			$dsorder = ($dsorder) ? $dsorder : "asc";
    };

    $dordered = $dorderby;
    session_register("dordered");
    session_register("dsorder");

		$docs->read_template("list.tpl");

		$count = 0;
		$datalines = array();
		$docs->listall();
		while($row = $docs->db_fetch_row()) 
		{
			if ($field == "oid") 
			{
				$key = $row[$dorderby];
				if (strlen($lookfor) > 0) 
				{
					$xd = $doclist[$row[oid]];
					if (preg_match("/^$lookfor.*/i",$xd)) 
					{
						$datalines[$row[docid]] = $row;
					};
				} 
				else 
				{
					$datalines[$row[docid]] = $row;
				};
			} 
			else 
			{
				$key = $row[$field];
				if (strlen($lookfor) > 0) 
				{
					if (preg_match("/^$lookfor.*/i",$key)) 
					{
						$datalines[$row[docid]] = $row;
          };
				} 
				else 
				{
					$datalines[$row[docid]] = $row;
				};
			};
		}; // while

		if ($dsorder == "asc") {
			ksort($datalines);
		} else {
			krsort($datalines);
		};

		reset($datalines);	
		$count = 0;
		while(list(,$a) = each($alfa)) 
		{
			$docs->vars(array("char" => $a));
			$alfabeet .= $docs->parse("alfa"); 
		};
		$act = "fselected2";
    $deact = "fcaption2";
		while(list(,$row) = each($datalines)) 
		{
			$count++;
			$style = (($count % 2) == 1) ? "fcaption3" : "fcaption2";
			$docs->vars(array("id"		=> $row[docid]));
			$stat_tpl = ($row[status] == 2) ? "deaktiveeri" : "aktiveeri";
			$vis_tpl = ($row[visible] == 1) ? "hide" : "show";
			$status = $docs->parse($stat_tpl);
			$visibility = $docs->parse($vis_tpl);
			$docs->vars(array("id"					=> $row[docid],
												"title"				=> $row[title],
												"status" 			=> $status,
											  "style"				=> $style,
												"visibility"  => $visibility,
												"url"					=> "$baseurl/index.$ext?ID=$row[docid]",
												"modifiedby"  => $row[modifiedby],
												"sid"	        => ($dorderby == "docid") ? $act : $deact,
							          "stitle"      => ($dorderby == "title") ? $act : $deact,
							          "soid"				=> ($dorderby == "oid") ? $act : $deact,
							          "smodified"   => ($dorderby == "modified") ? $act : $deact,
							          "smodifiedby" => ($dorderby == "modifiedby") ? $act : $deact,
												"modified" 		=> $docs->time2date($row[modified]),
					       	   	  "asukoht" 		=> $doclist[$row[parent]]));
			$lines .= $docs->parse("line");
		#	};
		};
		$docs->ignore(array("aktiveeri","deaktiveeri","hide","show"));
		$docs->vars(array("line" 	=> $lines,
                                  "char"        => $alfabeet,
			          "fselect"     => $docs->option_list($field,array("docid" => ID,"title" => "Pealkiri",
   "oid"   => "Asukoht","modifiedby" => "Muutja")),	
				  "lookfor"     => $lookfor,
                                  "gid"  	=> ($dorderby == "docid") ? ( ($dsorder == "asc") ? "down" : "up" ) : "down",  
                                  "gtitle"  	=> ($dorderby == "title") ? ( ($dsorder == "asc") ? "down" : "up" ) : "down",  
                                  "goid"  	=> ($dorderby == "oid") ? ( ($dsorder == "asc") ? "down" : "up" ) : "down",  
                                  "gmodified"  	=> ($dorderby == "modified") ? ( ($dsorder == "asc") ? "down" : "up" ) : "down",  
                                  "gwho"  	=> ($dorderby == "modifiedby") ? ( ($dsorder == "asc") ? "down" : "up" ) : "down",  
				  "total" 	=> $count));
		$content = $docs->parse();
};
$title = sprintf("<a href='%s'>Dokumendid</a>",$back);
include("admin_footer.$ext");
?>
