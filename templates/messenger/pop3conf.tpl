<div class="text">
<b>H‰‰lesta POP3 kontot</b>
</div>
<HR size="1" width="100%" color="#C8C8C8">
{VAR:menu}
<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="textsmallbold">Konto nimetus</td>
<td class="textsmall"><input type="text" name="name" size="40" value="{VAR:name}"></td>
</tr>
<tr>
<td class="textsmallbold">Eesnimi</td>
<td class="textsmall"><input type="text" name="name1" size="40" value="{VAR:name1}"></td>
</tr>
<tr>
<td class="textsmallbold">Perenimi</td>
<td class="textsmall"><input type="text" name="surname" size="40" value="{VAR:surname}"></td>
</tr>
<tr>
<td class="textsmallbold">E-post</td>
<td class="textsmall"><input type="text" name="address" size="40" value="{VAR:address}"></td>
</tr>
<tr>
<td class="textsmallbold">POP server</td>
<td class="textsmall"><input type="text" name="server" size="40" value="{VAR:server}"></td>
</tr>
<tr>
<td class="textsmallbold">Kasutaja</td>
<td class="textsmall"><input type="text" name="uid" size="40" value="{VAR:uid}"></td>
</tr>
<tr>
<td class="textsmallbold">Parool</td>
<td class="textsmall"><input type="password" name="password" size="40" value="{VAR:password}"></td>
</tr>
<tr>
<td class="textsmallbold" colspan="2" align="center">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</table>
</form>
