<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
<form method="POST" action="reforb.{VAR:ext}">
<tr>
<td class="fgtitle">Nimi:</td>
<td class="fgtext"><input type="text" name="name" size="40" value="{VAR:name}"></td>
</tr>
<tr>
<td class="fgtitle">Sisu (URL):</td>
<td class="fgtext"><input type="text" name="url" size="40" value="{VAR:url}"></td>
</tr>
<tr>
<td class="fgtitle">Mõõtmed:</td>
<td class="fgtext">
	Laius: <input type="width" name="width" size="4" value="{VAR:width}">
	Kõrgus: <input type="height" name="height" size="4" value="{VAR:height}">	
	</td>
</tr>
<tr>
<td class="fgtitle">Menüüd:</td>
<td class="fgtext">
<select size="30" name="menus[]" multiple>
{VAR:menus}
</select>
</td>
</tr>
<tr>
<td class="fgtitle" colspan="2" align="center">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</form>
</table>
