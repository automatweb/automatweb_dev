<form action='reforb.{VAR:ext}' method="POST">
<table>
	
	<tr>
		<td>Objekti nimi:</td>
		<td><input type='text' name='name' value='{VAR:name}'></td>
	</tr>
	<tr>
		<td>Serveri nimi:</td>
		<td><input type='text' name='servername' value='{VAR:servername}'></td>
	</tr>
	<tr>
		<td>IRC Server:</td>
		<td><input type='text' name='ircserver' value='{VAR:ircserver}'></td>
	</tr>
	<tr>
		<td>Port:</td>
		<td><input type='text' name='port' value='{VAR:port}'></td>
	</tr>
	<tr>
		<td colspan=2><input type='Submit' value='Salvesta &raquo;'></td>
	</tr>
</table>
{VAR:reforb}
</form>
