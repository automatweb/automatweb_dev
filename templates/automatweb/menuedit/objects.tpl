<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">

<script language="javascript">

var chk_status = true;

	function selall()
	{
		len = document.foo.elements.length;
		for (i=0; i < len; i++)
		{
			if (document.foo.elements[i].name.indexOf("sel") != -1)
			{
				document.foo.elements[i].checked=chk_status;
				window.status = ""+i+" / "+len;
			}
		}
		chk_status = !chk_status;
		return false;
	}

	function doSubmit(act)
	{
		if ( (act == "configure") || (act == "change") )
		{
			sel_item = 0;
			for (i = 0; i < document.foo.oid.length; i++)
			{
				if (document.foo.oid[i].checked == true)
				{
					sel_item = document.foo.oid[i].value;
				};
			};
			if (sel_item == 0)
			{
				for (i = 0; i < document.foo.elements.length; i++)
				{
					if (document.foo.elements[i].type == "checkbox"
						&& document.foo.elements[i].name.indexOf("sel") == 0
                                		&& document.foo.elements[i].checked 
						&& sel_item == 0)
					{
						sel_item = document.foo.elements[i].value;
					};
				};
			};
			if (sel_item == 0)
                        {

				alert('Vali objekt, mille omadusi muuta soovid');
				return false;
			};
			
		};
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

	function awcut()
	{
		document.foo.action.value="cut";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function awcopy()
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

	function awpaste()
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
		if (document.foo.type.selectedIndex > 1)
		{
			document.foo.action.value="menuedit_newobj";
			document.foo.submit();
			return true;
		}
		else
		{
			alert('Vali lisatav objekt');
		}
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
<body bgcolor="#FFFFFF" onLoad="MM_preloadImages('{VAR:baseurl}/images/blue/awicons/new_over.gif','{VAR:baseurl}/images/blue/awicons/save_over.gif','{VAR:baseurl}/images/blue/awicons/cut_over.gif','{VAR:baseurl}/images/blue/awicons/copy_over.gif','{VAR:baseurl}/images/blue/awicons/paste_over.gif','{VAR:baseurl}/images/blue/awicons/delete_over.gif','{VAR:baseurl}/images/blue/awicons/edit_over.gif','{VAR:baseurl}/images/blue/awicons/refresh_over.gif','{VAR:baseurl}/images/blue/awicons/search_over.gif','{VAR:baseurl}/images/blue/awicons/bugtrack_over.gif')">




<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">


<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>


<!-- SUB: ADD_CAT -->
<td width="100">


	<table border="0" cellpadding="0" cellspacing="0">
	
	<tr>
	<td>
	
	<select class="formselect" name="type">
	<option>VALI OBJEKT</option>
	<option>&nbsp;</option>

	{VAR:types}
	</select>
	
	</td>
	</tr>
	
	</table>

</td>
<td width="25" valign="middle"><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:doSubmit2()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img
name="new" alt="{VAR:LC_MENUEDIT_ADD}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a></td>
<!-- END SUB: ADD_CAT -->

<!--ikoonid-->
<td valign="bottom"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:seif()" onClick="seif()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MENUEDIT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()"  onClick="return awcut()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('cut','','{VAR:baseurl}/automatweb/images/blue/awicons/cut_over.gif',1)"><img name="cut" alt="Cut" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/cut.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return awcopy()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('copy','','{VAR:baseurl}/automatweb/images/blue/awicons/copy_over.gif',1)"><img name="copy" alt="Copy" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/copy.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return awpaste()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('paste','','{VAR:baseurl}/automatweb/images/blue/awicons/paste_over.gif',1)"><img name="paste" alt="Paste" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/paste.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return ddelete()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('delete','','{VAR:baseurl}/automatweb/images/blue/awicons/delete_over.gif',1)"><img name="delete" alt="{VAR:LC_MENUEDIT_DELETE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/delete.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return doSubmit('change')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('edit','','{VAR:baseurl}/automatweb/images/blue/awicons/edit_over.gif',1)"><img name="edit" alt="{VAR:LC_MENUEDIT_CHANGE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/edit.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--settings--><a
href="javascript:document.foo.submit()" onClick="return doSubmit('configure')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('settings','','{VAR:baseurl}/automatweb/images/blue/awicons/settings_over.gif',1)"><img name="settings" alt="Konfigureeri" title="Konfigureeri" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/settings.gif" width="25" height="25"><!--show--><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:window.parent.objects.location.href=show()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('show','','{VAR:baseurl}/automatweb/images/blue/awicons/show_over.gif',1)"><img name="show" alt="{VAR:LC_MENUEDIT_SHOW}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/show.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--refresh--><a
href="#" onClick='window.location.reload()' onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','{VAR:baseurl}/automatweb/images/blue/awicons/refresh_over.gif',1)"><img name="refresh" alt="{VAR:LC_MENUEDIT_REFRESH}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--search--><!--<a
href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('search','','{VAR:baseurl}/automatweb/images/blue/awicons/search_over.gif',1)"><img name="search" alt="Search" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/search.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="">--><!--bugtrack--><a
href="orb.aw?action=list&class=bugtrack&filt=all" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('bugtrack','','{VAR:baseurl}/automatweb/images/blue/awicons/bugtrack_over.gif',1)"><img name="bugtrack" alt="Bugtrack" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/bugtrack.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"></td>
<td align="right" class="celltext">&nbsp;&nbsp;[ <a target="list" href='languages.{VAR:ext}'><b>{VAR:lang_name}</b></a> ]&nbsp;&nbsp;</td>
</tr>
</table>





		</td></tr>
		</table>

	</td></tr>
	</table>

</td></tr>
</table>



<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">



<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr class="aste05">
<td width="1%" height="15" class="celltext">&nbsp;</td>
<td width="60%" height="15" class="celltext">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=name&order={VAR:order1}&period={VAR:period}'>{VAR:LC_MENUEDIT_NAME}</a>{VAR:sortedimg1}&nbsp;</td>
<td width="4%" align="center" class="celltext">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=jrk&order={VAR:order2}&period={VAR:period}'>{VAR:LC_MENUEDIT_ORDER}</a>{VAR:sortedimg2}&nbsp;</td>
<td width="6%" align="center" class="celltext">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=status&order={VAR:order6}&period={VAR:period}'>{VAR:LC_MENUEDIT_ACTIVE}</a>{VAR:sortedimg6}&nbsp;</td>
<td width="4%" align="center" class="celltext">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modifiedby&order={VAR:order3}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED_BY}</a>{VAR:sortedimg3}&nbsp;</td>
<td width="4%" align="center" class="celltext">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modified&order={VAR:order4}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED}</a>{VAR:sortedimg4}&nbsp;</td>
<td width="4%" align="center" class="celltext">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=class_id&order={VAR:order5}&period={VAR:period}'>{VAR:LC_MENUEDIT_TYPE}</a>{VAR:sortedimg5}&nbsp;</td>
<td width="2%" align="center" class="celltext">&nbsp;Link&nbsp;</td>

