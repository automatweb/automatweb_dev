<form action='reforb.{VAR:ext}' method=post>
<font color="red">{VAR:error}</font>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">E-mail:</td>
<td class="fcaption"><input type="text" name="email" VALUE='{VAR:email}'></td>
</tr>
<tr>
<td class="fcaption">Password:</td><td class="fform"><input type='password' NAME='pwd' ></td>
</tr>
<tr>
<td class="fcaption">Password 2x:</td><td class="fform"><input type='password' NAME='pwd2' ></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Salvesta!'></td>
</tr>
</table>
{VAR:reforb}
</form>
