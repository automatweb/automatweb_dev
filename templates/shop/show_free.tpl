<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption2">{VAR:LC_SHOP_PERIOD} {VAR:LC_SHOP_START}</td>
		<td class="fcaption2">{VAR:LC_SHOP_PERIOD} {VAR:LC_SHOP_END}</td>
		<td class="fcaption2">{VAR:LC_SHOP_RESERVED}</td>
		<td class="fcaption2">{VAR:LC_SHOP_FREE}</td>
		<td class="fcaption2">{VAR:LC_SHOP_VIEW}</td>
	</tr>

	<!-- SUB: LINE -->
	<tr>
		<td class="fcaption2">{VAR:period}</td>
		<td class="fcaption2">{VAR:period_end}</td>
		<td class="fcaption2">{VAR:num_sold}</td>
		<td class="fcaption2">{VAR:free}</td>
		<td class="fcaption2"><a href='{VAR:view}'>{VAR:LC_SHOP_VIEW} {VAR:LC_SHOP_ORDERS}</a></td>
	</tr>
	<!-- END SUB: LINE -->
	<tr>
		<td colspan=2 class="fcaption2">{VAR:LC_SHOP_TOTAL} {VAR:LC_SHOP_RESERVED}:</td>
		<td colspan=3 class="fcaption2">{VAR:t_sold}</td>
	</tr>
</table>
