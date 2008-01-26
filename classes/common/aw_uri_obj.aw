<?php

class aw_uri_obj extends _int_object
{
	private function _parse_uri($uri)
	{
		$tmp = @parse_url($uri);

		if (false === $tmp)
		{
			throw new awex_invalid_arg("Not a URI.");
		}

		if (!empty($tmp["query"]))
		{
			$args = array();
			$tmp2 = explode("&", $tmp["query"]);

			foreach ($tmp2 as $arg)
			{
				$tmp3 = explode("=", $arg, 2);
				$args[$tmp3[0]] = $tmp3[1];
			}
		}

		$this->set_prop("scheme", $tmp["scheme"]);
		$this->set_prop("host", $tmp["host"]);
		$this->set_prop("port", $tmp["port"]);
		$this->set_prop("user", $tmp["user"]);
		$this->set_prop("pass", $tmp["pass"]);
		$this->set_prop("path", $tmp["path"]);
		$this->set_prop("query", $tmp["query"]);
		$this->set_prop("fragment", $tmp["fragment"]);
		$this->set_prop("args", $args);
	}

	public function set_prop($name, $val)
	{
		switch ($name)
		{
			case "string":
				$this->_parse_uri($val);
				return parent::set_prop("string", $val);

			case "scheme":
			case "user":
			case "pass":
			case "host":
			case "port":
			case "path":
			case "query":
			case "fragment":
			case "args":
				$this->_update();
				return parent::set_prop($name, $val);

			default:
				return parent::set_prop($name, $val);
		}
	}

	public function arg($name)
	{
		$args = $this->prop("args");

		if (isset($args[$name]))
		{
			return $args[$name];
		}
		else
		{
			throw new awex_uri_url("URL query argument not found.");
		}
	}

	public function set_arg($name, $val)
	{
		$args = $this->prop("args");
		$args[$name] = $val;
		$this->set_prop("args", $args);
	}

	private function _update()
	{
		$uri = "";

		if ($this->prop("host"))
		{
			if ($this->prop("scheme"))
			{
				$uri .= $this->prop("scheme") . "://";
			}

			if ($this->prop("user") and $this->prop("pass"))
			{
				$uri .= $this->prop("user") . ":" . $this->prop("pass") . "@";
			}
			elseif ($this->prop("user"))
			{
				$uri .= $this->prop("user") . "@";
			}

			$uri .= $this->prop("host");

			if ($this->prop("port"))
			{
				$uri .= ":" . $this->prop("port");
			}
		}

		if ($this->prop("path"))
		{
			$uri .= $this->prop("path");
		}
		else
		{
			$uri .= "/";
		}

		if (count($this->prop("args")))
		{
			$uri .= "?";
			$first = true;

			foreach ($this->prop("args") as $name => $value)
			{
				if ($first)
				{
					$uri .= $name . "=" . $value;
					$first = false;
				}
				else
				{
					$uri .= "&" . $name . "=" . $value;
				}
			}
		}

		if ($this->prop("fragment"))
		{
			$uri .= "#" . $this->prop("fragment");
		}

		parent::set_prop("string", $uri);
	}

	public function __toString()
	{
		return $this->prop("string");
	}
}

class awex_uri extends aw_exception {}
class awex_uri_url extends awex_uri {}

?>
