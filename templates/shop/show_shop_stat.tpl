<form method="get" action="orb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td colspan=3 class="fcaption2"><a href='{VAR:change_stat}'>{VAR:LC_SHOP_CHANGE_SHOP}</a></td>
</tr>
<!--<tr>
	<td class="fcaption2"><input type='radio' name='type' value='by_cat'></td>
	<td colspan=2 class="fcaption2">{VAR:LC_SHOP_BY_CAT}</td>
</tr>-->
<tr>
	<td class="fcaption2">&nbsp;</td>
	<td colspan=2 class="fcaption2">{VAR:LC_SHOP_CHOOSE_CATEGORY}:</td>
</tr>
<tr>
	<td class="fcaption2">&nbsp;</td>
	<td colspan=2 class="fcaption2"><select multiple name='cats[]'>{VAR:categories}</td>
</tr>
<tr>
	<td class="fcaption2"><input type='checkbox' name='show_to' value='1'></td>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_SHOW_TURNOVER}?</td>
</tr>
<tr>
	<td class="fcaption2" colspan=3>{VAR:LC_SHOP_FROM}:</td>
</tr>
<tr>
	<td colspan=3 class="fform">{VAR:t_from}</td>
</tr>
<tr>
	<td colspan=3 class="fcaption2">{VAR:LC_SHOP_TILL}:</td>
</tr>
<tr>
	<td colspan=3 class="fform">{VAR:t_to}</td>
</tr>
<tr>
	<td class="fcaption2"><input checked type='radio' name='stat_type' VALUE='by_day'></td>
	<td colspan=2 class="fform">{VAR:LC_SHOP_BY_DAYS}</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_month'></td>
	<td colspan=2 class="fform">{VAR:LC_SHOP_BY_MONTHS}</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_wd'></td>
	<td colspan=2 class="fform">{VAR:LC_SHOP_BY_WEEKDAYS}</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_hr'></td>
	<td colspan=2 class="fform">{VAR:LC_SHOP_BY_HOURS}</td>
</tr>
<tr>
	<td class="fform" colspan="3"><input type="submit" value="{VAR:LC_SHOP_SHOW}">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
