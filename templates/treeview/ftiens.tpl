<script src="{VAR:baseurl}/automatweb/js/ua.js"></script>
<script src="{VAR:baseurl}/automatweb/js/ftiens4.js"></script>
<script language="javascript">
USETEXTLINKS = 1
LINKTARGET = "{VAR:linktarget}";
ICONPATH = '{VAR:baseurl}/automatweb/images/';
pr_{VAR:root} = gFld("<b>{VAR:rootname}</b>", "{VAR:rooturl}","{VAR:icon_root}")

<!-- SUB: TREE -->
	pr_{VAR:id} = insFld(pr_{VAR:parent}, gFld("{VAR:name}", "{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: TREE -->
<!-- SUB: DOC -->
	pr_{VAR:id} = insDoc(pr_{VAR:parent}, gLnk("R", "{VAR:name}","{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: DOC -->

foldersTree = pr_{VAR:root};
</script>

</head>
<body topmargin=0 marginheight=0>
<!--
<table border=0 width="100%" cellspacing="0" cellpadding="2">
	<td class="tableborder">
                                <table border="0" cellspacing="0" cellpadding="1" width=100%>
                                        <tr>
                                                <td class="tableshadow">
                                                                <table border="0" cellspacing="0" cellpadding="0" width=100%><tr><td class="tableinside"><table border="0" cellspacing="0" cellpadding="0">
                <form action='orb.{VAR:ext}' method='get' name='pfft'>
                                        <tr>
                                                <td class="tableinside" height="20" colspan="11" align=center><select class='formselect' name='period'>{VAR:periods}</select></td><td class="tableinside"><a href='javascript:document.pfft.submit()' onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','images/blue/awicons/refresh_over.gif',1)"><img name='refresh' alt='{VAR:LC_MENUEDIT_REFRESH}' border='0' SRC='{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif' width='25' height='25'></a><input type='hidden' name='action' value='folders'><input type='hidden' name='class' value='menuedit'>
                                                </td>
                                        </tr>
                                </form>
                                </table></td></tr></table>
                        </td>
                </tr>
                        </table>
                        </td>
                </tr>
                </table>
-->

<!-- Build the browser's objects and display default view of the
     tree. -->
<script>initializeDocument()</script>

