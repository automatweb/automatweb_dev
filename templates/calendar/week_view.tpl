<table border="0" width="100%" style="border-collapse: collapse; border: 1px solid #CCC;">
<!-- SUB: DAY -->
<tr>
<td valign="top" style="border: 1px solid #CCC; background: #EEE;">
<span style="font-size: 10px; font-weight: bold;">
<a style="text-decoration: none; color: black;" href="{VAR:daylink}">{VAR:lc_weekday}, {VAR:daynum}. {VAR:lc_month}</a></strong>
</span>
</td>
</tr>
<tr>
<td valign="top" style="border: 1px solid #CCC;">
<span style="font-size: 11px;">
<p>
	<!-- SUB: EVENT -->
		<strong>{VAR:time}</strong> - {VAR:name}<br>
	<!-- END SUB: EVENT -->
</span>
</td>
</tr>
<!-- END SUB: DAY -->
<!-- SUB: TODAY -->
<tr>
<td valign="top" style="border: 1px solid #CCC; background: #EEE;">
<span style="font-size: 10px; font-weight: bold;">
<a style="text-decoration: none; color: black;" href="{VAR:daylink}">{VAR:lc_weekday}, {VAR:daynum}. {VAR:lc_month}</a></strong>
</span>
</td>
</tr>
<tr>
<td valign="top" style="border: 1px solid #CCC; background: #F6F6F6;">
<span style="font-size: 11px;">
<p>
	<!-- SUB: EVENT -->
		<strong>{VAR:time}</strong> - {VAR:name}<br>
	<!-- END SUB: EVENT -->
</span>
</td>
</tr>
<!-- END SUB: TODAY -->
</table>
