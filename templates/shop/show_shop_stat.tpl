<form method="get" action="orb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td colspan=3 class="fcaption2"><a href='{VAR:change_stat}'>Muuda poode</a></td>
</tr>
<!--<tr>
	<td class="fcaption2"><input type='radio' name='type' value='by_cat'></td>
	<td colspan=2 class="fcaption2">Kategooriate kaupa</td>
</tr>-->
<tr>
	<td class="fcaption2">&nbsp;</td>
	<td colspan=2 class="fcaption2">Vali kategooriad:</td>
</tr>
<tr>
	<td class="fcaption2">&nbsp;</td>
	<td colspan=2 class="fcaption2"><select multiple name='cats[]'>{VAR:categories}</td>
</tr>
<tr>
	<td class="fcaption2"><input type='checkbox' name='show_to' value='1'></td>
	<td class="fcaption2" colspan=2>N&auml;itan ka k&auml;ivet?</td>
</tr>
<tr>
	<td class="fcaption2" colspan=3>Alates:</td>
</tr>
<tr>
	<td colspan=3 class="fform">{VAR:t_from}</td>
</tr>
<tr>
	<td colspan=3 class="fcaption2">Kuni:</td>
</tr>
<tr>
	<td colspan=3 class="fform">{VAR:t_to}</td>
</tr>
<tr>
	<td class="fcaption2"><input checked type='radio' name='stat_type' VALUE='by_day'></td>
	<td colspan=2 class="fform">P&auml;evade kaupa</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_month'></td>
	<td colspan=2 class="fform">Kuude kaupa</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_wd'></td>
	<td colspan=2 class="fform">N&auml;dalap&auml;evade kaupa</td>
</tr>
<tr>
	<td class="fcaption2"><input type='radio' name='stat_type' VALUE='by_hr'></td>
	<td colspan=2 class="fform">Tundide kaupa</td>
</tr>
<tr>
	<td class="fform" colspan="3"><input type="submit" value="N&auml;ita">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
