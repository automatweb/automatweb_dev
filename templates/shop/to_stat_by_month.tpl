<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_TARGET}:</td>
	<td colspan=3 class="fform">{VAR:name}</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_FROM}:</td>
	<td colspan=3 class="fform">{VAR:from}</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_TO}:</td>
	<td colspan=3 class="fform">{VAR:to}</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_MONTH}</td>
	<td class="fform">{VAR:LC_SHOP_TOT_TO}</td>
	<td class="fform">{VAR:LC_SHOP_ORDERS}</td>
	<td class="fform">{VAR:LC_SHOP_AVERAGE}</td>
</tr>
<!-- SUB: MONTH -->
<tr>
	<td class="fcaption2">{VAR:mon}</td>
	<td class="fform">{VAR:sum}</td>
	<td class="fform">{VAR:cnt}</td>
	<td class="fform">{VAR:avg}</td>
</tr>
<!-- END SUB: MONTH -->
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_TOTAL}:</td>
	<td class="fform">{VAR:t_sum}</td>
	<td class="fform">{VAR:t_cnt}</td>
	<td class="fform">{VAR:t_avg}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>&nbsp;</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>{VAR:LC_SHOP_TOTAL} {VAR:LC_SHOP_IS} <font color="#ff0000">{VAR:LC_SHOP_GREEN}</font>, {VAR:LC_SHOP_AVERAGE} {VAR:LC_SHOP_IS} <font color="#00ff00">{VAR:LC_SHOP_RED}</font> {VAR:LC_SHOP_NUMB_OF_ORDERS} <font color="#0000ff">{VAR:LC_SHOP_BLUE}</font>.</td></tr>
<tr>
	<td class="fcaption2" colspan=4><img src='{VAR:chart}'></td>
</tr>
</table>