<table class="aste01" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="celltext">Tellimuse nr:</td>
<td class="celltext">{VAR:order_id}</td>
</tr>
<tr>
<td class="celltext">Kasutaja:</td>
<td class="celltext">{VAR:uid}</td>
</tr>
<tr>
<td class="celltext">Kell:</td>
<td class="celltext">{VAR:time}</td>
</tr>
<tr>
<td colspan="2" class="celltext">Tellimisformi sisestus:</td>
</tr>
<tr>
<td colspan="2" class="celltext"><pre>{VAR:of_entry}</pre></td>
</tr>
<tr>
<td colspan="2" class="celltext">Tellitud kaubad:</td>
</tr>
<tr>
	<td colspan="2">
		<table border="0">
			<tr>
				<td class="celltext">Kaup</td>
				<td class="celltext">Kogus</td>
				<td class="celltext">&Uuml;he hind</td>
				<td class="celltext">Kokku hind</td>
			</tr>
			<!-- SUB: ITEM -->
			<tr>
				<td class="celltext"><pre>{VAR:item_op}</pre></td>
				<td class="celltext" valign="top">{VAR:count}</td>
				<td class="celltext" valign="top">{VAR:price}</td>
				<td class="celltext" valign="top">{VAR:it_price}</td>
			</tr>
			<!-- END SUB: ITEM -->
			<tr>
				<td class="celltext">Kokku:</td>
				<td class="celltext">{VAR:t_count}</td>
				<td class="celltext">&nbsp;</td>
				<td class="celltext">{VAR:t_price}</td>
			</tr>
		</table>
	</td>
</tr>
</table>

<a href='{VAR:another}'>Alusta uut tellimust selle p&otilde;hjalt</a>
