


<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset={VAR:charset}">
<title>{VAR:title_action}{VAR:uid}@AutomatWeb</title>
<link REL="icon" HREF="{VAR:baseurl}/automatweb/images/icons/favicon.ico" TYPE="image/x-icon">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css" />


<!-- SUB: aw_styles -->
<!-- END SUB: aw_styles -->

<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/browserdetect.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/ajax.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/autosuggest.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/CalendarPopup.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/popup_menu.js"></script>


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

function generic_unloader()
{
	// don't do anything. screw you.
}

function check_generic_unloader()
{
	if (generic_unloader)
	{
		generic_unloader();
	}
};

// -->
</script>
</head>
<body link="#0000ff" vlink="#0000ff" onBeforeUnLoad="check_generic_unloader();" onLoad="create_objects(); check_generic_loader();">
	<!-- SUB: LANG_STRING -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td class="aw04yah">
<!-- SUB: YAH -->
{VAR:site_title}
<!-- END SUB: YAH -->
	</td>
	<td nowrap align="right" class="aw04yah">{VAR:header_text}<span class="mlang">[{VAR:lang_string}]</span>&nbsp;&nbsp;{VAR:ui_lang}&nbsp;&nbsp;</td>
</tr>
</table>
	<!-- END SUB: LANG_STRING -->

{VAR:content}

</body>
</html>
