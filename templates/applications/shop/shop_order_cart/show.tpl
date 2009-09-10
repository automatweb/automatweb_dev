<table id="shop_basket_content">
	<tr>
		<td id="contentSidebar">
			<div id="contentSidebarStart"><a href="/"><img src="/img/contentSidebarStart{VAR:LC_IMAGE_SUFIX}.gif" alt="{VAR:LC_GO_TO_FRONTPAGE}" title="{VAR:LC_GO_TO_FRONTPAGE}" /></a></div>
			<div class="contentSidebarBasketNav">
				<div class="contentSidebarBasketNavHeader"><!-- --></div>
				<ul class="level1">
					<li class="levelActive"><span>Ostukorv</span></li>
					<li class=""><span>Tellimuse andmed</span></li>
					<li class=""><span>Tellija andmed</span></li>
					<li><span>Ostukorvi kinnitus</span></li>
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
				<td>Ostukorv</td>
			</tr>
		</table>
	</div>
</div>

<div class="contentMainBasket contentMainBasket1">
	<form action="{VAR:baseurl}/index.{VAR:ext}" method="post">

		<table class="table1">
			<thead>
				<tr>
					<th class="first">Toode</th>
					<th class="second">Kogus</th>
					<th class="third">Hind ({VAR:currency})</th>
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
										<p class="title"><a href="">{VAR:packet_name}, {VAR:brand_name}</a></p>
										<p>
											{VAR:Tootekood}: {VAR:code}, ( {VAR:price} / tk)
										</p>
										<p class="removeProduct">
											<a href="JavaScript:void(0);" onclick="removeProductFromBasket(this, '{VAR:remove_url}');">{VAR:Eemalda toode}</a>
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
							{VAR:total_price}
						</td>
					</tr>
				<!-- END SUB: PRODUCT -->			
			</tbody>
		</table>

		<table class="total1">
			<tr>
				<td class="first">
						<strong>Kokku</strong><br />
				</td>
				<td class="second">
					{VAR:basket_total_price}
				</td>
			</tr>
		</table>


		{VAR:reforb}
		<input type="hidden" name="go_to_after" value="{VAR:baseurl}/index.aw?action=order_data&class=shop_order_cart&cart={VAR:cart}&section={VAR:section}">
		<input type="hidden" name="cart" value="{VAR:cart}">
		<input type="hidden" name="section" value="{VAR:section}">
		<!-- SUB: HAS_PRODUCTS -->
		<table class="buttons">
			<tr>
				<td class="left" style="width: 300px;">
					<!--<input type="submit" value="MUUDA KOGUST" />-->
				</td>
				<td class="space"></td>
				<td class="right">
					<input type="submit" value="Edasi"/>
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