<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset={VAR:charset}"> 
<title>{VAR:title_action}{VAR:uid}@AutomatWeb</title>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/awplanner.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<!-- SUB: custom_css -->
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/orb.aw?class=css&action=get_user_css&fastcall=1&name={VAR:custom}.css">
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

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v3.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

// -->
</script>
<script language="Javascript" src="js/cbobjects.js">
</script>
<script language="Javascript">
function generic_loader()
{
	// don't do anything. screw you.
}

function check_generic_loader()
{
	if (generic_loader)
	{
		generic_loader();
	}
};
</script>
</head>
<!-- kas see create_objects on vajalik? -->
<body bgcolor='#eeeeee' link='#0000ff' vlink='#0000ff' onLoad="create_objects(); check_generic_loader()" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">


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
