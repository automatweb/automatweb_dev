<?php
class pspell
{
	function pspell($arr)
	{
		if(!$this->is_available())
		{
			error::raise(array(
				"id" => "ERR_PSPELL_NA",
				"msg" => t("pspell::pspell(): could not find pspell extension")
			));
		}
	}

	function init($arr)
	{
		if(!$arr["lang"])
		{
			$arr["lang"] = aw_ini_get("pspell.default_lang");
		}
		$this->lang = $arr["lang"];
		if(!($this->driver = pspell_new($this->lang)))
		{
			error::raise(array(
				"id" => "ERR_PSPELL_INIT_FAIL",
				"msg" => t("pspell::init(): could not init pspell dictionary")
			));
		}
	}

	function is_available()
	{
		return extension_loaded("pspell");
	}
	
	function check($str)
	{
		return pspell_check($this->driver, $str);
	}

	function suggest($str)
	{
		return pspell_suggest($this->driver, $str);
	}

	function check_str($str)
	{
		$ret = array();
		$this->vals = explode(" ", $str);
		foreach($this->vals as $val)
		{
			if(!$this->check($val))
			{
				$ret[$val] = $this->suggest($val);
			}
		}
		return $ret;
	}

	function load_js_spelling($arr)
	{
		$this->print_text_vars($arr["str"]);
		foreach($arr["str"] as $idx => $str)
		{
			$ret = $this->check_str($str);
			echo "words[$idx] = [];\n";
			echo "suggs[$idx] = [];\n";
			foreach($this->vals as $key => $val)
			{
				echo "words[$idx][$key] = '".escape_quote($val)."';\n";
			}
			foreach($ret as $key => $val)
			{
				echo "suggs[$idx][$key] = [";
				foreach($val as $key2 => $val2)
				{
					if($val2)
					{
						echo "'".escape_quote($val)."'";
						if($key2 + 1 < count($val))
						{
							echo ", ";
						}
					}
				}
				echo "];\n";
			}
		}
	}

	function print_text_vars($strs)
	{
		foreach($strs as $key => $val)
		{
			echo "textinputs[$key] = decodeURIComponent(\"".$val."\");\n";
		}
	}

	function escape_quote($str)
	{
		return preg_replace ( "/'/", "\\'", $str);
	}
}
?>