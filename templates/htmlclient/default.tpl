<form action='{VAR:handler}.{VAR:ext}' method='post' name='changeform' enctype='multipart/form-data' {VAR:form_target}>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='100000000'>
<table border='0' width='100%' cellspacing='1' cellpadding='1' bgcolor='#FFFFFF'>

{VAR:content}

<!-- SUB: ERROR -->
<tr>
	<td colspan="2" bgcolor="red" align="center"><span style='color: white; font-weight: bold;'>{VAR:error_text}</span></td>
</tr>
<!-- END SUB: ERROR -->

<!-- SUB: PROP_ERR_MSG -->
<tr>
	<td class="chformleftcol" width='160'>
	</td>
	<td class='chformrightcol'>
<span style='color: red'>{VAR:err_msg}</span>
	</td>
</tr>	
<!-- END SUB: PROP_ERR_MSG -->

<!-- SUB: LINE -->
<tr>
        <td class='chformleftcol' width='160' nowrap>
        {VAR:caption}
        </td>
        <td class='chformrightcol'>
        {VAR:element}
        </td>
</tr>
<!-- END SUB: LINE -->

<!-- SUB: HEADER -->
<tr>
	<td class='chformsubheader' width='160'>
	&nbsp;
	</td>
	<td class='chformsubheader'>
	{VAR:caption}
	</td>
</tr>
<!-- END SUB: HEADER -->

<!-- SUB: SUB_TITLE -->
<tr>
	<td colspan='2' class='chformsubtitle'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: SUB_TITLE -->

<!-- SUB: CONTENT -->
<tr>
	<td colspan='2' class='chformrightcol'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: CONTENT -->

<!-- SUB: SUBMIT -->
<tr>
	<td class='chformleftcol' align='center' width='160'>&nbsp;</td>
	<td class='chformrightcol'>
		<input type='submit' value='Salvesta' class='small_button' onClick='submit_changeform(); return false;'>
	</td>
</tr>
<!-- END SUB: SUBMIT -->

<!-- SUB: SUBITEM -->
        {VAR:element}
        {VAR:caption}
	&nbsp;
<!-- END SUB: SUBITEM -->

<!-- SUB: SUBITEM2 -->
        {VAR:caption}
        {VAR:element}
	&nbsp;
<!-- END SUB: SUBITEM2 -->

{VAR:reforb}
<script type="text/javascript">
function submit_changeform()
{
	{VAR:submit_handler}
	document.changeform.submit();
}
</script>
</form>
</table>

<!-- SUB: iframe_body_style -->
body {
        background-color: #FFFFFF;
        margin: 0px;
        overflow-y: hidden;
        overflow:hidden;
}
<!-- END SUB: iframe_body_style -->
