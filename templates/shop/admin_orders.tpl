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
	<td bgcolor="#F8F1E4" class="text1">Order ID</td>
	<td bgcolor="#F8F1E4" class="text1">Name</td>
	<td bgcolor="#F8F1E4" class="text1">Date / Time</td>
	<td bgcolor="#F8F1E4" class="text1">Agent</td>
	<td bgcolor="#F8F1E4" class="text1">IP adress</td>
	<td bgcolor="#F8F1E4" class="text1">Price</td>
	<td bgcolor="#F8F1E4" class="text1">View</td>
	<td bgcolor="#F8F1E4" class="text1">Change</td>
	<td bgcolor="#F8F1E4" class="text1">Cancel</td>
	<td bgcolor="#F8F1E4" class="text1">Mark as paid</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2">{VAR:order_id}</td>
	<td class="fcaption2">{VAR:name}</td>
	<td class="fcaption2">{VAR:when}</td>
	<td class="fcaption2">{VAR:user}</td>
	<td class="fcaption2">{VAR:ip}</td>
	<td class="fcaption2">{VAR:price}</td>
	<td class="fcaption2"><a href='{VAR:view}'>View</a></td>
	<td class="fcaption2"><a href='{VAR:change}'>{VAR:LC_SHOP_CHANGE}</a></td>
	<td class="fcaption2"><a href='{VAR:cancel}'>{VAR:LC_SHOP_CANCEL}</a></td>
	<td class="fcaption2">
		<!-- SUB: IS_F -->
		<a href='{VAR:fill}'>Mark as paid</a>
		<!-- END SUB: IS_F -->
		<!-- SUB: FILLED -->
		Order is paid
		<!-- END SUB: FILLED -->
	</td>
</tr>
<!-- END SUB: LINE -->
</table>
