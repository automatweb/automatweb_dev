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
<td class="fcaption">Nimi:</td>
<td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td>
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
		<td class="fcaption">Fondi suurus:</td>
		<td class="fform">
			<select NAME='st[fontsize]'>{VAR:fontsize}</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">V&auml;rv</td>
		<td class="fform"><input type="text" name="st[color]" VALUE='{VAR:color}'> <a href="#" onclick="varvivalik('color');">vali v&auml;rv</a></td>
	</tr>
	<tr>
		<td class="fcaption">Tausta v&auml;rv:</td>
		<td class="fform"><input type="text" name="st[bgcolor]" VALUE='{VAR:bgcolor}'> <a href="#" onclick="varvivalik('bgcolor');">vali v&auml;rv</a></td>
	</tr>
	<tr>
		<td class="fcaption">Fondi stiil:</td>
		<td class="fform">
			<select NAME='st[fontstyle]'>{VAR:fontstyles}</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Align:</td>
		<td class="fform"><input type="radio" name="st[align]" VALUE='left' {VAR:align_left}>Vasak <input type="radio" name="st[align]" VALUE='center' {VAR:align_center}>Keskel <input type="radio" name="st[align]" VALUE='right' {VAR:align_right}>Parem</td>
	</tr>
	<tr>
		<td class="fcaption">Valign:</td>
		<td class="fform"><input type="radio" name="st[valign]" VALUE='top' {VAR:valign_top}>&Uuml;leval <input type="radio" name="st[valign]" VALUE='center' {VAR:valign_center}>Keskel <input type="radio" name="st[valign]" VALUE='bottom' {VAR:valign_bottom}>All</td>
	</tr>
	<tr>
		<td class="fcaption">K&otilde;rgus:</td>
		<td class="fform"><input type="text" name="st[height]" VALUE='{VAR:height}'></td>
	</tr>
	<tr>
		<td class="fcaption">Laius:</td>
		<td class="fform"><input type="text" name="st[width]" VALUE='{VAR:width}'></td>
	</tr>
	<tr>
		<td class="fcaption">Nowrap:</td>
		<td class="fform"><input type="checkbox" name="st[nowrap]" VALUE=1 {VAR:nowrap}></td>
	</tr>
	<tr>
		<td class="fform" colspan="2">
			<input type="submit" value="Salvesta">
			{VAR:reforb}
		</td>
	</tr>
</table>
</form>
								