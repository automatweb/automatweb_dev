<form action="reforb.{VAR:ext}" method="POST">
<table border=1>
<tr>
	<td>Nimi</td>
	<td>V&auml;&auml;rtus</td>
	<td>Kustuta</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td>{VAR:name}</td>
	<td><input type='text' size="50" name='val[{VAR:name}]' value='{VAR:value}'></td>
	<td><input type="checkbox" name="del[{VAR:name}]" VALUE="1"></td>
</tr>
<!-- END SUB: LINE -->
<tr>
	<td><input type='text' name='new_name' value=''></td>
	<td><input type='text' size="50" name='new_value' value=''></td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="3"><input type="submit" value="Salvesta"></td>
</tr>
</table>
{VAR:reforb}
</form>