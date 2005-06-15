<!-- SUB: TITLE -->
{VAR:title}
<!-- END SUB: TITLE -->

<!-- SUB: TITLE_SEL -->
<b>{VAR:title}</b>
<!-- END SUB: TITLE_SEL -->

<!-- SUB: TITLE_SEP -->
|
<!-- END SUB: TITLE_SEP -->


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
<input type="submit" name="goto_prev" value="<< Tagasi">
<!-- END SUB: PREV_PAGE -->

<input type="submit" value="Salvesta">

<!-- SUB: NEXT_PAGE -->
<input type="submit" name="goto_next" value="Edasi >>">
<!-- END SUB: NEXT_PAGE -->

<!-- SUB: CONFIRM -->
<input type="submit" value="Saada" name="confirm">
<!-- END SUB: CONFIRM -->
</form>


<!-- SUB: FORM_HEADER -->
<tr>
	<td colspan="2">{VAR:form_name}</td>
</tr>
<!-- END SUB: FORM_HEADER -->

<!-- SUB: TABLE_FORM -->
<tr><td colspan="2">
<table border="1">
	<tr>
	<!-- SUB: HEADER -->
		<td>{VAR:caption}</td>
	<!-- END SUB: HEADER -->
	</tr>

	<!-- SUB: FORM -->
	<tr>
		<!-- SUB: ELEMENT -->
		<td>{VAR:element}</td>
		<!-- END SUB: ELEMENT -->
	</tr>
	<!-- END SUB: FORM -->

</table>
</td></tr>
<!-- END SUB: TABLE_FORM -->