<table id="content">
	<tr>
		<td id="contentSidebar">
			<div class="contentSidebarBasketNav">
				<div class="contentSidebarBasketNavHeader"><!-- --></div>
				<ul class="level4">
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_1}</span></li>
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_2}</span></li>
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_3}</span></li>
					<li class="levelActive"><span>{VAR:LC_BASKET_STEP_4}</span></li>
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
							<td class="shopStep"><span>4</span></td>
							<td>{VAR:LC_BASKET_STEP_4}</td>
						</tr>
					</table>
				</div>
			</div>
					
			<div class="contentMainBasket contentMainBasket2">
						
			<form action="{VAR:confirm_url}" method="POST">

				<table class="product_table">
					<tr>
						<th colspan="2">{VAR:LC_PRODUCT}</th>
						<th>{VAR:LC_COUNT}</th>
						<th>{VAR:LC_PRICE} ({VAR:LC_MONEY_EXT})</th>
					</tr>
					<!-- SUB: PRODUCT -->
					<tr>
						<td class="product_image"><img src="{VAR:image_url}" alt="" /></td>
						<td class="product_info">
							<p class="product_name">{VAR:name}, {VAR:brand_name}</p>
							<p>{VAR:LC_PRODUCT_NR}: {VAR:code} ({VAR:price} / {VAR:LC_PIECE2})</p>
						</td>
						<td class="product_amount">{VAR:amount}</td>
						<td class="product_sum">{VAR:total_price}</td>
					</tr>
					<!-- END SUB: PRODUCT -->
<!-- SUB: HAS_DELIVERY -->
					<tr>
						<td></td>
						<td class="orderer_information" colspan="3">
							<table class="orderer_information_table">
								<tr>
									<td class="caption">{VAR:LC_DELIVERY_METHOD}:</td>
									<td class="element">{VAR:delivery_name}</td>
								</tr>
								<tr>
									<td class="caption">{VAR:LC_PAYMENT_METHOD}:</td>
									<td class="element">{VAR:payment_name}</td>
								</tr>
							</table>
						</td>
					</tr>
<!-- END SUB: HAS_DELIVERY -->
					<tr>
						<td></td>
						<td class="orderer_information" colspan="3">
							<table class="orderer_information_table">
								<tr>
									<td class="caption heading">{VAR:LC_YOUR_DATA}:</td>
									<td class="element"></td>
								</tr>
								<tr>
									<td class="caption">{VAR:LC_CLIENT_NR}:</td>
									<td class="element">{VAR:customer_no_value}</td>
								</tr>
								<tr>
									<td class="caption">{VAR:LC_NAME}:</td>
									<td class="element">{VAR:firstname_value} {VAR:lastname_value}</td>
								</tr>
								<tr>
									<td class="caption">{VAR:LC_ADDRESS}:</td>
									<td class="element">{VAR:address_value}, {VAR:city_value} {VAR:index_value}</td>
								</tr>
								<tr>
									<td class="caption">{VAR:LC_MOBILE_PHONE}:</td>
									<td class="element">{VAR:mobilephone_value}</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td class="price_information price_caption price_heading" colspan="3">{VAR:LC_VALIDATION}:</td>
						<td class="price_information price"></td>
					</tr>
					<tr>
						<td class="price_information price_caption" colspan="3">{VAR:LC_PRODUCTS2}:</td>
						<td class="price_information price">{VAR:cart_total} {VAR:currency}</td>
					</tr>
<!-- SUB: HAS_DELIVERY_PRICE -->
					<tr>
						<td class="price_information price_caption" colspan="3">{VAR:LC_DELIVERPRICE}:</td>
						<td class="price_information price">{VAR:delivery_price} {VAR:currency}</td>
					</tr>

					<tr>
						<td class="price_information price_caption" colspan="3">{VAR:LC_SUM}:</td>
						<td class="price_information price total">{VAR:total} {VAR:currency}</td>
					</tr>
<!-- END SUB: HAS_DELIVERY_PRICE -->
				</table>

				<table class="buttons">
					<tr>
						<td class="left">
							<input type="submit" value="{VAR:LC_BACK}" onclick="history.back(); return false;" />
						</td>
						<td class="space"></td>
						<td class="right">
							<input type="submit" value="{VAR:LC_FORWARD}" style="width: 175px;" />
						</td>
					</tr>
				</table>
			</form>
		</div>
		</td>
	</tr>
</table>
