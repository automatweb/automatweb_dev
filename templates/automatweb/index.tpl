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
<body bgcolor='#F7F7F7' link='#0000ff' vlink='#0000ff' onLoad="create_objects()" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">


<table border=0 width="100%" cellspacing="0" cellpadding="2">
<tr>
<td align="left" class="yah">&nbsp;
{VAR:site_title}
</td>
</tr>
</table>
<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
<table border="0" cellpadding="0" cellspacing="0">
{VAR:content}
</table>
<div align="center">
<font face="Verdana,Arial,Helvetica,sans-serif" size="-2" color="#8AABBE">AutomatWeb&trade;<br><br></font>
</div>
</center>
</body>
</html>
<!-- 
{VAR:menu} {VAR:rmenu}
<strong>{VAR:qcount}</strong> päringut, <strong>{VAR:timers}</strong><br>
-->
