<form method="get" action="orb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_TARGET}:</td>
	<td class="fform">{VAR:name}</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_TOT_TO}:</td>
	<td class="fform">{VAR:t_turnover}</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_TOT_ORD}:</td>
	<td class="fform">{VAR:t_orders}</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_ORD_AV}:</td>
	<td class="fform">{VAR:avg_order}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_DET_STAT}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_FROM}:</td>
</tr>
<tr>
	<td colspan=2 class="fform">{VAR:t_from}</td>
</tr>
<tr>
	<td colspan=2 class="fcaption2">{VAR:LC_SHOP_TO}:</td>
</tr>
<tr>
	<td colspan=2 class="fform">{VAR:t_to}</td>
</tr>
<tr>
	<td class="fcaption2"><input checked type='radio' name='stat_type' VALUE='by_day'></td>
	<td class="fform">{VAR:LC_SHOP_BY_DAYS}:</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_month'></td>
	<td class="fform">{VAR:LC_SHOP_BY_MONTHS}:</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_wd'></td>
	<td class="fform">{VAR:LC_SHOP_BY_WEEKDAYS}:</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_hr'></td>
	<td class="fform">{VAR:LC_SHOP_BY_HOURS}:</td>
</tr>
<tr>
	<td class="fform" colspan="2"><input type="submit" value="{VAR:LC_SHOP_SHOW}">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
