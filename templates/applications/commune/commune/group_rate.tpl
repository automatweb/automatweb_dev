<table border="0" align="center" cellpadding="0" cellspacing="0" width="100%" class="text">
<form action="{VAR:handler}.{VAR:ext}" method="{VAR:method}" name="changeform" {VAR:form_target}>
<!-- SUB: rateform[rate] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[rate] -->
<!-- SUB: rateform[image] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">&nbsp;</td>
<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:value}</td>
</tr>
<!-- END SUB: rateform[image] -->
<!-- SUB: rateform[image_comment] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[image_comment] -->
<!-- SUB: rateform[current] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[current] -->
<!-- SUB: rateform[name] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[name] -->
<!-- SUB: rateform[add_friend_link] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[add_friend_link] -->
<!-- SUB: rateform[add_ignored_link] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[add_ignored_link] -->
<!-- SUB: rateform[add_blocked_link] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[add_blocked_link] -->
<!-- SUB: rateform[send_message_link] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[send_message_link] -->
<!-- SUB: rateform[contact_list_link] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[contact_list_link] -->
<!-- SUB: rateform[comments][list] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[comments][list] -->
{VAR:rateform[comments][capt2]}

<!-- SUB: rateform[comments][capt] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[comments][capt] -->
<!-- SUB: rateform[comments][comment] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center;width:80px">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[comments][comment] -->
{VAR:rateform[vsbt2]}
{VAR:lastrate}
{VAR:reforb}
<script type="text/javascript">
function submit_changeform(action)
{
	{VAR:submit_handler}
	if (typeof action == "string" && action.length > 0)
	{
		document.changeform.action.value = action;
	};
	document.changeform.submit();
}
</script>
</form>
</table>