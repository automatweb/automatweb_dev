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
	<td class="fcaption2">Month</td>
	<td class="fform">Orders</td>
</tr>
<!-- SUB: MONTH -->
<tr>
	<td class="fcaption2">{VAR:mon}</td>
	<td class="fform">{VAR:cnt}</td>
</tr>
<!-- END SUB: MONTH -->
<tr>
	<td class="fcaption2">Total:</td>
	<td class="fform">{VAR:t_cnt}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>&nbsp;</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4>The number of orders is <font color="#0000ff">blue</font>.</td>
</tr>
<tr>
	<td class="fcaption2" colspan=4><img src='{VAR:chart}'></td>
</tr>
</table>