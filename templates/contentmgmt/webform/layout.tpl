<!-- SUB: group -->
<fieldset style="border: 1px solid #AAA; -moz-border-radius: 0.5em;">
<legend>{VAR:grp_caption}</legend>
	<table style="border-collapse: collapse; font-size: 11px; border-color: #CCC;" cellpadding="3px">
	<!-- SUB: property -->
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}"><input type="text" name="prop_ord[{VAR:prp_key}]" value="{VAR:prp_order}" size="2" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;"></td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:prp_key}</td>
		<td width="150" bgcolor="{VAR:bgcolor}"><input type="text" name="prpnames[{VAR:prp_key}]" value="{VAR:prp_caption}" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;"></td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:prp_type}</td>
		<td width="30" align="center" bgcolor="{VAR:bgcolor}"><input type="checkbox" name="mark[{VAR:prp_key}]" value="1" style="border: 3px solid blue;"></td>
	</tr>
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}">Pealkirja asukoht:</td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:capt_ord}</td>
		<td width="150" bgcolor="{VAR:bgcolor}">
		<!-- SUB: clf1 -->
		Välja tüüp:
		<!-- END SUB: clf1 -->
		</td>
		<td width="100" bgcolor="{VAR:bgcolor}">
		{VAR:clf_type}
		</td>
		<td width="30" align="center" bgcolor="{VAR:bgcolor}">
		<!--<input type="checkbox" name="mark[{VAR:prp_key}]" value="1" style="border: 3px solid blue;">-->
		</td>
	</tr>
	<!-- SUB: clf3 -->
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}">Variandid</td>
		<td width="100" bgcolor="{VAR:bgcolor}" colspan="4">
		<input type="text" name="prp_metas[{VAR:prp_key}]" value="{VAR:prp_metas}" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC; width:350px">
		<input type="button" name="meta_submit[{VAR:prp_key}]" value="Muuda käsitsi" onclick="window.open('{VAR:metamgr_link}', '', 'toolbar=yes,directories=yes,status=yes,location=yes,resizable=,scrollbars=,menubar=yes,height=400,width=600');">
		</td>
	</tr>
	<!-- END SUB: clf3 -->
	<!-- END SUB: property -->
	</table>
	</fieldset>
<!-- END SUB: group -->

<!-- SUB: textarea_options -->
<tr>
	<td colspan="5" bgcolor="{VAR:bgcolor}">
		<input type="checkbox" name="prpconfig[{VAR:prp_key}][richtext]" value="1" {VAR:richtext_checked}> RTE
		<input type="hidden" name="xconfig[{VAR:prp_key}][richtext]" value="{VAR:richtext}">
	</td>
</tr>
<!-- END SUB: textarea_options -->

