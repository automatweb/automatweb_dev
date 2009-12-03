<?php
/*
@classinfo  maintainer=kristo
*/

define("OBJ_SQL_UNIQUE", 1);
define("OBJ_SQL_COUNT", 2);
define("OBJ_SQL_MAX", 3);
define("OBJ_SQL_MIN", 4);

class obj_sql_func
{
	function obj_sql_func($func, $name, $params = null)
	{
		$this->sql_func = $func;
		$this->params = $params;
		$this->name = $name;
	}

	function __toString()
	{
		return "obj_sql_func(".$this->sql_func.",".$this->name.",".join(safe_array($this->params)).")";
	}
}