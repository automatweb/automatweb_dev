<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css">
<script src="{VAR:baseurl}/automatweb/js/ua.js"></script>
<script src="{VAR:baseurl}/automatweb/js/ftiens4.js"></script>
<script type="text/javascript">
USETEXTLINKS = 1
ICONPATH = '{VAR:baseurl}/automatweb/images/'
PERSERVESTATE = 0
LINKTARGET = 'list'
SHOWNODE = ''
HIGHLIGHT = 1;
HIGHLIGHT_COLOR = '#0000FF';
HIGHLIGHT_BG = '#EEEEEE';

pr_{VAR:root} = gFld("<b>AutomatWeb</b>", "{VAR:rooturl}", "{VAR:baseurl}/automatweb/images/aw_ikoon.gif")
<!-- SUB: TREE -->
	pr_{VAR:id} = insFld(pr_{VAR:parent}, gFld("{VAR:name}", "{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: TREE -->
<!-- SUB: DOC -->
	pr_{VAR:id} = insDoc(pr_{VAR:parent}, gLnk("R", "{VAR:name}","{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: DOC -->

foldersTree = pr_{VAR:root};
</script>

</head>
<body bgcolor="#eeeeee" topmargin=0 marginheight=0>
<table border=0 width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left" class="yah">&nbsp;{VAR:uid} @ {VAR:date}</td>
	</tr>
	<!-- SUB: has_toolbar -->
	<tr>
		<form action='orb.{VAR:ext}' method='get' name='pform'>
		<td>{VAR:toolbar}</td>
		</form>
	</tr>
	<!-- END SUB: has_toolbar -->
</table>

<!-- Build the browser's objects and display default view of the
     tree. -->
<script>initializeDocument()</script>

</html>
