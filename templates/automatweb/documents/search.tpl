<form method="GET" action="orb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width="100%">
<tr>
<td class="fgtitle" colspan="2">
	<b>Otsing:</b> |
</td>
</tr>
<!-- SUB: field -->
<tr>
	<td class="fcaption2">{VAR:caption}:</td>
	<td class="fform"><input type="text" name="fields[{VAR:name}]" size="60" value='{VAR:value}'></td>
</tr>
<!-- END SUB: field -->
<tr>
<td class="fgtext">
M‰‰rangud
</td>
<td class="fgtext">
Otsi ka arhiividest: <input type="checkbox" name="search_archive" value="1" {VAR:search_archive}
</td>
</tr>
<tr>
<td class="fgtext" colspan="2" align="center">
	<input type="submit" value=" Otsi ">
	{VAR:reforb}
</td>
</tr>
</table>
</form>
