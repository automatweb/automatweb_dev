<table border='0' class="aw04contenttable" align="center" cellpadding="0" cellspacing="0" width="100%">
<!-- SUB: SHOW_CHANGEFORM -->
<form action='{VAR:handler}.{VAR:ext}' method='{VAR:method}' name='changeform' enctype='multipart/form-data' {VAR:form_target}>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='100000000'>
<!-- END SUB: SHOW_CHANGEFORM -->

{VAR:content}

<!-- SUB: ERROR -->
<tr>
	<td colspan="2" bgcolor="red" align="center"><span style='color: white; font-weight: bold;'>{VAR:error_text}</span></td>
</tr>
<!-- END SUB: ERROR -->

<!-- SUB: PROP_ERR_MSG -->
<tr>
	<td class="aw04contentcellleft" width='80'></td>
	<td class='aw04contentcellright'><span style='color: red'>{VAR:err_msg}</span>	</td>
</tr>	
<!-- END SUB: PROP_ERR_MSG -->

<!-- SUB: LINE -->
<tr>
        <td class='aw04contentcellleft' width='80' nowrap>
		{VAR:caption}
		</td>
        <td class='aw04contentcellright'>
		{VAR:element}
        </td>
</tr>
<!-- END SUB: LINE -->

<!-- SUB: HEADER -->
<tr>
	<td class='aw04contentcellsubheader' width='80'>
	&nbsp;
	</td>
	<td class='aw04contentcellsubheader'>
	{VAR:caption}
	</td>
</tr>
<!-- END SUB: HEADER -->

<!-- SUB: SUB_TITLE -->
<tr>
	<td colspan='2' class='aw04contentcellsubtitle'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: SUB_TITLE -->

<!-- SUB: CONTENT -->
<tr>
	<td colspan='2' class='aw04contentcellcontent'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: CONTENT -->

<!-- SUB: SUBMIT -->
<tr>
	<td class='aw04contentcellleft' align='center' width='80'>&nbsp;</td>
	<td class='aw04contentcellright'>
		<input type='submit' name='{VAR:name}' value='{VAR:sbt_caption}' class='aw04formbutton' onClick='submit_changeform("{VAR:action}"); return false;'>
	</td>
</tr>
<!-- END SUB: SUBMIT -->

<!-- SUB: SUBITEM -->
	<span style='color: red'>{VAR:err_msg}</span>
        {VAR:element}
        <span class="aw04contentcellright">{VAR:caption}</span>
	&nbsp;
<!-- END SUB: SUBITEM -->

<!-- SUB: SUBITEM2 -->
	<span style='color: red'>{VAR:err_msg}</span>
        <div class="aw04contentcellleft">{VAR:caption}</div>
        <div class="aw04contentcellright">{VAR:element}</div>
<!-- END SUB: SUBITEM2 -->

<!-- SUB: GRIDITEM -->
	<div class="aw04gridcell_caption">{VAR:caption}: {VAR:element}</div>
<!-- END SUB: GRIDITEM -->

<!-- SUB: GRIDITEM_NO_CAPTION -->
	<div class="aw04gridcell_no_caption">{VAR:element}</div>
<!-- END SUB: GRIDITEM_NO_CAPTION -->

<!-- SUB: GRID_HBOX -->
<table border=0 cellspacing=0 cellpadding=0 width='100%'>
<tr>
<!-- SUB: GRID_HBOX_ITEM -->
<td valign='top' {VAR:item_width} style='padding-left: 5px;'>
{VAR:item}
</td>
<!-- END SUB: GRID_HBOX_ITEM -->
</tr>
</table>
<!-- END SUB: GRID_HBOX -->

<!-- SUB: GRID_VBOX -->
<table border=0 cellspacing=0 cellpadding=0 width='100%'>
<!-- SUB: GRID_VBOX_ITEM -->
<tr>
<td valign='top'>{VAR:item}</td>
</tr>
<!-- END SUB: GRID_VBOX_ITEM -->
</table>
<!-- END SUB: GRID_VBOX -->

<!-- SUB: PROPERTY_HELP -->
<div id="property_{VAR:property_name}_help" style="display: none;">
<strong>{VAR:property_caption} - {VAR:property_comment}</strong>
<p>{VAR:property_help}</p>
</div>
<!-- END SUB: PROPERTY_HELP -->

<!-- SUB: SHOW_CHANGEFORM2 -->
{VAR:reforb}
<script type="text/javascript">
function submit_changeform(action)
{
	changed = 0;
	{VAR:submit_handler}
	if (typeof action == "string" && action.length>0)
	{
		document.changeform.action.value = action;
	};
	document.changeform.submit();
}
</script>
</form>
<!-- END SUB: SHOW_CHANGEFORM2 -->
</table>

<!-- SUB: iframe_body_style -->
body {
        background-color: #FFFFFF;
        margin: 0px;
        overflow-y: hidden;
        overflow:hidden;
}
<!-- END SUB: iframe_body_style -->
<!-- SUB: CHECK_LEAVE_PAGE -->
<script language="javascript">

changed = 0;
function set_changed()
{
	changed = 1;
}

function generic_loader()
{
	// set onchange event handlers for all form elements
	var els = document.changeform.elements;
	var cnt = els.length;
	for(var i = 0; i < cnt; i++)
	{
		if (els[i].attachEvent)
		{
			els[i].attachEvent('onChange',set_changed);
		}
		else
		{
			els[i].setAttribute("onChange","set_changed();");
		}
	}
}

function generic_unloader()
{
	if (changed)
	{
		if (confirm("Andmed on salvestamata, kas soovite andmed enne lahkumist salvestada?"))
		{
			document.changeform.submit();
		}
	}
}
</script>
