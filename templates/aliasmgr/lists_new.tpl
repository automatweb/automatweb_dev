<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">




<table border="0" cellpadding="0" cellspacing="0">
	<form method="GET" name="foo">
	<tr>
		<td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>
		<!--<td width="50" valign="middle"> -->
		<td width="50">
			<select name="aselect" class="formselect">
				<option>--Vali alias--</option>
				{VAR:aliases}
			</select>
		</td>

<!--ikoonid-->
<td valign="bottom"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:redir()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img name="new" alt="{VAR:LC_ALIASMGR_ADD}" title="{VAR:LC_ALIASMGR_ADD}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a>

<a href="javascript:window.location.href='{VAR:baseurl}/automatweb/orb.{VAR:ext}?class=aliasmgr&action=search&docid={VAR:id}'" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('search','','{VAR:baseurl}/automatweb/images/blue/awicons/search_over.gif',1)"><img name="search" alt="{VAR:LC_ALIASMGR_SEARCH}" title="{VAR:LC_ALIASMGR_SEARCH}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/search.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25">

<a href="javascript:awdelete()" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('delete','','{VAR:baseurl}/automatweb/images/blue/awicons/delete_over.gif',1)"><img name="delete" alt="{VAR:LC_ALIASMGR_DELETE}" title="{VAR:LC_ALIASMGR_DELETE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/delete.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="">

<a href="javascript:awchange()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('edit','','{VAR:baseurl}/automatweb/images/blue/awicons/edit_over.gif',1)"><img name="edit" alt="{VAR:LC_ALIASMGR_CHANGE}" title="{VAR:LC_ALIASMGR_CHANGE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/edit.gif" width="25" height="25"></a><img SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="">
<!--refresh--><a href="javascript:window.location.reload()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','{VAR:baseurl}/automatweb/images/blue/awicons/refresh_over.gif',1)"><img name="refresh" alt="{VAR:LC_ALIASMGR_REFRESH}" title="{VAR:LC_ALIASMGR_REFRESH}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="">
<IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:saveform()" onClick="saveform()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MENUEDIT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="">
</td>
</tr>
</table>


		</td>
		</tr>
		</table>


	</td>
	</tr>
	</table>

</td>
</tr>
</table>


<script language="Javascript">
var chk_status = true;
var chlinks = new Array();
{VAR:chlinks}

function selall()
{
	len = document.aform.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.aform.elements[i].name.indexOf("check") != -1)
		{
			document.aform.elements[i].checked=chk_status;
		}
	}
	chk_status = !chk_status;
	return false;
}

function redir()
{
	with(document.foo)
	{
		if (aselect.selectedIndex == 0)
		{
			alert('Vali alias!');
		}
		else
		{
			cl = aselect.options[aselect.selectedIndex].value;
			window.location.href="orb.{VAR:ext}?class="+cl+"&action=new&parent={VAR:parent}&period={VAR:period}&alias_to={VAR:id}&return_url={VAR:return_url}";
		};
	};
};

function awchange()
{
	len = document.aform.elements.length;
	cnt = 0;
	chk = 0;
	for (i = 0; i < len; i++)
	{
		with(document.aform.elements[i])
		{
			if (type == "checkbox" && name.indexOf("check") != -1)
			{
				if (checked)
				{
					cnt++;
					chk = value;
				}	
			}
		}
	};
	if (cnt == 1)
	{
		window.location.href = chlinks[chk];
	}
	else
	{
		alert('Palun valige 1 objekt muutmiseks');
	};
}

function awdelete()
{
	len = document.aform.elements.length;
	idx = 0;
	dellink = "";
	for (i = 0; i < len; i++)
	{
		with(document.aform.elements[i])
		{
			if (type == "checkbox" && name.indexOf("check") != -1 )
			{
				if (checked)
				{
					idx++;
					if (idx > 1)
					{
						dellink += ';';
					}
					dellink += value;	
				};
			}
		}
	};

	if (idx > 0)
	{
		if (confirm('Kustutada need ' + idx + ' aliast?'))
		{
			window.location.href = 'orb.{VAR:ext}?class=aliasmgr&action=delete_alias&oid={VAR:id}&id='+dellink;
		};
	}
	else
	{
		alert('Vali kustutatavad objektid.');
	}
}

function saveform()
{
	document.aform.submit();
}
</script>

<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr>
<td colspan="2" class="title">
</td>
</form>
</tr>
<form name="aform" action="reforb.{VAR:ext}">
{VAR:table}
{VAR:reforb}
</form>
</table>
