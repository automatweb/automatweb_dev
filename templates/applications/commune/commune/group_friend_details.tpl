
<table class="text" border="0" align="center" width="100%">
<tr>
<td>
{VAR:view}
</td>
</tr>
<form action="{VAR:handler}.{VAR:ext}" method="{VAR:method}" name="changeform" {VAR:form_target}>
<!-- SUB: friendgroups -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even">{VAR:element}</td>
</tr>
<!-- END SUB: friendgroups -->
<!-- SUB: addfriend -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd">{VAR:element}</td>
</tr>
<!-- END SUB: addfriend -->
<!-- SUB: addignored -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even">{VAR:element}</td>
</tr>
<!-- END SUB: addignored -->
<!-- SUB: addblocked -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd">{VAR:element}</td>
</tr>
<!-- END SUB: addblocked -->
{VAR:profile_comments[capt2]}
<!-- SUB: profile_comments[list] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even">{VAR:element}</td>
</tr>
<!-- END SUB: profile_comments[list] -->
<!-- SUB: profile_comments[capt] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd">{VAR:element}</td>
</tr>
<!-- END SUB: profile_comments[capt] -->
<!-- SUB: profile_comments[comment] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even">{VAR:element}</td>
</tr>
<!-- END SUB: profile_comments[comment] -->
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