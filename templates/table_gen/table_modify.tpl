<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#a0a0a0><a href='{VAR:change}'>{VAR:LC_TABLE_CHANGE}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:styles}'>{VAR:LC_TABLE_EDIT}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:admin}'>{VAR:LC_TABLE_ADMIN}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:view}'>{VAR:LC_TABLE_PREVIEW}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:import}'>{VAR:LC_TABLE_IMPORT}</a></td>
		<!-- SUB: ALIAS_LINK -->
		<td bgcolor=#f0f0f0><a href='{VAR:url}'>{VAR:title}</a></td>
		<!-- END SUB: ALIAS_LINK -->
		<td bgcolor=#f0f0f0><a href='{VAR:addstyle}'>{VAR:LC_TABLE_ADD_STYLE}</a></td>
	</tr>
</table>
<br>
<form action='reforb.{VAR:ext}' method=post>
<input type='submit' NAME='save_table' VALUE='{VAR:LC_TABLE_SAVE}'>
<table border=1 bgcolor=#cccccc cellspacing=2 cellpadding=2>
<tr>
<td bgcolor=#dddddd>{VAR:LC_TABLE_TABLE_NAME}:</td><td colspan=100><input type='text' name='table_name' VALUE='{VAR:table_name}'><input type='checkbox' name='show_title' VALUE=1 {VAR:show_title}></td>
</tr>
<tr>
<td bgcolor=#dddddd>{VAR:LC_TABLE_TABLE_HEADER}:</td><td colspan=100><textarea name='table_header' cols=50 rows=4>{VAR:table_header}</textarea></td>
</tr>
<!-- SUB: LINE -->
<tr>
<!-- SUB: COL -->
<td bgcolor=#dddddd colspan={VAR:colspan} rowspan={VAR:rowspan}>
<!-- SUB: AREA -->
<textarea class='small_button' name="text[{VAR:row}][{VAR:col}]" cols="{VAR:num_cols}" rows="{VAR:num_rows}">{VAR:text}</textarea>
<!-- END SUB: AREA -->
<!-- SUB: BOX -->
<input type='text' class='small_button' SIZE='{VAR:num_cols}' NAME='text[{VAR:row}][{VAR:col}]' VALUE='{VAR:text}'>
<!-- END SUB: BOX -->
</td>
<!-- END SUB: COL -->
</tr>
<!-- END SUB: LINE -->
<tr>
<td>{VAR:LC_TABLE_TABLE_FOOTER}:</td><td colspan=100><textarea name='table_footer' cols=50 rows=4>{VAR:table_footer}</textarea></td>
</table>
<input type='submit' NAME='save_table' VALUE='{VAR:LC_TABLE_SAVE}'>
{VAR:reforb}
</form>
