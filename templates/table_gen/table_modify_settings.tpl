<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0><a href='tables.{VAR:ext}?type=change_table&id={VAR:table_id}'>Toimeta</a></td>
		<td bgcolor=#a0a0a0><a href='tables.{VAR:ext}?type=settings&id={VAR:table_id}'>M&auml;&auml;rangud</a></td>
		<td bgcolor=#f0f0f0><a href='tables.{VAR:ext}?type=change_styles&parent={VAR:table_id}'>Stiiliraamat</a></td>
		<td bgcolor=#f0f0f0><a href='tables.{VAR:ext}?type=show_table&id={VAR:table_id}'>Eelvaade</a></td>
		<td bgcolor=#f0f0f0><a href='tables.{VAR:ext}?type=image_list&parent={VAR:table_id}'>Pildid</a></td>
		<td bgcolor=#f0f0f0><a href='tables.{VAR:ext}?type=delete_table&id={VAR:table_id}'>Kustuta</a></td>
	</tr>
</table>

<script language='javascript'>

function varv(vrv) 
{
	document.forms[0].bgcolor.value="#"+vrv;
} 

function varvivalik() 
{
  aken=window.open("/vv.html","varvivalik","HEIGHT=220,WIDTH=310")
 	aken.focus()
}
</script>

<form action='refcheck.{VAR:ext}' method=post ENCTYPE="multipart/form-data">
<table border=0 cellspacing=1 cellpadding=2>
<tr>
<td class="fcaption">Nimi:</td>
<td class="fform"><input type='text' NAME='name' VALUE='{VAR:table_name}'></td>
</tr>
<tr>
<td class="fcaption">Comment:</td>
<td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:table_comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">Taustav&auml;rv:</td>
<td class="fform"><input type='text' NAME='bgcolor' VALUE='{VAR:table_bgcolor}'> <a href="#" onclick="varvivalik();">vali v&auml;rv</a></td>
</tr>
<tr>
<td class="fcaption">Serva laius:</td>
<td class="fform"><input type='text' NAME='border' VALUE='{VAR:table_border}'></td>
</tr>
<tr>
<td class="fcaption">cellpadding:</td>
<td class="fform"><input type='text' NAME='cellpadding' VALUE='{VAR:table_cellpadding}'></td>
</tr>
<tr>
<td class="fcaption">cellspacing:</td>
<td class="fform"><input type='text' NAME='cellspacing' VALUE='{VAR:table_cellspacing}'></td>
</tr>
<tr>
<td class="fcaption">K&otilde;rgus:</td>
<td class="fform"><input type='text' NAME='height' VALUE='{VAR:table_height}'></td>
</tr>
<tr>
<td class="fcaption">Hspace:</td>
<td class="fform"><input type='text' NAME='hspace' VALUE='{VAR:table_hspace}'></td>
</tr>
<tr>
<td class="fcaption">Vspace:</td>
<td class="fform"><input type='text' NAME='vspace' VALUE='{VAR:table_vspace}'></td>
</tr>
<tr>
<td class="fcaption">Default stiil:</td>
<td class="fform"><select NAME='def_style'>	
<!-- SUB: STYLE_LINE -->
	<option VALUE='{VAR:def_style_value}' {VAR:def_style_selected}>{VAR:def_style_text}
<!-- END SUB: STYLE_LINE -->
</select>
</td>
</tr>
</table>

<input type='submit' NAME='save_table_settings' VALUE='Salvesta tabel'>
<input type='hidden' NAME='action' VALUE='admin_table'>
<input type='hidden' NAME='id' VALUE='{VAR:table_id}'>
</form>
