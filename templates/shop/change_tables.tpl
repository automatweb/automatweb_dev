
<form method="POST" action="reforb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<!-- SUB: TYPE -->
<tr>
	<td class="fcaption2">Kaupu t&uuml;&uuml;biga {VAR:typename} n&auml;idatakse arvel kasutades tabelit <select name='tables[{VAR:type_id}]'>{VAR:tables}</select></td>
</tr>
<!-- END SUB: TYPE -->
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Salvesta">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
