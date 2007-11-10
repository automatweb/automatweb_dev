<?php
// $Header: /home/cvs/automatweb_dev/classes/core/util/minify_js_and_css.aw,v 1.1 2007/11/10 12:56:18 hannes Exp $
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
	
	function compress_js($script)
	{
		require_once("../addons/packer.php-1.0/class.JavaScriptPacker.php");
		
		$packer = new JavaScriptPacker($script, 'Normal', true, false);
		$packed = $packer->pack();
		
		return $packed;
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
		header ("cache-control: must-revalidate");
		$offset = 60 * 60;
		$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
		header ($expire);
		
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
		header ("Content-type: text/css; charset: UTF-8");
		header ("cache-control: must-revalidate");
		$offset = 60 * 60;
		$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
		header ($expire);
		
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
