<?php

class protocol_finder
{
	function inst($url)
	{
		$data = parse_url($url);
		switch($data["scheme"])
		{
			case "":
			case "http":
				return get_instance("protocols/file/http");

			case "ftp":
				return get_instance("protocols/file/ftp");
		}
		error::raise(array(
			"id" => ERR_NO_PROTOCOL,
			"msg" => sprintf(t("protocol_fnider::inst(%s): no protocol implemented for url"), $url)
		));
	}
}
?>