<td width="10%" align="center" colspan="2" class="celltext"><b>&nbsp;<a href='#' onClick="selall();return false;">{VAR:LC_MENUEDIT_CHOOSE}</a>&nbsp;</b></td>
<!--<td width="5%" align="center" class="celltext"><b>&nbsp;Default&nbsp;</b></td>-->
</tr>
<!-- SUB: CUT -->
aste03
<!-- END SUB: CUT -->
<!-- SUB: COPIED -->
aste03
<!-- END SUB: COPIED -->
<!-- SUB: NORMAL -->
aste07
<!-- END SUB: NORMAL -->



<!-- SUB: LINE -->
<tr class="{VAR:is_cut}">
<td height="15" class="celltext"><img src="{VAR:icon}"></td>
<!-- SUB: CAN_CHANGE -->
<td height="15" class="celltext" onMouseOver="this.style.backgroundColor='#A2BCCC';" onMouseOut="this.style.backgroundColor='#DBE8EE';">&nbsp;
<a {VAR:target} href="{VAR:change}">{VAR:name}</a>
&nbsp;</td>
<!-- END SUB: CAN_CHANGE -->

<!-- SUB: CAN_VIEW -->
<td height="15" class="celltext">&nbsp;
{VAR:name}
&nbsp;</td>
<!-- END SUB: CAN_VIEW -->
<td class="celltext" align=center>
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='ord[{VAR:oid}]' VALUE='{VAR:order}' SIZE=3 MAXLENGTH=4><input type='hidden' name='old_ord[{VAR:oid}]' value='{VAR:order}'>
<!-- END SUB: NFIRST -->
</td>
<td align="center" class="celltext">
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:oid}]' value=1 {VAR:active}><input type='hidden' NAME='old_act[{VAR:oid}]' VALUE='{VAR:active2}'>
<!-- END SUB: CAN_ACTIVE -->
</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;<a target='_blank' href='{VAR:link}'>Link</a>&nbsp;</td>


<td class="celltext">&nbsp;<input type="radio" name="oid" value="{VAR:oid}"
<!-- SUB: FE -->
onClick="sel_type={VAR:class_id};sel_form={VAR:form_id};sel_entry={VAR:oid};mk_ops()"
<!-- END SUB: FE -->
<!-- SUB: NFE -->
onClick="sel_type={VAR:class_id};cur_arr=0;sel_form=0;cl();"
<!-- END SUB: NFE -->
>&nbsp;</td>
<td class="celltext">&nbsp;<input type="checkbox" NAME="sel[{VAR:oid}]" VALUE="{VAR:oid}">&nbsp;</td>
<!--<td class="celltext">&nbsp;<input type="radio" name="default" value="{VAR:oid}" {VAR:checked}>&nbsp;</td>-->
</tr>
<!-- END SUB: LINE -->





<!--
<tr>
<td height="15" colspan=10 class="fgtext"><select class='small_button' name='default2'>{VAR:default}</select></td>
<td class="celltext">&nbsp;<input type="radio" name="default" value="-1" {VAR:checked}>&nbsp;</td>
</tr>
-->
<!-- SUB: FORMS_SHOWN -->

<tr>
<td height="10" colspan="10" class="celltext">{VAR:LC_MENUEDIT_FORMS_CHOOSE_OUTPUT}: <select class='small_button' name='op_id' onFocus="mk_ops()"><option value=''><option value=''><option value=''><option value=''></select></td>
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
