<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">

<script language="javascript">

var chk_status = true;

	function selall()
	{
		for (i=0; i < document.foo.elements.length; i++)
		{
			if (document.foo.elements[i].type == "checkbox" && document.foo.elements[i].name.indexOf("sel") != -1)
			{
				document.foo.elements[i].checked=chk_status;
			}
		}
		chk_status = !chk_status;
		return false;
	}

	function doSubmit(act)
	{
		document.foo.action.value="menuedit_redirect";
		document.foo.subaction.value=act;
		document.foo.submit();
		return true;
	}

	function seif()
	{
		document.foo.action.value="submit_order_doc";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function cut()
	{
		document.foo.action.value="cut";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function copy()
	{
		document.foo.action.value="copy";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function ddelete()
	{
		document.foo.action.value="o_delete";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function paste()
	{
		document.foo.action.value="paste";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function show()
	{
		if (sel_type == 8)
		{
			if (document.foo.op_id.options.selectedIndex < 0)
			{
				alert('{VAR:LC_MENUEDIT_FORMS_CHOOSE_OUTPUT}');
				return "#";
			}

			if (!sel_entry)
			{
				alert('{VAR:LC_MENUEDIT_FORMS_CHOOSE_ENTRY}');
				return "#";
			}
			op_id = document.foo.op_id.options[document.foo.op_id.options.selectedIndex].value;
			url = "orb.{VAR:ext}?class=form&action=show_entry&id="+sel_form+"&op_id="+op_id+"&entry_id="+sel_entry;
			window.parent.objects.location.href = url;
			return url;
		}
		return "#";
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

var sel_type;
sel_form = 8;

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
		if (eval("typeof(ops_"+sel_form+")") != "undefined")
		{
			eval("far = ops_"+sel_form);
			populate_list(foo.op_id, far);
			cur_arr = sel_form;
		}
	}
}

function cl()
{
	if (foo.op_id)
		clearList(foo.op_id);
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
<td height="15" colspan="9" class="fgtitle_new">&nbsp;<b>{VAR:LC_MENUEDIT_OBJECTS}: 
<!-- SUB: ADD_CAT -->
<select class="fgtitle_button" name="type">{VAR:types}</select>&nbsp;<a class="fgtitle_link" href='javascript:doSubmit2()'>{VAR:LC_MENUEDIT_ADD}</a>
<!-- END SUB: ADD_CAT -->
| <a href='javascript:document.foo.submit()' onClick="return seif()" class="fgtitle_link">{VAR:LC_MENUEDIT_SAVE}</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return doSubmit('change')">{VAR:LC_MENUEDIT_CHANGE}</a>
| <a href="javascript:window.parent.objects.location.href=show()"  class="fgtitle_link" >{VAR:LC_MENUEDIT_SHOW}</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return ddelete()">{VAR:LC_MENUEDIT_DELETE}</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return cut()">Cut</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return copy()">Copy</a>
<!-- SUB: PASTE -->
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return paste()">Paste</a>
<!-- END SUB: PASTE -->
| <a href='#' onClick='window.location.reload()' class="fgtitle_link">{VAR:LC_MENUEDIT_REFRESH}</a></b>
</b>
</td>
<td align=right class="fgtitle_new">[<a target="list" href='languages.{VAR:ext}'><b><font size=2 color="#FF8080">{VAR:lang_name}</font></b></a>]&nbsp;&nbsp; <a href='orb.aw?action=list&class=bugtrack&filt=all' class='fgtitle_link'>BugTrack</a>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" class="title">&nbsp;</td>
<td height="15" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=name&order={VAR:order1}&period={VAR:period}'>{VAR:LC_MENUEDIT_NAME}</a>{VAR:sortedimg1}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=jrk&order={VAR:order2}&period={VAR:period}'>{VAR:LC_MENUEDIT_ORDER}</a>{VAR:sortedimg2}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=status&order={VAR:order6}&period={VAR:period}'>{VAR:LC_MENUEDIT_ACTIVE}</a>{VAR:sortedimg6}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modifiedby&order={VAR:order3}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED_BY}</a>{VAR:sortedimg3}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modified&order={VAR:order4}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED}</a>{VAR:sortedimg4}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=class_id&order={VAR:order5}&period={VAR:period}'>{VAR:LC_MENUEDIT_TYPE}</a>{VAR:sortedimg5}&nbsp;</td>
<td align="center" class="title">&nbsp;Link&nbsp;</td>

<td align="center" colspan="2" class="title">&nbsp;<a href='#' onClick="selall();return false;">{VAR:LC_MENUEDIT_CHOOSE}</a>&nbsp;</td>
<td align="center" class="title">&nbsp;Default&nbsp;</td>
</tr>
<!-- SUB: CUT -->
fgtext2
<!-- END SUB: CUT -->
<!-- SUB: COPIED -->
title
<!-- END SUB: COPIED -->
<!-- SUB: NORMAL -->
fgtext
<!-- END SUB: NORMAL -->
<!-- SUB: LINE -->
<tr>
<td height="15" class="{VAR:is_cut}">&nbsp;<img src="{VAR:icon}">&nbsp;</td>
<td height="15" class="{VAR:is_cut}">&nbsp;<a {VAR:target} href="{VAR:change}">{VAR:name}</a>&nbsp;</td>
<td class="{VAR:is_cut}" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='ord[{VAR:oid}]' VALUE='{VAR:order}' SIZE=2 MAXLENGTH=3><input type='hidden' name='old_ord[{VAR:oid}]' value='{VAR:order}'>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td align="center" class="{VAR:is_cut}">&nbsp;
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:oid}]' value=1 {VAR:active}><input type='hidden' NAME='old_act[{VAR:oid}]' VALUE='{VAR:active2}'>
<!-- END SUB: CAN_ACTIVE -->
&nbsp;</td>
<td align="center" class="{VAR:is_cut}" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="{VAR:is_cut}" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="{VAR:is_cut}" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td align="center" class="{VAR:is_cut}" nowrap>&nbsp;<a target='_blank' href='{VAR:link}'>Link</a>&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="radio" name="oid" value="{VAR:oid}"
<!-- SUB: FE -->
onClick="sel_type={VAR:class_id};sel_form={VAR:form_id};sel_entry={VAR:oid};mk_ops()"
<!-- END SUB: FE -->
<!-- SUB: NFE -->
onClick="sel_type={VAR:class_id};cur_arr=0;sel_form=0;cl();"
<!-- END SUB: NFE -->
>&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="checkbox" NAME="sel[{VAR:oid}]" VALUE=1>&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="radio" name="default" value="{VAR:oid}" {VAR:checked}>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->

<tr>
<td height="15" colspan=10 class="fgtext"><select class='small_button' name='default2'>{VAR:default}</select></td>
<td class="fgtext2">&nbsp;<input type="radio" name="default" value="-1" {VAR:checked}>&nbsp;</td>
</tr>
<!-- SUB: FORMS_SHOWN -->
<tr>
<td height="15" colspan=11 class="fgtext">{VAR:LC_MENUEDIT_FORMS_CHOOSE_OUTPUT}: <select class='small_button' name='op_id' onFocus="mk_ops()"><option value=''><option value=''><option value=''><option value=''></select></td>
</tr>
<!-- END SUB: FORMS_SHOWN -->

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
