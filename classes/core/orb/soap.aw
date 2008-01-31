<?php
/*
@classinfo  maintainer=kristo
*/

class soap extends aw_template
{
	function soap()
	{
		$this->init();
	}

	function do_request($arr)
	{
		require_once(aw_ini_get("basedir")."/addons/soapclient.aw");
		$soapclient = new C_SoapClient($arr["server"]);
		$soapclient->namespace = $arr["class"];
		$soapclient->debug = 0;

		$return = $soapclient->call($arr["action"] , $arr["params"]);
		return $return;
	}
		

	function decode_request()
	{
		
	}
}

?>
