<script language='javascript'>

function varv(vrv) 
{
	document.forms[0].elements[2].value="#"+vrv;
} 

function varvivalik() 
{
  aken=window.open("/vv.html","varvivalik","HEIGHT=220,WIDTH=310")
 	aken.focus()
}
</script>

<form action='reforb.{VAR:ext}' METHOD=post>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="fcaption">Nimi:</td>
<td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td>
<td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">Taustav&auml;rv:</td>
<td class="fform"><input type='text' NAME='st[bgcolor]' VALUE='{VAR:bgcolor}'> <a href="#" onclick="varvivalik();">vali v&auml;rv</a></td>
</tr>
<tr>
<td class="fcaption">Serva laius:</td>
<td class="fform"><input type='text' NAME='st[border]' VALUE='{VAR:border}'></td>
</tr>
<tr>
<td class="fcaption">cellpadding:</td>
<td class="fform"><input type='text' NAME='st[cellpadding]' VALUE='{VAR:cellpadding}'></td>
</tr>
<tr>
<td class="fcaption">cellspacing:</td>
<td class="fform"><input type='text' NAME='st[cellspacing]' VALUE='{VAR:cellspacing}'></td>
</tr>
<tr>
<td class="fcaption">K&otilde;rgus:</td>
<td class="fform"><input type='text' NAME='st[height]' VALUE='{VAR:height}'></td>
</tr>
<tr>
<td class="fcaption">Laius:</td>
<td class="fform"><input type='text' NAME='st[width]' VALUE='{VAR:width}'></td>
</tr>
<tr>
<td class="fcaption">Hspace:</td>
<td class="fform"><input type='text' NAME='st[hspace]' VALUE='{VAR:hspace}'></td>
</tr>
<tr>
<td class="fcaption">Vspace:</td>
<td class="fform"><input type='text' NAME='st[vspace]' VALUE='{VAR:vspace}'></td>
</tr>
<tr>
<td class="fcaption">Esimese <input size=2 type='text' name='st[num_frows]' value='{VAR:num_frows}'> rea default stiil:</td>
<td class="fform"><select name='st[frow_style]'><option value=''>{VAR:frow_style}</select></td>
</tr>
<tr>
<td class="fcaption">Esimese <input size=2 type='text' name='st[num_fcols]' value='{VAR:num_fcols}'> tulba default stiil:</td>
<td class="fform"><select name='st[fcol_style]'><option value=''>{VAR:fcol_style}</select></td>
</tr>
<tr>
<td class="fcaption">Headeri stiil:</td>
<td class="fform"><select name='st[header_style]'><option value=''>{VAR:header_style}</select></td>
</tr>
<tr>
<td class="fcaption">Footeri stiil:</td>
<td class="fform"><select name='st[footer_style]'><option value=''>{VAR:footer_style}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input type="submit" VALUE='Salvesta' class='small_button'></td>
</tr>
</table>
{VAR:reforb}
</form>