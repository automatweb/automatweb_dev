<form method="POST" action="reforb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Nimi:</td>
	<td class="fcaption2" ><input type='text' name='name' value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2">Valem:</td>
	<td class="fcaption2" ><input type='text' name='eq' value='{VAR:eq}'></td>
</tr>
<tr>
	<td colspan=2 class="fcaption2">Kui nimi on t&uuml;hi, siis pannakse nimeks valem.</td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Salvesta">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
