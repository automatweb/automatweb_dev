<table border="0" cellspacing="1" cellpadding="1" bgcolor="#CCCCCC">
<tr>
<td>
	{VAR:menubar}
</td>
</tr>
<tr>
<form method="POST" action="reforb.{VAR:ext}" name="event">
<td>
<table border="0" cellspacing="1" cellpadding="1" bgcolor="#ffffff">
<tr>
<td colspan="2" class="header1">
<table border="0" width="100%" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
<tr>
<td class="fgtitle"><strong>Algab</strong></td>
<td class="fgtitle">{VAR:start}</td>
<td class="fgtitle"><select class="lefttab" name="shour">{VAR:shour}</select> t:<select class="lefttab" name="smin">{VAR:smin}</select>m</td>
</tr>
<tr>
<td class="fgtitle"><strong>Kestab</strong></td>
<td class="fgtitle"><select class="lefttab" name="dhour">{VAR:dhour}</select> t:<select class="lefttab" name="dmin">{VAR:dmin}</select>m
&nbsp;&nbsp;
</td>
<td class="fgtitle"><strong>Korduv sündmus: <input type="checkbox" name="repcheck" value="1" {VAR:repcheck}></td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="fgtitle"><strong>Nimi</strong></td>
<td class="fgtitle"><input type="text" name="title" size="30" maxlength="60" value="{VAR:title}"></td>
</tr>
<tr>
<td class="fgtitle"><strong>Koht</strong></td>
<td class="fgtitle"><input type="text" name="place" size="30" maxlength="60" value="{VAR:place}"></td>
</tr>
<tr>
<td class="fgtitle"><strong>Lisa objekt</strong></td>
<td class="fgtitle"><img src="{VAR:obj_icon}">&nbsp;<big>{VAR:object}</big>
&nbsp;
<input type="submit" name="object" value="Muuda..."></td>
</tr>
<tr>
<td class="fgtitle"><strong>Kalendris näidatakse</strong></td>
<td class="fgtitle">
<select name="showtype">
{VAR:showtype}
</select>
&nbsp;
</tr>
<tr>
<td class="fgtitle"><strong>Värv</strong></td>
<td class="fgtitle"><select name="color">{VAR:color}</select></td>
</tr>
<!--
<tr>
<td class="fgtitle"><strong>{VAR:LC_PLANNER_PRIVATE}</strong></td>
<td class="fgtitle"><input type="checkbox" name="private" {VAR:private} value="1"></td>
</tr>
-->
<tr>
<td class="fgtitle" colspan="2">
<strong>Sisu</strong><br>
<textarea name="description" cols="60" rows="10" wrap="soft">{VAR:description}</textarea>
</td>
</tr>
<tr>
<td class="fgtitle"><strong>Kalender</strong></td>
<td class="fgtitle"><select name="folder">{VAR:calendars}</select>
<a href="{VAR:calendar_url}"><img border="0" src="{VAR:icon_url}">Näita kalendrit</a></td>
</tr>
<tr>
<td class="fgtitle" align="center" colspan="2">
<input type="submit" value="Salvesta">
<!-- SUB: delete -->
<input type="submit" name="delete" value="{VAR:LC_PLANNER_DELETE}" onClick="if (confirm('Oled kindel?')) {document.event.submit()}">
<!-- END SUB: delete -->
{VAR:reforb}
</td>
</tr>
</table>
</td>
</form>
</tr>
</table>
