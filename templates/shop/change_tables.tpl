
<form method="POST" action="reforb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<!-- SUB: TYPE -->
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_ITEMS_WITH_TYPE} {VAR:typename} {VAR:LC_SHOP_ON_INVOICE} <select name='tables[{VAR:type_id}]'>{VAR:tables}</select></td>
</tr>
<!-- END SUB: TYPE -->
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="{VAR:LC_SHOP_SAVE}">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
