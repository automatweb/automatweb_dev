<?php

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
		$strings = array();
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
			foreach($strings as $string)
			{
				fwrite($fp, "#: ".str_replace(aw_ini_get("basedir")."/","", $file_from).":".$string["line"]."\n");
				fwrite($fp, "msgid \"".$string["str"]."\"\n");
				fwrite($fp, "msgstr \"\"\n");
				fwrite($fp, "\n");
			}
			fclose($fp);
		}
	}
}