<form action=reforb.{VAR:ext} method=post>
<input class='small_sub' type='submit' NAME='save' VALUE='Salvesta!'>
<table border=0>
<tr>
<td bgcolor=#d0d0d0>
<table border=0 bgcolor=#f0f0f0>
<tr>
<!-- SUB: DC -->
<td bgcolor=#f0f0f0 align=left valign=bottom>
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
<td bgcolor=#d0d0d0 colspan={VAR:colspan} rowspan={VAR:rowspan}>
<table border=0 bgcolor=#EEEEEE height=100% width=100% hspace=0 vspace=0 cellpadding=2 cellspacing=0>
<tr>
<td rowspan={VAR:num_els_plus3}><a href='{VAR:exp_left}'><img border=0 alt='Kustuta vasak cell' src='/images/left_r_arr.gif'></a></td>
<td colspan=2 align=center><a href='{VAR:exp_up}'><img border=0 alt='Kustuta &uuml;lemine  cell' src='/images/up_r_arr.gif'></a></td>
<td rowspan={VAR:num_els_plus3}><a href='{VAR:exp_right}'><img border=0 alt='Kustuta parem cell' src='/images/right_r_arr.gif'></td>
</tr>
<!-- SUB: ELEMENT -->
<!-- element starts -->
<tr>
<td align=right class=plain>Element:</td>
<td><select class='small_button' name='elsel_{VAR:element_id}'>
<!-- SUB: ELSEL -->
<option VALUE='{VAR:el_id}' {VAR:el_selected}>{VAR:el_name}
<!-- END SUB: ELSEL -->
</select></td>
</tr>
<!-- element ends -->
<!-- END SUB: ELEMENT -->
<tr>
<td align=right class=plain>Stiil:</td>
<td><select class='small_button' name='stylesel_{VAR:cell_id}'><option value=''>{VAR:stylesel}</select></td>
</tr>
<tr>
<td colspan=2 align=center><a href='{VAR:exp_down}'><img border=0 alt='Kustuta alumine cell' src='/images/down_r_arr.gif'></a></td></tr>
</table>
</td>
<!-- col ends -->
<!-- END SUB: COL -->
<td bgcolor=#f0f0f0 valign=bottom align=left>
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
<input class='small_sub' type='submit' NAME='save' VALUE='Salvesta!'>
{VAR:reforb}
</form>
