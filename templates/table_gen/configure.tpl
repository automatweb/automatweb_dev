{VAR:menu}
<br>
<form action='reforb.{VAR:ext}' method=post>
<table border=0 bgcolor=#cccccc cellspacing=1 cellpadding=2>
<tr>
<td class="hele_hall_taust">Näita aliasi?</td>
<td class="hele_hall_taust"><input type="checkbox" name="aliases" value="1" {VAR:aliases}></td>
</tr>
<tr>
<td class="hele_hall_taust">Viimase muutmise kuupäev?</td>
<td class="hele_hall_taust"><input type="checkbox" name="last_changed" value="1" {VAR:last_changed}></td>
</tr>
<tr>
<td class="hele_hall_taust">{VAR:LC_TABLE_NAME}</td>
<td class="hele_hall_taust"><input type="text" name="table_name" value="{VAR:table_name}">
<input type='checkbox' name='show_title' VALUE=1 {VAR:show_title}>
</td>
</tr>
<tr>
<td class="hele_hall_taust">{VAR:LC_TABLE_TABLE_HEADER}</td>
<td class="hele_hall_taust"><textarea name="table_header" cols="50" rows="4">{VAR:table_header}</textarea></td>
</tr>
<tr>
<td class="hele_hall_taust">{VAR:LC_TABLE_TABLE_FOOTER}</td>
<td class="hele_hall_taust"><textarea name="table_footer" cols="50" rows="4">{VAR:table_footer}</textarea></td>
</tr>
<tr>
<td class="hele_hall_taust">{VAR:LC_TABLE_CHOOSE_TABLE_STYLE}:</td>
<td class="hele_hall_taust">
<select name='table_style'><option value=''>{VAR:tablestyle}</select>
</td>
</tr>
<tr>
<td class="hele_hall_taust">{VAR:LC_TABLE_CHOOSE_CELL_STYLE}:</td><td class="hele_hall_taust">
<select name='default_style'><option value=''>{VAR:defaultstyle}</select>
</td>
</tr>
<tr>
<td colspan="2" align="center" class="hele_hall_taust">
{VAR:reforb}
<input type="submit" value="Salvesta">
</td>
</tr>
</table>
</form>
