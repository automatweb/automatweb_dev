<script language=javascript>
var st=1;
function selall()
{
<!-- SUB: SELLINE -->

	document.forms[1].elements[{VAR:row}].checked=st;

<!-- END SUB: SELLINE -->
st = !st;
return false;
}

function doSubmit(val)
{
	document.fiib.action.value=val;
	document.fiib.submit();
}

function doAsk(caption){
var answer=confirm(caption)
if (answer)
	return true;
else
	return false;
}

function doDelete()
{
<!-- SUB: DEL_LINE -->
if (document.forms[1].elements[{VAR:row}].checked)
{
	if (!doAsk("Oled kindel, et tahad formi {VAR:form_name} kustutada?"))
		document.forms[1].elements[{VAR:row}].checked=0;
}
<!-- END SUB: DEL_LINE -->
	doSubmit("delete_forms");
}
</script>

<table border="0" cellspacing="0" cellpadding="0"  width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0"  width=100%>
<tr>
<td height="15" colspan="10" class="fgtitle">&nbsp;<b>{VAR:LC_FORMS_BIG_CATEGORYS}: 
<!-- SUB: ADD_CAT -->
<a href='forms.{VAR:ext}?type=add_category&parent={VAR:parent}'>{VAR:LC_FORMS_ADD_CATEGORY}</a>
<!-- END SUB: ADD_CAT -->
 | {VAR:LC_FORMS_SETTINGS} | {VAR:LC_FORMS_STYLES}</b></td>
</tr>
<tr>
<td height="15" class="title">&nbsp;{VAR:LC_FORMS_NAME}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_DISCRIPTION}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_CHANGER}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_CHANGED}&nbsp;</td>
<td align="center" colspan="4" class="title">&nbsp;{VAR:LC_FORMS_ACTION}&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">

<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
<tr>
<td>{VAR:space_images}{VAR:image}</td>
<td valign=center class="fgtext">&nbsp;<a href='forms.{VAR:ext}?type=list&parent={VAR:category_id}{VAR:op}'>{VAR:category_name}</a> &nbsp;({VAR:count})&nbsp;</td>
</tr>
</table>

</td>

<td class="fgtext">&nbsp;{VAR:category_comment}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>

<!-- Tegevused -->
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='forms.{VAR:ext}?type=change_category&id={VAR:category_id}&parent={VAR:parent}'>Info</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>

<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('{VAR:LC_FORMS_ARE_YOU_SURE_DEL_CAT}?','forms.{VAR:ext}?type=delete_category&id={VAR:category_id}&parent={VAR:parent}')">{VAR:LC_FORMS_DELETE}</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>

<!-- ACL -->
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:category_id}&file=form_category.xml'>ACL</a>
<!-- END SUB: CAN_ACL -->
&nbsp;</td>

<!-- Paste -->
<!-- SUB: CAN_PASTE -->
<td class="fgtext2">&nbsp;
<a href='forms.{VAR:ext}?type=paste&id={VAR:category_id}'>Paste</a>
&nbsp;</td>
<!-- END SUB: CAN_PASTE -->
</tr>
<!-- END SUB: LINE -->

</table>
</td></tr></table>

<br>

<form action=refcheck.{VAR:ext} method = post>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">

<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr><td class="title" align=right>{VAR:LC_FORMS_CHOOSE_DEFAULT_STYLE}: </td><td
class="fgtitle"><select class="small_button" name="def_style">
<!-- SUB: DEF_STYLE_ITEM -->
<option VALUE='{VAR:style_id}' {VAR:style_selected}>{VAR:style_name}
<!-- END SUB: DEF_STYLE_ITEM -->
</select><input class="small_button" type='submit' NAME='save' VALUE='{VAR:LC_FORMS_SAVE}'></td></tr></table>
<input type='hidden' NAME='action' VALUE='set_category_style'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>

</td></tr></table>
</form>

<form action='refcheck.{VAR:ext}' method=post name=fiib>

