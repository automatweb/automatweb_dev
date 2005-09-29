<?php

class ss_parser_base
{
	/** returns an array of urls that the current page contains
	**/
	function get_links()
	{
	}

	/** returns the current url
	**/
	function get_url()
	{
		return $this->url;
	}

	/** returns a string that contains the text of the current page
	**/
	function get_text_content()
	{
	}

	/** returns the last modified timestamp of the current page or NULL if it can not be read
	**/
	function get_last_modified()
	{
		
	}

	/** returns the title of the current page or NULL if none can be read
	**/
	function get_title()
	{
	}

	//////////// private
	function _init_content()
	{
		if ($this->content !== NULL)
		{
			return;
		}

		if ($this->url[0] == "/")
		{
			// filesystem, get from that
			list($usec, $sec) = explode(" ", microtime());
			$tm_s = ((float)$usec + (float)$sec);

			$this->content = $this->_get($this->url);
			$this->headers = "Last-modified: ".gmdate("D, d M Y H:i:s",filemtime($this->url))." GMT\n";

			list($usec, $sec) = explode(" ", microtime());
			$tm_e = ((float)$usec + (float)$sec);

			echo "fetch ".$this->url.", took ".($tm_e-$tm_s)." seconds <br>\n";
			flush();

			return;
		}

		// fetch the page and put headers in $this->headers and content in $this->content
		$http = get_instance("protocols/file/http");

		list($usec, $sec) = explode(" ", microtime());
		$tm_s = ((float)$usec + (float)$sec);

		$this->content = $http->get($this->url);
		$this->headers = $http->get_headers();
		if (strpos($this->headers, "404 Not Found") !== false)
		{
			$this->content = "";
		}

		list($usec, $sec) = explode(" ", microtime());
		$tm_e = ((float)$usec + (float)$sec);

		echo "fetch ".$this->url.", took ".($tm_e-$tm_s)." seconds <br>\n";
		flush();

		//echo "content for ".$this->url." = ".$this->content." <br>";
		unset($http);

		$this->content = html_entity_decode(str_replace("&#160;", " ", $this->content));
	}

	function _put($fn, $c)
	{
		$fp = fopen($fn, "w");
		error::raise_if(!$fp, array(
			"id" => "ERR_NO_ACC",
			"msg" => sprintf(t("parser_base::_put(%s, content): can not open %s for writing!"), $fn, $fn)
		));
		fwrite($fp, $c);
		fclose($fp);
	}

	function _get($fn)
	{
		$fp = fopen($fn, "r");
		error::raise_if(!$fp, array(
			"id" => "ERR_NO_ACC",
			"msg" => sprintf(t("parser_base::_get(%s): can not open file for reading!"), $fn)
		));
		$res = fread($fp, filesize($fn));
		fclose($fp);
		return $res;
	}
}
