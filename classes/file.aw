<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/file.aw,v 2.153 2007/04/12 07:44:06 kristo Exp $
/*

@classinfo trans=1 relationmgr=yes syslog_type=ST_FILE
@tableinfo files index=id master_table=objects master_index=oid	
@default table=files

@default group=general

	@property filename type=text store=no field=name form=+emb
	@caption Faili nimi

	@property signed type=text store=no editonly=1
	@caption Allkirjastatud

	@property signatures type=text store=no editonly=1
	@caption Allkirjastajad

	@property file type=fileupload form=+emb
	@caption Vali fail

	@property type type=hidden

	@property ord type=textbox size=3 table=objects field=jrk
	@caption J&auml;rjekord

	@property comment type=textbox table=objects field=comment
	@caption Faili allkiri

	@property file_url type=textbox table=objects field=meta method=serialize
	@caption Url, kust saadakse faili sisu

	@property showal type=checkbox ch_value=1
	@caption N&auml;ita kohe

@default group=settings

	@property newwindow type=checkbox ch_value=1 
	@caption Uues aknas
	
	@default table=objects
	@default field=meta
	@default method=serialize
	
	@property show_framed type=checkbox ch_value=1 
	@caption N&auml;ita saidi raamis

	@property show_icon type=checkbox ch_value=1 default=8
	@caption N&auml;ita ikooni

@default group=dates
	@property j_time type=date_select 
	@caption J&otilde;ustumise kuup&auml;ev

	@property act_date type=date_select 
	@caption Avaldamise kuup&auml;ev


	@property udef1 type=textbox display=none
	@caption User-defined 1

	@property udef2 type=textbox display=none
	@caption User-defined 2

@default group=transl
	
	@property transl type=callback callback=callback_get_transl
	@caption T&otilde;lgi

@default group=keywords

	@property grkeywords2 type=keyword_selector field=meta method=serialize group=keywords reltype=RELTYPE_KEYWORD
	@caption AW M&auml;rks&otilde;nad


@groupinfo settings caption=Seadistused
@groupinfo dates caption=Ajad
@groupinfo transl caption=T&otilde;lgi
@groupinfo keywords caption="M&auml;rks&otilde;nad"
@groupinfo acl caption=&Otilde;igused
@default group=acl
	
	@property acl type=acl_manager store=no
	@caption &Otilde;igused

@reltype KEYWORD value=2 clid=CL_KEYWORD
@caption M&auml;rks&otilde;na
*/


