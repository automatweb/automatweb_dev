<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption2">{VAR:item}</td>
	</tr>
	<tr>
		<td class="fcaption2">Vali mis men&uuml;&uuml;de all see kaup veel n&auml;ha on:</td>
	</tr>
	<tr>
		<td class="fcaption2">
			<form action='reforb.{VAR:ext}' method=POST>
				<select class="small_button" size=10 name='menus[]' multiple>{VAR:menus}</select><br>
				<input type='submit' value='Salvesta'>
				{VAR:reforb}
			</form>
		</td>
	</tr>
</table>
