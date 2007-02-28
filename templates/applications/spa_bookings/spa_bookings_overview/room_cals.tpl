<table id="sisu"><tr><td>
<form name="changeform" action="reforb.{VAR:ext}" method="POST">
{VAR:reforb}

{VAR:toolbar}
{VAR:picker}
<table cellpadding="5" cellspacing="10" border="0">
<tr>
	<!-- SUB: CAL -->
	<td valign="top" align="left" style="border: 1px black solid;">
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
</form>
</div>
</td></tr></table>
