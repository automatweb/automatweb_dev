<form method="GET" action="orb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Otsi nimest:</td>
	<td class="fform"><input type="text" name="s_name" size="40" value='{VAR:s_name}'></td>
</tr>
<tr>
	<td class="fcaption2">Otsi sisust:</td>
	<td class="fform"><input type="text" name="s_content" size="40" value='{VAR:s_content}'></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Otsi"></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Leitud dokumendid:</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2"><a target="_blank" href='{VAR:change}'>{VAR:name}</a></td>
	<td class="fform"><a href='{VAR:brother}'>Vennasta</a></td>
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:reforb}
</form>
