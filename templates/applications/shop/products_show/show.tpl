<table id="contentMainProductlist">
<!-- SUB: ROW -->
<tr>
	<!-- SUB: PRODUCT -->
		<td class="product">
				<div class="productImage">
					{VAR:image}
					<p>{VAR:brand}</p>
				</div>
				<div class="productInfo">
					<p><a href="{VAR:product_link}">{VAR:name}</a></p>
					<p>Hind:</p>
					<p>{VAR:price} {VAR:currency}</p>
				</div>
			</td>
	<!-- END SUB: PRODUCT -->

	<!-- SUB: PRODUCT_END -->
		<td class="product paddingRight0">
				<div class="productImage">
					{VAR:image}
					<p>{VAR:brand}</p>
				</div>
				<div class="productInfo">
					<p><a href="{VAR:product_link}">{VAR:name}</a></p>
					<p>Hind:</p>
					<p>{VAR:price} {VAR:currency}</p>
				</div>
			</td>
	<!-- END SUB: PRODUCT_END -->
</tr>
<tr class="productSpacer">
	<td colspan="4"></td>
</tr>
<!-- END SUB: ROW -->
</table>
