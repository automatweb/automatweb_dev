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

<!-- SUB: STYLE_DEFS -->
var style{VAR:style_id}_name = {VAR:style_name};
var style{VAR:style_id}_font1 = {VAR:style_font1};
var style{VAR:style_id}_font2 = {VAR:style_font2};
var style{VAR:style_id}_font3 = {VAR:style_font3};
var style{VAR:style_id}_fontsize = {VAR:style_fontsize};
var style{VAR:style_id}_color = {VAR:style_color};
var style{VAR:style_id}_bgcolor = {VAR:style_bgcolor};
var style{VAR:style_id}_fstyle = {VAR:style_fstyle};
var style{VAR:style_id}_alignl = {VAR:style_align_left};
var style{VAR:style_id}_alignc = {VAR:style_align_center};
var style{VAR:style_id}_alignr = {VAR:style_align_right};
var style{VAR:style_id}_valignt = {VAR:style_valign_top};
var style{VAR:style_id}_valignc = {VAR:style_valign_center};
var style{VAR:style_id}_valignb = {VAR:style_valign_bottom};
var style{VAR:style_id}_height = {VAR:style_height};
var style{VAR:style_id}_width = {VAR:style_width};
var style{VAR:style_id}_nowrap = {VAR:style_nowrap};

<!-- END SUB: STYLE_DEFS -->


function getChoice(el) 
{   
	for (var i = 0; i < el.length; i++) 
	{      
		if (el.options[i].selected == true) 
		{         
			return el.options[i].value      
		}   
	}   
	return null
}

var valid=0;
var inupdate=0;
var ininvd=0;

function change_style()
{
	if (ininvd == 1)
		return;

	inupdate=1;
	selID = getChoice(document.forms[0].id);
	eval('document.forms[0].font1.selectedIndex=style'+selID+'_font1');
	eval('document.forms[0].font2.selectedIndex=style'+selID+'_font2');
	eval('document.forms[0].font3.selectedIndex=style'+selID+'_font3');
	eval('document.forms[0].fontsize.selectedIndex=style'+selID+'_fontsize');
	eval('document.forms[0].colour.value=style'+selID+'_color');
	eval('document.forms[0].bgcolour.value=style'+selID+'_bgcolor');
	eval('document.forms[0].font_style.selectedIndex=style'+selID+'_fstyle');
	eval('document.forms[0].elements[8].checked=style'+selID+'_alignl');
	eval('document.forms[0].elements[9].checked=style'+selID+'_alignc');
	eval('document.forms[0].elements[10].checked=style'+selID+'_alignr');
	eval('document.forms[0].elements[11].checked=style'+selID+'_valignt');
	eval('document.forms[0].elements[12].checked=style'+selID+'_valignc');
	eval('document.forms[0].elements[13].checked=style'+selID+'_valignb');
	eval('document.forms[0].height.value=style'+selID+'_height');
	eval('document.forms[0].width.value=style'+selID+'_width');
	eval('document.forms[0].elements[17].checked=style'+selID+'_nowrap');
	valid=1;
	inupdate=0;
}

