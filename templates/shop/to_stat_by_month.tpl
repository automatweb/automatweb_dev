<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Target:</td>
	<td colspan=3 class="fform">{VAR:name}</td>
</tr>
<tr>
	<td class="fcaption2">From:</td>
	<td colspan=3 class="fform">{VAR:from}</td>
</tr>
<tr>
	<td class="fcaption2">To:</td>
	<td colspan=3 class="fform">{VAR:to}</td>
</tr>
<tr>
	<td class="fcaption2">Kuu</td>
	<td class="fform">Total turnover</td>
	<td class="fform">Orders</td>
	<td class="fform">Average</td>
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
	<td class="fcaption2">Total:</td>
	<td class="fform">{VAR:t_sum}</td>
	<td class="fform">{VAR:t_cnt}</td>
	<td class="fform">{VAR:t_avg}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>&nbsp;</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>Total is <font color="#ff0000">green</font>, average is <font color="#00ff00">red</font> and number of orders <font color="#0000ff">blue</font>.</td></tr>
<tr>
	<td class="fcaption2" colspan=4><img src='{VAR:chart}'></td>
</tr>
</table>