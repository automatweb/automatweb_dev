<!-- generic calendar template -->

<!-- <header> -->
<table border="0" cellspacing="1" cellpadding="1" width="100%">
<!-- header is a separate subtemplate because this way we can switch it off if we need to -->
<!-- SUB: header -->
<tr class="celltext">
	<!-- spacer for timetamps -->
		<td width="30">Time</td>
	<!-- header cells are repeated for each column -->
		<td>{VAR:hcell}</td>
</tr>
<!-- END SUB: header -->
<!-- </header -->

<!-- contents -->
<!-- SUB: content -->
<tr class="caldayname">
	<!-- separate column for timestamps -->
	<td valign="top" width="30" class="caldayname">
	<table border="0">
	<!-- SUB: timestamp -->
	<tr>
	<td>
	<a href="{VAR:add_link}">{VAR:time}</a>
	</td>
	</tr>
	<!-- END SUB: timestamp -->
	</table>
	</td>
	<td valign="top">
	<!-- nested table gives a greater flexibility -->
	<table border="0" width="100%" height="100%">
	<tr>
	<td valign="top">
	{VAR:cell}
	</td>
	</tr>
	</table>
	</td>
</tr>
<!-- END SUB: content -->
</table>
