{VAR:header}
<a href='{VAR:back}'>Tagasi</a>
<form action='reforb.{VAR:ext}' method=POST>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width="100%">
<tr>
	<td class="fcaption2">T&uuml;&uuml;p:</td>
	<td class="fcaption2">{VAR:type_id}</td>
</tr>
<tr>
	<td class="fcaption2">T&uuml;&uuml;bi nimi:</td>
	<td class="fcaption2">{VAR:type_name}</td>
</tr>
<tr>
	<td colspan="2" class="fcaption2">Kommentaarid:</td>
</tr>
<!-- SUB: COMMENT -->
<tr>
	<td class="fcaption2">{VAR:user} @ {VAR:tm}</td>
	<td class="fcaption2">{VAR:comment}</td>
</tr>
<!-- END SUB: COMMENT -->
<tr>
	<td colspan="2" class="fcaption2"><textarea name='comment' rows=10 cols=50></textarea></td>
</tr>
<tr>
	<td colspan="2" class="fcaption2"><input type='submit' value='Lisa kommentaar'></td>
</tr>
</table>
{VAR:reforb}
</form>