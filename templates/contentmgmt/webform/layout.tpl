<script type="text/javascript">
var chk_status = true;
function selall()
{
	len = document.changeform.elements.length;
	for (i = 0; i < len; i++)
	{
		document.changeform.elements[i].checked = chk_status;
	}
	chk_status = !chk_status;
}
</script>
<!-- SUB: group -->
<fieldset style="border: 1px solid #AAA; -moz-border-radius: 0.5em;">
<legend>{VAR:grp_caption}</legend>
	<table style="border-collapse: collapse; font-size: 11px; border-color: #CCC;" cellpadding="3px">
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}">Jrk</td>
		<td width="100" bgcolor="{VAR:bgcolor}">Pealkiri</td>
		<td width="150" bgcolor="{VAR:bgcolor}">Pealkirja asukoht</td>
		<td width="100" bgcolor="{VAR:bgcolor}">Tüüp</td>
		<td width="30" align="center" bgcolor="{VAR:bgcolor}"><a href="javascript:selall()">Vali</a></td>
	</tr>
	<!-- SUB: subt -->
	<tr>
		<td colspan="5" bgcolor={VAR:bgcolor}">{VAR:subtitle}</td>
	</tr>
	<!-- END SUB: subt -->
	<!-- SUB: property -->
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}"><input type="text" name="prop_ord[{VAR:prp_key}]" value="{VAR:prp_order}" size="2" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;"></td>
		<td width="150" bgcolor="{VAR:bgcolor}"><input type="text" name="prpnames[{VAR:prp_key}]" value="{VAR:prp_caption}" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;"></td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:capt_ord}</td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:prp_type}</td>
		<td width="30" align="center" bgcolor="{VAR:bgcolor}"><input type="checkbox" id="mark[{VAR:prp_key}]" name="mark[{VAR:prp_key}]" value="{VAR:prp_key}"></td>
	</tr>
	<!-- SUB: clf1 -->
	<tr>
		<td bgcolor="{VAR:bgcolor}"'>
		Välja tüüp:
		</td>
		<td bgcolor="{VAR:bgcolor}">
		{VAR:clf_type}
		</td>
		<td bgcolor="{VAR:bgcolor}" colspan="3">
		<!-- SUB: ordering -->
		Variantide paigutus:
		{VAR:v_order}
		<!-- END SUB: ordering -->
		</td>
	</tr>
	<tr>
		<td bgcolor="{VAR:bgcolor}" colspan="5">
		Uued variandid (eraldaja ;):
		<input type="text" name="prp_metas[{VAR:prp_key}]" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC; width:300px">
		</td>
	</tr>
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}" colspan="4">
		Variandid:
		{VAR:predefs}
		</td>
		<td bgcolor="{VAR:bgcolor}" align="right">
		<input type="button" name="meta_submit[{VAR:prp_key}]" value="Muuda" onclick="window.open('{VAR:metamgr_link}', '', 'toolbar=yes,directories=yes,status=yes,location=yes,resizable=yes,scrollbars=yes,menubar=yes,height=500,width=760');">
		</td>
	</tr>
	<!-- END SUB: clf1 -->
	<!-- SUB: clf2 -->
	<tr>
		<td bgcolor="{VAR:bgcolor}"'>
		Tekst:
		</td>
		<td bgcolor="{VAR:bgcolor}" colspan="4">
		<textarea name="prp_opts[{VAR:prp_key}][value]" cols="60" rows="4">{VAR:prp_value}</textarea>
		</td>
	</tr>
	<!-- END SUB: clf2 -->
	<!-- END SUB: property -->
	</table>
	</fieldset>
<!-- END SUB: group -->

