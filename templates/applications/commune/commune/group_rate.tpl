<table border="0" class="rate_aw04contentcellleft" align="center" cellpadding="0" cellspacing="0" width="100%">
<form action="{VAR:handler}.{VAR:ext}" method="{VAR:method}" name="changeform" enctype="multipart/form-data" {VAR:form_target}>
<input type="hidden" NAME="MAX_FILE_SIZE" VALUE="1000000">
<!-- SUB: rateform[rate] -->
<tr>
<td class="rate_rowbgcolor_odd" style="text-align:center">{VAR:caption}</td>
<td class="rate_rowbgcolor_even" style="text-align:center">{VAR:element}</td>
</tr>
<!-- END SUB: rateform[rate] -->
<!-- SUB: rateform[image] -->
<tr>
<td class="rate_rowbgcolor_even" style="text-align:center"></td>
<td class="rate_rowbgcolor_od" style="text-align:center">{VAR:value}</td>
</tr>
<!-- END SUB: rateform[image] -->
<!-- {VAR:rateform[image]} -->
{VAR:rateform[image_comment]}
{VAR:rateform[current]}
{VAR:rateform[name]}
{VAR:rateform[add_friend_link]}
{VAR:rateform[add_ignored_link]}
{VAR:rateform[add_blocked_link]}
{VAR:rateform[send_message_link]}
{VAR:rateform[contact_list_link]}
{VAR:rateform[comments][list]}
{VAR:rateform[comments][capt2]}
{VAR:rateform[comments][capt]}
{VAR:rateform[comments][comment]}
{VAR:rateform[vsbt2]}
{VAR:lastrate}
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