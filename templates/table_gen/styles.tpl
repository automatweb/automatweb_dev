<script language="javascript">
function exp_left(row,col)
{
	url = "orb.{VAR:ext}?class=table&action=exp_left&id={VAR:table_id}&row="+row+"&col="+col+"&cnt="+document.q.exp_count.value;
	window.location.href=url;
}
function exp_up(row,col)
{
	url = "orb.{VAR:ext}?class=table&action=exp_up&id={VAR:table_id}&row="+row+"&col="+col+"&cnt="+document.q.exp_count.value;
	window.location.href=url;
}
function exp_down(row,col)
{
	url = "orb.{VAR:ext}?class=table&action=exp_down&id={VAR:table_id}&row="+row+"&col="+col+"&cnt="+document.q.exp_count.value;
	window.location.href=url;
}
function exp_right(row,col)
{
	url = "orb.{VAR:ext}?class=table&action=exp_right&id={VAR:table_id}&row="+row+"&col="+col+"&cnt="+document.q.exp_count.value;
	window.location.href=url;
}
function split_ver(row,col)
{
	url = "orb.{VAR:ext}?class=table&action=split_ver&id={VAR:table_id}&row="+row+"&col="+col;
	window.location.href=url;
}
function split_hor(row,col)
{
	url = "orb.{VAR:ext}?class=table&action=split_hor&id={VAR:table_id}&row="+row+"&col="+col;
	window.location.href=url;
}

function pick_style(row,col)
{
	url = "orb.{VAR:ext}?class=table&action=pick_style&id={VAR:table_id}";

	aurl = "";
	for (i=0; i < {VAR:rows}; i++)
	{	
		val = 0;
		eval("val = document.q.dr_"+i+".checked");
		if (val)
			aurl+="&frow[]="+i;
	}

	for (i=0; i < {VAR:cols}; i++)
	{	
		val =0;
		eval("val = document.q.dc_"+i+".checked");
		if (val)
			aurl+="&fcol[]="+i;
	}

	if (aurl != "")
		url+=aurl;
	else
		url+="&row="+row+"&col="+col;

	remote("no",200,200,url);
}
</script>
<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0><a href='{VAR:change}'>{VAR:LC_TABLE_FILL}</a></td>
		<td bgcolor=#a0a0a0><a href='{VAR:styles}'>{VAR:LC_TABLE_EDIT}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:admin}'>{VAR:LC_TABLE_ADMIN}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:view}'>{VAR:LC_TABLE_PREVIEW}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:import}'>{VAR:LC_TABLE_IMPORT}</a></td>
		<!-- SUB: ALIAS_LINK -->
		<td bgcolor=#f0f0f0><a href='{VAR:url}'>{VAR:title}</a></td>
		<!-- END SUB: ALIAS_LINK -->
		<td bgcolor=#f0f0f0><a href='{VAR:addstyle}'>{VAR:LC_TABLE_ADD_STYLE}</a></td>
	</tr>
