<!-- generic calendar template -->

<!-- <header> -->
<table border="1" cellspacing="1" cellpadding="1" width="100%">
<!-- header is a separate subtemplate because this way we can switch it off if we need to -->
<!-- SUB: header -->
<tr>
	<!-- header cells are repeated for each column -->
		<td>{VAR:hcell}</td>
</tr>
<!-- END SUB: header -->
<!-- </header -->

<!-- contents -->
<!-- SUB: content -->
<tr>
	<td valign="top">
	<!-- nested table gives a greater flexibility -->
	<table border="0" width="100%" height="100%">
	<tr>
	<td>
	{VAR:cell}
	</td>
	</tr>
	</table>
	</td>
</tr>
<!-- END SUB: content -->
</table>
