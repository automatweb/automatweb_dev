<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption2">{VAR:item}</td>
	</tr>
	<tr>
		<td class="fcaption2">Vali mis men&uuml;&uuml;de all see toode veel n&auml;ha on:</td>
	</tr>
	<tr>
		<td class="fcaption2">
			<form action='reforb.{VAR:ext}' method=POST>
				<select class="small_button" size=10 name='menus[]' multiple>{VAR:menus}</select><br>
				<input class="small_button" type='submit' value='Salvesta'>
				{VAR:reforb}
			</form>
		</td>
	</tr>
	<tr>
		<td class="fcaption2">Vali mis men&uuml;&uuml; alla minnakse p2rast toote tellimist:</td>
	</tr>
	<tr>
		<td class="fcaption2">
			<form action='reforb.{VAR:ext}' method=POST>
				<select class="small_button" size=10 name='redir'>{VAR:redir}</select><br>
				<input class="small_button" type='submit' value='Salvesta'>
				{VAR:reforb2}
			</form>
		</td>
	</tr>
</table>
