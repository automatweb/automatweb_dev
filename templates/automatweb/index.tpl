<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset={VAR:charset}"> 
<title>{VAR:title} / AutomatWeb</title>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<!-- SUB: custom_css -->
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/get_css.aw?name={VAR:custom}.css">
<!-- END SUB: custom_css -->
<script language="Javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
<script language="Javascript">
<!--
function remote(toolbar,width,height,file) {
	self.name = "root";
	var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=0, "+
	"menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	openwindow = window.open(file,"remote",wprops);
}

function box2(caption,url){
var answer=confirm(caption)
if (answer)
window.location=url
}
// -->
</script>
<script language="Javascript" src="js/cbobjects.js">
</script>
</head>
<!-- kas see create_objects on vajalik? -->
<body bgcolor='#ffffff' link='#0000ff' vlink='#0000ff' onLoad="create_objects()" leftmargin="10" topmargin="10" marginwidth="10" marginheight="10">


<table border=0 width="780" cellspacing="0" cellpadding="2">
<tr>
<td align="left" class="yah">&nbsp;
{VAR:site_title}
</td>
</tr>
</table>

<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="2" BORDER=0 ALT=""><br>




<table border=0 width="100%" cellspacing=0 cellpadding=0 bgcolor="#FFFFFF">
<tr>



<td valign=top width=100% bgcolor="#FFFFFF">
{VAR:content}


</td>
</tr>
</table>
<div align="center">
<font face="Verdana,Arial,Helvetica,sans-serif" size="-2">(C) Struktuur Meedia 1999 - 2001. All Rights Reserved</font>
</div>
</center>
</body>
</html>
<!-- 
{VAR:menu} {VAR:rmenu}
<strong>{VAR:qcount}</strong> päringut, <strong>{VAR:timers}</strong><br>
-->
