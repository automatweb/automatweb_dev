
<form method="POST" action="reforb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<!-- SUB: TYPE -->
<tr>
	<td class="fcaption2">Items with type {VAR:typename} are shown on Invoice using table <select name='tables[{VAR:type_id}]'>{VAR:tables}</select></td>
</tr>
<!-- END SUB: TYPE -->
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Save">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
