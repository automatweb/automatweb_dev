<form method=POST action='reforb.{VAR:ext}'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fform">ACLi nimi:</td>
	<td class="fform"><input type="text" name="name" value="{VAR:name}"></td>
</tr>
<tr>
	<td class="fform">Vali p&auml;rg:</td>
	<td class="fform"><select name='chain' >{VAR:chains}</select></td>
</tr>
<tr>
	<td class="fform">Vali roll:</td>
	<td class="fform"><select name='role' >{VAR:roles}</select></td>
</tr>
<tr>
	<td class="fform">Vali grupid:</td>
	<td class="fform"><select multiple name='groups[]' >{VAR:groups}</select></td>
</tr>
<tr>
	<td class="fform" align="center"><input type="submit" value="Salvesta"></td>
	<td class="fform" align="center"><input type="submit" name='save_acl' value="Uuenda acl"></td>
</tr>
</table>
{VAR:reforb}
</form>
