<input type="submit" name="Button" class="button" onClick="window.location='{VAR:to_shop}';return false" value='{VAR:LC_SHOP_RETURN}'>
<input type="submit" name="Button" class="button" onClick="window.location='{VAR:order_hist}';return false" value='{VAR:LC_SHOP_VIEW_HISTORY}'>
<input type="submit" name="Button" class="button" onClick="window.location='{VAR:cancel_current}';return false" value='{VAR:LC_SHOP_CANCEL_CURRENT}'>
<p>
<!-- SUB: CAN_ORDER -->

<input type="submit" name="Button" class="button" onClick="window.location='{VAR:order}';return false" value='{VAR:LC_SHOP_ORDER_CART_ITEMS}'>
<!-- END SUB: CAN_ORDER -->


<b><font color="red" size="3">{VAR:status_msg}</font></b>
<form action='{VAR:baseurl}/orb.{VAR:ext}' method='POST' name='sh'>
<table border=0 bgcolor="#F8F1E4">
	<!-- SUB: ITEM -->
	<tr>
		<td colspan=20>
			{VAR:item_parent}
			{VAR:item}
		</td>
	</tr>
	<!-- SUB: F_ROW -->
	<tr>
			{VAR:row}
	</tr>
	<!-- END SUB: F_ROW -->

	<!-- END SUB: ITEM -->
</table>
<br>
<a href='/index.aw/3189'>Order excursion here</a><br>
<b>{VAR:LC_SHOP_CART_VALUE} {VAR:t_price}</b>&nbsp;&nbsp;<input type='submit' name="Button" class="button" value='{VAR:LC_SHOP_SUBMIT}'>
{VAR:reforb}
</form>
