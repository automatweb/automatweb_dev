<form action='reforb.{VAR:ext}' method="post">
<table border="1">
	<tr>
		<td>Nimi:</td>
		<td><input type="text" name="name" value="{VAR:value}"></td>
	</tr>
	<tr>
		<td colspan="2">Vali formid:</td>
	</tr>
	<tr>
		<td colspan="2"><select class="small_button" name="forms[]" multiple size="20">{VAR:forms}</select></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="Salvesta"></td>
	</tr>
</table>
{VAR:reforb}
</form>