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
<title>Menuedti</title>
</head>

<frameset rows="30%,70%" frameborder="yes" framespacing=1>
  <frame name="menus" src="<?php if ($parent) echo "menuedit.aw?parent=$parent&type=menus&period=$period"; else echo "blank.html"; ?>" scrolling=auto MARGINHEIGHT=0 MARGINWIDTH=0 >
  <frame name="objects" src="<?php if ($parent) echo "menuedit.aw?parent=$parent&type=objects&period=$period"; else echo "blank.html"; ?>" scrolling=auto MARGINHEIGHT=0 MARGINWIDTH=0 >
</frameset>

</html>
