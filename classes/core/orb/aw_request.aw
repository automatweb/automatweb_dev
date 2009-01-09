<?php

/*
@classinfo maintainer=voldemar
*/
class aw_request
{
	protected $args = array(); // Request parameters. Associative array of argument name/value pairs. read-only
	protected $type = ""; // request type. http|...
	protected $class; // requested class. aw_class.class_name
	protected $default_class = "admin_if";
	protected $action; // requested class action. one of aw_class.actions
	protected $default_action = "change";
	protected $method; // http request method.
	protected $is_fastcall = false; // boolean

	public function __construct($autoload = false)
	{
		if ($autoload)
		{
			// load current/active request
			$this->_autoload();
		}
	}

	/** Determines request type, arguments and loads them returning the specific request object
	@attrib api=1 params=pos
	@returns aw_request object
	**/
	public static function autoload()
	{
		// determine request type and create instance
		if (!empty($_SERVER["SERVER_PROTOCOL"]) and "http" === strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 4)))
		{
			$request = new aw_http_request(true);
		}
		else
		{
			$request = new aw_request(true);
		}

		return $request;
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
		if (empty($this->class))
		{
			return isset($_POST["class"]) ? $_POST["class"] : $_GET["class"];
		}
		return $this->class;
	}

	/**
	@attrib api=1 params=pos
	@returns string
		Requested class action/public method name
	**/
	public function action()
	{
                if (empty($this->action))
                {
                        return isset($_POST["action"]) ? $_POST["action"] : $_GET["action"];
                }

		return $this->action;
	}

	protected function _autoload()
	{
		// load arguments
		if (!empty($_POST))
		{
			$this->args = $_POST;
			$this->method = "POST";
			foreach(safe_array($_GET) as $k => $v)
			{
				$this->args[$k] = $v;
				$_POST[$k] = $v;	
			}
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

	protected function parse_args()
	{
		$this->type = "http"; // other types implemented later
		$this->is_fastcall = !empty($this->args["fastcall"]);
		// no name validation because requests can be formed and sent to other servers where different classes, methods, etc. defined
		$this->class = empty($this->args["class"]) ? $this->default_class : $this->args["class"];
		$this->action = empty($this->args["action"]) ? $this->default_action : $this->args["action"];
	}
}

/** Generic aw_request class unexpected condition indicator **/
class awex_request extends aw_exception {}

/** Requested entity not available **/
class awex_request_na extends awex_request {}

/** Invalid parameter **/
class awex_request_param extends awex_request {}

?>
