<table border=0 cellspacing=0 cellpadding=0 bgcolor="#CCCCCC">
<form method="POST" action="reforb.{VAR:ext}">
<tr>
<td>
<table border=0 cellspacing=1 cellpadding=0 bgcolor="#ffffff">
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
	<td class="fcaption2">ID</td>
	<td class="fcaption2"><strong>{VAR:id}</td>
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
	<td class="fcaption2">Koht</td>
	<td class="fform"><select name="place">{VAR:place}</select></td>
</tr>
<tr>
	<td class="fcaption2">Kontakt</td>
	<td class="fform"><input type="text" name="contact" size="60" value="{VAR:contact}"></td>
</tr>

<tr>
	<td class="fcaption2" valign="top">Kirjeldus</td>
	<td class="fform"><textarea name="description" cols="60" rows="20">{VAR:description}</textarea></td>
</tr>
<tr>
	<td colspan="2">
	<table border="0" cellspacing="1" cellpadding="0" width="100%">
	<tr>
	<td class="fform">Tasuta:
	<input type="checkbox" name="free" {VAR:free} value=1></td>
	<td class="fform">Hind:
	<input type="text" name="price" size="6" value="{VAR:price}"></td>
	<td class="fform">Hind flaieriga:
	<input type="text" name="priceflyer" size="6" value="{VAR:priceflyer}"></td>
	<td class="fform">Ainult flaieriga:
	<input type="checkbox" name="flyeronly" {VAR:flyeronly} value=1></td>
	</tr>
	</table>
<!--
	-->
	</td>
</tr>
<tr>
	<td class="fcaption2">Flaieri url</td>
	<td class="fform"><input type="text" name="flyer" size="60" value="{VAR:flyer}"></td>
</tr>
<tr>
	<td class="fcaption2">Piletid eelmüügist</td>
	<td class="fform"><input type="text" name="reservation" size="60" value="{VAR:reservation}"></td>
</tr>
<tr>
	<td class="fcaption2">Vanusepiirang</td>
	<td class="fform"><input type="text" name="agelimit" size="10" value="{VAR:agelimit}"></td>
</tr>
<tr>
	<td class="fcaption2">Algus</td>
	<td class="fform">{VAR:start}</td>
</tr>

<tr>
	<td class="fcaption2">Lopp</td>
	<td class="fform">{VAR:end}</td>
</tr>

<tr>
	<td class="fcaption2">URL</td>
	<td class="fform"><input type="text" name="url" size="60" value="{VAR:url}"></td>
</tr>
<tr>
<td class="fform" colspan="2">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</table>
</td>
</form>
</tr>
</table>
