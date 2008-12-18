<?php

/*
@classinfo maintainer=voldemar
*/
class aw_request
{
	private $args = array(); // Request parameters. Associative array of argument name/value pairs. read-only
	private $uri; // request uri aw_uri object if available, empty aw_uri object if not. read-only
	private $type = ""; // request type. http|...
	private $class; // requested class. aw_class.class_name
	private $default_class = "admin_if";
	private $action; // requested class action. one of aw_class.actions
	private $default_action = "change";
	private $method; // http request method.
	private $is_fastcall = false; // boolean

	public function __construct($autoload = false)
	{
		if ($autoload)
		{
			// load current/active request
			$this->autoload();
		}
		else
		{ // do basic construction
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


	/**
	@attrib api=1 params=pos
	@param name required type=string
		Argument name to get value for.
	@returns var
		Request argument value
	@throws awex_request
		When argument by $name not found
	**/
	public function arg($name)
	{
		if (!isset($this->args[$name]))
		{
			throw new awex_request_na("Argument not available");
		}

		return $this->args[$name];
	}

	/**
	@attrib api=1 params=pos
	@param name required type=string
		Argument name to find.
	@returns bool
	**/
	public function arg_isset($name)
	{
		return isset($this->args[$name]);
	}

	/**
	@attrib api=1 params=pos
	@returns array
		Request arguments. argument_name => argument_value pairs
	**/
	public function get_args()
	{
		return $this->args;
	}

	/**
	@attrib api=1 params=pos
	@param arg required type=string,array
		Arguments to set. String argument name or associative array of argument name/value pairs or array of argument names to set value for.
	@param val optional type=mixed
		Argument value. If $arg is array, then it is handled as array of argument names and this value will be set for all of those. Required in that case and when $arg is argument name.
	@returns void
	@comment
		Sets argument values.
	@throws
		awex_request_param when $arg parameter is not valid.
		awex_request_param with code 2 when $arg parameter is string argument name and no second argument given.
	**/
	public function set_arg($arg)
	{
		$sa = (2 === func_num_args());

		if (is_array($arg) and count($arg))
		{
			if ($sa)
			{
				$args = array();
				$val = func_get_arg(1);
				foreach ($arg as $name)
				{
					if (!is_string($name) or !strlen($name))
					{
						throw new awex_request_param("Invalid parameter value '" . var_export($name, true) . "'. Argument name expected.");
					}

					$args[$name] = $val;
				}

				$this->args = $args + $this->args;
			}
			else
			{
				$this->args = $arg + $this->args;
			}
		}
		elseif (is_string($arg) and strlen($arg))
		{
			if ($sa)
			{
				$this->args[$arg] = func_get_arg(1);
			}
			else
			{
				throw new awex_request_param("No value specified to set '{$arg}'.", 2);
			}
		}
		else
		{
			throw new awex_request_param("Invalid parameter value '" . var_export($arg, true) . "'. Array or argument name expected.");
		}

		$this->parse_args();
	}

	/**
	@attrib api=1 params=pos
	@param name optional type=string,array
		Name(s) of request argument(s) to unset.
	@returns array
		Argument names that weren't set in the first place.
	@comment
		Unsets request argument(s). If no arguments given, unsets all request arguments.
	**/
	public function unset_arg()
	{
		$not_found_args = array();
		if (func_num_args())
		{
			$name = func_get_arg(0);

			if (is_array($name))
			{
				foreach ($name as $arg)
				{
					if (isset($this->args[$arg]))
					{
						unset($this->args[$arg]);
					}
					else
					{
						$not_found_args[] = $arg;
					}
				}
			}
			else
			{
				if (!isset($this->args[$name]))
				{
					unset($this->args[$name]);
				}
				else
				{
					$not_found_args[] = $name;
				}
			}
		}
		else
		{
			$this->args = array();
		}

		$this->parse_args();
	}

	/**
	@attrib api=1 params=pos
	@returns boolean
	**/
	public function is_fastcall()
	{
		return $this->is_fastcall;
	}

	/**
	@attrib api=1 params=pos
	@returns string
		Type of current request. http
	**/
	public function type()
	{
		return $this->type;
	}

	/**
	@attrib api=1 params=pos
	@returns string
		Requested class name
	**/
	public function class_name()
	{
		return $this->class;
	}

	/**
	@attrib api=1 params=pos
	@returns string
		Requested class action/public method name
	**/
	public function action()
	{
		return $this->action;
	}

	private function autoload()
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

		// parse arguments
		$this->parse_args();
	}

	private function parse_args()
	{
		$this->type = "http"; // other types implemented later
		$this->is_fastcall = !empty($this->args["fastcall"]);

		// no name validation because requests can be formed and sent to other servers where different classes, methods, etc. defined
		$this->class = empty($this->args["class"]) ? $this->default_class : $this->args["class"];
		$this->action = empty($this->args["action"]) ? $this->default_action : $this->args["action"];
	}

	private function update_uri()
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

/** Generic aw_request class unexpected condition indicator **/
class awex_request extends aw_exception {}

/** Requested entity not available **/
class awex_request_na extends awex_request {}

/** Invalid parameter **/
class awex_request_param extends awex_request {}

?>
