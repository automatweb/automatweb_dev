<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/maitenance.aw,v 1.1 2003/08/01 14:15:43 axel Exp $
// maitenance.aw - Saidi hooldus 
/*

@classinfo syslog_type=ST_MAITENANCE relatiomgr=yes

@default table=objects
@default group=general

*/

class maitenance extends class_base
{
	function maitenance()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "maitenance",
			"clid" => CL_MAITENANCE
		));
	}

	function cache_clear($args)
	{
		echo "<br />
		<input type='button' value='clear cache' 
		onclick=\"document.location='".$this->mk_my_orb('cache_clear', array('clear' => '1'))."'\"><br />";
		
		$dir = aw_ini_get("cache.page_cache").'/';	
		$files = array();
		$cnt = 0;
		if ($handle = opendir($dir))
		{
			while (false !== ($file = readdir($handle)))
			{ 
				if ($file != "." && $file != "..")
				{ 
					$files[] = $file; 
				} 
			}
			closedir($handle); 
		}		
		
		if (isset($args['clear']))
		{
			echo 'about to delete '.count($files).' files<br />';
			
			foreach($files as $val)
			{
				unlink($dir.$val);
				$cnt++;
				if ($cnt > 100)
				{
					echo " #";
					flush(); 
					$cnt = 0;
				}
			}
			echo '<br />files deleted!!<br />';
			
		}
		else
		{
			echo 'total:'. count($files).' files';
			if (isset($args['list']))
			{
				arr($files);
			}

		}
		die();
	}
	
	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
