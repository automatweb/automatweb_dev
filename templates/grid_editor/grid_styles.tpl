<script language="javascript">

function pick_style()
{
	// here we gotta go over all elements/rows/columns and add them to the end of the picker url

	rows = "rows=";
	cols = "cols=";
	cells = "cells="; 

	len = document.changeform.elements.length;
	for (i = 0; i < len; i++)
	{
		el = document.changeform.elements[i];
		if (el.name.indexOf("sel_") != -1 && el.checked)
		{
			cells +=el.name;
		}
		else
		if (el.name.indexOf("dc_") != -1 && el.checked)
		{
			cols += el.name;
		}
		else
		if (el.name.indexOf("dr_") != -1 && el.checked)
		{
			rows += el.name;
		}
	}
	remote("no", 400, 400,"{VAR:selstyle}&"+rows+"&"+cols+"&"+cells+"&oid={VAR:oid}");
}
</script>


<table border=1 cellspacing=1 cellpadding=2>
	<tr>
		<!-- SUB: DC -->
			<td bgcolor="#FFFFFF" class="celltext">
				<input type='checkbox' NAME='dc_{VAR:col}' value=1>
			</td>
		<!-- END SUB: DC -->
		<td bgcolor="#FFFFFF">&nbsp;</td>
	</tr>
	<!-- SUB: LINE -->
	<tr>
		<!-- SUB: COL -->
		<td {VAR:td_style}>
			<input type="checkbox" name="sel_row={VAR:row};col={VAR:col}">
		</td>
		<!-- END SUB: COL -->

		<td bgcolor=#ffffff valign=bottom align=left>
			<input type='checkbox' NAME='dr_{VAR:row}' value=1><br>
		</td>
	</tr>
	<!-- END SUB: LINE -->
</table>
<a href='#' onClick='pick_style()'>Vali stiil</a>


<input type="hidden" name="ge_action" value="">
