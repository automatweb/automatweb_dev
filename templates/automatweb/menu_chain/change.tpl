<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fform" align="left" colspan="2">
		<input type="submit" name="submit" value="Salvesta">
		<input type="submit" name="submit" value="Webile" onClick="window.open('{VAR:weburl}');return false;">
	</td>
</tr>
<tr>
	<td class="fcaption2">
		Nimetus
	</td>
	<td class="fform">
		<input type="text" name="name" size="40" value="{VAR:name}">
	</td>
</tr>
<tr>
	<td class="fcaption2">
		Kommentaar	
	</td>
	<td class="fform">
		<input type="text" name="comment" size="40" value="{VAR:comment}">
	</td>
</tr>
<tr>
	<td class="fform" valign="top">
	Vali menüüd:
	</td>
	<td  class="fform">
	<select name="menus[]" size="20" multiple>
	{VAR:menus}
	</select>
	</td>
</tr>
<tr>
	<td class="fform" align="left" colspan="2">
		<input type="submit" name="submit" value="Salvesta">
		<input type="submit" name="submit" value="Webile" onClick="window.open('{VAR:weburl}');return false;">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
