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
		<td width="50" valign="middle">
			<select name="aselect" class="formselect">
				<option>--Vali alias--</option>
				{VAR:aliases}
			</select>
		</td>

<!--ikoonid-->
<td valign="bottom">
{VAR:buttons}
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
<script type="text/javascript">
function aw_save()
{
	cnt = 0;
	res = "";
	len = document.searchform.check.length;
	for (i = 0; i < len; i++)
	{
		if (document.searchform.check[i].checked)
		{
			if (cnt == 0)
			{
				res = document.searchform.check[i].value;
			}
			else
			{
				res = res + "," + document.searchform.check[i].value;
			}
			cnt++;
		};
	};
	if (res.length > 0)
	{
		link = '{VAR:saveurl}&alias=' + res;
		window.location = link;
	}
	else
	{
		alert('Ühtegi objekti pole valitud!');
	};
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
</script>
