<br>
<form action='reforb.{VAR:ext}' METHOD=post name=f1 enctype='multipart/form-data'>
<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC" width=100%>

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>{VAR:LC_FORMS_BIG_ELEMENTS}:
<a href="javascript:document.f1.submit()">{VAR:LC_FORMS_SAVE}</a>
&nbsp;|&nbsp;
<!-- SUB: CAN_ACTION -->
&nbsp;|&nbsp;<a href='forms.{VAR:ext}?type=cell_actions&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>{VAR:LC_FORMS_SMALL_ACTIONS}</a>
<!-- END SUB: CAN_ACTION -->
&nbsp;|&nbsp;<a href='forms.{VAR:ext}?type=cell_controllers&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>{VAR:LC_FORMS_CONTROLLERS}</a> | 
<!-- SUB: CAN_ADD -->
<a href='{VAR:add_el}'>{VAR:LC_FORMS_ADD}</a>
<!-- END SUB: CAN_ADD -->

</b></td>
</tr>

<!-- SUB: ELEMENT_LINE -->
<tr><td><a name='el_{VAR:after}'>{VAR:element}</td></tr>
<tr>
<td height="15" colspan="15" class="fgtitle"><a href="javascript:document.f1.submit()">{VAR:LC_FORMS_SAVE}</a>
<!-- SUB: EL_ADD -->

<!-- SUB: EL_NLAST -->
&nbsp;|&nbsp;<a href="forms.{VAR:ext}?type=add_element&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}&after={VAR:after}" >Lisa siia</a>
<!-- END SUB: EL_NLAST -->
&nbsp;|&nbsp;<a href="forms.{VAR:ext}?type=add_element&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}" >{VAR:LC_FORMS_ADD_TO_END}</a>
<!-- END SUB: EL_ADD -->

<!-- SUB: EL_ACL -->
&nbsp;|&nbsp;<a href="editacl.{VAR:ext}?oid={VAR:element_id}&file=form_element.xml" >ACL</a>
<!-- END SUB: EL_ACL -->
</td>
</tr>
<!-- END SUB: ELEMENT_LINE -->

</table>

</td></tr></table>

<font face='tahoma, arial, geneva, helvetica' size="2">
{VAR:reforb}
</font>
</form>
<script language=javascript>
function doSave(el)
{
	document.f1.savedfrom.value=el;
	document.f1.submit();
}
function doAddEl()
{
	document.f1.action.value='add_element';
	document.f1.submit();
}
</script>