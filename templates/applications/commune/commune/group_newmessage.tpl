<table class="text" border="0" align="center" width="100%">
<form action="{VAR:handler}.{VAR:ext}" method="{VAR:method}" name="changeform" {VAR:form_target}>
<!-- SUB: mgroup -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even">{VAR:element}</td>
</tr>
<!-- END SUB: mgroup -->
<!-- SUB: user_from -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd">{VAR:element}</td>
</tr>
<!-- END SUB: user_from -->
<!-- SUB: user_to -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even">{VAR:element}</td>
</tr>
<!-- END SUB: user_to -->
<!-- SUB: subject -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd">{VAR:element}</td>
</tr>
<!-- END SUB: subject -->
<!-- SUB: content -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even">{VAR:element}</td>
</tr>
<!-- END SUB: content -->
{VAR:SUBMIT}
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