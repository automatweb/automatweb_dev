<?php
// $Header: /home/cvs/automatweb_dev/classes/core/trans/pot_scanner.aw,v 1.10 2005/03/31 10:09:42 kristo Exp $
class pot_scanner extends core
{
	function pot_scanner()
	{
		$this->init();
	}

	function full_scan()
	{
		echo "scanning translation strings from classes\n\n";

		// gather list of classes
		$classes = $this->_get_class_list();

		// gather list of trans files
		$trans = $this->_get_trans_list();
		// for each class that is newer than trans file, update trans
		foreach($classes as $class => $tm)
		{
			$potf = aw_ini_get("transdir")."/".basename($class,".aw").".pot";
			if ($trans[$potf] < $tm)
			{
				//echo "scanning file $class \n";
				$this->scan_file($class, $potf);
			}
		}
		echo "all done \n\n";
	}

	function _get_class_list()
	{
		$ret = array();
		$this->_files_from_folder(aw_ini_get("classdir"), "aw", $ret);
		return $ret;
	}

	function _get_trans_list()
	{
		$ret = array();
		$this->_files_from_folder(aw_ini_get("transdir"), "pot", $ret);
		return $ret;
	}

	function _files_from_folder($dir, $ext, &$ret)
	{
		if ($dh = @opendir($dir)) 
		{
			while (false !== ($file = readdir($dh)))
			{ 
				$fn = $dir . "/" . $file;
				if (is_file($fn))
				{
					if (substr($file, -strlen($ext)) == $ext)
					{
						$ret[$fn] = filemtime($fn);
					}
				}
				else
				if (is_dir($fn) && $file != "." && $file != "..")
				{
					$this->_files_from_folder($fn, $ext, $ret);
				}
			}
			closedir($dh);
		}
	}

