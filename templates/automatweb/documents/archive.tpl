<form method="POST" action="reforb.{VAR:ext}" name="doc">
<br>
<table border=0 width="100%" cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="hele_hall_taust" colspan="2">
<input type="button" class='doc_button' value="Muuda" onClick="window.location.href='orb.{VAR:ext}?class=document&action=change&id={VAR:docid}'">
</td>
</tr>
<tr>
<td colspan="2" class="hele_hall_taust">
<table border="0" cellspacing="1" cellpadding="2">
{VAR:arc_table}
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
