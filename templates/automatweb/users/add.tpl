<form method="POST" ACTION='reforb.{VAR:ext}'>
{VAR:error}
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="fcaption">Kasutajanimi:</td>
<td class="fcaption"><input type="text" name="a_uid" VALUE='{VAR:uid}'></td>
</tr>
<tr>
<td class="fcaption">E-mail:</td>
<td class="fcaption"><input type="text" name="email" VALUE='{VAR:email}'></td>
</tr>
<tr>
<td class="fcaption">Parool:</td>
<td class="fform"><input type="password" name="pass"></td>
</tr>
<tr>
<td class="fcaption">Parool veelkord:</td>
<td class="fform"><input type="password" name="pass2"></td>
</tr>
<tr>
<td class="fform" align="center" colspan="2">
<input type="submit" value="Edasi">
{VAR:reforb}
</td>
</tr>
</table>
</form>
