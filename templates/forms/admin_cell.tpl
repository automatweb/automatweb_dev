<br>
<form action='reforb.{VAR:ext}' METHOD=post name=f1 enctype='multipart/form-data'>
<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC" width=100%>

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>ELEMENDID:
<a href="javascript:document.f1.submit()">Salvesta</a>
&nbsp;|&nbsp;<a href='forms.{VAR:ext}?type=change_form_cell&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>M&auml;&auml;rangud</a>
&nbsp;|&nbsp;<a href='{VAR:cell_style}'>Stiilid</a>
<!-- SUB: CAN_ACTION -->
&nbsp;|&nbsp;<a href='forms.{VAR:ext}?type=cell_actions&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>Actionid</a>
<!-- END SUB: CAN_ACTION -->
&nbsp;|&nbsp;<a href='forms.{VAR:ext}?type=cell_controllers&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>Kontrollerid</a> | 
<!-- SUB: CAN_ADD -->
<a href='{VAR:add_el}'>Lisa</a>
<!-- END SUB: CAN_ADD -->

</b></td>
</tr>

<!-- SUB: ELEMENT_LINE -->
<tr><td><a name='el_{VAR:after}'>{VAR:element}</td></tr>
<tr>
<td height="15" colspan="15" class="fgtitle"><a href="javascript:document.f1.submit()">Salvesta</a>
<!-- SUB: EL_ADD -->

<!-- SUB: EL_NLAST -->
&nbsp;|&nbsp;<a href="forms.{VAR:ext}?type=add_element&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}&after={VAR:after}" >Lisa siia</a>
<!-- END SUB: EL_NLAST -->
&nbsp;|&nbsp;<a href="forms.{VAR:ext}?type=add_element&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}" >Lisa l&otilde;ppu</a>
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