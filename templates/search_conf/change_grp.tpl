<form action='reforb.{VAR:ext}' method='POST' name='b88'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td class="fgtext">&nbsp;Nimi:&nbsp;</td>
<td class="fgtext">&nbsp;<input type="text" name="name" value="{VAR:name}">&nbsp;</td>
</tr>
<tr>
<td class="fgtext">&nbsp;Jrk:&nbsp;</td>
<td class="fgtext">&nbsp;<input type="text" name="ord" value="{VAR:ord}">&nbsp;</td>
</tr>
<tr>
<td class="fgtext">&nbsp;Men&uuml;&uuml;d:&nbsp;</td>
<td class="fgtext">&nbsp;<select class='small_button' size=20 name='menus[]' multiple>{VAR:menus}</select>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<input type='submit' class='small_button' value='Salvesta'>
<Br><br>
{VAR:reforb}
</form>