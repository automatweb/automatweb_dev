<form action=reforb.{VAR:ext} method=post>
<input class='small_sub' type='submit' NAME='save' VALUE='Salvesta!'>&nbsp;&nbsp;&nbsp;
<!-- SUB: PREVIEW -->
<a href='{VAR:preview}'>Eelvaade</a>
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
<a href='{VAR:exp_left}'><img border=0 alt='Kustuta vasak cell' src='{VAR:baseurl}/automatweb/images/left_r_arr.gif'></a>
<!-- END SUB: EXP_LEFT -->
</td>
<td colspan=2 align=center>&nbsp;
<!-- SUB: EXP_UP -->
<a href='{VAR:exp_up}'><img border=0 alt='Kustuta &uuml;lemine  cell' src='{VAR:baseurl}/automatweb/images/up_r_arr.gif'></a>
<!-- END SUB: EXP_UP -->
</td>
<td rowspan={VAR:num_els_plus3}>&nbsp;
<!-- SUB: EXP_RIGHT -->
<a href='{VAR:exp_right}'><img border=0 alt='Kustuta parem cell' src='{VAR:baseurl}/automatweb/images/right_r_arr.gif'></a>
<!-- END SUB: EXP_RIGHT -->
</td>
</tr>
<!-- SUB: ELEMENT -->
<tr>
<td align=right class=fgen_text>Element:</td>
<td><select class='small_button' name='elsel_{VAR:element_id}'>{VAR:elsel}</select></td>
</tr>
<!-- END SUB: ELEMENT -->
<tr>
<td align=right class=fgen_text>Stiil:</td>
<td class=fgen_text><select class='small_button' name='stylesel_{VAR:cell_id}'><option value=''>{VAR:stylesel}</select><br>
<a href='{VAR:ch_cell}'>Muuda</a>
</td>
</tr>
<!-- SUB: SPLITS -->
<tr>
<td colspan=2 align="center">&nbsp;
<!-- SUB: SPLIT_VERTICAL -->
&nbsp;| <a href='{VAR:split_ver}'><img alt='Jaga cell pooleks vertikaalselt' src='/images/split_cell_left.gif' border=0></a>&nbsp;
<!-- END SUB: SPLIT_VERTICAL -->

<!-- SUB: SPLIT_HORIZONTAL -->
&nbsp;| <a href='{VAR:split_hor}'><img alt='Jaga cell pooleks horisontaalselt' src='/images/split_cell_down.gif' border=0></a>
<!-- END SUB: SPLIT_HORIZONTAL -->
</td>
</tr>
<!-- END SUB: SPLITS -->
<tr>
<td colspan=2 align=center>&nbsp;
<!-- SUB: EXP_DOWN -->
<a href='{VAR:exp_down}'><img border=0 alt='Kustuta alumine cell' src='/images/down_r_arr.gif'></a>
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
<input class='small_sub' type='submit' NAME='save' VALUE='Salvesta!'>
{VAR:reforb}
</form>
