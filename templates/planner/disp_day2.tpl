<table border="0" cellspacing="0" cellpadding="0" width="100%">
<!-- SUB: content -->
<tr>
	<td valign="top">
	<!-- SUB: event -->
	<div class="caleventday">{VAR:time_start}
	<a href="{VAR:link}"><b>{VAR:name}</b></a>
	</div>
	<!-- END SUB: event -->
	</td>
</tr>
<!-- END SUB: content -->
</table>

<table border="1" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-color: #CCCCCC;">
<!-- SUB: dcell -->
<tr>
<td width="50" align="center"><small>{VAR:dcellheader}</small></td>
<!-- SUB: duration_cell -->
<td width="2" bgcolor="{VAR:color}">
</td>
<!-- END SUB: duration_cell -->
<td style="font-size: 12px; font-weight: normal; padding: 3px; text-decoration: none;">
<!-- SUB: d_event -->
		{VAR:time_start} <input type="checkbox" name="mark[]" value="{VAR:id}"><a href="{VAR:link}">{VAR:name}</a>
<!-- END SUB: d_event -->
</td>
</tr>
<!-- END SUB: dcell -->
</table>
