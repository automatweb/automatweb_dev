<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_xml.aw,v 2.2 2002/06/10 15:50:52 kristo Exp $
// AW_XML parser
class aw_xml {

	////
	// !Konstrueerib XML bloki
	// 2 argumenti:
	// type(string) - mis tüüpi blokk genereerida
	// source(mixed) - php andmestruktuur
	function aw_xml($args = array())
	{
		extract($args);
	}

	function parse($args = array())
	{
		switch($args["type"])
		{
			case "rpcquery":
				include("xml/rpcquery.aw");
				break;

			case "rdf":
				include("xml/rdf.aw");
				break;
			
			default:
				include("xml/empty.aw");
				break;
		};
		$t = new aw_xml_parser($args["source"]);
	}
};
?>
