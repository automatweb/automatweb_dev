<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>

<!-- SUB: HAS_CALENDAR -->
<tr>
<td class="fform">Perioodide sisestamise vorm:</td>
<td class="fform"><select name="period_entry_form">{VAR:period_entry_forms}</select></td>
</tr>
<!-- END SUB: HAS_CALENDAR -->

<!-- SUB: HAS_CALENDAR1 -->
<tr>
<td class="fform">Perioodiühik:</td>
<td class="fform"><select name="period_type">{VAR:period_types}</select></td>
</tr>
<tr>
<td class="fform">Ühikuid:</td>
<td class="fform"><input type="text" name="period_items" value="{VAR:period_items}" size="3"></td>
</tr>
<tr>
<td class="fform">Max sisestusi ühes ühikus:</td>
<td class="fform"><input type="text" name="period_max_items" value="{VAR:period_max_items}" size="3"></td>
</tr>
<tr>
<td class="fform">Deaktiveeritakse:</td>
<td class="fform">
	<input type="text" name="deact_before_items" value="{VAR:deact_before_items}" size="3">
	<select name="deact_before_type">{VAR:deact_before_types}</select>
	enne perioodiühiku algust
</td>
</tr>
<!-- END SUB: HAS_CALENDAR1 -->

<!-- SUB: IS_ORDER_FORM -->
<tr>
<td class="fform" style="font-weight: bold" colspan="2">
Tellimisvorm: vali vorm või pärg, millesse eventid paigutatakse
</td>
</tr>
<tr>
<td class="fform" valign="top">Vormid:</td>
<td class="fform"><input type="radio" name="of_target_type" value="form" {VAR:form_checked}><select name="of_target_form">{VAR:forms}</select></td>
</tr>
<tr>
<td class="fform" valign="top">Pärjad:</td>
<td class="fform"><input type="radio" name="of_target_type" value="chain" {VAR:chain_checked}><select name="of_target_chain">{VAR:chains}</select></td>
</tr>
<!-- END SUB: IS_ORDER_FORM -->
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE} form'></td>
</table>
{VAR:reforb}
</form>
  
