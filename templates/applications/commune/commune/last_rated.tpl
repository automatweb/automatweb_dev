<hr size="1" width="100%">
<table class="profile" width="100%">
	<tr>
	<td colspan="3">
	viimati hinnatud pilt:
	</td>
	</tr>
	<tr>
		<td rowspan="{VAR:rows}" class="rate_rowbgcolor_odd" style="width:20%;text-align:center"><a href="{VAR:rate_url}">{VAR:image}</a></td>
		<td class="rate_rowbgcolor_even" style="width:20%;text-align:center">Pildi allkiri: </td>
		<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:title}</td>
	</tr>
	<tr>
		<td class="rate_rowbgcolor_odd" style="width:20%;text-align:center">Profiil: </td>
		<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:name}</td>
	</tr>
		<td class="rate_rowbgcolor_even" style="width:20%;text-align:center">Keskmine hinne: </td>
		<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:rating}</td>
	</tr>
	{VAR:type}
</table>

<!-- SUB: rated -->
<tr>
	<td class="rate_rowbgcolor_odd" style="width:20%;text-align:center">Antud hinne: </td>
	<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:mark}</td>
</tr>
<!-- END SUB: rated -->
<!-- SUB: commented -->
<tr>
	<td class="rate_rowbgcolor_odd" style="width:20%;text-align:center">Lisatud kommentaar: </td>
	<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:comment}</td>
</tr>
<!-- END SUB: commented -->
<!-- SUB: void -->
<tr>
	<td colspan="2" class="rate_rowbgcolor_odd">&nbsp;</td>
</tr>
<!-- END SUB: void -->