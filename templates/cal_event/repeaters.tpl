<script language="JavaScript">
function ed_rep()
{
	if (!document.replist.check)
	{
		alert('Nimekiri on tühi, pole midagi muuta');
	}
	else
	{
		if (!document.replist.check.length)
		{
			active = document.replist.check.value;
		}
		else
		{
			active = 0;
			for (i = 1; i <= document.replist.check.length; i++)
			{
				if (document.replist.check[i-1].checked)
				{
					active = document.replist.check[i-1].value;
				};
			};
		};
		if (active == 0)
		{
			alert('Vali tsükkel, mida muuta soovid');
		}
		else
		{
			window.location = "{VAR:ed_link}&cycle=" + active;
		};
	};
};

function del_rep()
{
	if (!document.replist.check)
	{
		alert('Nimekiri on tühi, pole midagi kustutata');
	}
	else
	{
		if (!document.replist.check.length)
		{
			active = document.replist.check.value;
		}
		else
		{
			active = 0;
			for (i = 1; i <= document.replist.check.length; i++)
			{
				if (document.replist.check[i-1].checked)
				{
					active = document.replist.check[i-1].value;
				};
			};
		};
		if (active == 0)
		{
			alert('Vali tsükkel, mida kustutata soovid');
		}
		else
		{
			if (confirm('Soovid sa seda tsüklit tõesti kustutada?'))
			{
				window.location = "{VAR:del_link}&cycle=" + active;
			};
		};
	};
};
</script>
{VAR:menubar}





<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">



<table border="0" cellspacing="1" cellpadding="2" width="100%">



<tr class="aste01">

<td valign="middle" colspan="4"><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:add_link}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img
name="new" alt="Lisa" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:ed_rep()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('edit','','{VAR:baseurl}/automatweb/images/blue/awicons/edit_over.gif',1)"><img name="edit" alt="Muuda" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/edit.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:del_rep()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('delete','','{VAR:baseurl}/automatweb/images/blue/awicons/delete_over.gif',1)"><img name="delete" alt="Kustuta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/delete.gif" width="25" height="25"></a></td>
</tr>





<tr class="aste05">
<form method="POST" action="reforb.{VAR:ext}" name="replist">

<td class="celltext" align="center" width="2%">ID</td>
<td class="celltext" align="center">Algab</td>
<td class="celltext" align="center">Lõpeb</td>
<td class="celltext" align="center" width="4%">Vali</td>
</tr>
<!-- SUB: line -->
<tr class="aste01">
<td class="celltext">{VAR:id}</td>
<td class="celltext">{VAR:start}</td>
<td class="celltext">{VAR:end}</td>
<td class="celltext" align="center"><input type="radio" name="check" value="{VAR:id}"></td>
</tr>
<!-- END SUB: line -->
</form>




	</table>


</td>
</tr>
</table>
