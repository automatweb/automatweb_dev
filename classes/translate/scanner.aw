<?php
// parses a bunch o files and creates translation templates
classload("aw_template");
class scanner extends aw_template
{
	function scanner()
	{
		$this->init(array(
			"no_db" => 1
		));
	}

	function req_dir($args = array())
	{
		$path = $args["path"];
		if ($dir = opendir($path))
		{
			while (($file = readdir($dir)) !== false)
			{
				# skip the stuff that starts with .
				if (substr($file,0,1) == ".")
				{
					continue;
				};

				$fqfn = $path . "/" . $file;
				if (is_dir($fqfn) && !is_link($fqfn) && ($file != "CVS"))
				{
					$this->req_dir(array("path" => $fqfn));
				}
				elseif (is_file($fqfn) && is_readable($fqfn) && (preg_match("/\.aw$/",$fqfn)))
				{
					$this->files[] = $fqfn;
				};
			};
			closedir($dir);
		};
	}

	function make_keys($arr)
	{
		// max() needs at least one element
		$ret = array(0);
		if (is_array($arr))
		{
			foreach($arr as $v)
			{
				$ret[$v] = $v;
			}
		}
		return $ret;
	}


	function run()
	{
		$cdir = $this->cfg["basedir"] . "/classes";
		$this->files = array();
		$this->req_dir(array("path" => $cdir));
		$files = $this->files;
		ksort($files);

		$this->winsize = 4;

		$trans_ini = file_get_contents('config/ini/translate.ini');
		preg_match_all("/translate\.ids\[(\d+)\]/",$trans_ini, $mt);
		$trans_ids = $this->make_keys($mt[1]);
		$new_ini = "";
		$next_free = max($trans_ids)+1;
		$used = array();

		foreach($files as $fname)
		{
			$this->scan_file($fname);
			if (!empty($this->trans_id) && !defined($this->trans_id) && !$used[$this->trans_id])
			{
				// create a new trans_id then!
				$new_ini .= "translate.ids[$next_free] = " . $this->trans_id . "\n";
				$used[$this->trans_id] = 1;
				$next_free++;
			}
			if ($this->valid)
			{
				// write the translation out .. first figure out a name
				$outname = "xml/trtemplate/" . $this->trans_id . ".xml";

				// but before I can write it out, I need to read it in .. deserialize it
				// add the new strings .. and serialize it again .. and only then
				// can I write it out
				$old = file_exists($outname) ? $this->_unser($outname) : array();

				// but this shit will leave the original strings hanging around there
				$new = array_merge($old,$this->strings);

				$ser = aw_serialize(array_values($new),SERIALIZE_XML,array("ctag" => "trtemplate","num_prefix" => "string","enumerate" => false));

				$this->put_file(array(
					"file" => $outname,
					"content" => $ser,
				));
				print "writing $outname\n";


			}
		}

		$this->put_file(array(
			"file" => "config/ini/translate.ini",
			"content" => $trans_ini . $new_ini,
		));

		if (strlen($new_ini) > 0)
		{
			print "Following lines were added to translate.ini\n";
			print $new_ini;
			print "-----------\n";
		}

		print "ALL DONE!!!\n";
	}

	function scan_file($fname)
	{
		//print "scanning $fname\n";
		$source = file_get_contents($fname);
		//$source = join("",file($fname));
		$tokens = token_get_all($source);
		$this->result = array();
		$commstr = "";
		foreach($tokens as $token)
		{
			if (is_array($token) && $token[0] === T_COMMENT)
			{
				$commstr .= $token[1];
			};
		}
		$commlines = explode("\n",$commstr);
		$this->strings = array();
		$this->trans_id = "";
		// now I have to figure out the context .. or rather the name of the file I'll be writing to
		// how do I do that?
		foreach($commlines as $line)
		{
			if (preg_match("/^\s*@caption (.*)/",$line,$m))
			{
				$id = md5($m[1]);
				$this->strings[$id] = array(
					"id" => $id,
					"text" => $m[1],
					"ctx" => CTX_CAPTION,
				);
			};
			if (preg_match("/^\s*@comment (.*)/",$line,$m))
			{
				$id = md5($m[1]);
				$this->strings[$id] = array(
					"id" => $id,
					"text" => $m[1],
					"ctx" => CTX_COMMENT,
				);
			};

			if (preg_match("/^\s*@classinfo trans_id=(\w*)/",$line,$m))
			{
				$this->trans_id = $m[1];
			};
		}

		$this->valid = false;
		if (sizeof($this->strings) == 0)
		{

		}
		elseif (empty($this->trans_id))
		{
			//print "ERR: $fname doesn't have a defined translation context, skipping\n"; 
		}
		else
		{
			//$this->res = aw_serialize($strings,SERIALIZE_XML,array("ctag" => "trtemplate","num_prefix" => "string","enumerate" => false));
			$this->valid = true;
			print "Updating $fname\n";
			//print_r($res);
		};

		

		// now I have to check whether trans_id has been registered .. if so, do nothing
		// if not .. generate an unique ID for it and update some kind of INI file
		//print_r($strings);
		/*
		for ($i = 0; $i <= sizeof($tokens) - $winsize; $i++)
		{
			if (is_array($tokens[$i]) && ($tokens[$i][0] === T_STRING) && ($tokens[$i][1] === "tr"))
			{
				$this->check_tr(array_slice($tokens,$i,4));
			};
		};
		*/
		if (sizeof($this->result) > 0)
		{
			//print_r($this->result);
		};
	}

	function check_tr($win)
        {
                if (    ($win[1] === "(") &&
                        ($win[3] === ")") &&
                        (is_array($win[0]) && is_array($win[2]))
                )
                {
                        $this->result[] = $win[2][1];
                };
	}

	function _unser($old)
	{
		$source = file_get_contents($old);
		$res = array();
		$p = xml_parser_create();
		xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0);
		xml_parse_into_struct($p,$source,$vals,$index);
		xml_parser_free($p);
		foreach($vals as $key => $val)
		{
			if ($val["tag"] == "id" && $val["type"] == "complete")
			{
				$id = $val["value"];
			};
			if ($val["tag"] == "text" && $val["type"] == "complete")
			{
				$text = $val["value"];
			};
			if ($val["tag"] == "ctx" && $val["type"] == "complete")
			{
				$ctx = $val["value"];
			};
			if ($val["tag"] == "string" && $val["type"] == "close")
			{
				$res[$id] = array(
					"id" => $id,
					"text" => $text,
					"ctx" => $ctx,
				);
			};
                }
		return $res;
	}

	/*

	[106] => Array
		(
		[0] => 304
		[1] => tr
		)

	[107] => (
	[108] => Array
		(
		[0] => 312
		[1] => "teemade kataloog"
		)

	[109] => )
	*/



};
?>
