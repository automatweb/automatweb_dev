<div id="sisu">
<form name="changeform" action="reforb.{VAR:ext}" method="POST">
{VAR:toolbar}
{VAR:picker}
<table cellpadding="5" cellspacing="10" border="0">
<tr>
	<!-- SUB: CAL -->
	<td valign="top" align="left" style="border: 1px black solid;">
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
</div>
