<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="1" bgcolor="#ffffff">
<tr>
<td colspan="2" class="header1" align="center">
{VAR:today} -> Lisa uus event
</td>
</tr>
<tr>
<td colspan="2" class="header1">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
<tr>
<td class="fgtitle"><strong>Algab</strong></td>
<td class="fgtitle"><select class="lefttab" name="shour">{VAR:shour}</select> t:<select class="lefttab" name="smin">{VAR:smin}</select>m</td>
<td class="fgtitle"><strong>Kestab</strong></td>
<td class="fgtitle"><select class="lefttab" name="dhour">{VAR:dhour}</select> t:<select class="lefttab" name="dmin">{VAR:dmin}</select>m</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="fgtitle"><strong>Pealkiri</strong></td>
<td class="fgtitle"><input type="text" name="title" size="30" maxlength="60" value="{VAR:title}"></td>
</tr>
<tr>
<td class="fgtitle"><strong>Koht</strong></td>
<td class="fgtitle"><input type="text" name="place" size="30" maxlength="60" value="{VAR:place}"></td>
</tr>
<tr>
<td class="fgtitle"><strong>Tuleta meelde</strong></td>
<td class="fgtitle"><input type="text" name="reminder" size="2" maxlength="2" value="{VAR:reminder}"> minutit enne algust</td>
</tr>
<tr>
<td class="fgtitle"><strong>Privaatne</strong></td>
<td class="fgtitle"><input type="checkbox" name="private" {VAR:private} value="1"></td>
</tr>
<tr>
<td class="fgtitle" colspan="2">
<strong>Sisu</strong><br>
<textarea name="description" cols="60" rows="10" wrap="soft">
{VAR:description}
</textarea>
</td>
</tr>
<tr>
<td class="fgtitle" align="center" colspan="2">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</table>
</form>