<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0"  width=100%>
<tr>
	<td height="15" colspan="15" class="fgtitle">&nbsp;<b>{VAR:LC_FORMS_FORMS}:
	<!-- SUB: ADD_FORM -->
	<a href='forms.{VAR:ext}?type=add_form&parent={VAR:parent}'>{VAR:LC_FORMS_ADD}</a>
	<!-- END SUB: ADD_FORM -->
	 | {VAR:LC_FORMS_SETTINGS}
	<!-- SUB: CAN_IMPORT -->
	 | <a href='forms.{VAR:ext}?type=import_forms&parent={VAR:parent}&level=0'>{VAR:LC_FORMS_IMPORT}</a>
	<!-- END SUB: CAN_IMPORT -->
	 | <a href='javascript:doSubmit("export_forms")'>{VAR:LC_FORMS_EXPORT}</a>
	 | <a href='javascript:doSubmit("cut_forms")'>{VAR:LC_FORMS_CUT}</a>
	 | <a href='javascript:doDelete()'>{VAR:LC_FORMS_DELETE}</a>
</b></td>
</tr>
<tr>

<!-- FID -->
<td align="center" class="title">&nbsp;FID&nbsp;</td>

<!-- Nimi -->
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_NAME}&nbsp;</td>

<!-- Tyyp -->
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_TYPE}&nbsp;</td>

<!-- Kirjeldus -->
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_DISCRIPTION}&nbsp;</td>

<!-- Muudetud -->
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_CHANGER}&nbsp;</td>
<!-- Muudetud -->
<td align="center" class="title">&nbsp;{VAR:LC_FORMS_CHANGED}&nbsp;</td>

<!-- Tegevus -->
<td align="center" colspan="5" class="title">{VAR:LC_FORMS_ACTION}</td>

<!-- Impordi Ekspordi -->
<td align="center" colspan="2" class="title">&nbsp;<a href='#'
onClick="selall();return false;">{VAR:LC_FORMS_ALL}</a>&nbsp;</td>

</tr>

<!-- SUB: FLINE -->
<tr>

<!-- FID -->
<td align="center" class="{VAR:is_cut}">&nbsp;{VAR:form_id}&nbsp;</td>

<!-- Nimi -->
<td class="{VAR:is_cut}">&nbsp;{VAR:form_name}&nbsp;</td>

<!-- Tyyp -->
<td class="{VAR:is_cut}">&nbsp;{VAR:form_type}&nbsp;</td>

<!-- Kirjeldus -->
<td class="{VAR:is_cut}">&nbsp;{VAR:form_comment}&nbsp;</td>

<!-- Muudetud -->
<td align="center" class="{VAR:is_cut}">&nbsp;{VAR:modifiedby}&nbsp;</td>
<!-- Muudetud -->
<td align="center" class="{VAR:is_cut}">&nbsp;{VAR:modified}&nbsp;</td>

<!-- Tegevused -->

<td class="fgtext2">&nbsp;
<!-- SUB: FILL -->
<a href='forms.{VAR:ext}?type=preview&id={VAR:form_id}'>{VAR:LC_FORMS_FILL}</a>
<!-- END SUB: FILL -->
&nbsp;</td>

<td class="fgtext2" nowrap>&nbsp;
<!-- SUB: VIEW_FILLED -->
<a href='forms.{VAR:ext}?type=filled_forms&id={VAR:form_id}'>{VAR:LC_FORMS_FILLED_FORMS}</a>
<!-- END SUB: VIEW_FILLED -->
&nbsp;</td>

<td class="fgtext2">&nbsp;
<!-- SUB: CHANGE -->
<a href='forms.{VAR:ext}?type=grid&id={VAR:form_id}'>{VAR:LC_FORMS_TOIMETA}</a>
<!-- END SUB: CHANGE -->
&nbsp;</td>

<td class="fgtext2">&nbsp;<a
href='forms.{VAR:ext}?type=output_list&id={VAR:form_id}'>{VAR:LC_FORMS_OUTPUTS}</a>&nbsp;</td>

<td class="fgtext2">&nbsp;
<!-- SUB: ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:form_id}&file=form.xml'>ACL</a>
<!-- END SUB: ACL -->
&nbsp;</td>

<!-- checkbox -->

<td class="chkbox" align=center>
<input type='checkbox' NAME='fex_{VAR:form_id}' VALUE=1 class='chkbox' align=center>
&nbsp;</td>

</tr>

<!-- END SUB: FLINE -->

</table>
</td></tr></table>

<input type='hidden' NAME='action' VALUE='export_forms'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>

<br>
