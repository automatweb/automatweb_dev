<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td colspan=2 class="fcaption2">{VAR:item}</td>
	</tr>
	<tr>
		<td colspan=2 class="fcaption2">{VAR:LC_SHOP_CHOOSE_MENU_GOOD}:</td>
	</tr>
	<tr>
		<td class="fcaption2">
			<form action='reforb.{VAR:ext}' method=POST>
				<select class="small_button" size=10 name='menus[]' multiple>{VAR:menus}</select><br>
				<input class="small_button" type='submit' value='{VAR:LC_SHOP_SAVE}'>
				{VAR:reforb}
			</form>
		</td>
		<td rowspan=3 class="fcaption" valign="top">
			<form action='reforb.{VAR:ext}' method=POST>
			{VAR:LC_SHOP_ITEM_POSS}:
			<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width=100% >
				<tr>
					<td class="fcaption2"><input type='checkbox' name='has_max' value=1 {VAR:has_max}></td>
					<td class="fcaption2">{VAR:LC_SHOP_ITEM_QUANT}</td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2"><input size=3 type='text' name='max_items' VALUE='{VAR:max_items}'>{VAR:LC_SHOP_ITEM_TOG}</td>
				</tr>
				<tr>
					<td class="fcaption2"><input type='checkbox' name='has_period' value=1 {VAR:has_period}></td>
					<td class="fcaption2">{VAR:LC_SHOP_ITEM_PER} <a href='javascript:remote("no",500,500,"{VAR:sel_period}")'>{VAR:LC_SHOP_CHOOSE_PER}</a></td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2">{VAR:LC_SHOP_FROM}: {VAR:per_from}</td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2">{VAR:LC_SHOP_HOW_MANY_REPLAY}: <input type='text' name='per_cnt' class='small_button' size=3 VALUE='{VAR:per_cnt}'></td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2"><a href='{VAR:per_prices}'>{VAR:LC_SHOP_PER_PRICE}</a></td>
				</tr>
				<tr>
					<td class="fcaption2"><input type='checkbox' name='has_objs' value=1 {VAR:has_objs}></td>
					<td class="fcaption2">{VAR:LC_SHOP_ITEM:OBJ}</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>{VAR:LC_SHOP_PRICE_FORMULA}</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>{VAR:price_eq}</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>{VAR:LC_SHOP_GOOD_TYPE}:</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>{VAR:type}</td>
				</tr>
			</table>
				<input class="small_button" type='submit' value='{VAR:LC_SHOP_SAVE}'>
				{VAR:reforb3}
			</form>
		</td>
	</tr>
	<tr>
		<td class="fcaption2">{VAR:LC_SHOP_CHOOSE_AFTER_ORDER}:</td>
	</tr>
	<tr>
		<td class="fcaption2">
			<form action='reforb.{VAR:ext}' method=POST>
				<select class="small_button" size=10 name='redir'>{VAR:redir}</select><br>
				<input class="small_button" type='submit' value='{VAR:LC_SHOP_SAVE}'>
				{VAR:reforb2}
			</form>
		</td>
	</tr>
</table>
