<table border="0" cellspacing="5" cellpadding="0">
<tr>
<td valign="top">
<table border="1" cellspacing="1" cellpadding="2" width="200">
<tr>
<td bgcolor="#ffccaa"><strong>Ürituste tüübid</strong></td>
</tr>
<tr>
<td><a href="{VAR:self}?op=show_events">Kõik</a></td>
</tr>
<!-- SUB: eventline -->
<tr>
<td><a href="{VAR:self}?op=show_events&type_id={VAR:id}">{VAR:name}</a></td>
</tr>
<!-- END SUB: eventline -->
<!-- SUB: active -->
<tr>
<td><strong>{VAR:name}</strong></td>
</tr>
<!-- END SUB: active -->
</table>
</td>
<td valign="top">
{VAR:table}
</td>
</tr>
</table>
