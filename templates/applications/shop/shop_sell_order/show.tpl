<table id="shop_basket_content">
	<tr>
		<td id="contentSidebar">
			<div class="contentSidebarBasketNav">
				<div class="contentSidebarBasketNavHeader_level4"><!-- --></div>
				<ul class="level4">
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_1}</span></li>
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_2}</span></li>
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_3}</span></li>
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_4}</span></li>
				</ul>
				<div class="contentSidebarBasketNavFooter_level4"><!-- --></div>
			</div>
			<br class="clear" />
			<div class="separator"></div>
		</td>
		<td id="contentMain">
			<h1 class="title">{VAR:LC_BASKET_STEP_4_DONE_TITLE}</h1>
			<div class="contentMainBasket contentMainBasket5">
					<table class="product_table">
						<tr>
							<th colspan="2">{VAR:LC_PRODUCT}</th>
							<th>{VAR:LC_COUNT}</th>
							<th>{VAR:LC_PRICE} ({VAR:currency})</th>
						</tr>
						<!-- SUB: ROW -->
						<tr>
							<td class="product_image"><img src="{VAR:image_url}" alt="" /></td>
							<td class="product_info">
								<p class="product_name">{VAR:name}, {VAR:brand_name}</p>
								<p>{VAR:LC_PRODUCT_NR}: {VAR:code} ({VAR:price} / {VAR:LC_PIECE2})</p>
							</td>
							<td class="product_amount">{VAR:amount}</td>
							<td class="product_sum">{VAR:sum}</td>
						</tr>
						<!-- END SUB: ROW -->
<!-- SUB: HAS_TRANSPORT_TYPE -->
						<tr>
							<td></td>
							<td class="orderer_information" colspan="3">
								<table class="orderer_information_table">
									<tr>
										<td class="caption">{VAR:LC_DELIVERY_METHOD}:</td>
										<td class="element">{VAR:transp_type}</td>
									</tr>
									<tr>
										<td class="caption">{VAR:LC_PAYMENT_METHOD}:</td>
										<td class="element">{VAR:payment_name}</td>
									</tr>
								</table>
							</td>
						</tr>
<!-- END SUB: HAS_TRANSPORT_TYPE -->
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
										<td class="element">{VAR:customer_no}</td>
									</tr>
									<tr>
										<td class="caption">{VAR:LC_NAME}:</td>
										<td class="element">{VAR:purchaser}</td>
									</tr>
									<tr>
										<td class="caption">{VAR:LC_ADDRESS}:</td>
										<td class="element">{VAR:delivery_address}</td>
									</tr>
									<tr>
										<td class="caption">{VAR:LC_MOBILE_PHONE}:</td>
										<td class="element">{VAR:mobile_phone}</td>
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
							<td class="price_information price">{VAR:cart_sum} {VAR:currency}</td>
						</tr>
<!-- SUB: HAS_DELIVERY_SUM -->
						<tr>
							<td class="price_information price_caption" colspan="3">{VAR:LC_DELIVERPRICE}:</td>
							<td class="price_information price">{VAR:delivery_sum} {VAR:currency}</td>
						</tr>
						<tr>
							<td class="price_information price_caption" colspan="3">{VAR:LC_SUM}:</td>
							<td class="price_information price total">{VAR:sum} {VAR:currency}</td>
						</tr>
<!-- END SUB: HAS_DELIVERY_SUM -->
					</table>
			</div>
		</td>
	</tr>
</table>
