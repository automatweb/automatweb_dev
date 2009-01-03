<?php

class aw_http_request extends aw_request
{
	protected $uri; // request uri aw_uri object if available, empty aw_uri object if not. read-only

	public function __construct($autoload = false)
	{
		parent::__construct($autoload);
		if (!$autoload)
		{
			$this->uri = new aw_uri();
		}
	}

	/**
	@attrib api=1 params=pos
	@returns aw_uri
		Request uri if available.
	@throws
		awex_request_na when uri is not available in current request type
	**/
	public function get_uri()
	{
		$this->update_uri();
		return clone $this->uri;
	}

	protected function _autoload()
	{
		// load arguments
		if (!empty($_POST))
		{
			$this->args = $_POST;
			$this->method = "POST";
		}
		elseif (!empty($_GET))
		{
			$this->args = $_GET;
			$this->method = "GET";
		}

		// load uri
		if (!empty($_SERVER["REQUEST_URI"]))
		{
			try
			{
				$this->uri = new aw_uri($_SERVER["REQUEST_URI"]);
			}
			catch (Exception $e)
			{
				$this->uri = new aw_uri();
			}
		}

		// parse special automatweb request variables
		$AW_GET_VARS = array();
		$pi = "";
		$section = "";
		$PATH_INFO = "";
		$QUERY_STRING = "";
		$REQUEST_URI = "";
		$PATH_INFO = "";

		if (!empty($_SERVER["PATH_INFO"]))
		{
			$PATH_INFO = preg_replace("|\?automatweb=[^&]*|","", $_SERVER["PATH_INFO"]);
		}

		if (!empty($_SERVER["QUERY_STRING"]))
		{
			$QUERY_STRING = preg_replace("|\?automatweb=[^&]*|","", $_SERVER["QUERY_STRING"]);
		}

		if (!empty($_SERVER["REQUEST_URI"]))
		{
			$REQUEST_URI = $_SERVER["REQUEST_URI"];
		}

		if (empty($QUERY_STRING) and empty($PATH_INFO) and !empty($REQUEST_URI))
		{
			$QUERY_STRING = str_replace(array("xmlrpc.aw", "index.aw", "orb.aw", "login.aw", "reforb.aw"), "", $REQUEST_URI);
		}

		if (strlen($PATH_INFO) > 0)
		{
			$pi = $PATH_INFO;
		}

		if (strlen($QUERY_STRING) > 0)
		{
			$pi .= "?".$QUERY_STRING;
		}

		$REQUEST_URI = preg_replace("|\?automatweb=[^&]*|","", $REQUEST_URI);
		$pi = preg_replace("|\?automatweb=[^&]*|ims", "", $pi);

		if ($pi)
		{
			// if $pi contains & or =
			if (preg_match("/[&|=]/",$pi))
			{
				// expand and import PATH_INFO
				// replace ? and / with & in $pi and output the result to AW_GET_VARS
				parse_str(str_replace("?","&",str_replace("/","&",$pi)),$AW_GET_VARS);
				$GLOBALS["fastcall"] = array_key_exists("fastcall", $AW_GET_VARS) ? $AW_GET_VARS["fastcall"] : null;
			}

			if (($_pos = strpos($pi, "section=")) === false)
			{
				// ok, we need to check if section is followed by = then it is not really the section but
				// for instance index.aw/set_lang_id=1
				// we check for that like this:
				// if there are no / or ? chars before = then we don't prepend

				$qpos = strpos($pi, "?");
				$slpos = strpos($pi, "/");
				$eqpos = strpos($pi, "=");
				$qpos = $qpos ? $qpos : 20000000;
				$slpos = $slpos ? $slpos : 20000000;

				if (!$eqpos || ($eqpos > $qpos || $slpos > $qpos))
				{
					// if no section is in url, we assume that it is the first part of the url and so prepend section = to it
					$pi = str_replace("?", "&", "section=".substr($pi, 1));
				}
			}

			if (($_pos = strpos($pi, "section=")) !== false)
			{
				// this here adds support for links like http://bla/index.aw/section=291/lcb=117
				$t_pi = substr($pi, $_pos+strlen("section="));
				if (($_eqp = strpos($t_pi, "=")) !== false)
				{
					$t_pi = substr($t_pi, 0, $_eqp);
					$_tpos1 = strpos($t_pi, "?");
					$_tpos2 = strpos($t_pi, "&");
					if ($_tpos1 !== false || $_tpos2 !== false)
					{
						// if the thing contains ? or & , then section is the part before it
						if ($_tpos1 === false)
						{
							$_tpos = $_tpos2;
						}
						else
						if ($_tpos2 === false)
						{
							$_tpos = $_tpos1;
						}
						else
						{
							$_tpos = min($_tpos1, $_tpos2);
						}
						$section = substr($t_pi, 0, $_tpos);
					}
					else
					{
						// if not, then te section is the part upto the last /
						$_lslp = strrpos($t_pi, "/");
						if ($_lslp !== false)
						{
							$section = substr($t_pi, 0, $_lslp);
						}
						else
						{
							$section = $t_pi;
						}
					}
				}
				else
				{
					$section = $t_pi;
				}
			}

			$AW_GET_VARS["section"] = $section;
		}

		$this->args = $this->args + $AW_GET_VARS;

		// parse arguments
		$this->parse_args();
	}

	protected function update_uri()
	{
		$this->uri->unset_arg();

		try
		{
			$this->uri->set_arg($this->args);
		}
		catch (Exception $e)
		{
			if (is_a($e, "awex_uri_type"))
			{
				if (awex_uri_type::RESERVED_CHR === $e->getCode())
				{
					throw new awex_request_na("This request contains arguments that can't be converted to URI argument names.");
				}
				else
				{
					throw new awex_request_na("This request contains argument values that can't be converted to URI arguments.");
				}
			}
			else
			{
				throw $e;
			}
		}
	}
}

?>
