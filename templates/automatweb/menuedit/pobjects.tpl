<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<script language="javascript">
	function doSubmit(act)
	{
		document.foo.action.value="menuedit_redirect";
		document.foo.subaction.value=act;
		document.foo.submit();
		return true;
	}
	function doSubmit2()
	{
		document.foo.action.value="menuedit_newobj";
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

	function paste()
	{
		document.foo.action.value="paste";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function ddelete()
	{
		// first, if nothing is selected, say so. or maybe we shouldn't? naah. better tell the user about his/her errors
		chk = false;
		if (document.foo.oid.type == "radio")
		{
			// stupid dom. if there is only one radiobutton it is not put in the array like several radiobuttons.
			if (document.foo.oid.checked==true)
			{
				chk = true;
			}
		}
		else
		{
			for (i = 0; i < document.foo.oid.length; i++)
			{
				if (document.foo.oid[i].checked==true)
				{
					chk = true;
				}
			}
		}
		if (!chk)
		{
			for(i = 0; i < document.foo.elements.length; i++)
			{
				if (document.foo.elements[i].type == "checkbox")
				{
					if (document.foo.elements[i].name.substr(0,3) == "sel" && document.foo.elements[i].checked)
					{
						chk = true;
					}
				}
			}
		}
		if (!chk)
		{
			alert("Vali objekt(id), mida soovid kustutada!");
			return false;
		}

		if (confirm("Oled kindel et soovid valitud objekte kustutada?"))
		{
			document.foo.action.value="o_delete";
			document.foo.subaction.value="";
			document.foo.submit();
			return true;
		}
		return false;
	}
</script>
</head>
<body>
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>


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
	<!-- {VAR:LC_MENUEDIT_OBJECTS}: -->
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
<td valign="bottom"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:document.foo.submit()" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MENUEDIT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()"  onClick="return cut()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('cut','','{VAR:baseurl}/automatweb/images/blue/awicons/cut_over.gif',1)"><img name="cut" alt="Cut" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/cut.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return copy()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('copy','','{VAR:baseurl}/automatweb/images/blue/awicons/copy_over.gif',1)"><img name="copy" alt="Copy" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/copy.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return paste()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('paste','','{VAR:baseurl}/automatweb/images/blue/awicons/paste_over.gif',1)"><img name="paste" alt="Paste" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/paste.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return ddelete()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('delete','','{VAR:baseurl}/automatweb/images/blue/awicons/delete_over.gif',1)"><img name="delete" alt="{VAR:LC_MENUEDIT_DELETE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/delete.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onClick="return doSubmit('change')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('edit','','{VAR:baseurl}/automatweb/images/blue/awicons/edit_over.gif',1)"><img name="edit" alt="{VAR:LC_MENUEDIT_CHANGE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/edit.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--settings--><a
href="javascript:document.foo.submit()" onClick="return doSubmit('configure')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('settings','','{VAR:baseurl}/automatweb/images/blue/awicons/settings_over.gif',1)"><img name="settings" alt="" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/settings.gif" width="25" height="25"><!--show--><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:window.parent.objects.location.href=show()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('show','','{VAR:baseurl}/automatweb/images/blue/awicons/show_over.gif',1)"><img name="show" alt="{VAR:LC_MENUEDIT_SHOW}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/show.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--refresh--><a
href="#" onClick='window.location.reload()' onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','{VAR:baseurl}/automatweb/images/blue/awicons/refresh_over.gif',1)"><img name="refresh" alt="{VAR:LC_MENUEDIT_REFRESH}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--search--><!--<a
href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('search','','{VAR:baseurl}/automatweb/images/blue/awicons/search_over.gif',1)"><img name="search" alt="Search" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/search.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="">--><!--bugtrack--><a
href="orb.aw?action=list&class=bugtrack&filt=all" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('bugtrack','','{VAR:baseurl}/automatweb/images/blue/awicons/bugtrack_over.gif',1)"><img name="bugtrack" alt="Bugtrack" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/bugtrack.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"></td>
<td align="right" class="celltext">&nbsp;&nbsp;[ <a target="list" href='{VAR:baseurl}/automatweb/orb.{VAR:ext}?class=languages&action=admin_list'><b>{VAR:lang_name}</b></a> ]&nbsp;&nbsp;</td>
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

<td height="15" class="celltext">&nbsp;</td>
<td height="15" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=obj_list&parent={VAR:parent}&sortby=name&order={VAR:order1}&period={VAR:period}'>{VAR:LC_MENUEDIT_NAME}</a>{VAR:sortedimg1}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=obj_list&parent={VAR:parent}&sortby=jrk&order={VAR:order2}&period={VAR:period}'>{VAR:LC_MENUEDIT_ORDER}</a>{VAR:sortedimg2}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=obj_list&parent={VAR:parent}&sortby=status&order={VAR:order6}&period={VAR:period}'>{VAR:LC_MENUEDIT_ACTIVE}</a>{VAR:sortedimg6}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=obj_list&parent={VAR:parent}&sortby=modifiedby&order={VAR:order3}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED_BY}</a>{VAR:sortedimg3}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=obj_list&parent={VAR:parent}&sortby=modified&order={VAR:order4}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED}</a>{VAR:sortedimg4}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=obj_list&parent={VAR:parent}&sortby=class_id&order={VAR:order5}&period={VAR:period}'>{VAR:LC_MENUEDIT_TYPE}</a>{VAR:sortedimg5}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MENUEDIT_LEAD}?&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MENUEDIT_FORUM}?&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MENUEDIT_FRONTPAGE}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MENUEDIT_RIGHT}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MENUEDIT_TEXT} OK?&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MENUEDIT_PICTURES} OK?&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MENUEDIT_LINK}&nbsp;</td>
<td align="center" colspan="2" class="celltext">&nbsp;{VAR:LC_MENUEDIT_CHOOSE}&nbsp;</td>
</tr>



