<?php
header('Content-type: text/css');
$offset = 3600 * 24 * 30;
$expire = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
header($expire);
?>

body { background: white; font-size: 100%;}
#content { width: auto;margin: 0 5%; padding: 0;border: 0; float: none !important; color: black; background: transparent none; }
div#content {margin-left: 10%; padding-top: 1em; }
#header, #footer {width: 100%; border-collapse: collapse;}
#header td {padding: 0;}
.r {text-align: right;}
.l {text-align: left;}
div#mast {margin-bottom: -8px;}
div#mast img {vertical-align: bottom;}

a:link, a:visited { color: #520; background: transparent; font-weight: bold; text-decoration: underline; }
#content a:link:after, #content a:visited:after {content: " (" attr(href) ") ";font-size: 90%;}