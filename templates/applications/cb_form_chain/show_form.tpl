<table border="0" align="center" width="100%">
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

</table>

<!-- SUB: PREV_PAGE -->
<input type="button" onClick="window.location.href='{VAR:prev_link}'" value="<< Tagasi">
<!-- END SUB: PREV_PAGE -->

<input type="submit" value="Salvesta">

<!-- SUB: NEXT_PAGE -->
<input type="button" onClick="window.location.href='{VAR:next_link}'" value="Edasi >>">
<!-- END SUB: NEXT_PAGE -->

<!-- SUB: CONFIRM -->
<input type="submit" value="Saada" name="confirm">
<!-- END SUB: CONFIRM -->
</form>
