<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset={VAR:charset}">
<title>{VAR:title_action}{VAR:uid}@AutomatWeb</title>
<link REL="icon" HREF="{VAR:baseurl}/automatweb/images/icons/favicon.ico" TYPE="image/x-icon">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css">
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/mm.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>
<script type="text/javascript">
<!--
function activate_window()
{
	if (window != window.top)
	{
		if (top.document.getElementById('status') == '[object HTMLDivElement]') //et siis kontrollime kas aken on desktopis
		{
			top.winMakeActive2((window.name.indexOf('frei') == 1) ? window.name : parent.window.name);
		}
	}
}
// -->
</script>
<script type="text/javascript">
<!--
function remote(toolbar,width,height,file)
{
	self.name = "root";
	var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=0, "+
	"menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	openwindow = window.open(file,"remote",wprops);
}

function box2(caption,url)
{
	var answer=confirm(caption)
	if (answer)
	{
		window.location=url
	}
}

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

// -->
</script>
</head>
<body onmousedown="activate_window();" bgcolor='#FFFFFF' link='#0000ff' vlink='#0000ff' onLoad="create_objects(); check_generic_loader();">
<table border="0" width="100%" cellspacing="0" cellpadding="2">
<tr>
<td align="left" class="yah">&nbsp;
{VAR:site_title}
</td>
</tr>
</table>
<table border="0" cellpadding="0" cellspacing="0">
{VAR:content}
</table>
<div align="center">
<font face="Verdana,Arial,Helvetica,sans-serif" size="-2" color="#8AABBE">AutomatWeb&reg;<br><br></font>
</div>
</center>
</body>
</html>
