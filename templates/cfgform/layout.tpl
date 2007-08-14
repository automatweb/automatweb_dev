<style>
table.cfgform_layout_tbl
{
	font-size: 11px;
	border-collapse: collapse;
	border-color: #CCC;
}

.cfgform_layout_tbl td
{
	vertical-align: top;
}

.cfgform_layout_tbl td input
{
	border: 1px solid #CCC;
	padding: 2px;
	background-color: #FCFCEC;
}
</style>

<script type="text/javascript">
function cfgformToggleOpts(id)
{
	el = document.getElementById("cfgformPrpOpts" + id);
	im = document.getElementById("cfgformOptsBtn" + id);

	if (el.style.display=="none")
	{
		el.style.display="block";
		im.src="{VAR:baseurl}/automatweb/images/aw06/closer_up.gif";
	}
	else
	{
		el.style.display="none";
		im.src="{VAR:baseurl}/automatweb/images/aw06/closer_down.gif";
	}
}

function cfgformToggleSelectProps(grpId)
{
	var inputElems = document.body.getElementsByTagName("input");
	var el = null;
	var prevState = null;

	for (i in inputElems)
	{
		el = inputElems[i];

		if ("checkbox" == el.type && el.className == ("prpGrp" + grpId))
		{
			prevState = el.checked;
			el.checked = !prevState;
		}
	}
}
</script>

<fieldset style="border: 1px solid blue; -moz-border-radius: 0.5em;">
	<legend>{VAR:capt_legend_tbl}</legend>
	<table cellpadding="2" class="cfgform_layout_tbl">
	<tr>
		<td width="50">{VAR:capt_prp_order}</td>
		<td width="100">{VAR:capt_prp_key}</td>
		<td width="150">{VAR:capt_prp_caption}</td>
		<td width="100">{VAR:capt_prp_type}</td>
		<td width="30"></td>
	</tr>
	</table>
</fieldset>

<!-- SUB: group -->
<fieldset style="border: 1px solid #AAA; -moz-border-radius: 0.5em;">
<legend>{VAR:grp_caption}</legend>
	<table cellpadding="2" class="cfgform_layout_tbl">
	<tr>
		<td colspan="6" style="text-align: right;"><a href="javascript:cfgformToggleSelectProps('{VAR:grp_id}')">{VAR:capt_prp_mark}</a></td>
	</tr>
	<!-- SUB: property -->
	<tr>
		<td width="50" bgcolor="{VAR:bgcolor}"><input type="text" name="prop_ord[{VAR:prp_key}]" value="{VAR:prp_order}" size="2"></td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:prp_key}</td>
		<td width="150" bgcolor="{VAR:bgcolor}"><input type="text" name="prpnames[{VAR:prp_key}]" value="{VAR:prp_caption}"></td>
		<td width="100" bgcolor="{VAR:bgcolor}">{VAR:prp_type}</td>
		<td width="200" bgcolor="{VAR:bgcolor}">
		<!-- SUB: options -->
			<div class="closer">{VAR:prp_opts_caption} <a href="#" onclick="javascript:cfgformToggleOpts({VAR:tmp_id})"><img src="{VAR:baseurl}/automatweb/images/aw06/closer_down.gif" id="cfgformOptsBtn{VAR:tmp_id}" width="20" height="15" border="0"></a></div>
			<div id="cfgformPrpOpts{VAR:tmp_id}" style="display: none;">
{VAR:prp_options}
			</div>
		<!-- END SUB: options -->
		</td>
		<td width="30" align="center" bgcolor="{VAR:bgcolor}"><input type="checkbox" class="prpGrp{VAR:grp_id}" name="mark[{VAR:prp_key}]" value="1" style="border: 3px solid blue;"></td>
	</tr>
	<!-- END SUB: property -->
	</table>
</fieldset>
<!-- END SUB: group -->

<!-- SUB: textarea_options -->
			{VAR:richtext_caption}<input type="checkbox" name="prpconfig[{VAR:prp_key}][richtext]" value="1" {VAR:richtext_checked}>
			<input type="hidden" name="xconfig[{VAR:prp_key}][richtext]" value="{VAR:richtext}"><br />
			{VAR:rows_caption} <input type="text" size="2" name="prpconfig[{VAR:prp_key}][rows]" value="{VAR:rows}"><br />
			{VAR:cols_caption} <input type="text" size="2" name="prpconfig[{VAR:prp_key}][cols]" value="{VAR:cols}"><br />
<!-- END SUB: textarea_options -->

<!-- SUB: textbox_options -->
			{VAR:size_caption} <input type="text" size="2" name="prpconfig[{VAR:prp_key}][size]" value="{VAR:size}"><br />
<!-- END SUB: textbox_options -->

<!-- SUB: relpicker_options -->
			{VAR:no_edit_caption}<input type="checkbox" name="prpconfig[{VAR:prp_key}][no_edit]" value="1" {VAR:no_edit_checked}>
			<input type="hidden" name="xconfig[{VAR:prp_key}][no_edit]" value="{VAR:no_edit}"><br />
<!-- END SUB: relpicker_options -->
