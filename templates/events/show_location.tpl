<table border="1" cellpadding="1" cellspacing="2" width="400">
<tr>
<td colspan="2" bgcolor="#FFCCAA" align="center"><strong>{VAR:name}</strong></td>
</tr>
<tr>
<td><strong>Tüüp</strong></td>
<td>{VAR:tname}</td>
</tr>
<tr>
<td valign="top" colspan="2">
{VAR:description}
</td>
</tr>
<tr>
<td><strong>Aadress</strong></td>
<td>{VAR:address}</td>
</tr>
<tr>
<td><strong>Telefon</strong></td>
<td>{VAR:phone}</td>
</tr>
<tr>
<td><strong>URL</strong></td>
<td>{VAR:url}</td>
</tr>
</table>
<table border="1" cellpadding="1" cellspacing="2" width="400">
<tr>
<td colspan="2" bgcolor="#FFCCAA" align="center"><strong>Siin toimuvad üritused</strong></td>
</tr>
<!-- SUB: line -->
<tr>
<td>{VAR:start}</td>
<td><a href="{VAR:self}?op=show_event&id={VAR:id}">{VAR:name}</a></td>
</tr>
<!-- END SUB: line -->
</table>
