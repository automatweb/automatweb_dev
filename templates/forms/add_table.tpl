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
<td class="fform">Vali formid, millest elemente v&otilde;etakse:</td><td class="fform"><select name='forms[]' multiple>{VAR:forms}</select></td>
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
<td class="fform">Tulba pealkiri</td>
<td class="fform">Sorditav?</td>
</tr>

<!-- SUB: ROW -->
<tr>
<td class="fform">{VAR:column}</td>

<!-- SUB: COL -->
<td align="center" class="fform"><input type='radio' name='columns[{VAR:column}]' value='{VAR:el_id}' {VAR:checked}></td>
<!-- END SUB: COL -->

<td align="center" class="fform"><input type='radio' name='columns[{VAR:column}]' value='change' {VAR:change_checked}></td>
<td align="center" class="fform"><input type='radio' name='columns[{VAR:column}]' value='view' {VAR:view_checked}></td>
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
