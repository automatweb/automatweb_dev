<form action=reforb.{VAR:ext} method=post>
<input class='small_sub' type='submit' NAME='save' VALUE='{VAR:LC_FORMS_SAVE}!'>&nbsp;&nbsp;&nbsp;
<!-- SUB: PREVIEW -->
<a href='{VAR:preview}'>{VAR:LC_FORMS_PREVIEW}</a>
<!-- END SUB: PREVIEW -->
<table border=0>
<tr>
<td bgcolor=#d0d0d0>
<table border=0 bgcolor=#f0f0f0>
<tr>
<!-- SUB: DC -->
<td bgcolor=#ffffff align=left valign=bottom>
<table width=100% border=0>
<tr>
<!-- SUB: FIRST_C -->
<td align=left valign=bottom><a href='{VAR:add_col}'><img src='/images/add_col_first.gif' border=0></a></td>
<!-- END SUB: FIRST_C -->
<td align=right valign=bottom><a href='{VAR:del_col}'><img src='/images/del_col.gif' border=0></a></td>
<td align=right valign=bottom><a href='{VAR:add_col}'><img src='/images/add_col.gif' border=0></a></td>
</tr>
</table>
</td>
<!-- END SUB: DC -->
</tr>
<!-- SUB: LINE -->
<tr>
<!-- SUB: COL -->
<!-- col begins -->
<td bgcolor=#d0d0d0 colspan="{VAR:colspan}" rowspan="{VAR:rowspan}">
<table border=0 bgcolor=#ffffff height=100% width=100% hspace=0 vspace=0 cellpadding=2 cellspacing=0>
<tr>
<td rowspan={VAR:num_els_plus3}>&nbsp;
<!-- SUB: EXP_LEFT -->
<a href='{VAR:exp_left}'><img border=0 alt='{VAR:LC_FORMS_DELETE_LEFT_CELL}' src='{VAR:baseurl}/automatweb/images/left_r_arr.gif'></a>
<!-- END SUB: EXP_LEFT -->
</td>
<td colspan=2 align=center>&nbsp;
<!-- SUB: EXP_UP -->
<a href='{VAR:exp_up}'><img border=0 alt='{VAR:LC_FORMS_DELETE_UP_CELL}' src='{VAR:baseurl}/automatweb/images/up_r_arr.gif'></a>
<!-- END SUB: EXP_UP -->
</td>
<td rowspan={VAR:num_els_plus3}>&nbsp;
<!-- SUB: EXP_RIGHT -->
<a href='{VAR:exp_right}'><img border=0 alt='{VAR:LC_FORMS_DELETE_RIGHT_CELL}' src='{VAR:baseurl}/automatweb/images/right_r_arr.gif'></a>
<!-- END SUB: EXP_RIGHT -->
</td>
</tr>
<tr>
<td class="fgen_text" colspan=2><a href='{VAR:ch_cell}'>{VAR:LC_FORMS_CHANGE}</a> | <a href='{VAR:addel}'>{VAR:LC_FORMS_ADD_ELEMENT}</a></td>
</tr>
<!-- SUB: ELEMENT -->
<tr>
<td align=right class=fgen_text>{VAR:LC_FORMS_ELEMENT}:</td>
<td>{VAR:el_name}</td>
</tr>
<!-- END SUB: ELEMENT -->
<tr>
<td align=right class=fgen_text>{VAR:LC_FORMS_STYLE}:</td>
<td class=fgen_text>{VAR:style_name} <input type='checkbox' name='sel[{VAR:row}][{VAR:col}]' value='1'>
</td>
</tr>
<!-- SUB: SPLITS -->
<tr>
<td colspan=2 align="center">&nbsp;
<!-- SUB: SPLIT_VERTICAL -->
&nbsp;| <a href='{VAR:split_ver}'><img alt='{VAR:LC_FORMS_DEV_CELL_VERT}' src='/images/split_cell_left.gif' border=0></a>&nbsp;
<!-- END SUB: SPLIT_VERTICAL -->

<!-- SUB: SPLIT_HORIZONTAL -->
&nbsp;| <a href='{VAR:split_hor}'><img alt='{VAR:LC_FORMS_DEV_CELL_HOR}' src='/images/split_cell_down.gif' border=0></a>
<!-- END SUB: SPLIT_HORIZONTAL -->
</td>
</tr>
<!-- END SUB: SPLITS -->
<tr>
<td colspan=2 align=center>&nbsp;
<!-- SUB: EXP_DOWN -->
<a href='{VAR:exp_down}'><img border=0 alt='{VAR:LC_FORMS_DELETE_LOWER_CELL}' src='/images/down_r_arr.gif'></a>
<!-- END SUB: EXP_DOWN -->
</td></tr>
</table>
</td>
<!-- col ends -->
<!-- END SUB: COL -->
<td bgcolor=#ffffff valign=bottom align=left>
<table height=100% border=0 cellspacing=0 cellpadding=0 hspace=0 vspace=0>
<!-- SUB: FIRST_R -->
<tr><td valign=top><a href='{VAR:add_row}'><img src='/images/add_row_first.gif' BORDER=0></a></td></tr>
<!-- END SUB: FIRST_R -->
<tr><td valign=bottom><a href='{VAR:del_row}'><img src='/images/del_row.gif' BORDER=0></a></td></tr>
<tr><td valign=bottom><a href='{VAR:add_row}'><img src='/images/add_row.gif' BORDER=0></a></td></tr>
</table>
</td>
</tr>
<!-- END SUB: LINE -->
</table></td></tr></table>
<select name='selstyle' >{VAR:styles}</select><br>
<input class='small_sub' type='submit' NAME='save' VALUE='{VAR:LC_FORMS_SAVE}!'>
{VAR:reforb}
</form>
<form action='reforb.{VAR:ext}' method=POST>
<input type='submit' class='small_sub' value='{VAR:LC_FORMS_ADD}'> <input type='text' name='nrows' size=3 class='small_button'> {VAR:LC_FORMS_ROW_ROW} 
{VAR:addr_reforb}
</form>
<form action='reforb.{VAR:ext}' method=POST>
<input type='submit' class='small_sub' value='{VAR:LC_FORMS_ADD}'> <input type='text' name='ncols' size=3 class='small_button'> {VAR:LC_FORMS_COLUMN} 
{VAR:addc_reforb}
</form>
