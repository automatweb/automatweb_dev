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
