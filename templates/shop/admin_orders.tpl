<form action='orb.aw' method="GET">
<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td colspan=2> In this view You can search from made reservations by passenger name, order ID or agent name. </td>
</tr>
<tr>
<td>Name:</td><td><input type='text' name='s_name' value='{VAR:s_name}'></td>
</tr>
<tr>
<td>ID:</td><td><input type='text' name='s_id' value='{VAR:s_id}'></td>
</tr>
<tr>
<td>Agent:</td><td><input type='text' name='s_agent' value='{VAR:s_agent}'></td>
</tr>
<tr>
<td colspan=2><input type='submit' value='Otsi'></td>
</tr>
</table>
{VAR:reforb}
</form>
<table width="750" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="left">

<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#F8F1E4"><img src="{VAR:baseurl}/img/1_table_title_nurk2.gif" align="" width="13" height="20" border="0" alt=""></td>

    <td bgcolor="#F8F1E4" class="text1">
    {VAR:LC_goto_page}:
<b>

<!-- SUB: PAGE -->
<a href='{VAR:goto_page}'>{VAR:from} - {VAR:to}</a> |
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} |
<!-- END SUB: SEL_PAGE -->
</b>
&nbsp;&nbsp;
    </td>

  </tr>
</table>
<hr>
<table width="750" border="0" cellspacing="1" cellpadding="2" bgcolor="#F8F1E4">
<tr>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_ORDER} ID</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_NAME}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_WHEN}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_USER}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_IP}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_PRICE}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_VIEW}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_CHANGE}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_CANCEL}</td>
	<td bgcolor="#F8F1E4" class="text1">{VAR:LC_SHOP_MARK_PAID}</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2">{VAR:order_id}</td>
	<td class="fcaption2">{VAR:name}</td>
	<td class="fcaption2">{VAR:when}</td>
	<td class="fcaption2">{VAR:user}</td>
	<td class="fcaption2">{VAR:ip}</td>
	<td class="fcaption2">{VAR:price}</td>
	<td class="fcaption2"><a href='{VAR:view}'>{VAR:LC_SHOP_VIEW}</a></td>
	<td class="fcaption2"><a href='{VAR:change}'>{VAR:LC_SHOP_CHANGE}</a></td>
	<td class="fcaption2"><a href='{VAR:cancel}'>{VAR:LC_SHOP_CANCEL}</a></td>
	<td class="fcaption2">
		<!-- SUB: IS_F -->
		<a href='{VAR:fill}'>{VAR:LC_SHOP_MARK_PAID}</a>
		<!-- END SUB: IS_F -->
		<!-- SUB: FILLED -->
		{VAR:LC_SHOP_ORDER_PAID}
		<!-- END SUB: FILLED -->
	</td>
</tr>
<!-- END SUB: LINE -->
</table>
