<table border="0" cellspacing="1" cellpadding="2">
<tr>
<form method="GET">
<td valign="top">
<strong>Otsi:</strong><br>
<input type="text" name="lookfor" value="{VAR:lookfor}" size="20" maxlength="20">
<br>
<input type="hidden" name="op" value="search_event">
<input type="submit" value="Otsi">
</form>
<br>
"j%" {VAR:LC_EVENTS_FINDS_BEG_J}<br>
"st" {VAR:LC_EVENTS_FINDS_ALL_INS}<i>st</i>
<br>
jne,jne.

</td>
<td valign="top">
{VAR:table}
</td>
</tr>
</table>
