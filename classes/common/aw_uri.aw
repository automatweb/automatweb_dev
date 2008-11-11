<?php
/*
@classinfo  maintainer=voldemar

Class for parsing, editing and constructing URI-s. Type conversion to string is automatic. Currently supports only URL-s.

@examples
$uri = new aw_uri("www.com.net/dev/main.aw?foo=bar");
$uri->set_arg("foo", "foobar");
echo $uri;

Outputs:
www.com.net/dev/main.aw?foo=foobar

*/

class aw_uri
{
	// uri parts
	private $scheme;
	private $host;
	private $port;
	private $user;
	private $pass;
	private $path;
	private $query;
	private $fragment;
	private $args = array();
	private $string;

	// uri character classes
	public $reserved_chars = array(";", "/", "?", ":", "@",  "&", "=", "+", ",", "$");

	// ...
	protected $updated = false;

	public function __construct($uri = null)
	{
		if (isset($uri))
		{
			$this->set($uri);
		}
	}

	/**
	@attrib api=1
	@returns string
		Returns uri as string
	**/
	public function get()
	{
		if (!$this->updated)
		{
			$this->update_string();
		}

		return $this->string;
	}

	/**
	@attrib api=1
	@returns string
		Returns uri as string with xml reserved characters converted to entities
	**/
	public function get_xml()
	{
		return str_replace("&", "&amp;", $this->get());
	}

	/**
	@attrib api=1
	@param uri required type=string
		URI to load
	@returns void
	@errors
		Throws awex_uri_arg if $uri is not a URI and can't be loaded.
	**/
	public function set($uri)
	{
		$tmp = @parse_url($uri);

		if (false === $tmp)
		{
			throw new awex_uri_arg("Not a URI.");
		}

		$args = array();
		if (!empty($tmp["query"]))
		{
			$tmp2 = explode("&", $tmp["query"]);

			foreach ($tmp2 as $arg)
			{
				$tmp3 = explode("=", $arg, 2);
				$args[$tmp3[0]] = isset($tmp3[1]) ? urldecode($tmp3[1]) : null;
			}
		}

		$this->scheme = isset($tmp["scheme"]) ? $tmp["scheme"] : null;
		$this->host = isset($tmp["host"]) ? $tmp["host"] : null;
		$this->port = isset($tmp["port"]) ? $tmp["port"] : null;
		$this->user = isset($tmp["user"]) ? $tmp["user"] : null;
		$this->pass = isset($tmp["pass"]) ? $tmp["pass"] : null;
		$this->path = isset($tmp["path"]) ? $tmp["path"] : null;
		$this->query = isset($tmp["query"]) ? $tmp["query"] : null;
		$this->fragment = isset($tmp["fragment"]) ? $tmp["fragment"] : null;
		$this->args = $args;
		$this->string = $uri;
		$this->updated = true;
	}

	/**
	@attrib api=1
	@param name required type=string
		URI query argument/parameter name
	@returns string
		Query argument value. Returns NULL if argument not set.
	**/
	public function arg($name)
	{
		return isset($this->args[$name]) ? (string) $this->args[$name] : null;
	}

	/**
	@attrib api=1
	@param name required type=string
		URI query argument/parameter name
	@param val required type=string
		New value for argument
	@returns void
	@comment
		Sets query parameter value to $val
	**/
	public function set_arg($name, $val)
	{
		if (!is_scalar($val))
		{
			throw new awex_uri_type("Tried to assing non-scalar value to URI query argument");
		}

		if (str_replace($this->reserved_chars, "a", $name) !== $name) // !!! a asemele midagi
		{
			throw new awex_uri_type("Reserved character(s) in argument name");
		}

		$this->args[$name] = (string) $val;
		$this->updated = false;
	}

	private function update_string()
	{
		$uri = "";

		if ($this->host)
		{
			if ($this->scheme)
			{
				$uri .= $this->scheme . "://";
			}

			if ($this->user and $this->pass)
			{
				$uri .= $this->user . ":" . $this->pass . "@";
			}
			elseif ($this->user)
			{
				$uri .= $this->user . "@";
			}

			$uri .= $this->host;

			if ($this->port)
			{
				$uri .= ":" . $this->port;
			}
		}

		if ($this->path)
		{
			$uri .= $this->path;
		}
		else
		{
			$uri .= "/";
		}

		if (count($this->args))
		{
			$uri .= "?";
			$first = true;

			foreach ($this->args as $name => $value)
			{
				if ($first)
				{
					$uri .= $name . "=" . urlencode($value);
					$first = false;
				}
				else
				{
					$uri .= "&" . $name . "=" . urlencode($value);
				}
			}
		}

		if ($this->fragment)
		{
			$uri .= "#" . $this->fragment;
		}

		$this->string = $uri;
		$this->updated = true;
	}

	public function __toString()
	{
		return $this->get();
	}
}

/* Generic aw_uri class exception */
class awex_uri extends aw_exception {}

/* Generic condition when invalid argument given as method parameter */
class awex_uri_arg extends awex_uri {}

/* Method argument type not what expected */
class awex_uri_type extends awex_uri {}

?>
