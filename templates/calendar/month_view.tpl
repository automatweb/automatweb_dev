<table border="1" style="border: 1px solid #8AAABE;" cellspacing="0">
<tr>
<!-- SUB: HEADER -->
	<!-- SUB: HEADER_CELL -->
	<th width="150">
		{VAR:dayname}
	</th>
	<!-- END SUB: HEADER_CELL -->
<!-- END SUB: HEADER -->
</tr>
<!-- SUB: WEEK -->
<tr>
	<!-- SUB: DAY -->
	<td width="150" valign="top" style="border: 1px solid #8AAABE; background-color: #FFF;">
	<strong>{VAR:daynum}</strong>
	<small>
	<p>
		<!-- SUB: EVENT -->
			<strong>{VAR:time}</strong> - {VAR:name}<br>
		<!-- END SUB: EVENT -->
	</small>
	</td>
	<!-- END SUB: DAY -->
</tr>
<!-- END SUB: WEEK -->
</table>