	function scan_file($file_from, $file_to)
	{
		// tokenizer extension?
		// echo dbg::dump(token_get_all($this->get_file(array("file" => $file_from))));
		// no line numbers

		// regex?
		// preg_match_all("/t\([\"|'](.*)[\"|']\)/imsU", $this->get_file(array("file" => $file_from)), $mt);
		// regex would work, but we need the damn line numbers

		// manual scanner :(
		$strings = $this->_scan_file_props($file_from);

		$meth_name_chars = "1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM";

		$fc = $this->get_file(array("file" => $file_from));
		
		$len = strlen($fc);
		$line = 1;
		for($i = 0; $i < $len; $i++)
		{
			if ($fc{$i} == "t" && strpos($meth_name_chars, $fc{$i-1}) === false)
			{
				$i++;

				// skip spaces
				while ($fc{$i} == " ")
				{
					$i++;
				}

				if ($fc{$i} == "(")
				{
					// we got a real t() call, scan parameter
					// skip spaces
					$i++;
					while ($fc{$i} == " ")
					{
						$i++;
					}
					
					// get separator
					$sep = $fc{$i};
					if ($sep != "\"" && $sep != "'")
					{
						$i--; 
						continue;
					}

					$i++;
					$param = "";
					// scan until end of separator, also check for escapes
					while ($fc{$i} != $sep || ($fc{$i} == $sep && $fc{$i-1} == "\\"))
					{
						$param .= $fc{$i};
						$i++;
					}

					$strings[] = array(
						"line" => $line,
						"str" => $param
					);
				}
			}

			if ($fc{$i} == "\n")
			{
				$line++;
			}
		}

		if (count($strings))
		{
			echo "scanned file $file_from \n";
			$fp = fopen($file_to, "w");
			// add special POT header
			fwrite($fp, "msgid \"\"\n");
			fwrite($fp, "msgstr \"\"\n");
			fwrite($fp, "\"Project-Id-Version: Automatweb 2.0\\n\"\n");
			fwrite($fp, "\"POT-Creation-Date: " . date("r") . "\\n\"\n");
			fwrite($fp, "\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"\n");
			fwrite($fp, "\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"\n");
			fwrite($fp, "\"MIME-Version: 1.0\\n\"\n");
			fwrite($fp, "\"Content-Type: text/plain; charset=ISO-8859-1\\n\"\n");
			fwrite($fp, "\"Content-Transfer-Encoding: 8bit\\n\"\n");
			fwrite($fp, "\"Generated-By: AutomatWeb POT Scanner\\n\"\n");
			fwrite($fp, "\n\n");

			// put same strings on one line
			$res = array();
			foreach($strings as $string)
			{
				$str = $string["str"];
				$str = str_replace('"','\"',$str);

				if (isset($res[$str]))
				{
					$res[$str]["comment"] .= " ".str_replace(aw_ini_get("basedir")."/","", $file_from).":".$string["line"];
				}
				else
				{

					$res[$str] = array(
						"comment" => "#: ".str_replace(aw_ini_get("basedir")."/","", $file_from).":".$string["line"],
						"msgid" => $str
					);
				}
			}

			foreach($res as $dat)
			{
				fwrite($fp, $dat["comment"]."\n");
				fwrite($fp, "msgid \"".$dat["msgid"]."\"\n");
				fwrite($fp, "msgstr \"\"\n");
				fwrite($fp, "\n");
			}
			fclose($fp);
		}
	}

	function warning_scan()
	{
		echo "scanning files for places that should have translation strings\n\n";

		// gather list of classes
		$classes = $this->_get_class_list();

		foreach($classes as $class => $tm)
		{
			$this->scan_file_warn($class);
		}
		if (!$this->warn_cnt)
		{
			echo "no translation warnings found!\n\n";
		}
		else
		{
			echo "finished with ".$this->warn_cnt." warnings \n\n";
		}
	}

	function scan_file_warn($from_file)
	{
		$fc = file($from_file);
		
		// "caption" => "Foo"
		foreach($fc as $ln => $line)
		{
			// only apply for classes that extend from class_base
			if (preg_match("/class(.*)extends(.*)/ims", $line, $mt))
			{
				if (trim($mt[2]) != "class_base")
				{
					return;
				}
			}

			if (preg_match("/\"caption\"(\s*)=>(\s*)['|\"](.*)['|\"]/imsU", $line))
			{
				if (strpos($line, "t(") === false)
				{
					echo "$from_file:".($ln+1)." / untranslated caption ->\n".trim($line)."\n";
					$this->warn_cnt++;
				}
			}
			else
			if (preg_match("/die(\s*)\(['|\"](.*)['|\"]\)/imsU", $line))
			{
				echo "$from_file:".($ln+1)." / die() with untranslated string ->\n".trim($line)."\n";
				$this->warn_cnt++;
			}
			else
			if (preg_match("/raise_error\((.*),['|\"](.*)['|\"]/imsU", $line))
			{
				echo "$from_file:".($ln+1)." / error message with untranslated string ->\n".trim($line)."\n";
				$this->warn_cnt++;
			}
			else
			if (preg_match("/\"msg\"(\s*)=>(\s*)['|\"](.*)['|\"]/imsU", $line))
			{
				echo "$from_file:".($ln+1)." / untranslated message ->\n".trim($line)."\n";
				$this->warn_cnt++;
			}
			else
			if (preg_match("/\"tooltip\"(\s*)=>(\s*)['|\"](.*)['|\"]/imsU", $line))
			{
				echo "$from_file:".($ln+1)." / untranslated tooltip ->\n".trim($line)."\n";
				$this->warn_cnt++;
			}
			else
			if (preg_match("/\"text\"(\s*)=>(\s*)['|\"](.*)['|\"]/imsU", $line))
			{
				echo "$from_file:".($ln+1)." / untranslated menu item text ->\n".trim($line)."\n";
				$this->warn_cnt++;
			}
			else
			if (preg_match("/\"confirm\"(\s*)=>(\s*)['|\"](.*)['|\"]/imsU", $line))
			{
				echo "$from_file:".($ln+1)." / untranslated confirm ->\n".trim($line)."\n";
				$this->warn_cnt++;
			}
			else
			if (preg_match("/\"title\"(\s*)=>(\s*)['|\"](.*)['|\"]/imsU", $line))
			{
				echo "$from_file:".($ln+1)." / untranslated title ->\n".trim($line)."\n";
				$this->warn_cnt++;
			}
		}
	}

	function _scan_file_props($file_from)
	{
		$strings = array();

		// get filename and make the prop file from that
		$propf = aw_ini_get("basedir")."/xml/properties/".basename($file_from,".aw").".xml";
		if (!file_exists($propf))
		{
			return;
		}

		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array(
			"file" => basename($propf, ".xml"),
			"clid" => clid_for_name(basename($propf, ".xml"))
		));

		// generate strings for 
		//  1) property captions
		//  2) property comments
		//  3) property help
		foreach($props as $pn => $pd)
		{
			$strings[] = array(
				"line" => "prop_".$pn,
				"str" => "Omaduse ".$pd["caption"]." ($pn) caption",
			);
			$strings[] = array(
				"line" => "prop_".$pn."_comment",
				"str" => "Omaduse ".$pd["caption"]." ($pn) kommentaar",
			);
			$strings[] = array(
				"line" => "prop_".$pn."_help",
				"str" => "Omaduse ".$pd["caption"]." ($pn) help",
			);
		}
		
		//  4) group captions
		$grps = $cu->get_groupinfo();
		foreach($grps as $gn => $gd)
		{
			$strings[] = array(
				"line" => "group_".$gn,
				"str" => "Grupi ".$gd["caption"]." ($gn) pealkiri",
			);
		}

		//  5) relation captions
		$ri = $cu->get_relinfo();
		foreach($ri as $gn => $gd)
		{
			if (substr($gn, 0, 8) == "RELTYPE_")
			{
				$strings[] = array(
					"line" => "rel_".$gn,
					"str" => "Seose ".$gd["caption"]." ($gn) tekst",
				);
			}
		}

		return $strings;
	}

	function make_aw()
	{
		echo "creating aw files from translated po files\n\n";
		// for each language dir
		$langs = array();
		$dir = aw_ini_get("basedir")."/lang/trans";
		if ($dh = @opendir($dir)) 
		{
			while (false !== ($file = readdir($dh)))
			{
				$fn = $dir."/".$file;
				if (is_dir($fn) && $file != "." && $file != "..")
				{
					if (strlen($file) == 2)
					{
						$langs[$file] = $file;
					}
				}
			}
		}

		foreach($langs as $lang)
		{
			echo "scanning language $lang \n";		

			// get .po files
			$po_files = array();
			$this->_files_from_folder($dir."/".$lang."/po", "po", $po_files);

			// get .aw files
			$aw_files = array();
			$this->_files_from_folder($dir."/".$lang."/aw", "aw", $aw_files);

			// compare times 
			foreach($po_files as $fn => $tm)
			{
				$awfn = $dir."/".$lang."/aw/".basename($fn, ".po").".aw";

				// if .po is newer
				if (!isset($aw_files[$awfn]) || $aw_files[$awfn] < $tm)
				{
					// make new .aw file
					$this->_make_aw_from_po($fn, $awfn);
				}
			}
		}

		echo "all done\n";
	}

	function _make_aw_from_po($from_file, $to_file)
	{
		$f = array();

		$lines = file($from_file);
		$cnt = count($lines);
		for($i = 0; $i < $cnt;  $i++)
		{
			$line = $lines[$i];

			if (substr($line, 0, 5) == "msgid")
			{
				$msgid = substr($line, 7, strlen($line)-9);
			}
			else
			if (substr($line, 0, 6) == "msgstr")
			{
				$str = substr(trim($line), 8, strlen($line)-10);
				while (trim($lines[$i+1]) != "")
				{
					$i++;
					$line = $lines[$i];
					$tmp = substr(trim($line), 1, strlen($line)-3);
					if (trim($tmp) != "")
					{
						$str .= $tmp;
					}
				}

				// write msgid/msgstr pair
				if ($str != "")
				{
					$f[] = "\$GLOBALS[\"TRANS\"][\"".$this->_code_quote($msgid)."\"] = \"".$this->_code_quote($str)."\";\n";
				}
			}
		}

		$fp = fopen($to_file, "w");
		fwrite($fp, "<?php\n");
		foreach($f as $e)
		{
			fwrite($fp, $e);
		}
		fwrite($fp, "?>");
		fclose($fp);
		echo "wrote file $to_file \n";
	}

	function _code_quote($str)
	{
		return str_replace("\"", "\\\"", $str);
	}
}
