<?php 
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_MENUEDIT))
{
	$tt->prog_acl_error("view", PRG_MENUEDIT);
}
?>
<html>
<head>
<title><?php echo $uid,"@AutomatWeb";?></title>
</head>

<frameset cols="30%,*" frameborder="yes" framespacing=1>
  <frame name="menuFrame" src="menuedit.aw?type=folders" MARGINHEIGHT=0 MARGINWIDTH=0 scrolling=yes>
  <frame name="list" src="menuedit_right.aw" MARGINHEIGHT=0 MARGINWIDTH=0 scrolling=auto>
</frameset>

</html>
