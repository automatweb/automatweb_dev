<form action="reforb.{VAR:ext}" method="POST">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption2">Alates:</td>
		<td class="fcaption2">Kuni:</td>
		<td class="fcaption2">Hind:</td>
		<td class="fcaption2">N&auml;dala hind:</td>
		<td class="fcaption2">P&auml;eva hind:</td>
		<td class="fcaption2">Kustuta:</td>
	</tr>
	<!-- SUB: PERIOD -->
	<tr>
		<td class="fcaption2">{VAR:from}</td>
		<td class="fcaption2">{VAR:to}</td>
		<td class="fcaption2"><input type='text' name='price[{VAR:id}]' value='{VAR:price}' size=5 class='small_button'></td>
		<td class="fcaption2"><input type='radio' name='price_type[{VAR:id}]' value='1' {VAR:week_check}></td>
		<td class="fcaption2"><input type='radio' name='price_type[{VAR:id}]' value='2' {VAR:day_check}></td>
		<td class="fcaption2"><input type='checkbox' name='del[{VAR:id}]' value=1></td>
	</tr>
	<!-- END SUB: PERIOD -->
	<tr>
		<td class="fcaption2" colspan=8><input type='submit' value='Salvesta'></td>
	</tr>
</table>
{VAR:reforb}
</form>