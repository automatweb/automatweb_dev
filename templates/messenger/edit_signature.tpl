{VAR:menu}
<span class="textsmallbold">{VAR:title}</span>
<HR size="1" width="100%" color="#C8C8C8">
<form method="POST" action='reforb.{VAR:ext}'>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="textsmallbold">Nimetus</td>
<td class="textsmall"><input type="text" name="name" size="30" value="{VAR:name}"></td>
</tr>
<tr>
<td class="textsmallbold" colspan="2">Sisu</td>
</tr>
<td class="textsmall" colspan="2">
<textarea name="signature">
{VAR:signature}
</textarea>
</td>
</tr>
<tr>
<td class="textsmallbold" colspan="2" align="center">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</table>
</form>
