<?php

/** aw orb def generator

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_orb_gen.aw,v 1.3 2004/10/29 16:22:45 duke Exp $

	@comment 
	generates orb defs, based on information from docgen_analyzer
**/

class docgen_orb_gen extends class_base
{
	function docgen_orb_gen()
	{
		$this->init("core/docgen");
	}

	function _get_orb_defs($data)
	{
		$xml  = "<?xml version='1.0'?>\n";
		$xml .= "<orb>\n";

		$folder = substr(dirname($data["file"]), 1);

		$xml .= "\t<class name=\"".$data["name"]."\" folder=\"".$folder."\" extends=\"".$data["extends"]."\">\n";

		foreach($data["functions"] as $f_name => $f_data)
		{
			// func is public if name attrib is set
			$attr = $f_data["doc_comment"]["attribs"];
			if (($a_name = $attr["name"]) != "")
			{
				$xml .= "\t\t<action name=\"$a_name\"";
				$x_a = array();
				if (isset($attr["default"]) && $attr["default"] == 1)
				{
					$x_a[] = "default=\"1\"";
				}

				if (isset($attr["nologin"]) && $attr["nologin"] == 1)
				{
					$x_a[] = "nologin=\"1\"";
				}

				if (isset($attr["is_public"]) && $attr["is_public"] == 1)
				{
					$x_a[] = "is_public=\"1\"";
				}

				if (isset($attr["all_args"]) && $attr["all_args"] == 1)
				{
					$x_a[] = "all_args=\"1\"";
				}

				if (isset($attr["is_content"]) && $attr["is_content"] == 1)
				{
					$x_a[] = "is_content=\"1\"";
				}

				if (isset($attr["caption"]) && $attr["caption"] != "")
				{
					$x_a[] = "caption=\"".str_replace("&", "&amp;", $attr["caption"])."\"";
				}

				$xml .= " ".join(" ", $x_a).">\n";

				$xml .= "\t\t\t<function name=\"$f_name\">\n";
				$xml .= "\t\t\t\t<arguments>\n";
	
				// make parameters
				$par = new aw_array($f_data["doc_comment"]["params"]);

				foreach($par->get() as $p_name => $p_dat)
				{
					$xml .= "\t\t\t\t\t<".$p_dat["req"]." name=\"$p_name\"";
					
					$x_p = array();
					if (isset($p_dat["type"]) && $p_dat["type"] != "")
					{
						$x_p[] = "type=\"".$p_dat["type"]."\"";
					}

					if (isset($p_dat["acl"]) && $p_dat["acl"] != "")
					{
						$x_p[] = "acl=\"".$p_dat["acl"]."\"";
					}

					if (isset($p_dat["default"]) && $p_dat["default"] != "")
					{
						$x_p[] = "default=\"".$p_dat["default"]."\"";
					}

					if (isset($p_dat["value"]) && $p_dat["value"] != "")
					{
						$x_p[] = "value=\"".$p_dat["value"]."\"";
					}

					$xml .= " ".join(" ", $x_p)."/>\n";
				}
				$xml .= "\t\t\t\t</arguments>\n";
				$xml .= "\t\t\t</function>\n";
				$xml .= "\t\t</action>\n\n";
			}
		}
		$xml .= "\t</class>\n";
		$xml .= "</orb>\n";
		return $xml;
	}
	
	function make_orb_defs_from_doc_comments()
	{
		$p = get_instance("parser");
		$files = array();
		$p->_get_class_list(&$files, $this->cfg["basedir"]."/classes");

		foreach($files as $file)
		{
			$ignp = $this->cfg["basedir"]."/classes/core/locale";
			if (substr($file, 0, strlen($ignp)) == $ignp)
			{
				continue;
			}
			// check if file is modified
			$clmod = @filemtime($file);
			$xmlmod = @filemtime($this->cfg["basedir"]."/xml/orb/".basename($file, ".aw").".xml");

			if ($clmod >= $xmlmod)
			{
				$da = get_instance("core/docgen/docgen_analyzer");
				$cld = $da->analyze_file($file, true);
				if (!is_array($cld["classes"]) || count($cld["classes"]) < 1)
				{
					continue;
				}

				foreach($cld["classes"] as $class => $cldat)
				{
					if (is_array($cldat["functions"]) && $class != "" && strtolower($class) == strtolower(basename($file, ".aw")) && $cldat["extends"] != "core")
					{
						echo "make orb defs for $file\n";

						$od = str_replace(substr($this->cfg["basedir"]."/classes/",1), "", $this->_get_orb_defs($cldat));
						$od = str_replace(substr($this->cfg["basedir"]."/classes",1), "", $od);

						$this->put_file(array(
							"file" => $this->cfg["basedir"]."/xml/orb/".$class.".xml",
							"content" => $od
						));
					}
				}
				flush();
			}
		}
		echo ("all done\n");
	}
}
?>
