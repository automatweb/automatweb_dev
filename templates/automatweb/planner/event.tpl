<form method="POST" action="reforb.{VAR:ext}" name="event">
<table border="0" cellspacing="1" cellpadding="1" bgcolor="#ffffff">
<tr>
<td colspan="2" class="header1" align="center">
{VAR:today} -> {VAR:caption}
</td>
</tr>
<tr>
<td colspan="2" class="header1">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
<tr>
<td class="fgtitle"><strong>Algab</strong></td>
<td class="fgtitle"><select class="lefttab" name="shour">{VAR:shour}</select> t:<select class="lefttab" name="smin">{VAR:smin}</select>m</td>
<td class="fgtitle"><strong>Kestab</strong></td>
<td class="fgtitle"><select class="lefttab" name="dhour">{VAR:dhour}</select> t:<select class="lefttab" name="dmin">{VAR:dmin}</select>m
&nbsp;&nbsp;
<input type="checkbox" name="repeater" value="1" {VAR:repcheck}><a href="#" onClick="aw_popup_scroll('orb.aw?class=planner&action=repeaters','repeat',500,500)">Kordused</a>
</td>
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
<td class="fgtitle"><strong>Osalejad (kasutajad)</strong></td>
<td class="fgtitle"><input type="text" name="users" size="30" value="{VAR:users}"></td>
</tr>
<tr>
<td class="fgtitle"><strong>Osalejad (grupid)</strong></td>
<td class="fgtitle"><input type="text" name="groups" size="30" value="{VAR:groups}"></td>
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
<!-- SUB: delete -->
<input type="submit" name="delete" value="Kustuta" onClick="if (confirm('Oled kindel?')) {document.event.submit()}">
<!-- END SUB: delete -->

<input type="hidden" name="dayskip" value="{VAR:dayskip}">
<input type="hidden" name="daypwhen" value="{VAR:daypwhen}">

<input type="hidden" name="weekskip" value="{VAR:weekskip}">
<input type="hidden" name="weekpwhen" value="{VAR:weekpwhen}">

<input type="hidden" name="monskip" value="{VAR:monskip}">
<input type="hidden" name="monpwhen" value="{VAR:monpwhen}">
<input type="hidden" name="monpwhen2" value="{VAR:monpwhen2}">

<input type="hidden" name="yearskip" value="{VAR:yearskip}">
<input type="hidden" name="yearpwhen" value="{VAR:yearpwhen}">

<input type="hidden" name="repeat" value="{VAR:repeat}">
<input type="hidden" name="repeat_value" value="{VAR:repeat_value}">
<input type="hidden" name="repeat_type" value="{VAR:repeat_type}">

{VAR:reforb}
</td>
</tr>
</table>
</form>

