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

<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=change_table&id={VAR:parent}'>{VAR:LC_STYLE_ADMIN}</a></td>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=settings&id={VAR:parent}'>{VAR:LC_STYLE_SETTINGS}</a></td>
		<td bgcolor=#a0a0a0><a href='tables.phtml?type=change_styles&parent={VAR:parent}'>{VAR:LC_STYLE_STYLEBOOK}</a></td>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=show_table&id={VAR:parent}'>{VAR:LC_STYLE_PREVIEW}</a></td>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=delete_table&id={VAR:parent}'>{VAR:LC_STYLE_DELETE}</a></td>
	</tr>
</table>

<form action='refcheck.phtml' METHOD=post>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_NAME}</td>
		<td class="fform"><input type="text" name="name" VALUE='{VAR:style_name}'></td>
	</tr>
	<tr>
		<td class="fcaption">Font:</td>
		<td class="fform">
			<select NAME='font1'>
				<option VALUE='arial' {VAR:font1_sel_arial}>Arial
				<option VALUE='times' {VAR:font1_sel_times}>Times
				<option VALUE='verdana' {VAR:font1_sel_verdana}>Verdana
				<option VALUE='tahoma' {VAR:font1_sel_tahoma}>Tahoma
				<option VALUE='geneva' {VAR:font1_sel_geneva}>Geneva
				<option VALUE='helvetica' {VAR:font1_sel_helvetica}>Helvetica
			</select>
			<select NAME='font2'>
				<option VALUE='arial' {VAR:font2_sel_arial}>Arial
				<option VALUE='times' {VAR:font2_sel_times}>Times
				<option VALUE='verdana' {VAR:font2_sel_verdana}>Verdana
				<option VALUE='tahoma' {VAR:font2_sel_tahoma}>Tahoma
				<option VALUE='geneva' {VAR:font2_sel_geneva}>Geneva
				<option VALUE='helvetica' {VAR:font2_sel_helvetica}>Helvetica
			</select>
			<select NAME='font3'>
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
		<td class="fcaption">{VAR:LC_STYLE_FONT_SIZE}:</td>
		<td class="fform">
			<select NAME='fontsize'>
				<!-- SUB: FONTSIZE -->
					<option VALUE='{VAR:fontsize_value}' {VAR:fontsize_selected}>{VAR:fontsize_value}
				<!-- END SUB: FONTSIZE -->
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_COLOR}</td>
		<td class="fform"><input type="text" name="colour" VALUE='{VAR:font_colour}'> <a href="#" onclick="varvivalik('colour');">{VAR:LC_STYLE_CHOOSE_COLOR}</a></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_BACK_COLOR}:</td>
		<td class="fform"><input type="text" name="bgcolour" VALUE='{VAR:bgcolour}'> <a href="#" onclick="varvivalik('bgcolour');">{VAR:LC_STYLE_CHOOSE_COLOR}</a></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_FONT_STYLE}:</td>
		<td class="fform">
			<select NAME='font_style'>
				<option VALUE='normal' {VAR:font_style_normal_selected}>{VAR:LC_STYLE_COMMON}
				<option VALUE='bold' {VAR:font_style_bold_selected}>Bold
				<option VALUE='italic' {VAR:font_style_italic_selected}>Italic
				<option VALUE='underline' {VAR:font_style_underline_selected}>Underline
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Align:</td>
		<td class="fform"><input type="radio" name="align" VALUE='left' {VAR:align_left}>{VAR:LC_STYLE_LEFT} <input type="radio" name="align" VALUE='center' {VAR:align_center}>{VAR:LC_STYLE_MIDDLE} <input type="radio" name="align" VALUE='right' {VAR:align_right}>{VAR:LC_STYLE_RIGHT}</td>
	</tr>
	<tr>
		<td class="fcaption">Valign:</td>
		<td class="fform"><input type="radio" name="valign" VALUE='top' {VAR:valign_top}>{VAR:LC_STYLE_UP} <input type="radio" name="valign" VALUE='center' {VAR:valign_center}>{VAR:LC_STYLE_MIDDLE} <input type="radio" name="valign" VALUE='bottom' {VAR:valign_bottom}>{VAR:LC_STYLE_DOWN}</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_HEIGHT}:</td>
		<td class="fform"><input type="text" name="height" VALUE='{VAR:height}'></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_WITHD}:</td>
		<td class="fform"><input type="text" name="width" VALUE='{VAR:width}'></td>
	</tr>
	<tr>
		<td class="fcaption">Nowrap:</td>
		<td class="fform"><input type="checkbox" name="nowrap" VALUE=1 {VAR:nowrap_checked}></td>
	</tr>
	<tr>
		<td class="fform" colspan="2">
			<input type="submit" value="{VAR:LC_STYLE_SAVE}">
			<input type="hidden" name="action" value="admin_table_styles">
			<input type="hidden" name="id" value="{VAR:style_id}">
			<input type="hidden" name="back" value="{VAR:back}">
			<input type='hidden' name='parent' VALUE="{VAR:parent}">
		</td>
	</tr>
</table>
</form>
								