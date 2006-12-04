<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css" />
<form action="{VAR:baseurl}/reforb.{VAR:ext}" method="POST">
<table border="0" cellpadding="0" cellspacing="0">
{VAR:form}
</table>
{VAR:reforb}

<input type="submit" value="{VAR:join_but_text}">
</form>

<!-- SUB: ERROR_MESSAGE -->
<tr>
	<td colspan="2"><font color="red">{VAR:msg}</font></td>
</tr>
<!-- END SUB: ERROR_MESSAGE -->
