<script language="JavaScript">
function ed_rep()
{
	active = 0;
	for (i = 1; i <= document.replist.check.length; i++)
	{
		if (document.replist.check[i-1].checked)
		{
			active = document.replist.check[i-1].value;
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

function del_rep()
{
	active = 0;
	for (i = 1; i <= document.replist.check.length; i++)
	{
		if (document.replist.check[i-1].checked)
		{
			active = document.replist.check[i-1].value;
		};
	};
	if (active == 0)
	{
		alert('Vali tsükkel, mida kustutada soovid');
	}
	else
	{
		window.location = "{VAR:del_link}&cycle=" + active;
	};
};
</script>
<table border="0" cellspacing="1" cellpadding="1" bgcolor="#CCCCCC" width="500">
<tr>
<td>
	{VAR:menubar}
</td>
</tr>
<tr>
<td>

<table border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF" width="100%">
<form method="POST" action="reforb.{VAR:ext}" name="replist">
<tr>
<td class="fgtitle" colspan="4">&nbsp;
<a href='{VAR:add_link}'>Lisa</a> |
<a href='javascript:ed_rep()'>Muuda</a> |
<a href='javascript:del_rep()'>Kustuta</a>
</td>
</tr>
<tr>
<td class="fgtitle" align="center"><b>ID</b></td>
<td class="fgtitle" align="center"><b>Algab</b></td>
<td class="fgtitle" align="center"><b>Lõpeb</b></td>
<td class="fgtitle" align="center"><b>Vali</b></td>
</tr>
<!-- SUB: line -->
<tr>
<td class="fgtitle">{VAR:id}</td>
<td class="fgtitle">{VAR:start}</td>
<td class="fgtitle">{VAR:end}</td>
<td class="fgtitle" align="center"><input type="radio" name="check" value="{VAR:id}"></td>
</tr>
<!-- END SUB: line -->
</form>
</table>


</td>
</tr>

</table>

