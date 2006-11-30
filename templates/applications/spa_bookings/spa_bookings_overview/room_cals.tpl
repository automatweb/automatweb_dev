<form name="changeform" action="reforb.{VAR:ext}" method="POST">
{VAR:toolbar}
{VAR:picker}
<table border="1">
<tr>
	<!-- SUB: CAL -->
	<td valign="top" align="left">
		{VAR:name}
		{VAR:cal}
	</td>
	<!-- END SUB: CAL -->
</tr>
</table>
<script language="javascript">
function submit_changeform()
{
	document.changeform.submit();
}
</script>
{VAR:reforb}
</form>
