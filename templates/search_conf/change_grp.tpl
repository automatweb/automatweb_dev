<form action='reforb.{VAR:ext}' method='POST' name='b88'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td class="fgtext">&nbsp;{VAR:LC_SEARCH_CONF_NAME}:&nbsp;</td>
<td class="fgtext">&nbsp;<input type="text" name="name" value="{VAR:name}">&nbsp;</td>
</tr>
<tr>
<td class="fgtext">&nbsp;Jrk:&nbsp;</td>
<td class="fgtext">&nbsp;<input type="text" name="ord" value="{VAR:ord}">&nbsp;</td>
</tr>
<tr>
<td class="fgtext">&nbsp;{VAR:LC_SEARCH_CONF_USONLY_NOLOG}:&nbsp;</td>
<td class="fgtext">&nbsp;<input type="checkbox" name="no_usersonly" value="1" {VAR:no_usersonly}>&nbsp;</td>
</tr>
<tr>
<td class="fgtext">&nbsp;Users only:&nbsp;</td>
<td class="fgtext">&nbsp;<input type="checkbox" name="users_only" value="1" {VAR:users_only}>&nbsp;</td>
</tr>
<tr>
<td class="fgtext">&nbsp;{VAR:LC_SEARCH_CONF_MENUS}:&nbsp;</td>
<td class="fgtext">&nbsp;<select class='small_button' size=20 name='menus[]' multiple>{VAR:menus}</select>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<input type='submit' class='small_button' value='{VAR:LC_SEARCH_CONF_SAVE}'>
<Br><br>
{VAR:reforb}
</form>