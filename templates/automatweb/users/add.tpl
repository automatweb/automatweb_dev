<script language="javascript">
function gp()
{
	pwd = new String("");
	for (i = 0; i < 8; i++)
	{
		rv = Math.random()*(123-97);
		rn = parseInt(rv);
		rt = rn+97;
		pwd = pwd + String.fromCharCode(rt);
	}
	document.ua.pass.value = pwd;
	document.ua.pass2.value = pwd;
	document.ua.genpwd.value = pwd;
}
</script>
<form method="POST" ACTION='reforb.{VAR:ext}' name='ua'>
{VAR:error}
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="fcaption">Nimi liitumisformist:</td>
<td class="fcaption">{VAR:name}</td>
</tr>
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
<td class="fform"><input type="password" name="pass"> (<a href='#' onClick='gp();'>Genereeri</a>)</td>
</tr>
<tr>
<td class="fcaption">Parool veelkord:</td>
<td class="fform"><input type="password" name="pass2"></td>
</tr>
<tr>
<td class="fcaption">Genereeritud parool:</td>
<td class="fform"><input type="text" name="genpwd"></td>
</tr>
<tr>
<td class="fcaption">Saada tervitusmeil:</td>
<td class="fform"><input type="checkbox" name="send_welcome_mail" value="1"></td>
</tr>
<tr>
<td class="fform" align="center" colspan="2">
<input type="submit" value="Edasi">
{VAR:reforb}
</td>
</tr>
</table>
</form>
