<a href='{VAR:view}'>Vaata</a>
<form method="POST" action="reforb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Nimi:</td>
	<td class="fcaption2" ><input type='text' name='name' value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2">Kommentaar:</td>
	<td class="fcaption2" ><textarea name='comment' rows=5 cols=30>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2">Kaubaartikkel:</td>
	<td class="fcaption2" ><select class='small_button' name='item'>{VAR:items}</select></td>
</tr>
<tr>
	<td class="fcaption2">Mitu tulpa:</td>
	<td class="fcaption2"><input type='text' name='num_cols' value='{VAR:num_cols}' class='small_button' size=3></td>
</tr>
<tr>
<td class="fcaption2" colspan=2>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">&nbsp;</td>
	<!-- SUB: H_EL -->
		<td class="fcaption2">{VAR:el_name}</td>
	<!-- END SUB: H_EL -->
	<td class="fcaption2">Kogus</td>
	<td class="fcaption2">J&auml;&auml;k</td>
	<td class="fcaption2">Parent men&uuml;&uuml;</td>
	<td class="fcaption2">Hind (keskmine)</td>
	<td class="fcaption2">Broneeritud</td>
	<td class="fcaption2">T&auml;ituvuse %</td>
	<td class="fcaption2">Summa</td>
	<td class="fcaption2">Nimi</td>
	<td class="fcaption2">Vaata</td>
	<td class="fcaption2">ID</td>
	<td class="fcaption2">Pealkiri</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fcaption2" align="center">{VAR:line_num}</td>
<!-- SUB: EL -->
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][{VAR:el_id}]' value='1' {VAR:checked}></td>
<!-- END SUB: EL -->
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][total]' value='1' {VAR:tot_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][used]' value='1' {VAR:used_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][parent]' value='1' {VAR:parent_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][price]' value='1' {VAR:price_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][bron]' value='1' {VAR:bron_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][f_percent]' value='1' {VAR:f_percent_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][money]' value='1' {VAR:money_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][name]' value='1' {VAR:name_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][view]' value='1' {VAR:view_checked}></td>
<td class="fcaption2" align="center"><input type='checkbox' name='els[{VAR:line_num}][i_id]' value='1' {VAR:i_id_checked}></td>
<td class="fcaption2" align="center"><input type='text' name='title[{VAR:line_num}]' value='{VAR:title}' class='small_button'></td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
<tr>
	<td class="fcaption2">Vali perioodi alguse element:</td>
	<td class="fcaption2"><select name='start_el'>{VAR:els}</select></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Salvesta"></td>
</tr>
</table>
{VAR:reforb}
</form>
