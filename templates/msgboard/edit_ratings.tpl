<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td class="fgtitle">
<b>Foorumi hinded:</b>
<a href="{VAR:change_link}">Konfigureeri</a>
|
<a href="{VAR:new_rate_link}">Uus hinne</a>
</td>
</tr>
</table>


<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="hele_hall_taust">Hinded:</td>
<td class="fform">

<table border="0" cellspacing="1" cellpadding="1" width="100%">
<tr>
<td class="hele_hall_taust" align="center"><b>Jrk</b></td>
<td class="hele_hall_taust" align="center"><b>Nimetus</b></td>
<td class="hele_hall_taust" align="center"><b>V‰‰rtus</b></td>
<td class="hele_hall_taust" align="center"><b>Vali</b></td>
</tr>
<!-- SUB: rateline -->
<tr>
<td class="hele_hall_taust">
	<input type="text" size="2" maxlength="3" name="rate_order[{VAR:id}]" value="{VAR:ord}">
</td>
<td class="hele_hall_taust">
	<input type="text" size="30" name="rate_name[{VAR:id}]" value="{VAR:name}">
</td>
<td class="hele_hall_taust">
	<input type="text" size="6" name="rate_value[{VAR:id}]" value="{VAR:rate}">
</td>
<td class="hele_hall_taust">
	<input type="checkbox" name="rate_check[{VAR:id}]" value="1">
</td>
</tr>
<!-- END SUB: rateline -->
</table>

</td>
</tr>
<tr>
<td class="hele_hall_taust" colspan=2>
<input type='submit' VALUE='{VAR:LC_MSGBOARD_SAVE}' CLASS="small_button">
<input type='submit' name="delete" VALUE='Kustuta'>
</td>
</tr>
</table>
{VAR:reforb}
</form>
