<html>
<head>
<title></title>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<script src="{VAR:baseurl}/automatweb/js/mm.js"></script>
</head>
<body bgcolor="#eeeeee" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<!-- FORM BEGIN -->
<table border=0 width="100%" cellspacing="0" cellpadding="0">
<form name="menus" method="post" action="reforb.{VAR:ext}">
<tr><td align="left">



<table border=0 width="100%" cellspacing="0" cellpadding="2">
<script language="Javascript">
function ddelete()
{
	document.menus.action.value = 'delete';
}
</script>
<tr>
<td align="left" class="yah">&nbsp;
<!-- SUB: YAH -->
<a href='{VAR:yah_link}'>{VAR:yah_name}</a> / 
<!-- END SUB: YAH -->
</td>
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



			<table border="0" cellpadding="0" cellspacing="0">
			<tr><td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

<!-- SUB: ADMIN -->
<td valign="middle"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:add_menu}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img name="new" alt="{VAR:LC_MANAGER_HINT_NEW_FOLDER}"  title="{VAR:LC_MANAGER_HINT_NEW_FOLDER}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a></td>

<td valign="middle"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:add_file}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('fileupload','','{VAR:baseurl}/automatweb/images/blue/awicons/file_upload_over.gif',1)"><img name="fileupload" alt="{VAR:LC_MANAGER_HINT_UPLOAD_FILE}"  title="{VAR:LC_MANAGER_HINT_UPLOAD_FILE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/file_upload.gif" width="25" height="25"></a></td>


<td valign="middle"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:document.menus.submit()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MANAGER_HINT_SAVE}" title="{VAR:LC_MANAGER_HINT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a></td>

<td valign="middle"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:if (confirm('Delete selected objects?')) {document.menus.submit()}" onClick="return ddelete()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('delete','','{VAR:baseurl}/automatweb/images/blue/awicons/delete_over.gif',1)"><img name="delete" alt="{VAR:LC_MANAGER_HINT_DELETE}" title="{VAR:LC_MANAGER_HINT_DELETE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/delete.gif" width="25" height="25"></a></td>
<!-- END SUB: ADMIN -->



		</tr></table>


</td></tr></table>
</td></tr></table>
</td></tr></table>



{VAR:menu_table}
{VAR:menu_reforb}

</body>
</html>
