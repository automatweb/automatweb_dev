<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_CODESTAT))
{
	$tt->prog_acl_error("view", PRG_CODESTAT);
}
?>
<html>
<head>
<title>stats</title>
</head>
<body bgcolor="#FFFFFF">
<table width=90% border=0 cellspacing=10>
<tr align="center" ><td rowspan=3>
<pre>
<?php
echo join("",file("/www/automatweb/public/scripts/stats"));
?>
</pre>
<td><img src="orb.aw?class=graph&action=show&id=5158">
<tr align="center"><td><img src="orb.aw?class=graph&action=show&id=5159">
<tr align="center"><td><img src="orb.aw?class=graph&action=show&id=5160">
</table>
</body>
</html>
