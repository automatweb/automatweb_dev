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





<td width="30" class="celltext">
<b>
{VAR:LC_POLL_BIG_POLLS}:&nbsp;
</b>
</td>
<td width="25" valign="middle"><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="poll.{VAR:ext}?type=add"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img
name="new" alt="{VAR:LC_POLL_ADD}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a></td>


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

<td align="center" class="celltext"  width="40%">&nbsp;{VAR:LC_POLL_QUESTION}&nbsp;</td>
<td align="center" class="celltext"  width="20%">&nbsp;{VAR:LC_POLL_MUUTJA}&nbsp;</td>
<td align="center" class="celltext"  width="20%">&nbsp;{VAR:LC_POLL_CHANGED}&nbsp;</td>
<td align="center" class="celltext"  width="10%">&nbsp;{VAR:LC_POLL_ACTIVITY}&nbsp;</td>
<td align="center" class="celltext" colspan=2  width="10%">{VAR:LC_POLL_ACTION}</td>
</tr>

<!-- SUB: LINE -->
<tr class="aste07">
<td align="left" class="celltext">&nbsp;{VAR:name}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:modified}&nbsp;</td>

<!-- SUB: ACTIVE -->
<td align="center" class="celltext">&nbsp;<font color="#ff0000">{VAR:LC_POLL_YES}</font>&nbsp;</td>
<!-- END SUB: ACTIVE -->

<!-- SUB: NACTIVE -->
<td align="center" class="celltext">&nbsp;<a href="poll.{VAR:ext}?type=set_active&id={VAR:id}">{VAR:LC_POLL_NO}</a>&nbsp;</td>
<!-- END SUB: NACTIVE -->

<td align="center" class="celltext" width="5%"><a href='poll.{VAR:ext}?type=change&id={VAR:id}'><img src="{VAR:baseurl}/automatweb/images/blue/obj_edit.gif" border="0" alt="{VAR:LC_POLL_CHANGE}"></a>&nbsp;</td>
<td align="center" class="celltext" width="5%"><a href='poll.{VAR:ext}?type=delete&id={VAR:id}'><img src="{VAR:baseurl}/automatweb/images/blue/obj_delete.gif" border="0" alt="{VAR:LC_POLL_DELETE}"></a>&nbsp;</td>
</tr>

<!-- END SUB: LINE -->

</table>
</td></tr></table>
