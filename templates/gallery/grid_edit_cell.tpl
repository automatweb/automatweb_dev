		<table border=0 cellpadding=1 cellspacing=1>
			<!-- SUB: HAS_IMG -->
			<tr>
				<td colspan=2 align=center class="celltext"><img src='{VAR:imgurl}'><input type='checkbox' name='erase[{VAR:page}][{VAR:row}][{VAR:col}]' value=1>Kustuta pilt</td>
			</tr>
			<!-- END SUB: HAS_IMG -->

			<!-- SUB: BIG -->
			<tr>
				<td colspan=2 align=center class="celltext"><a href='{VAR:bigurl}'>Suur pilt</a></td>
			</tr>
			<!-- END SUB: BIG -->
			<tr>
				<td align=right class="celltext">Tekst:</td>
				<td><input type='text' NAME='g[{VAR:page}][{VAR:row}][{VAR:col}][caption]' VALUE='{VAR:caption}' size="28" class="formtext"></td>
			</tr>
			<tr>
				<td align=right class="celltext">Kuup&auml;ev:</td>
				<td><input type='text' NAME='g[{VAR:page}][{VAR:row}][{VAR:col}][date]' VALUE='{VAR:date}' size=10 class="formtext"></td>
			</tr>
			<tr>
				<td align=right class="celltext">V&auml;ike pilt:</td>
				<td><input type='file' NAME='g_{VAR:page}_{VAR:row}_{VAR:col}_tn' class="formfile"></td>
			</tr>
			<tr>
				<td align=right class="celltext">Tekstilink:</td>
				<td><input type="checkbox" name="g[{VAR:page}][{VAR:row}][{VAR:col}][has_textlink]" {VAR:has_textlink}><input type='text' NAME='g[{VAR:page}][{VAR:row}][{VAR:col}][textlink]' class="formtext" value="{VAR:textlink}" size="26"></td>
			</tr>
			<tr>
				<td align=right class="celltext">Pilt:</td>
				<td><input type='file' NAME='g_{VAR:page}_{VAR:row}_{VAR:col}_img' class="formfile"></td>
			</tr>
			<tr>
				<td align=right class="celltext">Jrk:</td>
				<td><input type='text' NAME='g[{VAR:page}][{VAR:row}][{VAR:col}][ord]' class="formtext" size="3" value='{VAR:ord}'></td>
			</tr>
			<!-- SUB: IS_AUTOMATIC_GAL -->
			<tr>
				<td align=right class="celltext">Link:</td>
				<td><input type='text' NAME='g[{VAR:page}][{VAR:row}][{VAR:col}][glink]' class="formtext" value='{VAR:glink}'></td>
			</tr>
			<!-- END SUB: IS_AUTOMATIC_GAL -->
		</table>