</table>
<br>
<form action='reforb.{VAR:ext}' method=post NAME='q'>
<input type='submit' NAME='save_table' VALUE='{VAR:LC_TABLE_SAVE}'>
<table border=0 bgcolor=#cccccc cellspacing=2 cellpadding=2>
<tr>
<td bgcolor=#FFFFFF colspan=2>{VAR:LC_TABLE_CHOOSE_TABLE_STYLE}:</td><td bgcolor="#FFFFFF" colspan=100><select name='table_style'><option value=''>{VAR:tablestyle}</select></td>
</tr>
<tr>
<td colspan=2 bgcolor="#FFFFFF">{VAR:LC_TABLE_CHOOSE_CELL_STYLE}:</td><td bgcolor="#FFFFFF" colspan=100><select name='default_style'><option value=''>{VAR:defaultstyle}</select></td>
</tr>
<tr>
<td bgcolor="#FFFFFF" colspan=2>{VAR:LC_TABLE_HOW_MANY_CELL_DEL}:</td><td bgcolor="#FFFFFF" colspan=100><input type='text' name='exp_count' value=1 size=2></td>
</tr>
<tr>
<!-- SUB: DC -->
<td bgcolor="#FFFFFF">
<!-- SUB: FIRST_C -->
<a href='{VAR:add_col}'><img alt="{VAR:LC_TABLE_ADD_COL}" src='/images/rohe_nool_alla.gif' border=0></a>
<!-- END SUB: FIRST_C -->
<input type='checkbox' NAME='dc_{VAR:col}' value=1>&nbsp;<a href="javascript:box2('{VAR:LC_TABLE_SURE_DEL_COLUMN}?','{VAR:del_col}')"><img alt="{VAR:LC_TABLE_DEL_COL}" src='/images/puna_nool_alla.gif' border=0></a>
<a href='{VAR:add_col}'><img alt="{VAR:LC_TABLE_ADD_COL}" src='/images/rohe_nool_alla.gif' border=0></a>
</td>
<!-- END SUB: DC -->
<td bgcolor="#FFFFFF">&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<!-- SUB: COL -->
<td bgcolor=#FFFFFF rowspan={VAR:rowspan} colspan={VAR:colspan}><b>T:</b> {VAR:text}<br><a class="tablegen_link" href='javascript:pick_style({VAR:row},{VAR:col})'>{VAR:LC_TABLE_SMALL_CHOOSE_STYLE}</a><br>{VAR:EXP_LEFT}&nbsp;{VAR:EXP_UP}&nbsp;{VAR:EXP_RIGHT}&nbsp;{VAR:EXP_DOWN}&nbsp;{VAR:SPLIT_VERTICAL}&nbsp;{VAR:SPLIT_HORIZONTAL}</td>
<!-- END SUB: COL -->
<td bgcolor=#ffffff valign=bottom align=left>
<!-- SUB: FIRST_R -->
<a href='{VAR:add_row}'><img alt="{VAR:LC_TABLE_ADD_ROW}" src='/images/rohe_nool_vasakule.gif' BORDER=0></a><br>
<!-- END SUB: FIRST_R -->
<a href="javascript:box2('{VAR:LC_TABLE_SURE_DEL_ROW}?','{VAR:del_row}')"><img src='/images/puna_nool_vasakule.gif' alt="{VAR:LC_TABLE_DEL_ROW}" BORDER=0></a><Br><input type='checkbox' NAME='dr_{VAR:row}' value=1><br>
<a href='{VAR:add_row}'><img alt="{VAR:LC_TABLE_ADD_ROW}" src='/images/rohe_nool_vasakule.gif' BORDER=0></a>
</td>
</tr>
<!-- END SUB: LINE -->
</table>
<input type='submit' NAME='save_table' VALUE='{VAR:LC_TABLE_SAVE}'>
{VAR:reforb}
</form>
<br>
<br>
<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0>
			<form action='orb.{VAR:ext}' method=get name='lv'>
				<input type='hidden' NAME='class' VALUE='table'>
				<input type='hidden' NAME='action' VALUE='add_col'>
				<input type='hidden' NAME='id' VALUE='{VAR:table_id}'>
				<input type='hidden' NAME='after' VALUE='0'>
				<a href='javascript:document.lv.submit();'>{VAR:LC_TABLE_ADD} </a><input type='text' NAME='num' size=2> <a href='javascript:document.lv.submit();'>{VAR:LC_TABLE_COL}</a>
			</form>
		</td>
		<td bgcolor=#f0f0f0>
			<form action='orb.{VAR:ext}' method=get name='lr'>
				<input type='hidden' NAME='class' VALUE='table'>
				<input type='hidden' NAME='action' VALUE='add_row'>
				<input type='hidden' NAME='id' VALUE='{VAR:table_id}'>
				<input type='hidden' NAME='after' VALUE='0'>
					<a href='javascript:document.lr.submit();'>{VAR:LC_TABLE_ADD} </a><input type='text' NAME='num' size=2> <a href='javascript:document.lr.submit();'>{VAR:LC_TABLE_ROW}</a>
			</form>
		</td>
	</tr>
</table>
