<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<script src="{VAR:baseurl}/automatweb/js/ua.js"></script>
<script src="{VAR:baseurl}/automatweb/js/mm.js"></script>
<script src="{VAR:baseurl}/automatweb/js/ftiens_new.js"></script>
<script language="javascript">

USETEXTLINKS = 1
pr_{VAR:root} = gFld("<b>{VAR:LC_MANAGER_TREE_ROOT}</b>", "{VAR:rooturl}","{VAR:baseurl}/automatweb/images/aw_ikoon.gif")

<!-- SUB: TREE -->
	pr_{VAR:id} = insFld(pr_{VAR:parent}, gFld("{VAR:name}", "{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: TREE -->
<!-- SUB: DOC -->
	pr_{VAR:id} = insDoc(pr_{VAR:parent}, gLnk("{VAR:name}", "{VAR:name}","{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: DOC -->
	
foldersTree = pr_{VAR:root};
</script>

</head>
<body bgcolor="#eeeeee" topmargin=0 marginheight=0>

	<table border=0 width="100%" cellspacing="0" cellpadding="2">
		<tr>
		<td align="left" class="yah">&nbsp;{VAR:uid} @ Sylvester</td>
		</tr>
	</table>
<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>

		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">
					
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
							<form action='orb.{VAR:ext}' method='get' name='pfft'>
                                  <tr>
                                                <td class="tableinside" valign="middle"><a href='javascript:document.pfft.submit()' onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','/automatweb/images/blue/awicons/refresh_over.gif',1)"><img name='refresh' alt='{VAR:LC_MANAGER_HINT_REFRESH}' title='{VAR:LC_MANAGER_HINT_REFRESH}' border='0' SRC='{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif' width='25' height='25'></a><input type='hidden' name='action' value='tree'><input type='hidden' name='class' value='manager'></td>
												<td align="right" class="celltext"><a href="{VAR:baseurl}/" target="_top">{VAR:LC_MANAGER_CLOSE_BROWSER}</a>&nbsp;&nbsp;</td>
                                        </tr>
                                </form>
                                </table>
		</td></tr></table>


				</td>
                </tr>
                </table>
								</td>
                </tr>
                </table>

<!-- Build the browser's objects and display default view of the
     tree. -->
<script>initializeDocument()</script>

</html>
