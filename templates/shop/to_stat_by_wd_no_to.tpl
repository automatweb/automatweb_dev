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
	<td class="fform">Ordered items</td>
</tr>
<!-- SUB: WD -->
<tr>
	<td class="fcaption2">{VAR:wd}</td>
	<td class="fform">{VAR:o_cnt}</td>
	<td class="fform">{VAR:i_cnt}</td>
</tr>
<!-- END SUB: WD -->
<tr>
	<td class="fcaption2">Total:</td>
	<td class="fform">{VAR:t_o_cnt}</td>
	<td class="fform">{VAR:t_i_cnt}</td>
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