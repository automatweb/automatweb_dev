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
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_TABLE_STYLE}:</td>
<td class="celltext"><select name='tablestyle'>{VAR:tablestyles}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_DEFAULT_STYLE}:</td>
<td class="celltext"><select name='def_style'><option VALUE=''>{VAR:def_style}</select>
</td>
</tr>
<tr class="aste01">
<td class="celltext" colspan=2>{VAR:LC_FORMS_TRY_JF_DATA}: &nbsp;<input type='checkbox' name='try_fill' value=1 {VAR:try_fill}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_FORM_HAS_ALIASMGR}:</td>
<td class="celltext"><input type="checkbox" name="has_aliasmgr" value="1" {VAR:has_aliasmgr}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_ALLOW_HTML}:</td>
<td class="celltext"><input type='checkbox' name='allow_html' value=1 {VAR:allow_html}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_FORM_HAS_CONTROLLERS}:</td>
<td class="celltext"><input type='checkbox' name='has_controllers' value=1 {VAR:has_controllers}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_EV_ENTRY_FORM}:</td>
<td class="celltext"><input type='checkbox' name='ev_entry_form' value=1 {VAR:ev_entry_form}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_CALENDAR_OP}:</td>
<td class="celltext"><select name="event_display_table">{VAR:event_display_tables}</select></td>
</tr>
<tr class="aste01">
<td class="celltext" colspan=2>{VAR:LC_FORMS_CAN_EMAIL_ACTION}: &nbsp;<input type='checkbox' name='email_form_action' value=1 {VAR:email_form_action}></td>
</tr>
<tr class="aste01">
<td class="celltext" colspan=2>{VAR:LC_FORMS_CONTROL_FORM_STATUS}?: &nbsp;<input type='checkbox' name='check_status' value=1 {VAR:check_status}>
<br>
{VAR:LC_FORMS_TEXT_DISPLAY_USER}: <input type="text" name="check_status_text" value="{VAR:check_status_text}" size="40">
</td>
</tr>
<!-- SUB: NOSEARCH -->
<tr class="aste01">
<td class="celltext" colspan=2>{VAR:LC_FORMS_AFTER_FILLING}:</td>
</tr>
<tr class="aste01">
<td class="celltext"><input type='radio' NAME='after_submit' VALUE='1' {VAR:as_1}>{VAR:LC_FORMS_CHANGE_INPUT}</td>
<td class="celltext">&nbsp;</td>
</tr>
<tr class="aste01">
<td class="celltext"><input type='radio' NAME='after_submit' VALUE='3' {VAR:as_3}>{VAR:LC_FORMS_GOT_TO_ADDRESS}:</td>
<td class="celltext"><input type='text' NAME='after_submit_link' value='{VAR:after_submit_link}' size="30"> <a href="javascript:remote('no',500,400,'{VAR:search_doc}')">{VAR:LC_FORMS_INTRA_LINK}</a></td>
</tr>
<tr class="aste01">
<td class="celltext"><input type='radio' NAME='after_submit' VALUE='4' {VAR:as_4}>{VAR:LC_FORMS_SHOW_ENTRIES}:</td>
<td class="celltext"><select name="after_submit_op">{VAR:ops}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_SQL_WRITER_WRITER}:</td>
<td class="celltext"><input type='checkbox' NAME='sql_writer_writer' value='1' {VAR:sql_writer_writer}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_SQL_WRITER_WRITER_FORM}:</td>
<td class="celltext"><select class="small_button" name='sql_writer_writer_form'>{VAR:sql_writer_writer_forms}</select></td>
</tr>
<!-- END SUB: NOSEARCH -->

<!-- SUB: SEARCH -->
<tr class="aste01">
<td class="celltext" colspan=2>{VAR:LC_FORMS_SEARCH_RESULTS_SHOW_TABLE}? <input type='checkbox' NAME='show_table' value='1' {VAR:show_table_checked}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_CHOOSE_TABLE}:</td>
<td class="celltext"><select name='table'>{VAR:tables}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_SQL_WRITER}:</td>
<td class="celltext"><input type='checkbox' NAME='sql_writer' value='1' {VAR:sql_writer}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_SQL_WRITER_FORM}:</td>
<td class="celltext"><select class="small_button" name='sql_writer_form'>{VAR:forms}</select></td>
</tr>
<tr class="aste01">
<td class="celltext" colspan=2>{VAR:LC_FORMS_SHOW_FORM_WITH_RESULTS}: <input type='checkbox' NAME='show_form_with_results' value='1' {VAR:show_form_with_results}></td>
</tr>
<!-- END SUB: SEARCH -->
<tr class="aste01">
<td class="celltext" colspan=2>{VAR:LC_FORMS_CHOOSE_ELEMENT_WHAT_PUT_FORM_ENTRY}</td>
</tr>
<tr class="aste01">
<td colspan=2 class="celltext"><select NAME='entry_name_el[]' multiple>{VAR:els}</select></td>
</tr>
<tr class="aste01">
<td class="celltext" colspan=2><input class='formbutton' type='submit' NAME='save_form_settings' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</table>
{VAR:reforb}
</form>
  
