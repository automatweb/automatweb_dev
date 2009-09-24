<div id="contentMainBtnBack"><a href="javascript:history.back(1)">Tagasi</a></div>
<div id="contentMainProduct">
	<form action="{VAR:submit_url}" name="cataloglist">
		<input type="hidden" name="class" value="shop_order_cart" />
		<input type="hidden" name="action" value="submit_add_cart" />
		<input type="hidden" name="oc" value="{VAR:oc}" />
		<input type="hidden" name="section" value="{VAR:section}" />
		<table>
			<tr>
				<td class="image">
{VAR:image}
				</td>
				<td class="details">
					<table>
						<tr>
							<td><h3>{VAR:name}<?php if(strlen("{VAR:brand}") > 0) { echo ", {VAR:brand}"; } ?>
							</h3></td>
							<td class="brand"><div>{VAR:brand_image}</div></td>
						</tr>
					</table>


					<div class="spacerDot"></div>
					<div class="detailsProdcount">
						<table>
							<tr>
								<td class="first">
									
									<input class="productCount" type="text" value="1" name="add_to_cart[{VAR:id}]" style="width: 25px;" size="3"/>
								</td>
								<td class="second">
									<a href="JavaScript:void(0);" class="productCountIncrease"></a>
								</td>
								<td class="third">
									<a href="JavaScript:void(0);" class="productCountDecrease"></a>
								</td>
							</tr>
						</table>
					</div>
					<div class="spacerDot"></div>

					<ul class="detailsMenu">
						<li class="basket floatRight"><input type="submit" value="Lisa ostukorvi"/></li>
<!--						<li class="addToNotebook floatLeft"><a href="{VAR:add_to_favourites_url}">Lemmikute hulka</a></li>
-->
					</ul>

					<div class="spacerDot"></div>

					<div class="detailsExtrainfo">
						<table>
							<tr>
								<!-- SUB: TAGS -->
										<td><img src="{VAR:image_url}" alt="" title="{VAR:comment}" /></td>
								<!-- END SUB: TAGS -->
							</tr>
						</table>
					</div>
					<div class="spacerDot"></div>
				</td>
			</tr>
		</table>
	</form>
</div>
