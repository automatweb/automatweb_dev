<style>
table.cfgform_layout_tbl
{
	font-size: 11px;
	border-collapse: collapse;
	border-color: #CCC;
}

.cfgform_layout_tbl td input
{
	border: 1px solid #CCC;
	padding: 2px;
	background-color: #FCFCEC;
}
</style>

<fieldset style="border: 1px solid blue; -moz-border-radius: 0.5em;">
	<legend>{VAR:capt_legend_tbl}</legend>
	<table cellpadding="2" class="cfgform_layout_tbl">
	<tr>
		<td width="50">{VAR:capt_prp_order}</td>
		<td width="100">{VAR:capt_prp_key}</td>
		<td width="150">{VAR:capt_prp_caption}</td>
		<td width="100">{VAR:capt_prp_type}</td>
		<td width="30">{VAR:capt_prp_mark}</td>
		<td width="200" align="right">{VAR:capt_prp_options}</td>
	</tr>
	</table>
</fieldset>

<!-- SUB: group -->
<fieldset style="border: 1px solid #AAA; -moz-border-radius: 0.5em;">
<legend>{VAR:grp_caption}</legend>
	<table cellpadding="2" class="cfgform_layout_tbl">
	<!-- SUB: property -->
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}"><input type="text" name="prop_ord[{VAR:prp_key}]" value="{VAR:prp_order}" size="2"></td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:prp_key}</td>
		<td width="150" bgcolor="{VAR:bgcolor}"><input type="text" name="prpnames[{VAR:prp_key}]" value="{VAR:prp_caption}"></td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:prp_type}</td>
		<td width="30" align="center" bgcolor="{VAR:bgcolor}"><input type="checkbox" name="mark[{VAR:prp_key}]" value="1" style="border: 3px solid blue;"></td>
		<td width="200" align="right" bgcolor="{VAR:bgcolor}">
{VAR:prp_options}
		</td>
	</tr>
	<!-- END SUB: property -->
	</table>
</fieldset>
<!-- END SUB: group -->

<!-- SUB: textarea_options -->
			RTE<input type="checkbox" name="prpconfig[{VAR:prp_key}][richtext]" value="1" {VAR:richtext_checked}>
			<input type="hidden" name="xconfig[{VAR:prp_key}][richtext]" value="{VAR:richtext}">
<!-- END SUB: textarea_options -->

<!-- SUB: textarea_options_old -->
<tr>
	<td colspan="5" bgcolor="{VAR:bgcolor}">
		<input type="checkbox" name="prpconfig[{VAR:prp_key}][richtext]" value="1" {VAR:richtext_checked}> RTE
		<input type="hidden" name="xconfig[{VAR:prp_key}][richtext]" value="{VAR:richtext}">
	</td>
</tr>
<!-- END SUB: textarea_options_old -->

