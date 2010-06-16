{VAR:show_thanku}<form method="POST">
<!-- SUB: shop_table -->
<table border="0" cellpadding="0" cellspacing="1" width="100%">
<tr>
	<td class="prodcarthead">Toote nimetus</td>
	<td class="prodcarthead">Kood</td>
	<td class="prodcarthead">V&auml;rvus</td>
	<td class="prodcarthead">Suurus</td>
	<td class="prodcarthead">Kogus</td>
	<td class="prodcarthead">Lehek&uuml;lg</td>
	<td class="prodcarthead">Pilt</td>
	<td class="prodcarthead">Hind</td>
	<td class="prodcarthead">Summa</td>
</tr>
<!-- SUB: shop_cart_table -->
	<tr class="prodcartitem">
		<td>{VAR:name_value}</td>
		<td>{VAR:product_code_value}</td>
		<td>{VAR:product_color_value}</td>
		<td>{VAR:product_size_value}</td>
		<td>{VAR:product_count_value}</td>
		<td>{VAR:product_page_value}</td>
		<td>{VAR:product_image_value}</td>
		<td>{VAR:product_price_value}</td>
		<td>{VAR:product_sum_value_value} EUR</td>
	</tr>
<!-- END SUB: shop_cart_table -->



<tr class="prodcartitem">
	<td colspan="7"><b>Postikulu</b></td>
	<td colspan="1">{VAR:delivery_price}</td>
	<td colspan="2"> EUR</td>
</tr>
<tr class="prodcartitem">
	<td colspan="8"><b></b></td>
	<td colspan="3"></td>
</tr>
</table>
<!-- END SUB: shop_table -->
{VAR:add_items}{VAR:reforb}
							<input type="submit" value="Takaisin" onclick="history.back(); return false;" />
	
<input onClick="parent.location='{VAR:confirm_url}'" class="formbutton" type='button' name='' value='Kinnita tellimus'>
{VAR:add_persondata}
{VAR:show_confirm}