<?php
/*
	$Header: /home/cvs/automatweb_dev/classes/Attic/construct.aw,v 2.1 2002/06/10 15:57:30 kristo Exp $
	construct.aw - The Construct
	This is what we call a construct, we can load everything we need here, objects, data, weapons, etc
*/

class construct {
	function construct()
	{
		$this->data = array();
		$this->count = 0;
		$this->do = array();
	}
	
	function load($clid = 0, $key = 0, $args = array())
	{
		/*
		print "loading link $clid data $key into construct!<br>";
		*/
		$this->data[$clid][$key] = $args;
		$this->do[$args["oid"]] = 1;
		$this->count++;
	}


	function dump()
	{
		/*
		print "count = <b>" . $this->count . "</b> results";
		print sizeof($this->do) . " different objects<br>";
		*/
		/*
		print "<pre>";
		print_r($this->data);
		print "</pre>";
		*/
	}
};
?>
