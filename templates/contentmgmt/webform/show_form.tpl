<!-- SUB: SHOW_CONTENT -->
<b>S&uuml;ndmus:</b> {VAR:ev_title} <br>
<b>Kestab:</b> {VAR:ev_start} - {VAR:ev_end} <br>
<b>Sisu:</b>
<br>{VAR:ev_content}
<br><br>
<!-- END SUB: SHOW_CONTENT -->
<table class="{VAR:webform_form}">
<form action="orb.{VAR:ext}" method="POST" name="changeform" {VAR:form_target}>
{VAR:form}
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