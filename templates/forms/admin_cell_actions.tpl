<table bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0><a href='forms.{VAR:ext}?type=change_form_cell&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>M&auml;&auml;rangud</a></td>
		<td bgcolor=#f0f0f0><a href='styles.{VAR:ext}?type=select_style&parent={VAR:parent}'>Stiilid</a></td>
		<td bgcolor=#a0a0a0><a href='forms.{VAR:ext}?type=cell_actions&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>Actionid</a></td>
		<td bgcolor=#f0f0f0><a href='forms.{VAR:ext}?type=cell_controllers&f_id={VAR:form_id}&col={VAR:form_col}&row={VAR:form_row}'>Kontrollerid</a></td>
	</tr>
</table>
<br>
<form action='refcheck.{VAR:ext}' METHOD=post NAME=stil>
{VAR:elements}
<A NAME = "buttons">
<font face='tahoma, arial, geneva, helvetica' size="2"><input type='submit' NAME='save_cell_action' VALUE='Salvesta'>
<input type='hidden' NAME='action' VALUE='admin_cell'>
<input type='hidden' NAME='id' VALUE='{VAR:form_id}'>
<input type='hidden' NAME='col' VALUE='{VAR:form_col}'>
<input type='hidden' NAME='row' VALUE='{VAR:form_row}'>
</font></form>
    
