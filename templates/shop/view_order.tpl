

<input type="submit" name="Button" class="button" onClick="window.location='{VAR:bill}';return false" value='{VAR:LC_SHOP_VIEW_BILL}'>
<input type="submit" name="Button" class="button" onClick="window.location='{VAR:order_hist}';return false" value='{VAR:LC_SHOP_VIEW_HISTORY}'>
<FONT size=2><FONT face=Verdana,Geneva,Arial>
<br><br>
{VAR:LC_SHOP_USER} {VAR:user} (ip: {VAR:ip}) {VAR:LC_SHOP_ORDERED} {VAR:time} {VAR:LC_SHOP_OITEMS}<br><br>
</font>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
	<!-- SUB: ITEM -->
	<tr>
		<td colspan=20  bgcolor="#F2F2F2"><span class="txt">
		<b>	{VAR:parent_name} {VAR:is_hotel} {VAR:name} {VAR:period}</b>
		</td>
	</tr>
	<!-- SUB: F_ROW -->
	<tr>
		<td>{VAR:row}</td>
	</tr>
	<!-- END SUB: F_ROW -->

	<!-- END SUB: ITEM -->
</table>
<br>
<FONT size=2><FONT face=Verdana,Geneva,Arial>
{VAR:LC_SHOP_PRICE}: {VAR:price}<br>
<br>
{VAR:LC_SHOP_EDATA}
</font>
<pre>
<FONT size=2><FONT face=Verdana,Geneva,Arial>
{VAR:inf_form}
</font></pre>