class file extends class_base
{
	////
	// !Konstruktor
	function file()
	{
		//obj_set_opt("no_cache", 1);
		$this->init(array(
			"clid" => CL_FILE,
			"tpldir" => "file",
		));
		lc_load("definition");
		$this->lc_load("file","lc_file");

		$this->trans_props = array(
			"comment"
		);

		
		$this->type_whitelist = array(
			"ez" , "hqx", "cpt", "doc", "bin", "dms", "lha", "lzh", "exe", "dll",
			"oda", "pdf", "ai" , "eps", "ps" , "smi", "smil","mif", "xls", "ppt",
			"wbxml", "wmlc", "wmlsc", "bcpio", "vcd", "pgn", "cpio", "dcr", "dir",
			"dxr", "dvi", "spl", "gtar", "hdf", "js", "skp", "skd", "skt", "skm",
			"latex", "nc", "cdf", "shar", "swf", "sit", "sv4cpio", "sv4crc", "tar",
			"tcl", "tex", "texinfo", "texi", "roff", "man", "me", "ms", "ustar", 
			"xhtml", "zip", "au", "snd", "mid", "midi", "kar", "mpga", "mp2", "mp3",
			"aif", "aiff", "aifc", "m3u", "ram", "rm", "rpm", "ra", "wav", "pdb", "xyz",
			"bmp", "gif", "ief", "jpeg", "jpg", "jpe", "png", "tiff", "tif", "djvu", 
			"djv", "wbmp", "ras", "pnm", "pbm", "pgm", "ppm", "rgb", "xbm", "xpm",
			"xwd", "igs", "iges", "msh", "mesh", "silo", "wrl", "vrml", "css", "html",
			"htm", "asc", "txt", "rtx", "rtf", "sgml", "sgm", "tsv", "wml", "wmls", 
			"etx", "xml", "xsl", "mpeg", "mpg", "mpe", "qt", "mov", "mxu", "avi", 
			"movie", "ice", "sxw", "sxc"
		);
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "signatures":
				if(!aw_ini_get("file.ddoc_support"))
				{
					return PROP_IGNORE;
				}
				$re = $this->is_signed($arr["obj_inst"]->id());
				if($re["status"] != 1)
				{
					return PROP_IGNORE;
				}
				$ddoc_inst = get_instance(CL_DDOC);
				$signs = $ddoc_inst->get_signatures($re["ddoc"]);
				foreach($signs as $sig)
				{
					$sig_nice[] = sprintf(t("%s, %s (%s) - %s"), $sig["signer_ln"], $sig["signer_fn"], $sig["signer_pid"], date("H:i d/m/Y", $sig["signing_time"]));
				}
				$data["value"] = join("<br/>", $sig_nice);
				break;
			case "signed":
				if(!aw_ini_get("file.ddoc_support"))
				{
					return PROP_IGNORE;
				}
				$ddoc_inst = get_instance(CL_DDOC);
				$res = $this->is_signed($arr["obj_inst"]->id());
				switch($res["status"])
				{
					case 1:
						$url = $ddoc_inst->sign_url(array(
							"ddoc_oid" => $res["ddoc"],
						));
						$ddoc = obj($res["ddoc"]);
						$add_sig = html::href(array(
							"url" => "#",
							"caption" => t("Lisa allkiri"),
							"onClick" => "aw_popup_scroll(\"".$url."\", \"".t("Allkirjastamine")."\", 410, 250);",
						));
						$ddoc_link = html::href(array(
							"url" => $this->mk_my_orb("change", array(
								"id" => $ddoc->id(),
								"return_url" => get_ru(),
							), CL_DDOC),
							"caption" => t("DigiDoc konteinerisse"),
						));
						$data["value"] = $add_sig." (".$ddoc_link.")";
						break;
					case 0:
						$url = $ddoc_inst->sign_url(array(
							"ddoc_oid" => $res["ddoc"],
						));
						$ddoc = obj($res["ddoc"]);
						$add_sig = html::href(array(
							"url" => "#",
							"caption" => t("Allkirjasta"),
							"onClick" => "aw_popup_scroll(\"".$url."\", \"".t("Allkirjastamine")."\", 410, 250);",
						));
						$ddoc_link = html::href(array(
							"url" => $this->mk_my_orb("change", array(
								"id" => $ddoc->id(),
								"return_url" => get_ru(),
							), CL_DDOC),
							"caption" => t("DigiDoc konteiner"),
						));
						$data["value"] = $add_sig." (".$ddoc_link.")";

						break;
					case -1:
						$url = $ddoc_inst->sign_url(array(
							"file_oid" => $arr["obj_inst"]->id(),
						));
						$data["value"] = html::href(array(
							"url" => "#",
							"caption" => t("Allkirjasta fail"),
							"onClick" => "aw_popup_scroll(\"".$url."\", \"".t("Faili: %s, allkirjastamine")."\", 410, 250);",

						));
						break;
				}
				break;
			case "show_icon":
				if ($arr["obj_inst"]->prop("show_icon") == 8 || $arr["obj_inst"]->prop("show_icon") === NULL)
				{
					$data["value"] = !aw_ini_get("file.no_icon");
				}
				break;

			case "name":
				if ($arr["called_from"] == "releditor")
				{
					$data["value"] = html::href(array(
						"caption" => $arr["obj_inst"]->name(),
						"url" => $this->get_url($arr["obj_inst"]->id(), $arr["obj_inst"]->name())
					));
					return PROP_OK;
				}
				$retval = PROP_IGNORE;
				break;
			case "comment":
				$js = "<script>
					tb = document.getElementById(\"".$data["name"]."\");
					if(window.parent.name == \"InsertAWFupCommand\" && tb.value == \"\")
					{
						FCK=window.parent.opener.FCK;
						if(FCK.Selection.GetType() == \"Text\")
						{
							tb.value = (FCK.EditorDocument.selection)?FCK.EditorDocument.selection.createRange().text:FCK.EditorDocument.getSelection();
						}
					}
				</script>";
				$data["post_append_text"] = $js;
			break;
			case "filename":
				if ($arr["new"])
				{
					$retval = PROP_IGNORE;
				}
				classload("core/icons");

				$fname = $this->check_file_path($arr["obj_inst"]->prop("file"));

				if ($fname == "" && $arr["obj_inst"]->prop("file_url") == "")
				{
					$data["value"] = t("fail puudub");
					return PROP_OK;
				}
				else
				{
					$file = $fname;
				}

				if (is_file($file))
				{
					$size = @filesize($file);
					if ($size > 1024)
					{
						$filesize = number_format($size / 1024, 2)."kb";
					}
					else
					if ($size > (1024*1024))
					{
						$filesize = number_format($size / (1024*1024), 2)."mb";
					}
					else
					{
						$filesize = $size." b";
					}

					$name = $arr["obj_inst"]->prop("name");
					if (empty($name))
					{
						$name = $arr["obj_inst"]->prop("file");
					}

					$data["value"] = html::href(array(
						"url" => $this->get_url($arr["obj_inst"]->id(), $arr["obj_inst"]->name()),
						"caption" => html::img(array(
							"url" => icons::get_icon_url(CL_FILE,$name),
							"border" => "0"
							))." ".$name.", ".$filesize,
						"target" => "_blank",
						"alt" => $fname,
						"title" => $fname
					));
				}
				else
				{
					$fu = $arr["obj_inst"]->prop("file_url");
					$name = basename($fu);
					$data["value"] = html::href(array(
						"url" => $fu,
						"caption" => html::img(array(
							"url" => icons::get_icon_url(CL_FILE,$name),
							"border" => "0"
							))." ".$name,
						"target" => "_blank",
						"alt" => $fname,
						"title" => $fname
					));
				}
				if (is_oid($arr["obj_inst"]->id()))
				{
					$link_url = $this->get_url($arr["obj_inst"]->id(), $arr["obj_inst"]->name());
					$url = $this->mk_my_orb("fetch_file_tag_for_doc", array("id" => $arr["obj_inst"]->id()), CL_FILE);
					$alias_url = $this->mk_my_orb("gen_file_alias_for_doc", array(
						"file_id" => $arr["obj_inst"]->id(),
						"close" => true,
					), CL_FILE);
					$data["value"] .= "&nbsp;&nbsp;
					<script language=\"javascript\">
						function getDocID()
						{
							q = window.parent.location.href;
							ar = new Array();
							ar = q.split('&');
							for(i=0;i<ar.length;i++)
							{
								pair = ar[i].split('=');
								if(pair[0]=='doc')
								{
									doc_url = pair[1];
									break;
								}
							}
							ar = doc_url.split('%26');
							for(i=0;i<ar.length;i++)
							{
								pair=ar[i].split('%3D');
								if(pair[0]=='id')
								{
									doc_id = pair[1];
									break;
								}
							}
							return doc_id;
						}
						if (window.parent.name == \"InsertAWFupCommand\")
						{
							url = '".$alias_url."' + '&doc_id=' + getDocID();
							document.write(\"<script language=javascript>function SetAttribute( element, attName, attValue ) { if ( attValue == null || attValue.length == 0 ) {element.removeAttribute( attName, 0 ) ;} else {element.setAttribute( attName, attValue, 0 ) ;}}</sc\"+\"ript><a href='\"+url+\"' onClick='submit_changeform();FCK=window.parent.opener.FCK;var eSelected = FCK.Selection.MoveToAncestorNode(\\\"A\\\");if (eSelected) { eSelected.href=\\\"".$link_url."\\\";eSelected.innerHTML=\\\"".$arr["obj_inst"]->prop("name")."\\\"; SetAttribute( eSelected, \\\"_fcksavedurl\\\", \\\"$link_url\\\" ) ; } else { FCK.InsertHtml(aw_get_url_contents(\\\"$url\\\")); } '>".t("Paiguta dokumenti")."</a>\");
						}
					</script>
					";
				}	
				break;

			case "file":
				$data["value"] = "";
				break;

		}
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$request = &$arr["request"];
		$retval = PROP_OK;
		switch($data["name"])
		{	
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "name":
				$retval = PROP_IGNORE;
				break;

			case "file_url":
				if (!empty($data["value"]))
				{
					$proto_find = get_instance("protocols/protocol_finder");
					$proto_inst = $proto_find->inst($data["value"]);

					$str = $proto_inst->get($data["value"]);
					preg_match("/<title>(.*)<\/title>/isU", $str, $mt);
					if ($mt[1] == "")
					{
						$mt[1] = basename($data["value"]);
					}
					$arr["obj_inst"]->set_name($mt[1]);
				}
				break;

			case "file":
				// see asi eeldab ajutise faili tegemist eksole?

				// ah sa raisk k&uuml;ll, siinkohal on mul ju konkreetse faili sisu
				if (is_array($data["value"]))
				{
					$file = $data["value"]["tmp_name"];
					$file_type = $data["value"]["type"];
					$file_name = $data["value"]["name"];
				}
				else
				{
					$file = $_FILES["file"]["tmp_name"];
					$file_name = $_FILES["file"]["name"];
					$file_type = $_FILES["file"]["type"];
				};

				// get extension and check whitelist
				/*if ($file != "" && !$this->file_is_in_whitelist($file_name))
				{
					$data["error"] = t("Faili t&uuml;&uuml;p ei ole lubatud!");
					return PROP_FATAL_ERROR;
				}*/

				if (is_uploaded_file($file))
				{
					if ($this->cfg["upload_virus_scan"])
					{
						if (($vir = $this->_do_virus_scan($file)))
						{
							$data["error"] = "Uploaditud failis on viirus $vir!";
							return PROP_FATAL_ERROR;
						}
					}

					
					$pathinfo = pathinfo($file_name);
					if (empty($file_type))
					{
						$mimeregistry = get_instance("core/aw_mime_types");
						$realtype = $mimeregistry->type_for_ext($pathinfo["extension"]);
						$file_type = $realtype;
					};
					
					$final_name = $this->generate_file_path(array(
						"type" => $file_type,
						"file_name" => $file_name
					));
						
					move_uploaded_file($file, $final_name);
					$data["value"] = $final_name;
					$arr["obj_inst"]->set_name($file_name);
					$arr["obj_inst"]->set_prop("type", $file_type);
					$this->file_type = $file_type;
	
					if (file_exists($arr["obj_inst"]->prop("file")))
					{
						unlink($arr["obj_inst"]->prop("file"));
					}
				}
				else
				if (is_array($data["value"]) && $data["value"]["content"] != "")
				{
					$final_name = $this->generate_file_path(array(
						"type" => "text/html",
						"file_name" => $file_name
					));
					$fc = fopen($final_name, "w");
					fwrite($fc, $data["value"]["content"]);
					fclose($f);
					$arr["obj_inst"]->set_name($data["value"]["name"]);
					$arr["obj_inst"]->set_prop("type", $data["value"]["type"]?$data["value"]["type"]:"text/html");
					$data["value"] = $final_name;
					$this->file_type = "text/html";
					if (file_exists($arr["obj_inst"]->prop("file")))
					{
						unlink($arr["obj_inst"]->prop("file"));
					}
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
				
			case "type":
				if ($this->file_type)
				{
					$data["value"] = $this->file_type;
				}
				break;
		};
		// cause everything is alreay handled here
		return $retval;
	}

	function callback_post_save($arr)
	{
		if ($arr["request"]["save_and_index"])
		{
			$i = get_instance(CL_SITE_SEARCH_CONTENT);
			$i->add_single_object_to_index(array("oid" => $arr["obj_inst"]->id()));
		}
		if ($arr["request"]["save_and_doc"] != "")
		{
			$link_url = $this->get_url($arr["obj_inst"]->id(), $arr["obj_inst"]->name());
			$url = $this->mk_my_orb("fetch_file_tag_for_doc", array("id" => $arr["obj_inst"]->id()), CL_FILE);
			die("
				<script type=\"text/javascript\" src=\"".aw_ini_get("baseurl")."/automatweb/js/aw.js\"></script>
				<script language='javascript'>


				function SetAttribute( element, attName, attValue ) 
				{ 
					if ( attValue == null || attValue.length == 0 ) 
					{
						element.removeAttribute( attName, 0 ) ;
					}
					else 
					{
						element.setAttribute( attName, attValue, 0 ) ;
					}
				}
	
				FCK=window.parent.opener.FCK;
				var eSelected = FCK.Selection.MoveToAncestorNode(\"A\");
				if (eSelected) 
				{ 
					eSelected.href=\"".$link_url."\";
					eSelected.innerHTML=\"".$arr["obj_inst"]->prop("name")."\"; 
					SetAttribute( eSelected, \"_fcksavedurl\", \"$link_url\" ) ; 
				} 
				else 
				{ 
					FCK.InsertHtml(aw_get_url_contents(\"$url\")); 
				}

				window.parent.close();
			</script>
			");
		}
	}

	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
		if (!$alias["target"])
		{
			return "";
		}

		$fi = $this->get_file_by_id($alias["target"], false);
		if ($fi["showal"] == 1 && $fi["meta"]["show_framed"])
		{
			$fi = $this->get_file_by_id($alias["target"], true);
			// so what if we have it twice?
			$this->dequote(&$fi["content"]);
			if (strpos(strtolower($fi["content"]),"<body"))
			{
				$fi["content"] .= "</body>";
				preg_match("/<body(.*)>(.*)<\/body>/imsU",$fi["content"],$map);
				// return only the body of the file
   				$replacement = str_replace("\n","",$map[2]);
			}
			else
			{
				$replacement = $fi["content"];
			};
		}
		else
		if ($fi["showal"] == 1)
		{
			$fi = $this->get_file_by_id($alias["target"], true);
			// n2itame kohe
			// kontrollime koigepealt, kas headerid on ehk v&auml;ljastatud juba.
			// dokumendi preview vaatamisel ntx on.
			if (trim($fi["type"]) == "text/html")
			{
				if (!headers_sent())
				{
					header("Content-type: text/html");
				};
    
				// so what if we have it twice?
				$this->dequote(&$fi["content"]);
				if (strpos(strtolower($fi["content"]),"<body>"))
				{
					$fi["content"] .= "</body>";
					preg_match("/<body(.*)>(.*)<\/body>/imsU",$fi["content"],$map);
					// return only the body of the file
	     				$replacement = str_replace("\n","",$map[2]);
				}
				else
				{
					$replacement = $fi["content"];
				};
			}
			// embed xml files
			elseif (trim($fi["type"]) == "text/xml")
			{
				$replacement = htmlspecialchars($fi["content"]);
				$replacement = str_replace("\n","<br />\n",$replacement);
				// tabs
				$replacement = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$replacement);
			}
			else
			{
				header("Content-type: ".$fi["type"]);
				header("Content-Disposition: filename=$fi[name]");
				die($fi["content"]);
			}
		}
		else
		{
			if ($fi["newwindow"])
			{
				$ss = "target=\"_blank\"";
			}
			
			$comment = $fi["comment"];
			if ($comment == "")
			{
				$comment = $fi["name"];
			}
		
			if ($fi["meta"]["show_framed"])
			{
				$url = $this->cfg["baseurl"]."/section=".aw_global_get("section")."/oid=$alias[target]";
			}
			else
			{
				$url = $this->get_url($alias["target"],$fi["name"]);
			};

			classload("core/icons");
			$icon = icons::get_icon_url(CL_FILE,$fi["name"]);

			if ($tpls["file_inplace"] != "")
			{
				$replacement = localparse($tpls["file_inplace"], array(
					"file_url" => $url,
					"file_name" => $comment,
					"file_icon" => $icon
				));
				$ret = array(
					"replacement" => $replacement,
					"inplace" => "file_inplace"
				);
				return $ret;
			}
			else
			if ($tpls["file"] != "")
			{
				$replacement = localparse($tpls["file"], array(
					"file_url" => $url,
					"file_name" => $comment,
					"file_icon" => $icon
				));
			}
			else
			{
				$fo = obj($alias["target"]);
				$fnoi = $fo->prop("show_icon");
				if ($fnoi == 8 || $fnoi === NULL)
				{
					$fnoi = !aw_ini_get("file.no_icon");
				}
				if ($fnoi)
				{
					$replacement = html::img(array(
						"url" => $icon,
						'border' => 0,
					));
				}
				$replacement .= " <a $ss class=\"sisutekst\" href='".$url."'>$comment</a>";
			}
		}
		return $replacement;
	}

	////
	// !Salvestab faili failisysteemi. For internal use, s.t. kutsutakse v&auml;lja save_file seest
	// returns the name of the file that the data was saved in
	function _put_fs($arr)
	{
		if ($arr["fs_folder_to_save_to"])
		{
			$file = $arr["fs_folder_to_save_to"]."/".$arr["name"];
		}
		else
		{
			$file = $this->generate_file_path($arr);
		}
		$this->put_file(array(
			"file" => $file,
			"content" => $arr["content"],
		));
		return $file;
	}

	function generate_file_path($arr)
	{
		if (!empty($arr["file_name"]) && $this->file_is_in_whitelist($arr["file_name"]))
		{
			$file_name = basename($arr["file_name"]);
			$i = 0;
			while(1)
			{
				$fn = aw_ini_get("site_basedir")."/files/".$i."/".$file_name;
				$dir = aw_ini_get("site_basedir")."/files/".$i;
				if (!is_dir($dir))
				{
					mkdir($dir, 0777);
				}
				if (!file_exists($fn))
				{
					return $fn;
				}
				$i++;
			}

		}
		$mt = get_instance("core/aw_mime_types");
		$site_basedir = $this->cfg["site_basedir"];
		// find the extension for the file
		list($major,$minor) = explode("/",$arr["type"]);
		if ($minor == "pjpeg" || $minor == "jpeg")
		{
			$minor = "jpg";
		}

		// first, we need to find a path to put the file
		$filename = gen_uniq_id();
		$prefix = substr($filename,0,1);
		if (!is_dir($site_basedir . "/files/" . $prefix))
		{
			mkdir($site_basedir . "/files/" . $prefix,0705);
		}

		//$minor = $mt->ext_for_type($arr["type"]);
		$file = $site_basedir . "/files/" . $prefix . "/" . "$filename.$minor";
		return $file;
	}

	////
	// !Checks whether a record in the files table is an image (can be embedded inside the web page)
	// $args should contain line from that table
	function can_be_embedded(&$row)
	{
		if (is_object($row))
		{
			return in_array($row->prop("type"),$this->cfg["embtypes"]);
		}
		else
		{
			return in_array($row["type"],$this->cfg["embtypes"]);
		}
	}

	/**
		@param oid required type=oid
		@comment
			finds out if given file object is signed or not.
		@returns
			array(
				status => [1|0|-1]
				ddoc => oid
			)
			Where "status" is:

			1 if is signed
			0 if file is set into digidoc container, which is not signed
			-1 if file isn't in digidoc container

			and "ddoc" is:
			ddoc objects id in which this file lies in.
	**/
	function is_signed($oid)
	{
		if(!is_oid($oid))
		{
			error::raise(array(
				"msg" => t("Vale objekti id!"),
			));
		}
		$c = new connection();
		$ret = $c->find(array(
			"from.class_id" => CL_DDOC,
			"type" => "RELTYPE_SIGNED_FILE",
			"to" => $oid,
		));
		$return = array();
		if(count($ret))
		{
			$ret = current($ret);
			$ret = $ret["from"];
			$inst = get_instance(CL_DDOC);
			$tmp = $inst->is_signed($ret);
			$return["status"] = $tmp?1:0;
			$return["ddoc"] = $ret;
		}
		else
		{
			$return["status"] = -1;
		}
		return $return;
	}

	////
	// !writes file to database - internal usage only, most of the parameters can be omitted
	// $file_id - if specified, overwrites it, if not, creates a new one
	// $name - the original name of file
	// $showal - if we should show the file immediately
	// $type - file MIME type
	// $content - file content
	// $newwindow - if one, file link will open in new window
	// $parent - where to save the file in aw 
	// $comment - comment
	// returns the id if the file
	function save_file($arr)
	{
		extract($arr);

		if ($content != "")
		{
			// stick the file in the filesystem
			$fs = $this->_put_fs(array(
				"type" => $type, 
				"content" => $content, 
				"fs_folder_to_save_to" => $arr["fs_folder_to_save_to"],
				"name" => $arr["name"]
			));
		}

		// now if we need to create a new object, do so
		if (!$file_id)
		{
			$o = obj();
			$o->set_parent($parent);
			$o->set_class_id(CL_FILE);
			$o->set_name($name);
			$o->set_comment($comment);
			$o->set_meta("show_framed", $show_framed);
			$o->set_prop("file", $fs);
			$o->set_prop("showal", $showal);
			$o->set_prop("type", $type);
			$o->set_prop("newwindow", $newwindow);

			if ($arr["fs_folder_to_save_to"] != "")
			{
				$o->set_meta("force_path", $arr["fs_folder_to_save_to"]);
			}
			else
			{
				$o->set_meta("force_path", "");
			}

			$file_id = $o->save();
		}
		else
		{
			// change existing
			$o = obj($file_id);
			if ($parent)
			{
				$o->set_parent($parent);
			}
			if (isset($comment))
			{
				$o->set_comment($comment);
			}
			$o->set_meta("show_framed",$show_framed);

			if ($fs != "")
			{
				$o->set_name($name);
				$o->set_prop("file",$fs);
				$o->set_prop("type",$type);
			}

			if ($arr["fs_folder_to_save_to"] != "")
			{
				$o->set_meta("force_path", $arr["fs_folder_to_save_to"]);
			}
			else
			{
				$o->set_meta("force_path", "");
			}
			$o->set_prop("showal", $showal);
			$o->set_prop("newwindow", $newwindow);
			$o->save();
		}

		return $file_id;
	}

	////
	// !Selle funktsiooni abil salvestatakse fail systeemi sisse,
	// soltuvalt parameetrist store v&auml;&auml;rtusest
	// argumendid:
	// filename(string) - faili nimi
	// type(string) - faili tyyp (MIME)
	// content(string) - faili sisu
	function put($args = array())
	{
		extract($args);
		$this->save_file(array(
			"type" => $type,
			"content" => $content,
			"parent" => $parent,
			"name" => $filename,
			"comment" => $comment
		));
	}

	////
	// !Salvestab special faili, ehk siis otse files kataloogi
	// argumendid:
	// name(string) - faili nimi
	// data(string) - faili sisu
	// path(string) - path alates "files" kataloogist
	// sys(bool) - kas panna faili systeemi juurde?
	function put_special_file($args = array())
	{
		if ($args["sys"])
		{
			$path = $this->cfg["basedir"] . "/files";
		}
		else
		{
			$path = $this->cfg["site_basedir"] . "/files";
		};

		if ($args["path"])
		{
			$path .= "/" . $args["path"];
		};

		$success =$this->put_file(array(
			"file" => $path . "/" . $args["name"],
			"content" => $args["content"],
		));

		return $success;
	}
	
	function get_special_file($args = array())
	{
		if ($args["sys"])
		{
			$path = $this->cfg["basedir"] . "/files";
		}
		else
		{
			$path = $this->cfg["site_basedir"] . "/files";
		};

		if ($args["path"])
		{
			$path .= "/" . $args["path"];
		};

		$contents  =$this->get_file(array(
			"file" => $path . "/" . $args["name"],
		));

		return $contents;
	}

	////
	// !Teeb failiobjekist koopia uue parenti alla
	// argumendid:
	// id - faili id, millest koopia teha
	// parent - koht, mille alla koopia teha
	function cp($args = array())
	{
		extract($args);
		$old = $this->get_file_by_id($id);
		$old["file_id"] = 0;
		$old["parent"] = $parent;
		$this->save_file($old);
	}

	////
	// !returns file by id
	function get_file_by_id($id, $fetch_file = true) 
	{
		$tmpo = obj($id);
		if ($tmpo->class_id() != CL_FILE)
		{
			return array();
		}
		$ret = $tmpo->fetch();
		$ret["id"] = $id;

		$ret["file"] = basename($ret["file"]);
		if ($fetch_file)
		{
			if ($ret["meta"]["file_url"] != "")
			{
				$proto_find = get_instance("protocols/protocol_finder");
				$proto_inst = $proto_find->inst($ret["meta"]["file_url"]);

				$ret["content"] = $proto_inst->get($ret["meta"]["file_url"]);
				$ret["type"] = $proto_inst->get_type();
			}
			else
			if ($ret["file"] != "")
			{
				// file saved in filesystem - fetch it
				if ($tmpo->meta("force_path") != "")
				{
					$tmp = $this->get_file(array("file" => $tmpo->prop("file")));
					if ($tmp !== false)
					{
						$ret["content"] = $tmp;
					}
				}
				else
				{
					$file = $this->check_file_path($tmpo->prop("file"));
					$tmp = $this->get_file(array("file" => $file));
					if ($tmp !== false)
					{
						$ret["content"] = $tmp;
					}
				}
			}
			else
			{
				$this->dequote($ret["content"]);
			};
		}

		if (aw_ini_get("user_interface.content_trans") == 1 && ($cur_lid = aw_global_get("lang_id")) != $tmpo->lang_id())
		{
			$trs = $tmpo->meta("translations");
			if (isset($trs[$cur_lid]))
			{
				$t = $trs[$cur_lid];
				foreach($this->trans_props as $p)
				{
					$ret[$p] = $t[$p];
				}
			}
		}
		return $ret;
	}

	/** N&auml;itab faili. DUH. 
		
		@attrib name=preview params=name nologin="1" default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function show($id)
	{
		if (is_array($id))
		{
			extract($id);
		}
		// allow only integer id-s
		$id = (int)$id;
		error::view_check($id);

		// if the user has access and imgbaseurl is set, then we can redirect the user to that
		// and let apache do the serving the file, that can take quite some time, if the file is large
		$fo = obj($id);
		if (aw_ini_get("image.imgbaseurl") != "" && $fo->prop("file_url") == "" && !$fo->meta("force_path") && $this->file_is_in_whitelist($fo->name()))
		{
			$fname = $fo->prop("file");
			$slash = strrpos($fname, "/");
			$f1 = substr($fname, 0, $slash);

			// get the last folder
			$slash1 = strrpos($f1, "/");
			$f2 = substr($f1, $slash1+1);

			header("Location: ".aw_ini_get("baseurl").aw_ini_get("image.imgbaseurl")."/".$f2."/".substr($fname, $slash+1));
			die();
		}

		$fc = $this->get_file_by_id($id);
		$pi = pathinfo($fc["name"]);
		$mimeregistry = get_instance("core/aw_mime_types");
		$tmp = $mimeregistry->type_for_ext($pi["extension"]);
		if ($tmp != "")
		{
			$fc["type"] = $tmp;
		}
		header("Accept-Ranges: bytes");
		header("Content-Length: ".strlen($fc["content"]));
		header("Content-type: ".$fc["type"]);
		header("Cache-control: public");
		//header("Content-Disposition: inline; filename=\"$fc[name]\"");
		//header("Content-Length: ".strlen($fc["content"]));
		//header("Pragma: no-cache");
		die($fc["content"]);
	}

	/**  
		
		@attrib name=view params=name nologin="1" default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function view($args = array())
	{
		extract($args);
		$fc = $this->get_file_by_id($id);
		if ($this->can_be_embedded($fc))
		{
			$this->mk_path($fc["parent"],"N&auml;ita faili");
			print $fc["content"];
		}
		else
		{
			if ($fc["type"] == "")
			{
				$pi = pathinfo($fc["name"]);
				$mimeregistry = get_instance("core/aw_mime_types");
				$fc["type"] = $mimeregistry->type_for_ext($pi["extension"]);
			}
			header("Content-type: ".$fc["type"]);
			header("Content-Disposition: filename=$fc[name]");
			header("Pragma: no-cache");
			die($fc["content"]);
		};


	}

	function get_url($id,$name)	
	{
		$retval = str_replace("automatweb/","",$this->mk_my_orb("preview", array("id" => $id),"file", false,true,"/"))."/".urlencode(str_replace("/","_",$name));
//		$retval = $this->mk_my_orb("preview", array("id" => $id),"file", false,true);
		return $retval;
	}

	////
	// !rewrites the url to the correct value
	// removes host name
	// translates site/files.aw/id=666/filename to orb calls
	// adds baseurl
	// removes fastcall=1
	function check_url($url)
	{
		if ($url == "")
		{
			return $url;
		}
		$url = preg_replace("/^http:\/\/(.*)\//U","/",$url);

		// don't convert image class urls
		if (strpos($url,"class=image") === false)
		{
			if (substr($url,0,6) == "/files")
			{
				$fileid = (int)(substr($url,13));
				$filename = urlencode(substr($url,strrpos($url,"/")));
				$url = "/orb.".aw_ini_get("ext")."/class=file/action=show/id=".$fileid."/".$filename;
			}
			else
			if (($sp = strpos($url,"fastcall=1")) !== false)
			{
				$url = substr($url,0,$sp).substr($url,$sp+10);
			}
		}
		$url = str_replace("automatweb/", "", $url);
		return aw_ini_get("baseurl").$url;
	}

	////
	// !saves a file that was uploaded in a form to the db
	// $name - the name of the file input in form
	// $parent - the parent object of the file
	// $file_id - if not specified, file will be added, else changed
	function add_upload_image($name,$parent,$file_id = 0, $fs_folder_to_save_to = null)
	{
		$file_id = (int)$file_id;

		$fd = obj();
		if ($file_id)
		{
			$fd = obj($file_id);
		}

		$tmp_name = $_FILES[$name]['tmp_name'];
		if (is_uploaded_file($tmp_name))
		{
			$type = $_FILES[$name]['type'];
			$fname = $_FILES[$name]["name"];

			// if a new file was uploaded, we can forget about the previous one 
			if ($fd->class_id() != CL_FILE)
			{
				$file_id = 0;
				$fd = array();
			}

			$fc = $this->get_file(array("file" => $tmp_name));

			$id = $this->save_file(array(
				"file_id" => $file_id,
				"parent" => $parent,
				"name" => $fname,
				"content" => $fc,
				"type" => $type,
				"fs_folder_to_save_to" => $fs_folder_to_save_to
			));

			return array("id" => $id,"url" => $this->get_url($id,$fname), "orig_name" => $fname);
		}
		else
		{
			if ($file_id)
			{
				if ($fd->class_id() != CL_FILE)
				{
					// we gots problems - this is probably an old image file from formgen
					if ($fd->class_id() == CL_IMAGE)
					{
						// let the image class handle this
						$im = get_instance(CL_IMAGE);
						$id = $im->get_image_by_id($file_id);
						return array("id" => $file_id,"url" => $id["url"]);
					}
					// if we get here, we're pretty much fucked, so bail out
					$this->raise_error(ERR_FILE_WRONG_CLASS, "Objekt $file_id on valet tyypi (".$fd->class_id().")",true);
				}
				else
				{
					return array("id" => $file_id,"url" => $this->get_url($file_id, $fd->name()), "orig_name" => $fd->name());
				}
			}
			else
			{
				return false;
			}
		}
	}

	function request_execute($obj)
	{
		return $this->show($obj->id());
	}

	function get_fields($o, $params = array())
	{
		// $o is file object
		// assume it is a csv file
		// parse it and return first row.
		if ($o->prop("file_url") != "")
		{
			$fp = fopen($o->prop("file_url"),"r");
		}
		else
		{
			$fp = fopen($o->prop("file"),"r");
		}
		$delim = ",";
		if ($params["separator"] != "")
		{
			$delim = $params["separator"];
		}
		if ($delim == "/t")
		{
			$delim = "\t";
		}
		$line = fgetcsv($fp, 100000, $delim);
		$ret = array();
		if(is_array($line))
		{
			foreach($line as $idx => $txt)
			{
				$ret[$idx+1] = $txt;
			}
		}

		return $ret;
	}

	function get_objects($o, $params = array())
	{
		$ret = array();
		if ($o->prop("file_url") != "")
		{
			$fp = fopen($o->prop("file_url"),"r");
		}
		else
		{
			$fp = fopen($o->prop("file"),"r");
		}
		$delim = ",";
		if ($params["separator"] != "")
		{
			$delim = $params["separator"];
		}
		if ($delim == "/t")
		{
			$delim = "\t";
		}
		$first = true;
		while ($line = fgetcsv($fp, 100000, $delim))
		{
			if ($first && $params["file_has_header"])
			{
				$first = false;
				continue;
			}
			$first = false;
			$dat = array();
			foreach($line as $idx => $val)
			{
				$dat[$idx+1] = $val;
			}
			$ret[] = $dat;
		}
		return $ret;
	}

	function get_folders($o)
	{
		return $this->get_objects($o);
	}

	// static
	function get_file_size($fn)
	{
		$fn = basename($fn);
		$path = aw_ini_get("site_basedir")."/files/".$fn{0}."/".$fn;
		return @filesize($path);
	}

	/** creates/updates a file object from the arguments
		@attrib api=1
		@param id optional type=int
		@param parent optional type=int
		@param content
		@param name required
		@param type optional type=string
			filetype

	**/
	function create_file_from_string($arr)
	{
		if (isset($arr["id"]))
		{
			$data["id"] = $arr["id"];
		}
		elseif (isset($arr["parent"]))
		{
			$data["parent"] = $arr["parent"];
		}
		else
		{
			error::raise(array(
				"msg" => t("Need either id or parent"),
			));
		};
		$data["return"] = "id";
		$data["file"] = array(
			"content" => $arr["content"],
			"name" => $arr["name"],
			"type" => $arr["type"],
		);
		$t = get_instance(CL_FILE);
		$rv = $t->submit($data);
		return $rv;
	}

	/** saves editable fields (given in $ef) to object $id, data is in $data

		@attrib api=1

		
	**/
	function update_object($ef, $id, $data)
	{
		return;
	}

	function _do_virus_scan($file)
	{
		$scanner = get_instance("core/virus_scanner");
		$ret = $scanner->scan_file($file);
		return $ret;
	}


	/** Generate a form for adding or changing an object 
		
		@attrib name=new params=name all_args="1" is_public="1" caption="Lisa"

		@param parent optional type=int acl="add"
		@param period optional
		@param alias_to optional
		@param alias_to_prop optional
		@param return_url optional
		@param reltype optional type=int

	**/
	function new_change($args)
	{
		return parent::change($args);
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	/**
		@attrib name=fetch_file_tag_for_doc
		@param id required
	**/
	function fetch_file_tag_for_doc($arr)
	{
		$o = obj($arr["id"]);
		$i = $o->instance();
		$s = $i->parse_alias(array("alias" => array("target" => $arr["id"])));
		die(str_replace(aw_ini_get("baseurl"), "", $s));
	}

	/**
		@attrib name=gen_file_alias_for_doc params=name
		@param doc_id required type=int
		@param file_id required type=int
		@param close optional type=bool
	**/
	function gen_file_alias_for_doc($arr)
	{
		$c = new connection();
		$c->load(array(
			"from" => $arr["doc_id"],
			"to" => $arr["file_id"],
		));
		$c->save();
		$close = "<script language=\"javascript\">
		javascript:window.parent.close();
		</script>";
		$out = $arr["close"]?$close:$c->id();
		die($out);
	}

	function callback_generate_scripts($arr)
	{
		// see if there is a site_search content class set to search from static content
		$ol = new object_list(array(
			"class_id" => CL_SITE_SEARCH_CONTENT,
			"lang_id" => array(),
			"site_id" => array(),
			"search_static" => 1
		));
		$rv = "";
		if ($ol->count())
		{
			$rv .= "
				nsbt = document.createElement('input');nsbt.name='save_and_index';nsbt.type='submit';nsbt.id='button';nsbt.value='".t("Salvesta ja indekseeri otsingusse")."'; el = document.getElementById('buttons');el.appendChild(nsbt);";
		}



		$rv .= "
		if (window.parent.name == \"InsertAWFupCommand\")
		{
		nsbt = document.createElement('input');nsbt.name='save_and_doc';nsbt.type='submit';nsbt.id='button';nsbt.value='".t("Salvesta ja paiguta dokumenti")."'; el = document.getElementById('buttons');el.appendChild(nsbt);}";

		return $rv;
	}

	function check_file_path($fname)
	{
		// get the file name
		$slash = strrpos($fname, "/");
		$f1 = substr($fname, 0, $slash);

		// get the last folder
		$slash1 = strrpos($f1, "/");
		$f2 = substr($f1, $slash1+1);

		// add site basedir
		return aw_ini_get("site_basedir")."/files/".$f2."/".substr($fname, $slash+1);
	}

	function file_is_in_whitelist($fn)
	{
		$ext = substr($fn, strrpos($fn, ".")+1);
		if (in_array($ext, $this->type_whitelist))
		{
			return true;
		}
		return false;
	}
};
?>
