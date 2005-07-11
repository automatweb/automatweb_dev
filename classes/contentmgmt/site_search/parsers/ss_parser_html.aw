<?php

class ss_parser_html extends ss_parser_base
{
	function ss_parser_html($url)
	{
		$this->url = $url;
		$this->url_parsed = parse_url($this->url);
		$this->content = NULL;
		$this->headers = NULL;
	}

	function get_links()
	{
		$this->_init_content();

		$ret = array();

		$base = $this->url_parsed["scheme"]."://".$this->url_parsed["host"];

		// I guess we need to figure out all <a tags
		preg_match_all("/<a(.*)>/imsU", $this->content, $mt, PREG_PATTERN_ORDER);
		if (is_array($mt))
		{
			foreach($mt[1] as $match)
			{
				// $match should contain "target="foo" href="bla" ..."
				if (preg_match("/href=(.*)/", $match, $mt2))
				{
					// it might still contain trailing crap and we can not rely on there being an end terminator
					// so manually parse this thang here
					$str = $mt2[1];
					if ($str[0] == "\"" || $str[0] == "'") 
					{
						$delim = $str[0];
					}
					else
					{
						$delim = " ";
					}

					$pos = 1;
					$len = strlen($str);
					while(($str[$pos] != $delim && $str[$pos-1] != "\\") && $pos < $len)
					{
						$pos++;
					}
					$str = substr($str, 1, $pos-1);
					if (substr($str, 0, 10) == "javascript")
					{
						continue;
					}
					if (substr($str, 0, 6) == "mailto")
					{
						continue;
					}

					// now, if baseurl is not included, add that
					
					if ($str[0] == "/")
					{
						$str = $base.$str;
					}
					else
					if ($str[0] == "?")
					{
						$str = $base."/".$str;
					}

					// remove trailing #asdasd anchors
					$pos = strpos($str, "#");
					if ($pos !== false)
					{
						$str = substr($str, 0, $pos);
					}

					// now, if the final url is just a filename, then get the dir from the current url and prepend that
					$pu = parse_url($str);
					if (!$pu["scheme"])
					{
						$str = dirname($this->url)."/".$str;
					}

					$ret[$str] = $str;
					//echo "found url ".$mt2[1]." , turned into $str <br>";
				}
			}
		}
		//die(dbg::dump($ret));
		return $ret;
	}

	function get_text_content()
	{
		$this->_init_content();

		// also remove javascript content
		$fc = preg_replace("/<script(.*)<\/script>/imsU","", $this->content);
		// and css styles
		$fc = preg_replace("/<style(.*)<\/style>/imsU","", $fc);
		// and html comments
		$fc = preg_replace("/<!--(.*)-->/imsU","", $fc);

		return trim(strip_tags($fc));
	}

	function get_last_modified()
	{
		return NULL;
	}

	function get_title($o)
	{
		$this->_init_content();

		if ($o->prop("title_regex") != "")
		{
			if (preg_match($o->prop("title_regex"), $this->content, $mt))
			{
				return trim(strip_tags($mt[1]));
			}
		}

		if (preg_match("/<TITLE>(.*)<\/TITLE>/iUs", $this->content, $mt))
		{
			return trim(strip_tags($mt[1]));
		}
		return NULL;
	}
}
?>