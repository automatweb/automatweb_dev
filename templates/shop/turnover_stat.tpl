<form method="get" action="orb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Target:</td>
	<td class="fform">{VAR:name}</td>
</tr>
<tr>
	<td class="fcaption2">Total turnover:</td>
	<td class="fform">{VAR:t_turnover}</td>
</tr>
<tr>
	<td class="fcaption2">Total orders:</td>
	<td class="fform">{VAR:t_orders}</td>
</tr>
<tr>
	<td class="fcaption2">Order average:</td>
	<td class="fform">{VAR:avg_order}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Detailed statistics:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>From:</td>
</tr>
<tr>
	<td colspan=2 class="fform">{VAR:t_from}</td>
</tr>
<tr>
	<td colspan=2 class="fcaption2">To:</td>
</tr>
<tr>
	<td colspan=2 class="fform">{VAR:t_to}</td>
</tr>
<tr>
	<td class="fcaption2"><input checked type='radio' name='stat_type' VALUE='by_day'></td>
	<td class="fform">By days:</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_month'></td>
	<td class="fform">By months:</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_wd'></td>
	<td class="fform">By weekdays:</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_hr'></td>
	<td class="fform">By hours:</td>
</tr>
<tr>
	<td class="fform" colspan="2"><input type="submit" value="Show">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
