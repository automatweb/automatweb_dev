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
<td height="15" colspan="10" class="fgtitle">&nbsp;<b>KATEGOORIAD: 
<!-- SUB: ADD_CAT -->
<a href='forms.{VAR:ext}?type=add_category&parent={VAR:parent}'>Lisa kategooria</a>
<!-- END SUB: ADD_CAT -->
 | Määrangud | Stiilid</b></td>
</tr>
<tr>
<td height="15" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kirjeldus&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" colspan="4" class="title">&nbsp;Tegevus&nbsp;</td>
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
<a href="javascript:box2('Oled kindel, et soovid seda kategooriat kustutada?','forms.{VAR:ext}?type=delete_category&id={VAR:category_id}&parent={VAR:parent}')">Kustuta</a>
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
<tr><td class="title" align=right>Vali default stiil: </td><td
class="fgtitle"><select class="small_button" name="def_style">
<!-- SUB: DEF_STYLE_ITEM -->
<option VALUE='{VAR:style_id}' {VAR:style_selected}>{VAR:style_name}
<!-- END SUB: DEF_STYLE_ITEM -->
</select><input class="small_button" type='submit' NAME='save' VALUE='Salvesta'></td></tr></table>
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
	<td height="15" colspan="15" class="fgtitle">&nbsp;<b>FORMID:
	<!-- SUB: ADD_FORM -->
	<a href='forms.{VAR:ext}?type=add_form&parent={VAR:parent}'>Lisa</a>
	<!-- END SUB: ADD_FORM -->
	 | Määrangud
	<!-- SUB: CAN_IMPORT -->
	 | <a href='forms.{VAR:ext}?type=import_forms&parent={VAR:parent}&level=0'>Impordi</a>
	<!-- END SUB: CAN_IMPORT -->
	 | <a href='javascript:doSubmit("export_forms")'>Ekspordi</a>
	 | <a href='javascript:doSubmit("cut_forms")'>Lõika</a>
	 | <a href='javascript:doDelete()'>Kustuta</a>
</b></td>
</tr>
<tr>

<!-- FID -->
<td align="center" class="title">&nbsp;FID&nbsp;</td>

<!-- Nimi -->
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>

<!-- Tyyp -->
<td align="center" class="title">&nbsp;Tüüp&nbsp;</td>

<!-- Kirjeldus -->
<td align="center" class="title">&nbsp;Kirjeldus&nbsp;</td>

<!-- Muudetud -->
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<!-- Muudetud -->
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>

<!-- Tegevus -->
<td align="center" colspan="5" class="title">Tegevus</td>

<!-- Impordi Ekspordi -->
<td align="center" colspan="2" class="title">&nbsp;<a href='#'
onClick="selall();return false;">K&otilde;ik</a>&nbsp;</td>

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
<a href='forms.{VAR:ext}?type=preview&id={VAR:form_id}'>T&auml;ida</a>
<!-- END SUB: FILL -->
&nbsp;</td>

<td class="fgtext2" nowrap>&nbsp;
<!-- SUB: VIEW_FILLED -->
<a href='forms.{VAR:ext}?type=filled_forms&id={VAR:form_id}'>T&auml;idetud formid</a>
<!-- END SUB: VIEW_FILLED -->
&nbsp;</td>

<td class="fgtext2">&nbsp;
<!-- SUB: CHANGE -->
<a href='forms.{VAR:ext}?type=grid&id={VAR:form_id}'>Toimeta</a>
<!-- END SUB: CHANGE -->
&nbsp;</td>

<td class="fgtext2">&nbsp;<a
href='forms.{VAR:ext}?type=output_list&id={VAR:form_id}'>V&auml;ljundid</a>&nbsp;</td>

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
