<script language="javascript">

function add_col(after, num)
{
	document.changeform.ge_action.value = "action=add_col;after="+after+";num="+num;
	document.changeform.submit();
}

function del_col(col, num)
{
	document.changeform.ge_action.value = "action=del_col;col="+col+";num="+num;
	document.changeform.submit();
}

function add_row(after, num)
{
	document.changeform.ge_action.value = "action=add_row;after="+after+";num="+num;
	document.changeform.submit();
}

function del_row(row, num)
{
	document.changeform.ge_action.value = "action=del_row;row="+row+";num="+num;
	document.changeform.submit();
}

function exp_down(row, col)
{
	document.changeform.ge_action.value = "action=exp_down;row="+row+";col="+col+";cnt="+document.changeform.exp_count.value;
	document.changeform.submit();
}

function exp_up(row, col)
{
	document.changeform.ge_action.value = "action=exp_up;row="+row+";col="+col+";cnt="+document.changeform.exp_count.value;
	document.changeform.submit();
}

function exp_left(row, col)
{
	document.changeform.ge_action.value = "action=exp_left;row="+row+";col="+col+";cnt="+document.changeform.exp_count.value;
	document.changeform.submit();
}

function exp_right(row, col)
{
	document.changeform.ge_action.value = "action=exp_right;row="+row+";col="+col+";cnt="+document.changeform.exp_count.value;
	document.changeform.submit();
}

function split_ver(row, col)
{
	document.changeform.ge_action.value = "action=split_ver;row="+row+";col="+col;
	document.changeform.submit();
}

function split_hor(row, col)
{
	document.changeform.ge_action.value = "action=split_hor;row="+row+";col="+col;
	document.changeform.submit();
}

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
		<td bgcolor="#FFFFFF" colspan=2 class="celltext">Mitu celli kustutada:</td><td bgcolor="#FFFFFF" colspan=100>
			<input type='text' name='exp_count' value=1 size=2 class="formtext">
		</td>
	</tr>
	<tr>
		<!-- SUB: DC -->
			<td bgcolor="#FFFFFF" class="celltext">
				<!-- SUB: FIRST_C -->
					<a href="javascript:add_col({VAR:after},1);"><img alt="{VAR:LC_TABLE_ADD_COL}" src='{VAR:baseurl}/automatweb/images/rohe_nool_alla.gif' border=0></a>
				<!-- END SUB: FIRST_C -->

				<input type='checkbox' NAME='dc_{VAR:col}' value=1>&nbsp;<a href="#" onClick="if (confirm('Oled kindel et tahad tulpa kustutada?')) { del_col({VAR:col},1); return true;} else { return false;} "><img alt="Kustuta tulp" src='{VAR:baseurl}/automatweb/images/puna_nool_alla.gif' border=0></a>
				<a href='javascript:add_col({VAR:after}, 1)'><img alt="Lisa tulp" src='{VAR:baseurl}/automatweb/images/rohe_nool_alla.gif' border=0></a>
			</td>
		<!-- END SUB: DC -->
		<td bgcolor="#FFFFFF">&nbsp;</td>
	</tr>
	<!-- SUB: LINE -->
	<tr>
		<!-- SUB: COL -->
		<td bgcolor=#FFFFFF rowspan={VAR:rowspan} colspan={VAR:colspan} class="celltext"><input type="checkbox" name="sel_row={VAR:row};col={VAR:col}"><br>
			{VAR:EXP_LEFT}
			{VAR:EXP_UP}
			{VAR:EXP_RIGHT}
			{VAR:EXP_DOWN}
			{VAR:SPLIT_VERTICAL}
			{VAR:SPLIT_HORIZONTAL}
		</td>
		<!-- END SUB: COL -->

		<td bgcolor=#ffffff valign=bottom align=left>
			<!-- SUB: FIRST_R -->
			<a href='javascript:add_row({VAR:after},1)'><img alt="{VAR:LC_TABLE_ADD_ROW}" src='{VAR:baseurl}/automatweb/images/rohe_nool_vasakule.gif' BORDER=0></a><br>
			<!-- END SUB: FIRST_R -->
			<a href="#" onClick="if (confirm('Oled kindel et soovid rida kustutada?')) { del_row({VAR:row},1); return true; } else {return false;} "><img src='{VAR:baseurl}/automatweb/images/puna_nool_vasakule.gif' alt="Kustuta rida" BORDER=0></a><Br>
			<input type='checkbox' NAME='dr_{VAR:row}' value=1><br>
			<a href='javascript:add_row({VAR:after},1)'><img alt="Lisa rida" src='{VAR:baseurl}/automatweb/images/rohe_nool_vasakule.gif' BORDER=0></a>
		</td>
	</tr>
	<!-- END SUB: LINE -->
</table>
<a href='#' onClick='pick_style()'>Vali stiil</a>


<input type="hidden" name="ge_action" value="">
