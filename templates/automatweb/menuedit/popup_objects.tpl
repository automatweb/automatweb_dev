<html>
<head>
<script language="Javascript">
function submitsection()
{
	// koostame nimekirja koigist checkboxidest
	with(document.picker)
	{
		tail = "";
		for (i = 0; i < chk.length; i++)
		{
			tail = tail + "&chk[" + chk[i].value + "]=1";
		};
	};

	with(document.picker)
	{
		for (i = 0; i < style.length; i++)
		{
			if (style[i].checked)
			{
				estyle = style[i].value;
			};
		};
	};
	window.parent.opener.location = "tpledit.aw?action=submitsection&tpl={VAR:tpl}&source={VAR:source}&style=" + estyle + tail;
	window.parent.close();
};

function select_all()
{
	boxes = document.picker;
	if (document.picker.allchecked.value == '1')
	{
		value = false;
		document.picker.allchecked.value = 0;
	}
	else
	{
		value = true;
		document.picker.allchecked.value = 1;
	};
	
	for (i = 0; i <= boxes.length; i++)
	{
		if (boxes[i].type == "checkbox") {
			name = boxes[i].name;
			re = /^chk/i;
			if (name.match(re))
			{
				boxes[i].checked = value;
			};
		};
	};
};
</script>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<body bgcolor="#FFFFFF"">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="0" width="100%">
<form name="picker" method="get">
<tr>
<td class="fgtitle" colspan="3"><strong>Stiil</strong></td>
</tr>
<tr>
<td bgcolor="#FFFFFF" class="checkbox">
<input type="radio" name="style" value="radio" checked> Radiobuttonid
</td>
<td class="checkbox" bgcolor="#FFFFFF">
<input type="radio" name="style" value="checkbox"> Checkboxid
</td>
<td class="checkbox" bgcolor="#FFFFFF">
<input type="radio" name="style" value="dropdown"> Dropdown
</td>
</tr>
<tr>
<td class="fgtitle" colspan="3" align="center">'{VAR:objname}' &nbsp; <strong><font color="red">{VAR:total}</font></strong> objekt &nbsp;
<strong><a href="javascript:submitsection()">[Lisa]</a></strong></td>
</tr>
<input type="hidden" name="type" value="submit_popup_section">
<input type="hidden" name="source" value="{VAR:source}">
</table>
</td>
</tr>
</table>
<p>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td colspan="4" class="fgtext" bgcolor="#FFFFFF">&nbsp;</td>
<td class="fgtext" bgcolor="#FFFFFF" align="center">
<a href="javascript:select_all()"><b>X</b></a>
</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">&nbsp;<img src="{VAR:icon}">&nbsp;</td>
<td height="15" class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="checkbox" bgcolor="#FFFFFF">&nbsp;<input type="checkbox" name="chk" value="{VAR:oid}"></td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
<input type="hidden" name="allchecked" value="0">
<input type="hidden" name="tpl" value="{VAR:tpl}">
</form>
</tr>
</table>
</body>
</html>
