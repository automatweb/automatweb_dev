
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

<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>




<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">


<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>





<td width="30" class="celltext">
<b>
GROUPS:&nbsp;
</b>
</td>

<!-- SUB: ADD_CAT -->
<td width="25" valign="middle"><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:addgrp}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img
name="new" alt="Add" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a></td>
<!-- END SUB: ADD_CAT -->


<!--ikoonid-->
<td valign="bottom" class="celltext">
<table border="0" cellpadding="0" cellspacing="0"><tr>


<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:foo.submit()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MENUEDIT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a></td>


<!--referesh-->
<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="#" onClick='window.location.reload()' onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','{VAR:baseurl}/automatweb/images/blue/awicons/refresh_over.gif',1)"><img name="refresh" alt="{VAR:LC_MENUEDIT_REFRESH}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif" width="25" height="25"></a></td>




</tr></table>
</td>
</tr>
</table>





		</td></tr>
		</table>

	</td></tr>
	</table>

</td></tr>
</table>
















<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">


<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr class="aste05">

<td height="15" class="celltext" width="30%">&nbsp;Name&nbsp;</td>
<td align="center" class="celltext" width="20%">&nbsp;Priority&nbsp;</td>
<td align="center" class="celltext" width="30%">&nbsp;Type;p&nbsp;</td>
<td align="center" class="celltext">&nbsp;Members&nbsp;</td>
<td align="center" class="celltext">&nbsp;Changer&nbsp;</td>
<td align="center" class="celltext">&nbsp;Changed&nbsp;</td>
<td align="center" colspan="3" class="celltext"  width="9%">&nbsp;Action&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr class="aste07">
<td height="15" class="celltext">&nbsp;<a href='{VAR:chmembers}' target='objects' onClick="window.location='orb.{VAR:ext}?class=groups&action=list_grps&parent={VAR:gid}';return true;">{VAR:name}</a>&nbsp;</td>
<td class="celltext" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='priority[{VAR:gid}]' VALUE='{VAR:priority}' SIZE=10>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;<a href="#">{VAR:members}</a>&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>

<td class="celltext" width="3%">
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:change}'><img src="{VAR:baseurl}/automatweb/images/blue/obj_edit.gif" alt="Change" border="0"></a>
<!-- END SUB: CAN_CHANGE -->
</td>

<td class="celltext" width="3%">
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('Are You sure You wish to delete this group?','{VAR:delete}')"><img src="{VAR:baseurl}/automatweb/images/blue/obj_delete.gif" border="0" alt="Delete"></a>
<!-- END SUB: CAN_DELETE -->
</td>

<td class="celltext" width="3%">
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:oid}&file=group.xml'><img src="{VAR:baseurl}/automatweb/images/blue/obj_acl.gif" border="0" alt="ACL"></a>
<!-- END SUB: CAN_ACL -->
</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
