<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr class="aste01">
<td class="celltext" colspan=2><strong>Kalender&lt;-&gt;sündmus relatsioon</strong></td>
</tr>
<tr class="aste01">
<td class="celltext">Vorm või pärg:</td>
<td class="celltext"><select name="cal_id" {VAR:objects_disabled}>{VAR:target_objects}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">Seoseelement:</td>
<td class="celltext"><select name="el_relation" {VAR:relation_disabled}>{VAR:relation_els}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">Sündmuse alguse element:</td>
<td class="celltext"><select name="el_start" {VAR:start_disabled}>{VAR:start_els}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">Sündmuse lõpu element:</td>
<td class="celltext"><select name="el_end" {VAR:end_disabled}>{VAR:end_els}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">Sündmuste arvu element:</td>
<td class="celltext"><select name="el_cnt" {VAR:count_disabled}>{VAR:cnt_els}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">Vormitabel kalendris:</td>
<td class="celltext"><select name="ev_table" {VAR:tables_disabled}>{VAR:ev_tables}</select></td>
</tr>
<tr class="aste01">
<td class="celltext" colspan=2 align=center><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
  
