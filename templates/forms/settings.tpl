<script language='javascript'>

function varv(vrv) 
{
	document.ffrm.bgcolor.value="#"+vrv;
} 

function varvivalik() 
{
  aken=window.open("/vv.html","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}
</script>

<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">{VAR:LC_FORMS_BACK_COL}:</td>
<td class="fform"><input type='text' NAME='bgcolor' VALUE='{VAR:form_bgcolor}'> <a href="#" onclick="varvivalik();">{VAR:LC_FORMS_CHOOSE_COL}</a></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_SIDE_WIDTH}:</td>
<td class="fform"><input type='text' NAME='border' VALUE='{VAR:form_border}'></td>
</tr>
<tr>
<td class="fform">cellpadding:</td>
<td class="fform"><input type='text' NAME='cellpadding' VALUE='{VAR:form_cellpadding}'></td>
</tr>
<tr>
<td class="fform">cellspacing:</td>
<td class="fform"><input type='text' NAME='cellspacing' VALUE='{VAR:form_cellspacing}'></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_HIGHT}:</td>
<td class="fform"><input type='text' NAME='height' VALUE='{VAR:form_height}'></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_WITDH}:</td>
<td class="fform"><input type='text' NAME='width' VALUE='{VAR:form_width}'></td>
</tr>
<tr>
<td class="fform">Hspace:</td>
<td class="fform"><input type='text' NAME='hspace' VALUE='{VAR:form_hspace}'></td>
</tr>
<tr>
<td class="fform">Vspace:</td>
<td class="fform"><input type='text' NAME='vspace' VALUE='{VAR:form_vspace}'></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_DEFAULT_STYLE}:</td>
<td class="fform"><select NAME='def_style'><option VALUE=''>{VAR:def_style}</select>
</td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_FORM_TRIED_FILL_USER_DATA}: &nbsp;<input type='checkbox' name='try_fill' value=1 {VAR:try_fill}></td>
</tr>
<!-- SUB: NOSEARCH -->
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_AFTER_FILLING}:</td>
</tr>
<tr>
<td class="fform"><input type='radio' NAME='after_submit' VALUE='1' {VAR:as_1}>{VAR:LC_FORMS_CHANGE_INPUT}</td>
<td class="fform">&nbsp;</td>
</tr>
<tr>
<td class="fform"><input type='radio' NAME='after_submit' VALUE='3' {VAR:as_3}>{VAR:LC_FORMS_GOT_TO_ADDRESS}:</td>
<td class="fform"><input type='text' NAME='after_submit_link' value='{VAR:after_submit_link}'></td>
</tr>
<!-- END SUB: NOSEARCH -->

<!-- SUB: SEARCH -->
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_SEARCH_RESULTS_SHOW_TABLE}? <input type='checkbox' NAME='show_table' value='1' {VAR:show_table_checked}></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_CHOOSE_TABLE}:</td>
<td class="fform"><select name='table'>{VAR:tables}</select></td>
</tr>
<!-- END SUB: SEARCH -->
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_ELEMENT_WHAT_PUT_FORM_ENTRY}</td>
</tr>
<tr>
<td colspan=2 class="fform"><select NAME='entry_name_el'>{VAR:els}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' NAME='save_form_settings' VALUE='{VAR:LC_FORMS_SAVE} form'></td>
</table>
{VAR:reforb}
</form>
  
