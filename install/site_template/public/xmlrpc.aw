<?php
include("const.aw");
include("site_header.aw");

classload("orb");
$o = new new_orb;
echo $o->handle_rpc_call(array(
	"method" => "xmlrpc"
));
?>