<!-- SUB: LINE -->
<tr class="aste07">
<td height="15" class="celltext">&nbsp;<img src="{VAR:icon}">&nbsp;</td>
<td height="15" class="celltext">&nbsp;<a href="{VAR:change}">{VAR:name}</a>&nbsp;</td>
<td class="celltext" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='jrk[{VAR:oid}]' VALUE='{VAR:order}' SIZE=2 MAXLENGTH=3><input type='hidden' name='old_ord[{VAR:oid}]' value='{VAR:order}'>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td align="center" class="fgtext">&nbsp;
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:oid}]' {VAR:active} value="1"><input type='hidden' NAME='old_act[{VAR:oid}]' VALUE='{VAR:active2}'>
<!-- END SUB: CAN_ACTIVE -->
&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<input type="checkbox" name="showlead[{VAR:oid}]" value="1" {VAR:showlead}>&nbsp;</td>
<td align="center" class="celltext">&nbsp;<input type="checkbox" name="is_forum[{VAR:oid}]" value="1" {VAR:is_forum}>&nbsp;</td>
<td align="center" class="celltext">&nbsp;<input type="checkbox" name="esilehel[{VAR:oid}]" value="1" {VAR:esilehel}>&nbsp;<input type='text' name='jrk1[{VAR:oid}]' size=2 class="small_button" maxlength=2 value='{VAR:jrk1}'>&nbsp;</td>
<td align="center" class="celltext">&nbsp;<input type="checkbox" name="esilehel_uudis[{VAR:oid}]" value="1" {VAR:esilehel_uudis}>&nbsp;<input type='text' name='jrk2[{VAR:oid}]' size=2 class="small_button" maxlength=2 value='{VAR:jrk2}'>&nbsp;</td>
<td align="center" class="celltext">&nbsp;<input type="checkbox" name="text_ok[{VAR:oid}]" value="1" {VAR:text_ok}>&nbsp;</td>
<td align="center" class="celltext">&nbsp;<input type="checkbox" name="pic_ok[{VAR:oid}]" value="1" {VAR:pic_ok}>&nbsp;</td>
<td align="center" class="celltext" nowrap>&nbsp;<a target='_blank' href='{VAR:link}'>Link</a>&nbsp;</td>

<td class="celltext">&nbsp;<input type="radio" name="oid" value="{VAR:oid}">&nbsp;</td>
<td class="celltext">&nbsp;<input type="checkbox" NAME="sel[{VAR:oid}]" VALUE=1>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->



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
