<?php
include("const.aw");
include("admin_header.".aw_ini_get("ext"));
classload("aw_template");
$tt = new aw_template;
$tt->init("");

if (!$tt->prog_acl("view", PRG_MENUEDIT))
{
	$tt->prog_acl_error("view", PRG_MENUEDIT);
}

// let's try to make the conversion less painful for users. This is a really-really bad
// solution (to put that stuff here), but it will be removed in a few days
$tableinfo = $tt->db_get_table("periods");
if (!$tableinfo["fields"]["obj_id"])
{
	$url = $tt->mk_my_orb("convert_periods",array(),"converters");
	header("Location: $url");
	exit;
};
?>
<html>
<head>
<title><?php echo $uid,"@AutomatWeb";?></title>
<link REL="icon" HREF="{VAR:baseurl}/automatweb/images/icons/favicon.ico" TYPE="image/x-icon">
</head>
<frameset cols="30%,*" frameborder="yes" framespacing="1">
  <frame name="menuFrame" src="orb.aw?class=admin_folders&action=folders&parent=<?php echo $parent; ?>" marginheight="0" marginwidth="0" scrolling="auto">
  <frame name="list" src="orb.aw?class=admin_menus&action=right_frame&parent=<?php echo $parent; ?>&period=<?php echo $period; ?>" marginheight="0" marginwidth="0" scrolling="auto">
</frameset>
</html>
