<?php
// $Header: /home/cvs/automatweb_dev/classes/core/util/minify_js_and_css.aw,v 1.3 2007/11/10 16:11:33 hannes Exp $
// minify_js_and_css.aw - Paki css ja javascript 
/*

@classinfo syslog_type=ST_MINIFY_JS_AND_CSS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class minify_js_and_css extends class_base
{
	function minify_js_and_css()
	{
		$this->init(array(
			"tpldir" => "core/util/minify_js_and_css",
			"clid" => CL_MINIFY_JS_AND_CSS
		));
	}
	
	function compress_js($script, $use_javascriptpacker=false)
	{
		// JavaScriptPacker seems to create lag. opera shows white page for a sec some times
		// so dont use it until there's less javascript in aw admin
		if ($use_javascriptpacker)
		{
			require_once("../addons/packer.php-1.0/class.JavaScriptPacker.php");
		
			$packer = new JavaScriptPacker($script, 'Normal', true, false);
			$packed = $packer->pack();
		}
		else
		{
			$packed = str_replace(array("\r\n", "\r", "\n", '  ', '    ', '    '), '', $script);
			$packed = $this->remove_js_comments($packed);
		}
		
		return $packed;
	}
	
	
	function remove_js_comments($str)
	{
		$res = '';
		$maybe_regex = true;
		$i=0;
		$current_char = '';
		
		while ($i+1<strlen($str))
		{
				if ($maybe_regex && $str{$i}=='/' && $str{$i+1}!='/' && $str{$i+1}!='*')
				{//regex detected
					if (strlen($res) && $res{strlen($res)-1} === '/')
					{
						$res .= ' ';
					}
					do
					{
						if ($str{$i} == '\\')
						{
							$res .= $str{$i++};
						} elseif ($str{$i} == '[')
						{
							do
							{
								if ($str{$i} == '\\')
								{
									$res .= $str{$i++};
								}
								$res .= $str{$i++};
							} while ($i<strlen($str) && $str{$i}!=']');
						}
						$res .= $str{$i++};
					} while ($i<strlen($str) && $str{$i}!='/');
					$res .= $str{$i++};
					$maybe_regex = false;
					continue;
				} elseif ($str{$i}=='"' || $str{$i}=="'")
				{
					//quoted string detected
					$quote = $str{$i};
					do
					{
						if ($str{$i} == '\\')
						{
							$res .= $str{$i++};
						}
						$res .= $str{$i++};
					} while ($i<strlen($str) && $str{$i}!=$quote);
					$res .= $str{$i++};
					continue;
				} elseif ($str{$i}.$str{$i+1}=='/*')
				{
					//multi-line comment detected
					$i+=3;
					while ($i<strlen($str) && $str{$i-1}.$str{$i}!='*/')
					{
						$i++;
					}
					if ($current_char == "\n")
					{
						$str{$i} = "\n";
					}
					else 
					{
						$str{$i} = ' ';
					}
				} elseif ($str{$i}.$str{$i+1}=='//')
				{
					//single-line comment detected
					$i+=2;
					while ($i<strlen($str) && $str{$i}!="\n")
					{
						$i++;
					}
				}
				
				$LF_needed = false;
				if (preg_match('/[\n\r\t ]/', $str{$i}))
				{
					if (strlen($res) && preg_match('/[\n ]/', $res{strlen($res)-1}))
					{
						if ($res{strlen($res)-1} == "\n") $LF_needed = true;
						$res = substr($res, 0, -1);
					}
					while ($i+1<strlen($str) && preg_match('/[\n\r\t ]/', $str{$i+1}))
					{
						if (!$LF_needed && preg_match('/[\n\r]/', $str{$i}))
						{
							$LF_needed = true;
						}
						$i++;
					}
				}
				
				if (strlen($str) <= $i+1)
				{
					break;
				}
		
				$current_char = $str{$i};
		
				if ($LF_needed)
				{
					$current_char = "\n";
				} elseif ($current_char == "\t")
				{
					$current_char = " ";
				} elseif ($current_char == "\r")
				{
					$current_char = "\n";
				}
		
				// detect unnecessary white spaces
				if ($current_char == " ")
				{
				if (strlen($res) && (preg_match('/^[^(){}[\]=+\-*\/%&|!><?:~^,;"\']{2}$/', $res{strlen($res)-1}.$str{$i+1}) ||
											preg_match('/^(\+\+)|(--)$/', $res{strlen($res)-1}.$str{$i+1}) // for example i+ ++j;
				))
				{
					$res .= $current_char;
				}
			} elseif ($current_char == "\n")
			{
				if (strlen($res) && (	preg_match('/^[^({[=+\-*%&|!><?:~^,;\/][^)}\]=+\-*%&|><?:,;\/]$/', $res{strlen($res)-1}.$str{$i+1}) ||
											(strlen($res)>1 && preg_match('/^(\+\+)|(--)$/', $res{strlen($res)-2}.$res{strlen($res)-1})) ||
											preg_match('/^(\+\+)|(--)$/', $current_char.$str{$i+1}) ||
											preg_match('/^(\+\+)|(--)$/', $res{strlen($res)-1}.$str{$i+1})// || // for example i+ ++j;
				)) 
				{
					$res .= $current_char;
				}
			} else $res .= $current_char;
		
			// if the next charachter be a slash, detects if it is a divide operator or start of a regex
			if (preg_match('/[({[=+\-*\/%&|!><?:~^,;]/', $current_char))
			{
				$maybe_regex = true;
			}
			elseif (!preg_match('/[\n ]/', $current_char))
			{
				$maybe_regex = false;
			}
		
			$i++;
		}
		if ($i<strlen($str) && preg_match('/[^\n\r\t ]/', $str{$i}))
		{
			$res .= $str{$i};
		}
		return $res;
	} 
	
	
	function compress_css($script)
	{
		// remove comments
		$packed = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $script);
		// remove tabs, spaces, newlines, etc.
		$packed = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $packed);
		
		return $packed;
	}
	
		/** outputs file
		
		@attrib name=get_js params=name nologin="1" default="0" is_public="1"
		
		@param name required
		
		@returns
		
		@comment

	**/
	function get_js($arr)
	{
		$s_salt = "this_is_a_salty_string_";
		ob_start ("ob_gzhandler");
		header ("Content-type: text/javascript; charset: UTF-8");
		header("Expires: ".gmdate("D, d M Y H:i:s", time()+315360000)." GMT");
		header("Cache-Control: max-age=315360000");
		
		$cache = get_instance('cache');
		echo $cache->file_get($s_salt.$arr["name"]);
		die();
	}
	
			/** outputs file
		
		@attrib name=get_css params=name nologin="1" default="0" is_public="1"
		
		@param name required
		
		@returns
		
		@comment

	**/
	function get_css($arr)
	{
		$s_salt = "this_is_a_salty_string_";
		ob_start ("ob_gzhandler");
		header ("content-type: text/css; charset: UTF-8");
		header("Expires: ".gmdate("D, d M Y H:i:s", time()+315360000)." GMT");
		header("Cache-Control: max-age=315360000");
		
		$cache = get_instance('cache');
		echo $cache->file_get($s_salt.$arr["name"]);
		die();
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
