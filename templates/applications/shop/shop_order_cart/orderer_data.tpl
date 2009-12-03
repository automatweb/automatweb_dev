<link rel="stylesheet" type="text/css" href="http://intranet.automatweb.com/automatweb/js/jquery.formValidator1.3.9/css/validationEngine.jquery.css">

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery.formValidator1.3.9/js/jquery.validationEngine.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery.formValidator1.3.9/js/jquery.validationEngine-et.js"></script>



<table id="content">
	<tr>
		<td id="contentSidebar">
			<div class="contentSidebarBasketNav">
				<div class="contentSidebarBasketNavHeader"><!-- --></div>
				<ul class="level3">
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_1}</span></li>
					<li class="levelDone"><span>{VAR:LC_BASKET_STEP_2}</span></li>
					<li class="levelActive"><span>{VAR:LC_BASKET_STEP_3}</span></li>
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
							<td class="shopStep"><span>3</span></td>
							<td>{VAR:LC_BASKET_STEP_3}</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="contentMainBasket contentMainBasket2">

				<p class="note">{VAR:LC_STARRED_FIELD_INFO}</p>

				<form action="{VAR:baseurl}/index.aw?section={VAR:section}" method="post">
					
					<input type="hidden" name="action" value="submit_order_data" />
					<input type="hidden" name="class" value="shop_order_cart" />
					<input type="hidden" name="confirm_url" value="{VAR:confirm_url}" />
					<input type="hidden" name="cart" value="{VAR:cart}" />

						<input type="hidden" name="section" value="{VAR:section}">
					<table class="orderer_data">

<!-- SUB: BIRTHDAY_SUB -->

						<tr class="helpTrigger">
							<td class="caption">
								<label for="contentMainBasket2Bornday">
									{VAR:birthday_caption}*
								</label>
							</td>
							<td>
								<input type="text" class="validate[required],custom[onlyNumber] text helpTrigger_02" value="{VAR:birthday_day_value}" id="contentMainBasket2Bornday" style="width: 25px;" name="birthday[day]" maxlength="2"  />
								<script type="text/javascript">
									(function() {
										var e = document.getElementById("contentMainBasket2Bornday");
										var defaultValue = "PP"
										e.onblur = function() {
											if (this.value=='') {this.value = defaultValue}
										}

										e.onfocus = function() {
											if (this.value==defaultValue) { this.value = ''; }
										}
									})();
								</script>
								<input type="text" class="validate[required],custom[onlyNumber] text helpTrigger_02" value="{VAR:birthday_month_value}" style="width: 25px;" name="birthday[month]" id="contentMainBasket2Month" maxlength="2" />
								<script type="text/javascript">
									(function() {
										var e = document.getElementById("contentMainBasket2Month");
										var defaultValue = "KK"
										e.onblur = function() {
											if (this.value=='') {this.value = defaultValue}
										}

										e.onfocus = function() {
											if (this.value==defaultValue) { this.value = ''; }
										}
									})();
								</script>
								<input type="text" class="validate[required],custom[onlyNumber] text helpTrigger_02" value="{VAR:birthday_year_value}" style="width: 59px;" name="birthday[year]" id="contentMainBasket2Year" maxlength="4" />
								<script type="text/javascript">
									(function() {
										var e = document.getElementById("contentMainBasket2Year");
										var defaultValue = "VVVV"
										e.onblur = function() {
											if (this.value=='') {this.value = defaultValue}
										}

										e.onfocus = function() {
											if (this.value==defaultValue) { this.value = ''; }
										}
									})();
								</script>
							</td>
							<td>
								<div class="hidden helptext">{VAR:LC_HELP_TEXT_BIRTHDAY}</div>
							</td>
						</tr>

<!-- END SUB: BIRTHDAY_SUB -->

						<!-- SUB: ORDERER_DATA -->

						<tr class="helpTrigger">
							<td class="caption">
								<label for="contentMainBasket2{VAR:var_name}">
									{VAR:caption}
<!-- SUB: REQUIRED -->
*
<!-- END SUB: REQUIRED -->
								</label>
							</td>
							<td class="helpTrigger">
								<input type="text" class="{VAR:class}" value="{VAR:value}" name="{VAR:var_name}" id="contentMainBasket2{VAR:var_name}" />
							</td>
							<td>
								<div class="hidden helptext">{VAR:LC_HELP_TEXT}</div>
							</td>
						</tr>
						
						<!-- END SUB: ORDERER_DATA -->


					</table>

					<table class="buttons">
						<tr>
							<td class="left">
								<input type="submit" value="{VAR:LC_BACK}" onclick="history.back(); return false;" />
							</td>
							<td class="space"></td>
							<td class="right">
								<input type="submit" class="submit" value="{VAR:LC_FORWARD}" style="width: 175px;" />
							</td>
						</tr>
					</table>
				</form>
			</div>
		</td>
	</tr>
</table>
