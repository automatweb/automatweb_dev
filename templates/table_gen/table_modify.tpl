{VAR:menu}
<br>
<form action='reforb.{VAR:ext}' method=post>
<input type='submit' NAME='save_table' VALUE='{VAR:LC_TABLE_SAVE}'>
<table border=1 bgcolor=#cccccc cellspacing=2 cellpadding=2>
<!-- SUB: extdata -->
<tr>
<td colspan=1 class="fgtitle">Aliased:</td>
<td colspan=101 class="fgtitle">
{VAR:extdata}
</td>
</tr>
<!-- END SUB: extdata -->

<!-- SUB: LINE -->
<tr>
<!-- SUB: COL -->
<td bgcolor=#dddddd colspan={VAR:colspan} rowspan={VAR:rowspan}>
<!-- SUB: H_HEADER-->
<b>{VAR:text}</b>
<!-- END SUB: H_HEADER-->
<!-- SUB: AREA -->
<textarea class='small_button' name="text[{VAR:row}][{VAR:col}]" cols="{VAR:num_cols}" rows="{VAR:num_rows}">{VAR:text}</textarea>
<!-- END SUB: AREA -->
<!-- SUB: BOX -->
<input type='text' class='small_button' SIZE='{VAR:num_cols}' NAME='text[{VAR:row}][{VAR:col}]' VALUE="{VAR:text}">
<!-- END SUB: BOX -->
</td>
<!-- END SUB: COL -->
</tr>
<!-- END SUB: LINE -->

</table>
<input type='submit' NAME='save_table' VALUE='{VAR:LC_TABLE_SAVE}'>
{VAR:reforb}
</form>
<!-- SUB: aliases -->
<iframe width="100%" height="800" frameborder="0" src="{VAR:aliasmgr_link}">
</iframe>
<!-- END SUB: aliases -->
