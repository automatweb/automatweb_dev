<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fform">Kommentaar:</td><td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fform">Mitu tulpa:</td><td class="fform"><input type='text' NAME='num_cols' VALUE='{VAR:num_cols}' size=3></td>
</tr>
<tr>
<td class="fform">Tabeli stiil:</td><td class="fform"><select name='tablestyle'>{VAR:tablestyles}</select></td>
</tr>
<tr>
<td class="fform">Pealkirja stiil tavaline:</td><td class="fform"><select name='header_normal'>{VAR:header_normal}</select></td>
</tr>
<tr>
<td class="fform">Pealkirja stiil sorditav:</td><td class="fform"><select name='header_sortable'>{VAR:header_sortable}</select></td>
</tr>
<tr>
<td class="fform">Pealkirja stiil sorditud:</td><td class="fform"><select name='header_sorted'>{VAR:header_sorted}</select></td>
</tr>
<tr>
<td class="fform">Celli stiil 1:</td><td class="fform"><select name='content_style1'>{VAR:content_style1}</select></td>
</tr>
<tr>
<td class="fform">Celli stiil 2:</td><td class="fform"><select name='content_style2'>{VAR:content_style2}</select></td>
</tr>
<tr>
<td class="fform">Sorditud celli stiil 1:</td><td class="fform"><select name='content_sorted_style1'>{VAR:content_sorted_style1}</select></td>
</tr>
<tr>
<td class="fform">Sorditud celli stiil 2:</td><td class="fform"><select name='content_sorted_style2'>{VAR:content_sorted_style2}</select></td>
</tr>
<tr>
<td class="fform">Vali formid, millest elemente v&otilde;etakse:</td><td class="fform"><select class='small_button' name='forms[]' multiple size=7>{VAR:forms}</select></td>
</tr>
<tr>
<td class="fform">Vali kataloogid, kuhu saab sisestusi liigutada:</td><td class="fform"><select class='small_button' name='moveto[]' size=10 multiple>{VAR:moveto}</select></td>
</tr>
<tr>
<td class="fform">Submit nupp</td><td class="fform">Tekst: <input type='text' name='submit_text' value='{VAR:submit_text}'> Jrk: <input type='text' class='small_button' size=3 value='{VAR:submit_jrk}'> &uuml;leval <input type='checkbox' name='submit_top' value='1' {VAR:top_checked}>  all  <input type='checkbox' name='submit_bottom' value='1' {VAR:bottom_checked}> </td>
</tr>
<tr>
<td class="fform">Lisa nupp:</td><td class="fform">Tekst: <input type='text' name='user_button_text' value='{VAR:user_button_text}'> Jrk: <input type='text' class='small_button' size=3 value='{VAR:but_jrk}'> &uuml;leval <input type='checkbox' name='user_button_top' value='1' {VAR:user_button_top}>  all <input type='checkbox' name='user_button_bottom' value='1' {VAR:user_button_bottom}>  &nbsp;Aadress:<input type='text' name='user_button_url' value='{VAR:user_button_url}'> </td>
</tr>
<!-- SUB: CHANGE -->
<tr>
<td class="fform" colspan=2>Vali mis tulbas mis element paikneb:</td>
</tr>
<tr>
<td class="fform" colspan=2>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Tulp</td>
<!-- SUB: TITLE -->
<td class="fform">{VAR:el_name}</td>
<!-- END SUB: TITLE -->
<td class="fform">Muutmine</td>
<td class="fform">Vaatamine</td>
<td class="fform">Special</td>
<td class="fform">Kustuta</td>
<td class="fform">Loodud</td>
<td class="fform">Muudetud</td>
<td class="fform">UID</td>
<td class="fform">Aktiivsus</td>
<td class="fform">Asukoha muutmine</td>
<td class="fform">Tulba pealkiri</td>
<td class="fform">Sorditav?</td>
</tr>

<!-- SUB: ROW -->
<tr>
<td class="fform">{VAR:column}</td>

<!-- SUB: COL -->
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='{VAR:el_id}' {VAR:checked}></td>
<!-- END SUB: COL -->

<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='change' {VAR:change_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='view' {VAR:view_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='special' {VAR:special_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='delete' {VAR:delete_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='created' {VAR:created_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='modified' {VAR:modified_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='uid' {VAR:uid_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='active' {VAR:active_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='chpos' {VAR:chpos_checked}></td>
<td class="fform"><input type='text' class='small_button' name='names[{VAR:column}]' VALUE='{VAR:c_name}'></td>
<td class="fform" align="center"><input type='checkbox' name='sortable[{VAR:column}]' VALUE='1' {VAR:sortable}></td>
</tr>
<!-- END SUB: ROW -->
</table>
</td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
{VAR:reforb}
</form>
