<?php

include("const.aw");
classload("replicator","bugtrack");

$bt=new bugtrack();

$hostkey=$bt->sitekeys[$baseurl]?
	$bt->sitekeys[$baseurl]:"unsetsitekey57005-49374";

$rh=new replicator_host($hostkey,"bugreplicate");

$req=$rh->parse_request($HTTP_GET_VARS);

if (!$req["nop"])
{
	if (!($reply["error"]=$req["error"]))
	{
		switch ($req["func"])
		{
			case "testing":
				$reply["servertime"]=time();
				$reply["servername"]=$SERVER_NAME;
				$reply["baseurl"]=$baseurl;
				$reply["hkey"]=$rh->key;// see pole mingi turvaauk, siia nigunii siis ei tule, kui õige sitekey teada pole
				$reply["tid"]=$req["tid"];
				$reply["hash"]=$req["hash"];
				break;

			case "new_bug":
				$newid=$bt->q_getid();
				$req["id"]=$newid;
				if (!$bt->q_insert($req))
					$reply["error"]="insert failed";

				$reply["id"]=$newid;
				break;
			
			case "delete_bug":
				if (!$bt->q_delete($req))
					$reply["error"]="delete failed";
				break;

			case "update_bug":
				if (!$bt->q_update($req))
					$reply["error"]="update failed";
				break;
				
			default:
				$reply["error"]="Unknown function ".$req["func"];
				break;
		}
	}

	$rh->respond($reply);
}
?>