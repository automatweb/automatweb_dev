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
	<td class="fform">{VAR:LC_SHOP_ORDERS}</td>
	<td class="fform">{VAR:LC_SHOP_ORDERED_ITEMS}</td>
</tr>
<!-- SUB: WD -->
<tr>
	<td class="fcaption2">{VAR:wd}</td>
	<td class="fform">{VAR:o_cnt}</td>
	<td class="fform">{VAR:i_cnt}</td>
</tr>
<!-- END SUB: WD -->
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_TOTAL}:</td>
	<td class="fform">{VAR:t_o_cnt}</td>
	<td class="fform">{VAR:t_i_cnt}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>&nbsp;</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>{VAR:LC_SHOP_TOTAL} {VAR:LC_SHOP_IS} <font color="#ff0000">{VAR:LC_SHOP_GREEN}</font>, {VAR:LC_SHOP_AVERAGE} {VAR:LC_SHOP_IS} <font color="#00ff00">{VAR:LC_SHOP_RED}</font> {VAR:LC_SHOP_NUMB_OF ORDERS} <font color="#0000ff">{VAR:LC_SHOP_BLUE}</font>.</td></tr>
<tr>
	<td class="fcaption2" colspan=4><img src='{VAR:chart}'></td>
</tr>
</table>