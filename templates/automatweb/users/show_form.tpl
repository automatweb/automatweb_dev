<form action="orb.{VAR:ext}" method="GET">
<table>
<tr>
	<td><input type="radio" name="entry_type" value="existing"> Vali olemasolev sisestus:</td>
</tr>
<tr>
	<td><select name="ex_entry">{VAR:entries}</select></td>
</tr>
<tr>
	<td><input type="radio" name="entry_type" value="new" checked> Tee uus sisestus:</td>
</tr>
<tr>
	<td>{VAR:form}</td>
</tr>
</table>
{VAR:reforb}
</form>