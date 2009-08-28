<div class="contentMainHead" id="contentMainHead2">
	<div class="title">{VAR:sel_menu_name}</div>
	<div class="stats"></div>

	<!-- SUB: PAGER -->
	<div class="pager">
		<span>{VAR:LC_PAGES}</span>
		<ul>
			<!-- SUB: PAGE_PREV -->
			<li class="btn btn_prev"><a href="{VAR:pager_url}"><span></span></a></li>
			<!-- END SUB: PAGE_PREV -->

			<!-- SUB: PAGE -->
			<li><a href="{VAR:pager_url}">{VAR:pager_nr}</a></li>
			<!-- END SUB: PAGE -->

			<!-- SUB: PAGE_SEL -->
			<li class="current">{VAR:pager_nr}</li>
			<!-- END SUB: PAGE_SEL -->

			<!-- SUB: PAGE_SEP -->
			<li><a href="{VAR:pager_url}" class="noUnderline">...</a></li>
			<!-- END SUB: PAGE_SEP -->

			<!-- SUB: PAGE_NEXT -->
			<li class="btn btn_next"><a href="{VAR:pager_url}"><span></span></a></li>
			<!-- END SUB: PAGE_NEXT -->
		</ul>
	</div>
	<!-- END SUB: PAGER -->
</div>

<table id="contentMainProductlist">
<!-- SUB: ROW -->
<tr>
	<!-- SUB: PRODUCT -->
		<td class="product">
				<div class="productImage">
					<a href="{VAR:product_link}" style="background-image:url({VAR:image_url})"></a>
					<p>{VAR:brand}</p>
				</div>
				<div class="productInfo">
					<p><a href="{VAR:product_link}">{VAR:name}</a></p>
					<p>{VAR:LC_PRICE_FROM}:</p>
					<p>{VAR:min_price} {VAR:LC_MONEY_EXT}</p>
				</div>
			</td>
	<!-- END SUB: PRODUCT -->

	<!-- SUB: PRODUCT_END -->
		<td class="product paddingRight0">
				<div class="productImage">
					<a href="{VAR:product_link}" style="background-image:url({VAR:image_url})"></a>
					<p>{VAR:brand}</p>
				</div>
				<div class="productInfo">
					<p><a href="{VAR:product_link}">{VAR:name}</a></p>
					<p>{VAR:LC_PRICE_FROM}:</p>
					<p>{VAR:min_price} {VAR:LC_MONEY_EXT}</p>
				</div>
			</td>
	<!-- END SUB: PRODUCT_END -->
</tr>
<tr class="productSpacer">
	<td colspan="4"></td>
</tr>
<!-- END SUB: ROW -->
</table>

<div class="contentMainFooter">
	<!-- SUB: PAGER -->
	<div class="pager">
		<span>{VAR:LC_PAGES}</span>
		<ul>
			<!-- SUB: PAGE_PREV -->
			<li class="btn btn_prev"><a href="{VAR:pager_url}"><span></span></a></li>
			<!-- END SUB: PAGE_PREV -->

			<!-- SUB: PAGE -->
			<li><a href="{VAR:pager_url}">{VAR:pager_nr}</a></li>
			<!-- END SUB: PAGE -->

			<!-- SUB: PAGE_SEL -->
			<li class="current">{VAR:pager_nr}</li>
			<!-- END SUB: PAGE_SEL -->

			<!-- SUB: PAGE_SEP -->
			<li><a href="{VAR:pager_url}" class="noUnderline">...</a></li>
			<!-- END SUB: PAGE_SEP -->

			<!-- SUB: PAGE_NEXT -->
			<li class="btn btn_next"><a href="{VAR:pager_url}"><span></span></a></li>
			<!-- END SUB: PAGE_NEXT -->
		</ul>
	</div>
	<!-- END SUB: PAGER -->
</div>