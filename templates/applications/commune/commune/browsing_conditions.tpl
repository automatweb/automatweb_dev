<form action="{VAR:handler}.{VAR:ext}" method="{VAR:method}" name="changeform2">
{VAR:opts_table}
<table border="0" align="center" width="100%" class="text">
<tr>
<td align="center">
<input type="submit" name="submit" value="Muuda sätteid" class="aw04formbutton" onClick="submit_changeform2('');" return false;">
</td>
</tr>
</table>
{VAR:reforb}
<script type="text/javascript">
function submit_changeform2(action)
{
	{VAR:submit_handler}
	if (typeof action == "string" && action.length>0)
	{
		document.changeform2.action.value = action;
	};
	document.changeform2.submit();
}
</script>
</form>