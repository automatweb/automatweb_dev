














<table border=1 cellspacing=1 cellpadding=2>
	<!-- SUB: LINE -->
	<tr>
		<!-- SUB: COL -->
		<td bgcolor=#FFFFFF rowspan={VAR:rowspan} colspan={VAR:colspan} class="celltext">
			<input type="text" class="formtext" size="{VAR:ta_cols}" name='aliases[{VAR:row}][{VAR:col}]' value="{VAR:content}">
		</td>
		<!-- END SUB: COL -->

		<!-- SUB: COL_TA -->
		<td bgcolor=#FFFFFF rowspan={VAR:rowspan} colspan={VAR:colspan} class="celltext">
			<textarea class="formtext" rows="{VAR:ta_rows}" cols="{VAR:ta_cols}" name='aliases[{VAR:row}][{VAR:col}]'>{VAR:content}</textarea>
		</td>
		<!-- END SUB: COL -->
	</tr>
	<!-- END SUB: LINE -->
</table>
