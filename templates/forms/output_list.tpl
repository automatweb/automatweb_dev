<br>
<br>
<table border=0 cellspacing=1 bgcolor=#cccccc cellpadding=2>
<tr>
<td class=title>Nimi</td>
<td class=title>Kommentaar</td>
<td class=title colspan=2 align=center>Tegevus</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class=plain>{VAR:name}</td>
<td class=plain>{VAR:comment}</td>
<td class=plain>
<!-- SUB: CHANGE_OP -->
<a href='{VAR:change}'>Muuda</a>
<!-- END SUB: CHANGE_OP -->
&nbsp;</td>
<td class=plain>
<!-- SUB: DELETE_OP -->
<a href="javascript:box2('Oled kindel, et soovid seda v&auml;ljundit kustutada?','{VAR:delete}')">Kustuta</a>
<!-- END SUB: DELETE_OP -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class=plain colspan=4 align=center>
<!-- SUB: ADD_OP -->
<a href='{VAR:add}'>Lisa</a>
<!-- END SUB: ADD_OP -->
</td>
</tr>
</table>
