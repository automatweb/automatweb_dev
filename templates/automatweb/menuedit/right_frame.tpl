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
}

function add()
{
	if (document.foo.type.selectedIndex < 2)
	{
		alert("Valige objekt, mida lisada soovite!");
	}
	else
	{
		url = "orb.{VAR:ext}?class="+document.foo.type.options[document.foo.type.selectedIndex].value+"&action=new&parent={VAR:parent}&period={VAR:period}";
		window.location = url;
	}
}

function submit(val)
{
	document.foo.action.value=val;
	document.foo.submit();
}

function change(val)
{
	cnt = 0;
	len = document.foo.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.foo.elements[i].name.indexOf("sel") != -1)
		{
			if (document.foo.elements[i].checked)
			{
				cnt++;
			}
		}
	}

	if (cnt == 1)
	{
		document.foo.action.value="change_redir";
		document.foo.submit();
	}
	else
	{
		alert("Valige ainult yks objekt palun!");
	}
}
</script>






<!-- begin ICONS table -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<form action='reforb.{VAR:ext}' method="post" name="foo">

<tr><td colspan="2" class="awmenuediticonsjoon1"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
<tr>
<td width="1"><IMG SRC="images/awmenueditor_iconbar_back.gif" WIDTH="5" HEIGHT="32" BORDER=0 ALT=""></td>
<td width="100%" height="32" background="images/awmenueditor_iconbar_back.gif">

	<table background="images/trans.gif" border="0" cellpadding="0" cellspacing="0">
	<tr>

<!-- SUB: ADD_CAT -->
	<td>{VAR:add_applet}</td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>
<!-- END SUB: ADD_CAT -->

	<td><a href="javascript:document.foo.submit()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','images/icons/save_over.gif',1)"><IMG name="save" SRC="images/icons/save.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="{VAR:LC_MENUEDIT_SAVE}"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

	<td><IMG SRC="images/icons/seperator.gif" WIDTH="2" HEIGHT="22" BORDER=0 ALT=""></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

	<td><a href="javascript:submit('cut')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('cut','','images/icons/cut_over.gif',1)"><IMG name="cut" SRC="images/icons/cut.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="Cut"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

	<td><a href="javascript:submit('copy')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('copy','','images/icons/copy_over.gif',1)"><IMG name="copy" SRC="images/icons/copy.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="Copy"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

<!-- SUB: PASTE -->
	<td><a href="javascript:submit('paste')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('paste','','images/icons/paste_over.gif',1)"><IMG name="paste" SRC="images/icons/paste.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="Paste"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>
<!-- END SUB: PASTE -->

	<td><a href="javascript:submit('delete')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('delete','','images/icons/delete_over.gif',1)"><IMG name="delete" SRC="images/icons/delete.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="{VAR:LC_MENUEDIT_DELETE}"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

	<td><a href="javascript:change()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('edit','','images/icons/edit_over.gif',1)"><IMG name="edit" SRC="images/icons/edit.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="{VAR:LC_MENUEDIT_CHANGE}"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

	<td><IMG SRC="images/icons/seperator.gif" WIDTH="2" HEIGHT="22" BORDER=0 ALT=""></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

	<td><a href="#" onMouseOut="MM_swapImgRestore()" onClick='window.location.reload()' onMouseOver="MM_swapImage('refresh','','images/icons/refresh_over.gif',1)"><IMG name="refresh" SRC="images/icons/refresh.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="{VAR:LC_MENUEDIT_REFRESH}"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

<!--	<td><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('search','','images/icons/search_over.gif',1)"><IMG name="search" SRC="images/icons/search.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT=""></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>-->

	<td><a href="orb.aw?action=list&class=bugtrack&filt=all" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('bugtrack','','images/icons/bugtrack_over.gif',1)"><IMG name="bugtrack" SRC="images/icons/bugtrack.gif" WIDTH="23" HEIGHT="22" BORDER=0 ALT="Bugtrack"></a></td>
	<td><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

	</tr>
	</table>

</td>
<tr><td colspan="2" class="awmenuediticonsjoon2"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
</table>
<!-- end ICONS table -->

{VAR:table}
{VAR:reforb}
</form>
