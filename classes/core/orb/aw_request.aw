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
	@throws awex_request_na
		When uri is not available in current request type
	**/
	public function get_uri()
	{
		if (!isset($this->args[$name]))
		{
			throw new awex_request_na("Argument not available");
		}

		return $this->args;
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
	@returns array
		Request arguments. argument_name => argument_value pairs
	**/
	public function get_args()
	{
		return $this->args;
	}

	/**
	@attrib api=1 params=pos
	@param args required type=array
		New arguments to set. Associative array of argument name/value pairs.
	@returns void
	@comment
		Sets arguments array. Old arguments are all discarded.
	@throws awex_request_param
		when $args parameter is not valid
	**/
	public function set_args($args)
	{
		if (!is_array($args))
		{
			throw new awex_request_param("Parameter must be an array");
		}

		$this->args = $args;
		$this->parse_args();
	}

	/**
	@attrib api=1 params=pos
	@param args required type=array
		New arguments to set. Associative array of argument name/value pairs.
	@param overwrite optional type=boolean default=true
		If false, new arguments specified in $args will not overwrite already defined ones.
	@returns void
	@throws awex_request_param
		when $args parameter is not valid
	**/
	public function add_args($args, $overwrite = true)
	{
		if (!is_array($args))
		{
			throw new awex_request_param("Parameter must be an array");
		}

		if ($overwrite)
		{
			$this->args = $args + $this->args;
		}
		else
		{
			$this->args = $this->args + $args;
		}

		$this->parse_args();
	}

	/**
	@attrib api=1 params=pos
	@param name required type=string
		Request argument name to unset.
	@returns void
	@comment
		Unsets a request argument.
	**/
	public function unset_arg($name)
	{
		if (!isset($this->args[$name]))
		{
			throw new awex_request("Argument '" . $name . "' doesn't exist");
		}

		unset($this->args[$name]);
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
}

/** Generic aw_request class unexpected condition indicator **/
class awex_request extends aw_exception {}

/** Requested entity not available **/
class awex_request_na extends awex_request {}

/** Invalid parameter **/
class awex_request_param extends awex_request {}

?>
