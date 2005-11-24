<style type="text/css">
.form_elem
{
border: 1px solid #EEE;
padding: 2px;
background-color: #FCFCEE;
 }
</style>
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
	<tr bgcolor="{VAR:bgcolor}">
		<td width="50">Jrk</td>
		<td width="100">Pealkiri</td>
		<td width="150">Pealkirja asukoht</td>
		<td width="100">Tüüp</td>
		<td width="30">Kõrvale</td>
		<td width="10">Vahe</td>
		<td width="30" align="center"><a href="javascript:selall()">Vali</a></td>
	</tr>
	<!-- SUB: subt -->
	<tr bgcolor="{VAR:bgcolor}">
		<td colspan="6">{VAR:subtitle}</td>
	</tr>
	<!-- END SUB: subt -->
	<!-- SUB: property -->
	<tr bgcolor="{VAR:bgcolor}">
		<td width="50"><input type="text" name="prop_ord[{VAR:prp_key}]" value="{VAR:prp_order}" size="2"></td>
		<td width="150"><input type="text" name="prpnames[{VAR:prp_key}]" value="{VAR:prp_caption}"></td>
		<td width="100">{VAR:capt_ord}</td>
		<td width="100">{VAR:prp_type}</td>
		<td width="30" align="center"><input type="checkbox" id="prp_opts[{VAR:prp_key}][nextto]" name="prp_opts[{VAR:prp_key}][nextto]" value="1" {VAR:nextto}></td>
		<td width="30" align="center"><input type="textbox" id="prp_opts[{VAR:prp_key}][space]" name="prp_opts[{VAR:prp_key}][space]" value="{VAR:space}" size="2"></td>
		<td width="30" align="center"><input type="checkbox" id="mark[{VAR:prp_key}]" name="mark[{VAR:prp_key}]" value="{VAR:prp_key}"></td>
	</tr>
	<!-- SUB: CLF5 -->
	<tr bgcolor="{VAR:bgcolor}">
		<td colspan="2">
		<!-- SUB: HEIGHT -->
		Kõrgus
		<input type="text" name="prp_opts[{VAR:prp_key}][height]" size="4" value="{VAR:ht}">
		<!-- END SUB: HEIGHT -->
		</td>
		<td colspan="5">
		Laius
		<input type="text" name="prp_opts[{VAR:prp_key}][width]" size="4" value="{VAR:wt}">
		</td>
	</tr>
	<!-- END SUB: CLF5 -->
	<!-- SUB: CLF1 -->
	<tr bgcolor="{VAR:bgcolor}">
		<td>
		Välja tüüp:
		</td>
		<td>
		{VAR:clf_type}
		</td>
		<td colspan="2">
		<!-- SUB: ordering -->
		Variantide paigutus:
		{VAR:v_order}
		<!-- END SUB: ordering -->
		</td>
		<td colspan="3">
		Sorteeri omaduse järgi:
		</td>
	</tr>
	<tr bgcolor="{VAR:bgcolor}">
		<td colspan="4">
		Uued variandid (eraldaja ;):
		<input type="text" name="prp_metas[{VAR:prp_key}]" style="width:300px">
		</td>
		<td colspan="3">
		{VAR:sort_by}
		</td>
	</tr>
	<tr bgcolor="{VAR:bgcolor}">
		<td width="50" colspan="4">
		Variandid:
		{VAR:predefs}
		</td>
		<td bgcolor="{VAR:bgcolor}" align="right" colspan="3">
		<input type="button" name="meta_submit[{VAR:prp_key}]" value="Muuda" onclick="window.open('{VAR:metamgr_link}', '', 'toolbar=yes,directories=yes,status=yes,location=yes,resizable=yes,scrollbars=yes,menubar=yes,height=500,width=760');">
		</td>
	</tr>
	<!-- END SUB: CLF1 -->
	<!-- SUB: CLF2 -->
	<tr bgcolor="{VAR:bgcolor}">
		<td>
			Tekst:
		</td>
		<td colspan="6">
			<textarea name="prp_opts[{VAR:prp_key}][value]" cols="60" rows="4">{VAR:prp_value}</textarea>
		</td>
	</tr>
	<!-- END SUB: CLF2 -->
	<!-- SUB: CLF3 -->
	<tr bgcolor="{VAR:bgcolor}">
		<td>Kausta ID:</td>
		<td colspan="6">
			<input type="text" name="prp_opts[{VAR:prp_key}][folder_id]" value="{VAR:fld_id}" />
		</td>
	</tr>
	<!-- SUB: NE_SELECT -->
	<tr bgcolor="{VAR:bgcolor}">
		<td>Nime väli:</td>
		<td colspan="2">
			{VAR:name_select}
		</td>
		<td>E-maili väli:</td>
		<td colspan="3">
			{VAR:email_select}
		</td>
	</tr>
	<!-- END SUB: NE_SELECT -->
	<!-- END SUB: CLF3 -->
	<!-- SUB: CLF4 -->
	<tr bgcolor="{VAR:bgcolor}">
		<td colspan="3">Vaikimisi kuupäev: {VAR:time_select}</td>
		<td colspan="4">
		Aasta alates: {VAR:year_from}
		Aasta kuni: {VAR:year_to}
		</td>
	</tr>
	<tr bgcolor="{VAR:bgcolor}">
		<td colspan="7">Kuu formaat: {VAR:mon_for}</td>
	</tr>
	<!-- END SUB: CLF4 -->
	<!-- END SUB: property -->
	</table>
	</fieldset>
<!-- END SUB: group -->

