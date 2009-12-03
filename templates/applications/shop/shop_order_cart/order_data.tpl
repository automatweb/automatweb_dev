<table id="content">
	<tr>
		<td id="contentSidebar">
			<div id="contentSidebarStart"><a href="/"><img src="img/contentSidebarStart{VAR:LC_IMAGE_SUFIX}.gif" alt="{VAR:LC_GO_TO_FRONTPAGE}" title="{VAR:LC_GO_TO_FRONTPAGE}" /></a></div>
			<div class="contentSidebarBasketNav">
				<div class="contentSidebarBasketNavHeader"><!-- --></div>
				<ul class="level2">
					<li class=""><span>Ostukorv</span></li>
					<li class="levelActive"><span>Tellimuse andmed</span></li>
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
							<td class="shopStep"><span>2</span></td>
							<td>Tellimuse andmed</td>
						</tr>
					</table>
				</div>
			</div>
					
			<div class="contentMainBasket contentMainBasket3">
						
				<p class="note">{VAR:LC_STARRED_FIELD_INFO}</p>

				<form action="{VAR:baseurl}/index.aw" method="POST">
					<input type="hidden" name="action" value="submit_order_data" />
					<input type="hidden" name="class" value="shop_order_cart" />
					<input type="hidden" name="section" value="{VAR:section}" />
					<input type="hidden" name="cart" value="{VAR:cart}" />
					<input type="hidden" name="oc" value="{VAR:oc}" />
					<input type="hidden" name="confirm_url" value="{VAR:confirm_url}" />
					<input type="hidden" name="next_action" value="orderer_data" />

					<table class="order_data">
						<tr class="helpTrigger">
							<td class="caption">
								<label for="contentMainBasket2DeliverMethod1">Kättetoimetamisviis*</label>
							</td>
							<td>
								<!-- SUB: DELIVERY -->
								<input type="radio" id="contentMainBasket2DeliverMethod1" value="{VAR:delivery_id}" name="delivery" checked="checked" {VAR:delivery_checked} /> {VAR:delivery_name} ({VAR:delivery_price} EUR)
								<!-- END SUB: DELIVERY -->
							</td>
							<td>
								<div class="hidden helptext">...</div>
							</td>
						</tr>
						<tr class="helpTrigger">
							<td class="caption">
								<label for="contentMainBasket2Paymethod1">Makseviis*</label>
							</td>
							<td>
								<!-- SUB: PAYMENT -->
								<input type="radio" id="contentMainBasket2Paymethod1" name="payment" value={VAR:payment_id} checked="checked" {VAR:payment_checked} />
								<label for="contentMainBasket2Paymethod1">{VAR:payment_name}</label>
								<!-- END SUB: PAYMENT -->
							</td>
							<td>
								<div class="hidden helptext"> ....</div>
							</td>
						</tr>
					</table>
					<p class="note_terms">{VAR:LC_DELIVERY_TEXT1} <a class="thickbox" href="#TB_inline?height=550&width=750&inlineId=ordering_terms" title="Sopimusehdot">{VAR:LC_DELIVERY_TEXT2}</a>.</p>
					<table class="buttons">
						<tr>
							<td class="left">
								<input type="submit" value="Tagasi" onclick="history.back(); return false;" />
							</td>
							<td class="space"></td>
							<td class="right">
								<input type="submit" value="Edasi" style="width: 175px;" />
							</td>
						</tr>
					</table>
				</form>
			</div>
		</td>
	</tr>
</table>
