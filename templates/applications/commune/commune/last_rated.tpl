<hr size="1" width="100%">
<table class="profile" width="100%">
	<tr>
	<td colspan="2">
	viimati hinnatud pilt:
	</td>
	</tr>
	<tr>
		<td rowspan="{VAR:rows}">{VAR:image}</td>
		<td>Pildi allkiri: </td><td>{VAR:title}</td>
	</tr>
	<tr>
		<td>Profiil: </td><td>{VAR:name}</td>
	</tr>
		<td>Keskmine hinne: </td><td>{VAR:rating}</td>
	</tr>
	{VAR:type}
</table>

<!-- SUB: rated -->
<tr>
	<td>Antud hinne: </td><td>{VAR:mark}</td>
</tr>
<!-- END SUB: rated -->
<!-- SUB: commented -->
<tr>
	<td>Lisatud kommentaar: </td><td>{VAR:comment}</td>
</tr>
<!-- END SUB: commented -->
<!-- SUB: void -->
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<!-- END SUB: void -->