<script language='javascript'>

el = "color";

function varv(vrv) 
{
	if (el == "color")
		document.forms[0].elements[6].value="#"+vrv;
	else
	if (el == "bgcolor")
		document.forms[0].elements[7].value="#"+vrv;
} 

function varvivalik(milline) 
{
	el = milline;
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
		<td class="fcaption">Font:</td>
		<td class="fform">
			<select NAME='st[font1]'>{VAR:font1}</select>
			<select NAME='st[font2]'>{VAR:font2}</select>
			<select NAME='st[font3]'>{VAR:font3}</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_FONT_SIZE}:</td>
		<td class="fform">
			<select NAME='st[fontsize]'>{VAR:fontsize}</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_COLOR}</td>
		<td class="fform"><input type="text" name="st[color]" VALUE='{VAR:color}'> <a href="#" onclick="varvivalik('color');">{VAR:LC_STYLE_CHOOSE_COLOR}</a></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_BACK_COLOR}:</td>
		<td class="fform"><input type="text" name="st[bgcolor]" VALUE='{VAR:bgcolor}'> <a href="#" onclick="varvivalik('bgcolor');">{VAR:LC_STYLE_CHOOSE_COLOR}</a></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_FONT_STYLE}:</td>
		<td class="fform">
			<select NAME='st[fontstyle]'>{VAR:fontstyles}</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Align:</td>
		<td class="fform"><input type="radio" name="st[align]" VALUE='left' {VAR:align_left}>{VAR:LC_STYLE_LEFT} <input type="radio" name="st[align]" VALUE='center' {VAR:align_center}>{VAR:LC_STYLE_MIDDLE} <input type="radio" name="st[align]" VALUE='right' {VAR:align_right}>{VAR:LC_STYLE_RIGHT}</td>
	</tr>
	<tr>
		<td class="fcaption">Valign:</td>
		<td class="fform"><input type="radio" name="st[valign]" VALUE='top' {VAR:valign_top}>{VAR:LC_STYLE_UP} <input type="radio" name="st[valign]" VALUE='center' {VAR:valign_center}>{VAR:LC_STYLE_MIDDLE} <input type="radio" name="st[valign]" VALUE='bottom' {VAR:valign_bottom}>{VAR:LC_STYLE_DOWN}</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_HEIGHT}:</td>
		<td class="fform"><input type="text" name="st[height]" VALUE='{VAR:height}'></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_WITHD}:</td>
		<td class="fform"><input type="text" name="st[width]" VALUE='{VAR:width}'></td>
	</tr>
	<tr>
		<td class="fcaption">Nowrap:</td>
		<td class="fform"><input type="checkbox" name="st[nowrap]" VALUE=1 {VAR:nowrap}></td>
	</tr>
	<tr>
		<td class="fform" colspan="2">
			<input type="submit" value="{VAR:LC_STYLE_SAVE}">
			{VAR:reforb}
		</td>
	</tr>
</table>
</form>
								