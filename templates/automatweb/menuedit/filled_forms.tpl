<html>
<head>
<link rel="stylesheet" href="/automatweb/css/site.css">
<link rel="stylesheet" href="/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="/automatweb/css/bench.css">
<script language="javascript">
	function doSubmit(act)
	{
		if (document.foo.op_id.options.selectedIndex < 0)
		{
			alert('Vali listboksist v2ljundi stiil!');
			return "#";
		}

		if (!sel_entry)
		{
			alert('Vali formi sisestus!');
			return "#";
		}
		op_id = document.foo.op_id.options[document.foo.op_id.options.selectedIndex].value;
		url = "orb.{VAR:ext}?class=form&action="+act+"&id="+sel_form+"&op_id="+op_id+"&entry_id="+sel_entry;
		
		return url;
	}
	function doSubmit2()
	{
		document.foo.action.value="menuedit_newobj";
		document.foo.submit();
		return true;
	}

var ops=new Array()
<!-- SUB: FORM -->
ops_{VAR:form_id} = new Array();
<!-- SUB: FORM_OP -->
ops_{VAR:form_id}[{VAR:cnt}] = new Array({VAR:op_id},"{VAR:op_name}");
<!-- END SUB: FORM_OP -->

<!-- END SUB: FORM -->

var sel_form;
sel_form = 0;

var sel_entry;
sel_form = 0;

var cur_arr;
cur_arr = 0;

function clearList(list)
{
	var listlen = list.length;

	for(i=0; i < listlen; i++)
		list.options[0] = null;
}

function addItem(list, arr)
{
	list.options[list.length] = new Option(arr[1],""+arr[0],false,false);
}

function populate_list(el,arr)
{
	clearList(el);
	for (i = 0; i < arr.length; i++)
		addItem(el,arr[i]);
}

function mk_ops()
{
	if (cur_arr != sel_form)
	{
		eval("far = ops_"+sel_form);
		populate_list(foo.op_id, far);
		cur_arr = sel_form;
	}
}
</script>
</head>
<body>
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="0" cellpadding="1" width=100%>
<tr>
<td bgcolor=#000000>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="9" class="fgtitle_new">&nbsp;<b>T&auml;IDETUD FORMID: 
 | <a href="javascript:window.parent.objects.location.href=doSubmit('show_entry');" class="fgtitle_link" >N&auml;ita</a>
 | <a href="javascript:window.parent.objects.location.href=doSubmit('show');"  class="fgtitle_link" >Muuda</a>
 | <a href='#' onClick='window.location.reload()' class="fgtitle_link">V&auml;rskenda</a></b>
</b>
</td>
<td align=right class="fgtitle_new"><a href='bug.{VAR:ext}?op=listall' class='fgtitle_link'>BugTrack</a>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" class="title">&nbsp;T&auml;itja&nbsp;</td>
<td align="center" class="title">&nbsp;Vatamisi&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" class="title">&nbsp;Formi nimi&nbsp;</td>
<td align="center" colspan="2" class="title">&nbsp;Vali&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">&nbsp;<a href="{VAR:change}">{VAR:filler}</a>&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:hits}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:form}&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="radio" name="entry_id" value="{VAR:oid}" onClick="sel_form={VAR:form_id};sel_entry={VAR:oid};mk_ops()">&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="checkbox" NAME="sel_{VAR:oid}" VALUE=1>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->

<tr>
<td height="15" colspan=6 class="fgtext">Vali v&auml;ljundi stiil, millega formi sisestust n&auml;idatakse: <select class='small_button' name='op_id' onFocus="mk_ops()"><option value=''><option value=''><option value=''><option value=''></select></td>
</tr>

</table>
</td>
</tr>
</table>
<input type="hidden" name="period" value="{VAR:period}">
<input type='hidden' NAME='subaction' VALUE=''>
{VAR:reforb}
</form>
</body>
</html>
