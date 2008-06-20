<?php
/*
@classinfo syslog_type=ST_SHORTCUT_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=hannes

@default table=objects
@default group=general

*/

class shortcut_manager extends class_base
{
	function shortcut_manager()
	{
		$this->init(array(
			"tpldir" => "admin/shortcut_manager",
			"clid" => CL_SHORTCUT_MANAGER
		));
	}
	
	/**
		@attrib name=parse_shorcuts_from_xml
	**/
	function parse_shorcuts_from_xml($arr)
	{
		$file = core::get_file(array("file"=>aw_ini_get("basedir")."/xml/shortcuts.xml"));

		$doc = new DOMDocument();
		$doc->loadXML( $file );
		
		$o_items = $doc->getElementsByTagName( "class" );
		$j=0;
		
		$out = "aw_shortcut_db = new Array();\n";
		$out .= "//// start xml/shortcuts.xml\n";
		foreach( $o_items as $class )
		{
			//$classes = $item->getElementsByTagName( "shortcut" );
			$s_class = $class->getAttribute("name");
			$out .= 'aw_shortcut_db["'.$s_class.'"] = new Array();';
			foreach($class->getElementsByTagName( "shortcut" ) as $shortcut)
			{
				foreach($shortcut->getElementsByTagName( "function" ) as $function)
				{
					$s_function = $function->getAttribute("name");
					foreach($function->getElementsByTagName( "arguments" ) as $arguments)
					{
						foreach($arguments->getElementsByTagName( "required" ) as $required)
						{
							$s_shortcut = $required->getAttribute("value");
							$out .= 'aw_shortcut_db["'.$s_class.'"]["'.$s_function.'"] = "'.$s_shortcut.'";'."<br>";
						}
					}
				}
			}
		}
		$out .= "\n//// end xml/shortcuts.xml\n";
		ob_start ("ob_gzhandler");
		header ("Content-type: text/javascript; charset: UTF-8");
		die($out);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			//$this->db_query("CREATE TABLE aw_shortcut_manager(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}

?>
