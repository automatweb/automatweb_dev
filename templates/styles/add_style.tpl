<script language='javascript'>

el = "colour";

function varv(vrv) 
{
	if (el == "colour")
		document.forms[0].colour.value="#"+vrv;
	else
	if (el == "bgcolour")
		document.forms[0].bgcolour.value="#"+vrv;
} 

function varvivalik(milline) 
{
	el = milline;
  aken=window.open("/vv.html","varvivalik","HEIGHT=220,WIDTH=310")
 	aken.focus()
}
</script>

<form action='refcheck.{VAR:ext}' METHOD=post>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption">Nimi</td>
		<td class="fform"><input type="text" name="name" VALUE='{VAR:style_name}'></td>
	</tr>
	<tr>
		<td class="fcaption">Font:</td>
		<td class="fform">
			<select NAME='font1'>
				<option VALUE=''>
				<option VALUE='arial' {VAR:font1_sel_arial}>Arial
				<option VALUE='times' {VAR:font1_sel_times}>Times
				<option VALUE='verdana' {VAR:font1_sel_verdana}>Verdana
				<option VALUE='tahoma' {VAR:font1_sel_tahoma}>Tahoma
				<option VALUE='geneva' {VAR:font1_sel_geneva}>Geneva
				<option VALUE='helvetica' {VAR:font1_sel_helvetica}>Helvetica
			</select>
			<select NAME='font2'>
				<option VALUE=''>
				<option VALUE='arial' {VAR:font2_sel_arial}>Arial
				<option VALUE='times' {VAR:font2_sel_times}>Times
				<option VALUE='verdana' {VAR:font2_sel_verdana}>Verdana
				<option VALUE='tahoma' {VAR:font2_sel_tahoma}>Tahoma
				<option VALUE='geneva' {VAR:font2_sel_geneva}>Geneva
				<option VALUE='helvetica' {VAR:font2_sel_helvetica}>Helvetica
			</select>
			<select NAME='font3'>
				<option VALUE=''>
				<option VALUE='arial' {VAR:font3_sel_arial}>Arial
				<option VALUE='times' {VAR:font3_sel_times}>Times
				<option VALUE='verdana' {VAR:font3_sel_verdana}>Verdana
				<option VALUE='tahoma' {VAR:font3_sel_tahoma}>Tahoma
				<option VALUE='geneva' {VAR:font3_sel_geneva}>Geneva
				<option VALUE='helvetica' {VAR:font3_sel_helvetica}>Helvetica
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Fondi suurus:</td>
		<td class="fform">
			<select NAME='fontsize'>
				<!-- SUB: FONTSIZE -->
					<option VALUE='{VAR:fontsize_value}' {VAR:fontsize_selected}>{VAR:fontsize_value}
				<!-- END SUB: FONTSIZE -->
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">V&auml;rv</td>
		<td class="fform"><input type="text" name="colour" VALUE='{VAR:font_colour}'> <a href="#" onclick="varvivalik('colour');">vali v&auml;rv</a></td>
	</tr>
	<tr>
		<td class="fcaption">Tausta v&auml;rv:</td>
		<td class="fform"><input type="text" name="bgcolour" VALUE='{VAR:bgcolour}'> <a href="#" onclick="varvivalik('bgcolour');">vali v&auml;rv</a></td>
	</tr>
	<tr>
		<td class="fcaption">Fondi stiil:</td>
		<td class="fform">
			<select NAME='font_style'>
				<option VALUE='normal' {VAR:font_style_normal_selected}>Tavaline
				<option VALUE='bold' {VAR:font_style_bold_selected}>Bold
				<option VALUE='italic' {VAR:font_style_italic_selected}>Italic
				<option VALUE='underline' {VAR:font_style_underline_selected}>Underline
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Align:</td>
		<td class="fform"><input type="radio" name="align" VALUE='left' {VAR:align_left}>Vasak <input type="radio" name="align" VALUE='center' {VAR:align_center}>Keskel <input type="radio" name="align" VALUE='right' {VAR:align_right}>Parem</td>
	</tr>
	<tr>
		<td class="fcaption">Valign:</td>
		<td class="fform"><input type="radio" name="valign" VALUE='top' {VAR:valign_top}>&Uuml;leval <input type="radio" name="valign" VALUE='center' {VAR:valign_center}>Keskel <input type="radio" name="valign" VALUE='bottom' {VAR:valign_bottom}>All</td>
	</tr>
	<tr>
		<td class="fcaption">K&otilde;rgus:</td>
		<td class="fform"><input type="text" name="height" VALUE='{VAR:height}'></td>
	</tr>
	<tr>
		<td class="fcaption">Laius:</td>
		<td class="fform"><input type="text" name="width" VALUE='{VAR:width}'></td>
	</tr>
	<tr>
		<td class="fcaption">Nowrap:</td>
		<td class="fform"><input type="checkbox" name="nowrap" VALUE=1 {VAR:nowrap_checked}></td>
	</tr>
	<tr>
		<td class="fform" colspan="2">
			<input type="submit" value="Salvesta">
			<input type="hidden" name="action" value="admin_style">
			<input type="hidden" name="id" value="{VAR:style_id}">
			<input type="hidden" name="back" value="{VAR:back}">
			<input type='hidden' name='parent' VALUE="{VAR:parent}">
		</td>
	</tr>
</table>
</form>
								