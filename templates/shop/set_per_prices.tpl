<!-- SUB: PAGE -->
<a href='{VAR:pageurl}'>{VAR:from} - {VAR:to}</a> |
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} | 
<!-- END SUB: SEL_PAGE -->

<form action="reforb.{VAR:ext}" method="POST">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption2">{VAR:LC_SHOP_FROM}:</td>
		<td class="fcaption2">{VAR:LC_SHOP_TO}:</td>
		<!-- SUB: CUR_H -->
		<td class="fcaption2">{VAR:LC_SHOP_PRICE} ({VAR:cur_name}):</td>
		<!-- END SUB: CUR_H -->
		<td class="fcaption2">{VAR:LC_SHOP_WEEK_PRICE}:</td>
		<td class="fcaption2">{VAR:LC_SHOP_2_WEEK_PRICE}:</td>
		<td class="fcaption2">Mitu kohta:</td>
		<td class="fcaption2">{VAR:LC_SHOP_DELETE}:</td>
	</tr>
	<!-- SUB: PERIOD -->
	<tr>
		<td class="fcaption2">{VAR:from}</td>
		<td class="fcaption2">{VAR:to}</td>
		<!-- SUB: CUR -->
		<td class="fcaption2"><input type='text' name='price[{VAR:id}][{VAR:cur_id}]' value='{VAR:price}' size=5 class='small_button'></td>
		<!-- END SUB: CUR -->
		<td class="fcaption2"><input type='radio' name='price_type[{VAR:id}]' value='1' {VAR:week_check}></td>
		<td class="fcaption2"><input type='radio' name='price_type[{VAR:id}]' value='2' {VAR:2week_check}></td>
		<td class="fcaption2"><input type='text' name='available[{VAR:id}]' value='{VAR:avail}' size=3></td>
		<td class="fcaption2"><input type='checkbox' name='del[{VAR:id}]' value=1></td>
	</tr>
	<!-- END SUB: PERIOD -->
	<tr>
		<td class="fcaption2" colspan=9><input type='submit' value='{VAR:LC_SHOP_SAVE}'></td>
	</tr>
</table>
{VAR:reforb}
</form>