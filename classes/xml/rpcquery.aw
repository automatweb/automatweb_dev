<?php
// $Header: /home/cvs/automatweb_dev/classes/xml/Attic/rpcquery.aw,v 1.1 2001/11/20 13:25:40 kristo Exp $
// Typical XML-RPC request
// <xml version="1.0">
// <methodCall>
//        <methodName>exampled.getStateName</methodName>
//        <params>
//                <param>
//                        <value><i4>41</i4></value>
//                </param>
//        </params>
//</methodCall>

// takes 2 arguments, name of the method and the parameters to it
function xml_create_struct($args = array())
{
	extract($args);
	


}
?>