function invalidate()
{
	ininvd=1;
	if (inupdate == 0)
		if (valid == 1)		// if a valid style is selected, select the empty style 
		{
			document.forms[0].id.options[0].selected=true;
			valid=0;
		}
	ininvd=0;
}
</script>
<form action='refcheck.{VAR:ext}' METHOD=post NAME=stil>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_BIG_STYLE}:</td>
		<td class="fform"><select NAME='id' onChange="change_style()">
				<!-- SUB: STYLE_LIST -->
						<option {VAR:style_item_active} VALUE='{VAR:style_item_value}'>{VAR:style_item_text}
				<!-- END SUB: STYLE_LIST -->
						</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Font:</td>
		<td class="fform">
			<select NAME='font1' onChange="invalidate()">
				<option VALUE='' >
				<option VALUE='arial' >Arial
				<option VALUE='times' >Times
				<option VALUE='verdana'>Verdana
				<option VALUE='tahoma' >Tahoma
				<option VALUE='geneva' >Geneva
				<option VALUE='helvetica' >Helvetica
			</select>
			<select NAME='font2' onChange="invalidate()">
				<option VALUE='' >
				<option VALUE='arial' >Arial
				<option VALUE='times' >Times
				<option VALUE='verdana' >Verdana
				<option VALUE='tahoma' >Tahoma
				<option VALUE='geneva' >Geneva
				<option VALUE='helvetica' >Helvetica
			</select>
			<select NAME='font3' onChange="invalidate()">
				<option VALUE='' >
				<option VALUE='arial' >Arial
				<option VALUE='times' >Times
				<option VALUE='verdana' >Verdana
				<option VALUE='tahoma' >Tahoma
				<option VALUE='geneva' >Geneva
				<option VALUE='helvetica' >Helvetica
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_FONT_SIZE}:</td>
		<td class="fform">
			<select NAME='fontsize' onChange="invalidate()">
				<!-- SUB: FONTSIZE -->
					<option VALUE='{VAR:fontsize_value}' >{VAR:fontsize_value}
				<!-- END SUB: FONTSIZE -->
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_COLOR}</td>
		<td class="fform"><input type="text" name="colour" VALUE='' onChange="invalidate()"> <a href="#" onclick="varvivalik('colour');">{VAR:LC_STYLE_CHOOSE_COLOR}</a></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_BACK_COLOR}:</td>
		<td class="fform"><input type="text" name="bgcolour" VALUE='' onChange="invalidate()"> <a href="#" onclick="varvivalik('bgcolour');">{VAR:LC_STYLE_CHOOSE_COLOR}</a></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_FONT_STYLE}:</td>
		<td class="fform">
			<select NAME='font_style' onChange="invalidate()">
				<option VALUE='normal' >{VAR:LC_STYLE_COMMON}
				<option VALUE='bold' >Bold
				<option VALUE='italic' >Italic
				<option VALUE='underline' >Underline
			</select>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Align:</td>
		<td class="fform"><input type="radio" name="align" VALUE='left' onClick="invalidate()">{VAR:LC_STYLE_LEFT} <input type="radio" name="align" VALUE='center'  onClick="invalidate()">{VAR:LC_STYLE_MIDDLE} <input type="radio" name="align" VALUE='right' onClick="invalidate()">{VAR:LC_STYLE_RIGHT}</td>
	</tr>
	<tr>
		<td class="fcaption">Valign:</td>
		<td class="fform"><input type="radio" name="valign" VALUE='top' onClick="invalidate()">{VAR:LC_STYLE_UP} <input type="radio" name="valign" VALUE='center'  onClick="invalidate()">{VAR:LC_STYLE_MIDDLE} <input type="radio" name="valign" VALUE='bottom' onClick="invalidate()">{VAR:LC_STYLE_DOWN}</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_HEIGHT}:</td>
		<td class="fform"><input type="text" name="height" VALUE='' onChange="invalidate()">{VAR:LC_STYLE_PIX}</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_WITHD}:</td>
		<td class="fform"><input type="text" name="width" VALUE='' onChange="invalidate()">{VAR:LC_STYLE_PIX}</td>
	</tr>
	<tr>
		<td class="fcaption">Nowrap:</td>
		<td class="fform"><input type="checkbox" name="nowrap" VALUE=1 onClick="invalidate()"></td>
	</tr>
</table>
<A NAME = "buttons">
<font face='tahoma, arial, geneva, helvetica' size="2"><input type='submit' NAME='save_cell_style' VALUE='{VAR:LC_STYLE_SAVE}'>
<input type='hidden' NAME='action' VALUE='select_style'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='back' VALUE='{VAR:back}'>
</font></form>
    
<script language="javascript">
change_style();
</script>