<form method="POST" action="reforb.{VAR:ext}">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2" colspan="2">
		<strong>{VAR:caption}</strong>
	</td>
</tr>
<tr>
	<td class="fform" colspan="2">
		<input type="submit" value="Salvesta">
	</td>
</tr>

<tr>
	<td class="fform">ID</td>
	<td class="fform"><strong>{VAR:id}</td>
</tr>

<tr>
	<td class="fcaption2">Nimi</td>
	<td class="fform"><input type="text" name="name" size="60" value="{VAR:name}"></td>
</tr>

<tr>
	<td class="fcaption2">Tüüp</td>
	<td class="fform"><select name="type">{VAR:type}</select></td>
</tr>

<tr>
	<td class="fcaption2" valign="top">Kirjeldus</td>
	<td class="fform"><textarea name="description" cols="60" rows="20">{VAR:description}</textarea></td>
</tr>

<tr>
	<td class="fcaption2">Aadress</td>
	<td class="fform"><input type="text" name="address" size="60" value="{VAR:address}"></td>
</tr>

<tr>
	<td class="fcaption2">Telefon</td>
	<td class="fform"><input type="text" name="phone" size="60" value="{VAR:phone}"></td>
</tr>

<tr>
	<td class="fcaption2">URL</td>
	<td class="fform"><input type="text" name="url" size="60" value="{VAR:url}"></td>
</tr>
<tr>
<td class="fform" colspan="2">
<input type="submit" value="Salvesta">
<!--
<input type="hidden" name="op" value="save">
<input type="hidden" name="docid" value="{VAR:docid}">
-->
{VAR:reforb}
</td>
</tr>
</table>
</form>
