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
<td class="fcaption">{VAR:LC_STYLE_NAME}:</td>
<td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_STYLE_COMMENT}:</td>
<td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_STYLE_BACK_COLOR}:</td>
<td class="fform"><input type='text' NAME='st[bgcolor]' VALUE='{VAR:bgcolor}'> <a href="#" onclick="varvivalik();">{VAR:LC_STYLE_CHOOSE_COLOR}</a></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_STYLE_EDGE_WIDTH}:</td>
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
<td class="fcaption">{VAR:LC_STYLE_HEIGHT}:</td>
<td class="fform"><input type='text' NAME='st[height]' VALUE='{VAR:height}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_STYLE_WITHD}:</td>
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
<td class="fcaption">{VAR:LC_STYLE_FIRST} <input size=2 type='text' name='st[num_frows]' value='{VAR:num_frows}'> {VAR:LC_STYLE_ROW_DEFAULT}:</td>
<td class="fform"><select name='st[frow_style]'><option value=''>{VAR:frow_style}</select></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_STYLE_FIRST} <input size=2 type='text' name='st[num_fcols]' value='{VAR:num_fcols}'> {VAR:LC_STYLE_COL_DEFAULT}:</td>
<td class="fform"><select name='st[fcol_style]'><option value=''>{VAR:fcol_style}</select></td>
</tr>
<tr>
<td class="fcaption">Header {VAR:LC_STYLE_STYLE}:</td>
<td class="fform"><select name='st[header_style]'><option value=''>{VAR:header_style}</select></td>
</tr>
<tr>
<td class="fcaption">Footer {VAR:LC_STYLE_STYLE}:</td>
<td class="fform"><select name='st[footer_style]'><option value=''>{VAR:footer_style}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input type="submit" VALUE='{VAR:LC_STYLE_SAVE}' class='small_button'></td>
</tr>
</table>
{VAR:reforb}
</form>