<script language='javascript'>

function varv(vrv) 
{
	document.ffrm.bgcolor.value="#"+vrv;
} 

function varvivalik() 
{
  aken=window.open("{VAR:baseurl}/automatweb/orb.{VAR:ext}?class=css&action=colorpicker","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}

function setLink(li,title)
{
	document.ffrm.after_submit_link.value=li;
}

</script>

<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">{VAR:LC_FORMS_TABLE_STYLE}:</td>
<td class="fform"><select name='tablestyle' class='small_button'>{VAR:tablestyles}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_DEFAULT_STYLE}:</td>
<td class="fform"><select NAME='def_style'><option VALUE=''>{VAR:def_style}</select>
</td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_FORM_TRIED_FILL_USER_DATA}: &nbsp;<input type='checkbox' name='try_fill' value=1 {VAR:try_fill}></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_FORM_HAS_ALIASMGR}</td>
<td class="fform"><input type="checkbox" name="has_aliasmgr" value="1" {VAR:has_aliasmgr}></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_ALLOW_HTML}: &nbsp;<input type='checkbox' name='allow_html' value=1 {VAR:allow_html}></td>
</tr>
<tr>
<td class="fform" colspan=2>Vorm kasutab kontrollereid: &nbsp;<input type='checkbox' name='has_controllers' value=1 {VAR:has_controllers}></td>
</tr>
<tr>
<td class="fform" colspan=2>Eventite sisestusvorm: &nbsp;<input type='checkbox' name='ev_entry_form' value=1 {VAR:ev_entry_form}></td>
</tr>
<tr>
<td class="fform" colspan=2>Kalendri vormitabel: &nbsp;<select name="event_display_table">{VAR:event_display_tables}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>Kas vormi saab kasutada e-posti actionite jaoks?: &nbsp;<input type='checkbox' name='email_form_action' value=1 {VAR:email_form_action}></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CONTROL_FORM_STATUS}?: &nbsp;<input type='checkbox' name='check_status' value=1 {VAR:check_status}>
<br>
{VAR:LC_FORMS_TEXT_DISPLAY_USER}: <input type="text" name="check_status_text" value="{VAR:check_status_text}" size="40">
</td>
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
<td class="fform"><input type='text' NAME='after_submit_link' value='{VAR:after_submit_link}'> <a href="javascript:remote('no',500,400,'{VAR:search_doc}')">Saidi sisene link</a></td>
</tr>
<tr>
<td class="fform"><input type='radio' NAME='after_submit' VALUE='4' {VAR:as_4}>{VAR:LC_FORMS_SHOW_ENTRIES}:</td>
<td class="fform"><select name="after_submit_op">{VAR:ops}</select></td>
</tr>
<tr>
<td class="fform">SQL-kirjutaja? </td>
<td class="fform"><input type='checkbox' NAME='sql_writer_writer' value='1' {VAR:sql_writer_writer}></td>
</tr>
<tr>
<td class="fform">Vali form kuhu kirjutatakse:</td>
<td class="fform"><select class="small_button" name='sql_writer_writer_form'>{VAR:sql_writer_writer_forms}</select></td>
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
<tr>
<td class="fform">SQL-kirjutaja? </td>
<td class="fform"><input type='checkbox' NAME='sql_writer' value='1' {VAR:sql_writer}></td>
</tr>
<tr>
<td class="fform">Vali kirjutamise form:</td>
<td class="fform"><select class="small_button" name='sql_writer_form'>{VAR:forms}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>Koos tulemustega n&auml;idatakse ka otsingut? <input type='checkbox' NAME='show_form_with_results' value='1' {VAR:show_form_with_results}></td>
</tr>
<!-- END SUB: SEARCH -->
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_ELEMENT_WHAT_PUT_FORM_ENTRY}</td>
</tr>
<tr>
<td colspan=2 class="fform"><select NAME='entry_name_el[]' multiple>{VAR:els}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' NAME='save_form_settings' VALUE='{VAR:LC_FORMS_SAVE} form'></td>
</table>
{VAR:reforb}
</form>
  
