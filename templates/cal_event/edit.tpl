{VAR:menubar}

<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr>
<td class="aste00">


<table border="0" cellspacing="0" cellpadding="2" width="100%">
<tr>
<form method="POST" action="reforb.{VAR:ext}" name="event">
<td class="aste01">

<table border="0" cellspacing="5" cellpadding="2">
<tr>
<td colspan="2">

		<table border="0" cellspacing="1" cellpadding="2">
		<tr>
		<td class="celltext" align="right" width="110">Algab:</td>
		<td class="celltext">{VAR:start}</td>
		<td class="celltext"><select class="formselect2" name="shour">{VAR:shour}</select> t : <select class="formselect2" name="smin">{VAR:smin}</select> m</td>
		</tr>
		<tr>
		<td class="celltext" align="right">Lõpeb:</td>
		<td class="celltext"><select class="formselect2" name="ehour">{VAR:ehour}</select> t : <select class="formselect2" name="emin">{VAR:emin}</select> m
		&nbsp;&nbsp;
		</td>
		<td class="celltext">Korduv sündmus: <input type="checkbox" name="repcheck" value="1" 	{VAR:repcheck}></td>
		</tr>	
		</table>

	</td>
	</tr>

	<tr>
	<td class="celltext" align="right">Värv:</td>
	<td class="celltext"><select name="color" class="formselect2">{VAR:color}</select></td>
	</tr>

	<tr>
	<td class="celltext" align="right" width="110">Nimi:</td>
	<td class="celltext"><input type="text" class="formtext" name="title" size="30" maxlength="60" value="{VAR:title}"></td>
	</tr>
	<tr>
	<td class="celltext" align="right">Koht:</td>
	<td class="celltext"><input type="text" class="formtext" name="place" size="30" maxlength="60" value="{VAR:place}"></td>
	</tr>
	
	
<!--
<tr>
<td class="fgtitle"><strong>{VAR:LC_PLANNER_PRIVATE}</strong></td>
<td class="fgtitle"><input type="checkbox" name="private" {VAR:private} value="1"></td>
</tr>
-->
<tr>
<td class="celltext" colspan="2">
Sisu:<br>
<textarea name="description" clasS="formtext" cols="65" rows="10" wrap="soft">{VAR:description}</textarea>
</td>
</tr>

<tr>
	<td class="celltext" align="right">Lisa objekt:</td>
	<td class="celltext"><img src="{VAR:obj_icon}">&nbsp;<big>{VAR:object}</big>
	&nbsp;
	<input type="submit" name="object" value="Muuda..." class="formbutton"></td>
	</tr>
	<tr>
	<td class="celltext" align="right">Kalendris näidatakse:</td>
	<td class="celltext">
	<select name="showtype" class="formselect2">
	{VAR:showtype}
	</select>
	&nbsp;
	</tr>

<tr>
<td class="celltext" align="right">Kalender:</td>
<td class="celltext"><select name="folder" class="formselect2">{VAR:calendars}</select>
<a href="{VAR:calendar_url}"><img border="0" src="{VAR:icon_url}">&nbsp;Näita kalendrit</a></td>
</tr>
<tr>
<td class="celltext" align="right">&nbsp;</td>
<td class="celltext">
<input type="submit" value="Salvesta" class="formbutton">
<!-- SUB: delete -->
<input type="submit" class="formbutton" name="delete" value="{VAR:LC_PLANNER_DELETE}" onClick="if (confirm('Oled kindel?')) {document.event.submit()}">
<!-- END SUB: delete -->
{VAR:reforb}
</td>
</tr>
</table>

</td>
</form>
</tr>
</table>

</td>
</tr>
</table>
<br>
