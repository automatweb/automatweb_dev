<?php 
// $Header: /home/cvs/automatweb_dev/automatweb/Attic/sorry.aw,v 2.1 2001/05/31 18:19:16 duke Exp $
header ("HTTP/1.1 404 Not Found");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML><HEAD>
<TITLE>404 Not Found</TITLE>
</HEAD><BODY>
<H1>Not Found</H1>
The requested URL 
<?php
echo $REQUEST_URI," " ;
?>was not found on this server.<P>
<HR>
<ADDRESS>Apache/1.3.14 Server at <?php 
include("const.aw");
echo $HTTP_HOST;
?> Port 80</ADDRESS>
</BODY></HTML>
