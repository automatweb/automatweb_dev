<?php
/*
@classinfo  maintainer=kristo
*/

class ss_parser_rtf extends ss_parser_base
{
	function ss_parser_rtf($url)
	{
		$this->url = $url;
		$this->url_parsed = parse_url($this->url);
		$this->content = NULL;
		$this->headers = NULL;
	}

	function get_links()
	{
		return array(); // docs contain no links
	}

	function get_text_content()
	{
		$this->_init_content();

		// write to temp file
		$fn = tempnam(aw_ini_get("server.tmpdir"), "ss-xls2txt");

		$this->_put($fn,$this->content);

		$cmd = aw_ini_get("server.rtf2txt")." < $fn";
		
		$txt = shell_exec($cmd);
		unlink($fn);
		return $txt;
	}

	function get_last_modified()
	{
		$ts = NULL;
		$lines = explode("\n", $this->headers);
		foreach($lines as $line)
		{
			list($nm, $val) = explode(" ", $line, 2);
			//echo "nm = $nm, val = $val <br>";
			if (trim($nm) == "Last-Modified:")
			{
				$ts = strtotime(trim($val));
				//echo "from $val got timestamp ".date("d.m.Y H:i:s", $ts)." <br>";
			}
		}
		return $ts;
	}

	function get_title()
	{
		$this->_init_content();

		return basename($this->url);
	}
}
?>
