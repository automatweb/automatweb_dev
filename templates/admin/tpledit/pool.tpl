<!-- object pool -->
<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<title>Object pool</title>
<script language="JavaScript">
function submit()
{
	// siin peame kuidagi kokku korjama koigi checkboxide v‰‰rtused	
	with(document.picker)
	{
		tail = "";
		for (i = 0; i < chk.length; i++)
		{
			if (chk[i].checked)
			{
				tail = tail + "&chk[" + chk[i].value + "]=1";
			};
		};
	};
	window.opener.location = "{VAR:self}?action=addstatic&tpl=" + document.picker.tpl.value + tail;
	window.close();

};

function cancel()
{
	window.close();
};

</script>
</head>
<body bgcolor="#FFFFFF">
<table border="0" cellspacing="0" cellpadding="0" bgcolor="#CCCCCC" width="100%">
<tr>
<form name="picker" method="GET">
<td>
	<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF" width="100%">
	<tr>
		<td class="fgtitle" align="center"><b>Nimi</b></td>
		<td class="fgtitle" align="center"><b>Vali</b></td>
	</tr>
	<!-- SUB: line -->
	<tr>
		<td class="fgtext">{VAR:name}</td>
		<td class="checkbox" align="center"><input type="checkbox" {VAR:checked} name="chk" value="{VAR:id}"></td>
	</tr>
	<!-- END SUB: line -->
	<tr>
		<td class="fgtitle" align="center" colspan="2">
		<strong>
			<a href="javascript:submit()">[ Lisa ]</a>
			<a href="javascript:cancel()">[ Katkesta ]</a>
		</strong>
		</td>
	</tr>
	</table>
</td>
</tr>
<input type="hidden" name="tpl" value="{VAR:tpl}">
</form>
</table>

</body>
</html>
