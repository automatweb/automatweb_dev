<table class="{VAR:webform_form}">
<form action="{VAR:form_handler}" method="{VAR:method}" name="changeform" {VAR:form_target}>
<!--<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='100000000'>-->

{VAR:content}

<!-- SUB: ERROR -->
<tr>
	<td colspan="2" class="{VAR:form_error}">{VAR:error_text}</td>
</tr>
<!-- END SUB: ERROR -->

<!-- SUB: PROP_ERR_MSG -->
<tr>
	<td class="{VAR:webform_caption}"></td>
	<td class="{VAR:webform_errmsg}">{VAR:err_msg}</td>
</tr>	
<!-- END SUB: PROP_ERR_MSG -->

<!-- SUB: LINE -->
<tr>
	<td class="{VAR:webform_caption}">
	{VAR:caption}
	</td>
	<td class="{VAR:webform_element}">
	{VAR:element}
	</td>
</tr>
<!-- END SUB: LINE -->
<!-- SUB: LINE_TOP -->
<tr>
	<td class="{VAR:webform_caption}" colspan="2">
	{VAR:caption}
	</td>
</tr>
<tr>
	<td class="{VAR:webform_element}" colspan="2">
	{VAR:element}
	</td>
</tr>
<!-- END SUB: LINE_TOP -->
<!-- SUB: LINE_BOTTOM -->
<tr>
	<td class="{VAR:webform_caption}" colspan="2">
	{VAR:element}
	</td>
</tr>
<tr>
	<td class="{VAR:webform_element}" colspan="2">
	{VAR:caption}
	</td>
</tr>
<!-- END SUB: LINE_BOTTOM -->
<!-- SUB: LINE_RIGHT -->
<tr>
	<td class="{VAR:webform_element}">
	{VAR:element}
	</td>
	<td class="{VAR:webform_caption}">
	{VAR:caption}
	</td>
</tr>
<!-- END SUB: LINE_RIGHT -->
<!-- SUB: HEADER -->
<tr>
	<td class="{VAR:webform_header}" colspan="2">
	{VAR:caption}
	</td>
</tr>
<!-- END SUB: HEADER -->

<!-- SUB: SUB_TITLE -->
<tr>
	<td colspan="2" class="{VAR:webform_subtitle}">
	{VAR:value}
	</td>
</tr>
<!-- END SUB: SUB_TITLE -->

<!-- SUB: CONTENT -->
<tr>
	<td colspan="2" class="{VAR:webform_content}">
	{VAR:value}
	</td>
</tr>
<!-- END SUB: CONTENT -->

<!-- SUB: SUBMIT -->
<tr>
	<td class="{VAR:webform_content}" colspan="2">
		<input type="submit" name="{VAR:name}" value="{VAR:sbt_caption}">
	</td>
</tr>
<!-- END SUB: SUBMIT -->

<!-- SUB: SUBMIT_RIGHT -->
<tr class="{VAR:webform_content}">
	<td>
	</td>
	<td>
		<input type="submit" name="{VAR:name}" value="{VAR:sbt_caption}">
	</td>
</tr>
<!-- END SUB: SUBMIT_RIGHT -->

<!-- SUB: SUBITEM -->
	<span style="color: red">{VAR:err_msg}</span>
        {VAR:element}
        {VAR:caption}
	&nbsp;
<!-- END SUB: SUBITEM -->

<!-- SUB: SUBITEM2 -->
	<span style="color: red">{VAR:err_msg}</span>
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
