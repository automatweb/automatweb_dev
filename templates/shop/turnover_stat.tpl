<form method="get" action="orb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Pood:</td>
	<td class="fform">{VAR:name}</td>
</tr>
<tr>
	<td class="fcaption2">Kokku k&auml;ivet:</td>
	<td class="fform">{VAR:t_turnover}</td>
</tr>
<tr>
	<td class="fcaption2">Kokku tellimusi:</td>
	<td class="fform">{VAR:t_orders}</td>
</tr>
<tr>
	<td class="fcaption2">Keskmine tellimus:</td>
	<td class="fform">{VAR:avg_order}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>T&auml;psem statistika:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Alates:</td>
</tr>
<tr>
	<td colspan=2 class="fform">{VAR:t_from}</td>
</tr>
<tr>
	<td colspan=2 class="fcaption2">Kuni:</td>
</tr>
<tr>
	<td colspan=2 class="fform">{VAR:t_to}</td>
</tr>
<tr>
	<td class="fcaption2"><input checked type='radio' name='stat_type' VALUE='by_day'></td>
	<td class="fform">P&auml;evade kaupa</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_month'></td>
	<td class="fform">Kuude kaupa</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_wd'></td>
	<td class="fform">N&auml;dalap&auml;evade kaupa</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_hr'></td>
	<td class="fform">Tundide kaupa</td>
</tr>
<tr>
	<td class="fform" colspan="2"><input type="submit" value="N&auml;ita">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
