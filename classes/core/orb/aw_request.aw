<?php

/*
@classinfo maintainer=voldemar
*/
class aw_request
{
	protected $args = array(); // Request parameters. Associative array of argument name/value pairs. read-only
	protected $class; // requested class. aw_class.class_name
	protected $default_class = "admin_if";
	protected $action; // requested class action. one of aw_class.actions
	protected $default_action = "change";
	protected $method; // http request method.
	protected $is_fastcall = false; // boolean
	protected $application; // object
	protected static $application_classes = array( //!!! tmp. teha n2iteks interface-ga. implements application
		"crm_sales",
		"realestate_manager",
		"mrp_workspace",
		"admin_if",
		"aw_object_search",
		"bug_tracker",
		"events_manager"
	);
	protected $protocol; // protocol object

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
		if (!empty($_SERVER["SERVER_PROTOCOL"]) and substr_count(strtolower($_SERVER["SERVER_PROTOCOL"]), "http") > 0)
		{ //!!! check if SERVER_PROTOCOL always set and 'http' when http request. A rumoured case that empty when https on some specific server/machine.
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
		Request argument value or NULL if argument not defined
	**/
	public function arg($name)
	{
		if (isset($this->args[$name]))
		{
			return $this->args[$name];
		}
		else
		{
			return null;
		}
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
	@returns object
		Currently active application object
	**/
	public function get_application()
	{
		if (!is_object($this->application))
		{
			if (in_array($this->class, self::$application_classes)) //!!! tmp solution
			{
				if (isset($this->args["id"]) and is_oid($this->args["id"]))
				{
					$application = new object($this->args["id"]);
					aw_session_set("aw_request_application_object_oid", $application->id());
				}
				elseif ("admin_if" === $this->class)
				{
					$core = new core();
					$id = admin_if::find_admin_if_id();
					$application = new object($id);
					aw_session_set("aw_request_application_object_oid", $application->id());
				}
				elseif (aw_ini_isset("class_lut." . $this->class))
				{
					$clid = aw_ini_get("class_lut." . $this->class);
					$application = obj(null, array(), $clid);
				}
				else
				{
					$application = new object(); //!!! mis on default?
				}
			}
			elseif (is_oid(aw_global_get("aw_request_application_object_oid")))
			{
				$application = new object(aw_global_get("aw_request_application_object_oid"));
			}
			else
			{
				$application = new object(); //!!! mis on default?
			}

			$this->application = $application;
		}

		return $this->application;
	}

	/**
	@attrib api=1 params=pos
	@returns boolean
	**/
	public function is_fastcall()
	{
		return $this->is_fastcall;
	}

	/** Current request protocol
	@attrib api=1 params=pos
	@returns object
	**/
	public function protocol()
	{
		return $this->protocol;
	}

	public function type() // DEPRECATED
	{ return get_class($this) === "aw_http_request" ? "http" : "";	}

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

	protected function _autoload()
	{
		// parse arguments
		$this->parse_args();
	}

	protected function parse_args()
	{ //!!! "restore previous application" on vaja ka teaostada, sest n2iteks kui k2iakse teises applicationis ja minnakse tagasi eelmisest avatud allobjektile, on application vale
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
