<table id="shop_basket_content">
	<tr>
		<td id="contentSidebar">
			<div class="contentSidebarBasketNav">
				<div class="contentSidebarBasketNavHeader"><!-- --></div>
				<ul class="level1">
					<li class="levelActive"><span>{VAR:LC_BASKET_STEP_1}</span></li>
					<li class=""><span>{VAR:LC_BASKET_STEP_2}</span></li>
					<li class=""><span>{VAR:LC_BASKET_STEP_3}</span></li>
					<li><span>{VAR:LC_BASKET_STEP_4}</span></li>
				</ul>
				<div class="contentSidebarBasketNavFooter"><!-- --></div>
			</div>
			<br class="clear" />
			<div class="separator"></div>
		</td>
		<td id="contentMain">

<div id="contentMainHead2">
	<div class="title">
		<table>
			<tr>
				<td class="shopStep"><span>1</span></td>
				<td>{VAR:LC_BASKET_STEP_1}</td>
			</tr>
		</table>
	</div>
</div>

<div class="contentMainBasket contentMainBasket1">
	<form action="{VAR:baseurl}/index.{VAR:ext}" method="post">

		<table class="table1">
			<thead>
				<tr>
					<th class="first">{VAR:LC_PRODUCT}</th>
					<th class="second">{VAR:LC_COUNT}</th>
					<th class="third">{VAR:LC_PRICE} ({VAR:LC_MONEY_EXT})</th>
				</tr>
			</thead>
			<tbody>
				<!-- SUB: PRODUCT -->
					<tr class="row">
						<td class="first">
							<table>
								<tr>
									<td style="width: 88px;">
										<img src="{VAR:image_url}" alt="" width="69" />
									</td>
									<td>
										<p class="title"><a href="">{VAR:name}, {VAR:brand_name}</a></p>
										<p>
											{VAR:LC_PRODUCT_NR}: {VAR:code}, ({VAR:LC_MONEY_EXT_EUR} {VAR:price} / {VAR:LC_PIECE2})
										</p>
										<p class="removeProduct">
											<a href="JavaScript:void(0);" onclick="removeProductFromBasket(this, '{VAR:remove_url}');">{VAR:LC_REMOVE_PRODUCT}</a>
										</p>
									</td>
								</tr>
							</table>
						</td>
						<td class="second">
							<table>
								<tr>
									<td class="firstCartamountCol">
										<input type="text" size="3" style="width: 25px;" name="add_to_cart[{VAR:id}]" value="{VAR:amount}" class="productCount"/>
									</td>
									<td class="secondCartamountCol">
										<a class="productCountIncrease" href="JavaScript:void(0);"/>
									</td>
									<td class="thirdCartamountCol">
										<a class="productCountDecrease" href="JavaScript:void(0);"/>
									</td>
								</tr>
							</table>

						<td class="third">
							{VAR:total_price_without_thousand_separator}
						</td>
					</tr>
				<!-- END SUB: PRODUCT -->			
			</tbody>
		</table>

		<table class="total1">
			<tr>
				<td class="first">
						<strong>{VAR:LC_PRODUCT_SUM}</strong><br />
						<!--alle Angaben in EUR, inkl. MwSt. 	<br /> 
						zzgl. Versand- und Servicekosten 	<br />
						(Anzeige erst nach Anmeldung mĆ¶glich) 	-->
				</td>
				<td class="second">
					{VAR:basket_total_price}
				</td>
			</tr>
		</table>


		{VAR:reforb}
		<input type="hidden" name="go_to_after" value="{VAR:baseurl}/index.aw?action=order_data&class=shop_order_cart&cart={VAR:cart}&section={VAR:LC_BASKET_STEP2_SECTION}">
		<input type="hidden" name="cart" value="{VAR:cart}">
		<input type="hidden" name="section" value="{VAR:LC_BASKET_STEP2_SECTION}">
		<!-- SUB: HAS_PRODUCTS -->
		<table class="buttons">
			<tr>
				<td class="left" style="width: 300px;">
					<!--<input type="submit" value="MUUDA KOGUST" />-->
				</td>
				<td class="space"></td>
				<td class="right">
					<input type="submit" value="{VAR:LC_FORWARD}"/>
				</td>
			</tr>
		</table>
		<!-- END SUB: HAS_PRODUCTS -->
	</form>

</div><!-- .contentMainBasket1 -->

		</td>
	</tr>
</table>

<script type="text/javascript">
/*
 * product remover
 */
function removeProductFromBasket(that, removeUrl) {
	$.ajax({
	  type: "POST",
	  url: removeUrl,
	  success: function(msg){
		  	var prodRow = $(that).parent().parent().parent().parent().parent().parent().parent();
				prodRow.hide();
				window.location.reload(true)
	  }
	});
}

</script>