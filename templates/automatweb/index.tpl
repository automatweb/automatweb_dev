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
<body bgcolor='#FFFFFF' link='#0000ff' vlink='#0000ff' onLoad="create_objects(); check_generic_loader();" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<style type="text/css">

div.menuBar,
div.menuBar a.menuButton,
div.menu,
div.menu a.menuItem {
  font-family: "MS Sans Serif", Arial, sans-serif;
  font-size: 11px;
  font-style: normal;
  font-weight: normal;
  color: #000000;
}

div.menuBar {
  background-color: transparent;
  text-align: left;
}

div.menuBar a.menuButton {
  background-color: transparent;
  color: #000000;
  cursor: default;
  left: 0px;
  margin: 1px;
  text-decoration: none;
  top: 0px;
  z-index: 100;
}

div.menuBar a.menuButton:hover {
  background-color: transparent;
  color: #000000;
}

div.menuBar a.menuButtonActive,
div.menuBar a.menuButtonActive:hover {
  background-color: #a0a0a0;
  color: #ffffff;
  left: 1px;
  top: 1px;
}

div.menu {
  background-color: #d0d0d0;
  border: 2px solid;
  border-color: #f0f0f0 #909090 #909090 #f0f0f0;
  left: 0px;
  padding: 0px 1px 1px 0px;
  position: absolute;
  top: 0px;
  visibility: hidden;
  z-index: 101;
}

div.menu a.menuItem {
  color: #000000;
  cursor: default;
  display: block;
  padding: 3px 1em;
  text-decoration: none;
  white-space: nowrap;
}

div.menu a.menuItem:hover, div.menu a.menuItemHighlight {
  background-color: #000080;
  color: #ffffff;
}

div.menu a.menuItem span.menuItemText {}

div.menu a.menuItem span.menuItemArrow {
  margin-right: -.75em;
}

div.menu div.menuItemSep {
  border-top: 1px solid #909090;
  border-bottom: 1px solid #f0f0f0;
  margin: 4px 2px;
}

</style>

<table border=0 width="100%" cellspacing="0" cellpadding="2">
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
