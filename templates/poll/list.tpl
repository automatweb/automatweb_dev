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
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:newpoll_url}"
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



<!-- SUB: ACTIVE -->
&nbsp;<font color="#ff0000">{VAR:LC_POLL_YES}</font>&nbsp;
<!-- END SUB: ACTIVE -->

<!-- SUB: NACTIVE -->
&nbsp;<a href="{VAR:activate_url}">{VAR:LC_POLL_NO}</a>&nbsp;
<!-- END SUB: NACTIVE -->

<!-- SUB: CHANGE -->
	<a href='{VAR:change_url}'><img src="{VAR:baseurl}/automatweb/images/blue/obj_edit.gif" border="0" alt="{VAR:LC_POLL_CHANGE}"></a>&nbsp;
<!-- END SUB: CHANGE -->

<!-- SUB: DELETE -->
	<a href='{VAR:delete_url}'><img src="{VAR:baseurl}/automatweb/images/blue/obj_delete.gif" border="0" alt="{VAR:LC_POLL_DELETE}"></a>&nbsp;
<!-- END SUB: DELETE -->

{VAR:table}
