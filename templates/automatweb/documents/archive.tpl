<form method="POST" action="reforb.{VAR:ext}" name="doc">
<br>
<table border=0 width="100%" cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="hele_hall_taust" colspan="2">
<input type="button" class='doc_button' value="Muuda" onClick="window.location.href='orb.{VAR:ext}?class=document&action=change&id={VAR:docid}'">

<input class='doc_button' type="submit" value="Eelvaade" onClick="window.location.href='{VAR:preview}';return false;"> <input type="submit" class='doc_button' value="Sektsioonid" onClick="window.location.href='{VAR:menurl}';return false;"> <input type="submit" class='doc_button' value="Webile" onClick="window.open('{VAR:baseurl}/index.{VAR:ext}?section={VAR:docid}');return false;"> <input type="button" class="doc_button" value="Teavita liste" onClick="if (confirm('Teavitada liste?')) { window.location.href='{VAR:self}?class=keywords&action=notify&id={VAR:docid}';}">

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
