<table border='0' class="aw04contenttable" align="center" cellpadding="0" cellspacing="0" width="100%">
<form action='{VAR:handler}.{VAR:ext}' method='{VAR:method}' name='changeform' enctype='multipart/form-data' {VAR:form_target}>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='100000000'>

{VAR:content}

<!-- SUB: ERROR -->
<tr>
	<td colspan="2" bgcolor="red" align="center"><span style='color: white; font-weight: bold;'>{VAR:error_text}</span></td>
</tr>
<!-- END SUB: ERROR -->

<!-- SUB: PROP_ERR_MSG -->
<tr>
	<td class="aw04contentcellleft" width='80'></td>
	<td class='aw04contentcellright'><span style='color: red'>{VAR:err_msg}</span>	</td>
</tr>	
<!-- END SUB: PROP_ERR_MSG -->

<!-- SUB: LINE -->
<tr>
        <td class='aw04contentcellleft' width='80' nowrap>
		{VAR:caption}
		</td>
        <td class='aw04contentcellright'>
		{VAR:element}
        </td>
</tr>
<!-- END SUB: LINE -->

<!-- SUB: HEADER -->
<tr>
	<td class='aw04contentcellsubheader' width='80'>
	&nbsp;
	</td>
	<td class='aw04contentcellsubheader'>
	{VAR:caption}
	</td>
</tr>
<!-- END SUB: HEADER -->

<!-- SUB: SUB_TITLE -->
<tr>
	<td colspan='2' class='aw04contentcellsubtitle'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: SUB_TITLE -->

<!-- SUB: CONTENT -->
<tr>
	<td colspan='2' class='aw04contentcellcontent'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: CONTENT -->

<!-- SUB: SUBMIT -->
<tr>
	<td class='aw04contentcellleft' align='center' width='80'>&nbsp;</td>
	<td class='aw04contentcellright'>
		<input type='submit' name='{VAR:name}' value='{VAR:sbt_caption}' class='aw04formbutton' onClick='submit_changeform("{VAR:action}"); return false;'>
	</td>
</tr>
<!-- END SUB: SUBMIT -->

<!-- SUB: SUBITEM -->
	<span style='color: red'>{VAR:err_msg}</span>
        {VAR:element}
        {VAR:caption}
	&nbsp;
<!-- END SUB: SUBITEM -->

<!-- SUB: SUBITEM2 -->
	<span style='color: red'>{VAR:err_msg}</span>
        {VAR:caption}
        {VAR:element}
	&nbsp;
<!-- END SUB: SUBITEM2 -->

{VAR:reforb}
<script type="text/javascript">
function submit_changeform(action)
{
	{VAR:submit_handler}
	if (typeof action == "string" && action.length>0)
	{
		document.changeform.action.value = action;
	};
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
