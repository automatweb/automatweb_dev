<table border="1" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
<!-- SUB: line -->
<tr>
	<td><a href="{VAR:self}?date={VAR:date}&day={VAR:day}&mon={VAR:mon}{VAR:add}">{VAR:weekday}</a></td>
	<td align="right">{VAR:day}.{VAR:month}</td>
</tr>
<!-- END SUB: line -->
<!-- SUB: active -->
<tr>
	<td><font color="#FFCCAA"><strong><a href="{VAR:self}?date={VAR:date}&day={VAR:day}&mon={VAR:mon}{VAR:add}">{VAR:weekday}</a></strong></font></td>
	<td align="right"><font color="#FFCCAA"><strong>{VAR:day}.{VAR:month}</strong></font></td>
</tr>
<!-- END SUB: active -->
<tr>
	<td><a href="{VAR:self}?date={VAR:prev}{VAR:add}">Eelmine<br>nädal</a></td>
	<td align="right"><a href="{VAR:self}?date={VAR:next}{VAR:add}">Järgmine<br>nädal</a></td>
</tr>
<tr>
	<td colspan="2" align="center"><a href="{VAR:self}?{VAR:add}">Täna</a></td>
</tr>
</table>
