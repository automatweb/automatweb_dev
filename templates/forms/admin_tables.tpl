<br>
<font size="+2" color="red">{VAR:status_msg}</font>
<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform" colspan="2"><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
<tr>
<td class="fform" colspan="2"><b>Tabelite konfiguratsiooni ei salvestata kui leitakse m6ni viga, <br>niiet enne kui siit lehelt &auml;ra l&auml;hed ole kindel<br> et muudatused on salvestatud!</b>
</td>
</tr>
<tr>
<td class="fform" align="right" width="50%">Kas salvestada formi andmed m6nda teise tabelisse:</td>
<td class="fform" width="50%"><input type='checkbox' NAME='save_table' VALUE='1' {VAR:save_table}></td>
</tr>
<tr>
<td colspan="2" class="fform">Vali tabelid, millesse saab andmeid salvestada:</td>
</tr>
<tr>
<td colspan="2" class="fform"><select class="small_button" name="tables[]" size=20 multiple>{VAR:tables}</select></td>
</tr>
<!-- SUB: SOME_TABLES -->
<tr>
	<td class="fform" width="50%" align="right">Kuhu tehakse objekt:</td>
	<td class="fform" width="50%"><select name='objs_where' class='small_button'>{VAR:objs_sel}</select></td>
</tr>
<!-- SUB: OBJ_SEL -->
<tr>
	<td class="fform" width="50%" align="right">Mis tulbas on objekti id:</td>
	<td class="fform" width="50%"><select name='obj_column' class='small_button'>{VAR:obj_column}</select></td>
</tr>
<!-- END SUB: OBJ_SEL -->

<!-- END SUB: SOME_TABLES -->

<!-- SUB: TABLE -->
<tr>
	<td class="fform" colspan="2">
		<br><br>
		<table bgcolor="#CCCCCC" cellpadding="3" cellspacing="1" border="0" width="100%">
			<tr>
				<td class="fform" width="50%" align="right">Tabel:</td>
				<td class="fform" width="50%">{VAR:table_name}</td>
			</tr>
			<tr>
				<td class="fform" width="50%" align="right">Unikaalne tulp tabelis:</td>
				<td class="fform" width="50%"><select name="indexes[{VAR:table_name}]">{VAR:cols}</select></td>
			</tr>
			<!-- SUB: HAS_OTHER_TABLES -->
			<tr>
				<td class="fform" width="50%" align="right">Vali tabelid millega see seotud on:</td>
				<td class="fform" width="50%"><select class='small_button' name='relations[{VAR:table_name}][]' multiple>{VAR:rel_tbls}</select></td>
			</tr>
			<!-- SUB: REL_TABLE -->
			<tr>
				<td class="fform" colspan="2" align="center">Tulp <select class='small_button' name='rel_cols[{VAR:table_name}][{VAR:foreign_table}][from]'>{VAR:rel_f_cols}</select> selles tabelis on relatsioonis tulbaga <select class='small_button' name='rel_cols[{VAR:table_name}][{VAR:foreign_table}][to]'>{VAR:rel_t_cols}</select> tabelist {VAR:foreign_table}</td>
			</tr>
			<!-- END SUB: REL_TABLE -->

			<!-- END SUB: HAS_OTHER_TABLES -->
		</table>
	</td>
</tr>
<!-- END SUB: TABLE -->
<tr>
<td class="fform" colspan="2"><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
