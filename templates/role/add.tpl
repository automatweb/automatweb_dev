<!--tabelraam-->
<form method=POST action='reforb.{VAR:ext}' name='aa'>
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="2">
<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.aa.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:this.document.aa.submit();">Salvesta</a>
</td>
<td>&nbsp;&nbsp;</td>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="#" onclick="this.document.aa.save_acl.value=1;this.document.aa.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Uuenda ACL" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="#" onClick="this.document.aa.save_acl.value=1;this.document.aa.submit();">Uuenda ACL</a>
</td></tr>
</table>

		</td>
		</tr>
		</table>


	</td>
	</tr>
	</table>

</td>
</tr>
</table>
<table border=0 cellpadding=2 cellspacing=1>
<tr>
	<td align=center>


<table border=0 cellspacing=1 cellpadding=1>
<tr>
	<td class="celltext">Rolli nimi:</td>
	<td colspan="2" class="celltext"><input type="text" name="name" value="{VAR:name}"></td>
</tr>
<tr>
	<td colspan="2" class="celltext">Vali &otilde;igused, mida muudetakse:</td>
	<td class="celltext">Lubatud</td>
</tr>
<!-- SUB: ACLS -->
<tr>
	<td class="celltext">{VAR:acl_name}</td>
	<td class="celltext"><input type="checkbox" name="acls[]" value="{VAR:acl_name}" {VAR:checked}></td>
	<td class="celltext">
		<!-- SUB: ACL_SET -->
		<input type="checkbox" name="acls_set[]" value="{VAR:acl_name}" {VAR:checked_set}>
		<!-- END SUB: ACL_SET -->
	</td>
</tr>
<!-- END SUB: ACLS -->
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
