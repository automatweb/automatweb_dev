<form action="{VAR:baseurl}/index.{VAR:ext}" method="GET">
<table border="1">
<tr>
	<td>Otsi:</td>
</tr>
<!-- SUB: GROUP -->
<tr>
	<td><input type="radio" name="group" value="{VAR:group}" {VAR:checked}>{VAR:name}</td>
</tr>
<!-- END SUB: GROUP -->

<tr>
	<td><input type="text" name="str" value="{VAR:str}"></td>
</tr>
<tr>
	<td><input type="submit" value="Otsi"></td>
</tr>
</table>
{VAR:reforb}
</form>
