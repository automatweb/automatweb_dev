<?php
// $Header: /home/cvs/automatweb_dev/vcl/Attic/html_frameset.aw,v 2.0 2001/06/27 19:35:54 duke Exp $

/*
$ht = new html_frameset(array(
		"rows" => "70%,30%",
		"cols" => "*",
		"frames" => array(
				"test1" => "test.html",
				"test2" => "test2.html",
			)
		));
print $ht->generate();

*/

class html_frameset
{
	function html_frameset($args = array())
	{
		extract($args);
		$retval = "";
		$retval .= sprintf("<frameset rows='%s' cols='%s'>\n",$rows,$cols);
		foreach($frames as $name => $url)
		{
			$retval .= sprintf("\t<frame name='%s' src='%s'>\n",$name,$url);
		};
		$retval .= sprintf("</frameset>");
		$this->retval = $retval;
	}

	function generate()
	{
		return $this->retval;
	}
};
?>
