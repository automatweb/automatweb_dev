{VAR:menu}
<span class="textsmallbold">{VAR:title}</span>
<HR size="1" width="100%" color="#C8C8C8">
<form method="POST"  action='reforb.{VAR:ext}'>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="textsmallbold">Eesnimi</td>
<td class="textsmall"><input type="text" name="name" size="30" value="{VAR:name}"></td>
</tr>
<tr>
<td class="textsmallbold">Perenimi</td>
<td class="textsmall"><input type="text" name="surname" size="30" value="{VAR:surname}"></td>
</tr>
<tr>
<td class="textsmallbold">E-posti aadress</td>
<td class="textsmall"><input type="text" name="email" size="30" value="{VAR:email}"></td>
</tr>
<tr>
<td class="textsmallbold" colspan="2" align="center">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</table>
</form>
