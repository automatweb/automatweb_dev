<form method=POST action='reforb.{VAR:ext}'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fform">Rolli nimi:</td>
	<td colspan="2" class="fform"><input type="text" name="name" value="{VAR:name}"></td>
</tr>
<tr>
	<td colspan="2" class="fform">Vali &otilde;igused, mida muudetakse:</td>
	<td class="fform">Lubatud / Keelatud</td>
</tr>
<!-- SUB: ACLS -->
<tr>
	<td class="fform">{VAR:acl_name}</td>
	<td class="fform"><input type="checkbox" name="acls[]" value="{VAR:acl_name}" {VAR:checked}></td>
	<td class="fform">
		<!-- SUB: ACL_SET -->
		<input type="checkbox" name="acls_set[]" value="{VAR:acl_name}" {VAR:checked_set}>
		<!-- END SUB: ACL_SET -->
	</td>
</tr>
<!-- END SUB: ACLS -->
<tr>
	<td class="fform" align="center"><input type="submit" value="Salvesta"></td>
	<td class="fform" colspan="2" align="center"><input type="submit" name="save_acl" value="Uuenda acl"></td>
</tr>
</table>
{VAR:reforb}
</form>
