<html>
<head>
<title>Vali kasutajad</title>
</head>
<style type="text/css">
	.fubartitle {font-family: Verdana,sans-serif; font-size: 9px; font-weight: bold; background: #EEEEFF; }
	.fubar {font-family: Verdana,sans-serif; font-size: 9px;};
</style>
<script language="JavaScript">
function store_n_close()
{
	res = new Array();
	c = 0;
	with(document.picker)
	{
		for(i = 0; i < sel.length; i++)
		{
			if (sel[i].checked)
			{
				//res.push(sel[i].value);
				res[c] = sel[i].value;
				c = c + 1;
			};
		};
	}
	
	with(window.opener.document)
	{
		if (writemessage.mtargets.value.length == 0)
		{
			writemessage.mtargets.value = res.join(',');
		}
		else
		{
			writemessage.mtargets.value = writemessage.mtargets.value + ',' + res.join(',');
		};
	};
	window.close();
};
</script>
</head>
<body bgcolor="#FFFFFF">
<form name="picker" action='reforb.{VAR:ext}'>
<table border="0" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE" width="100%">
<tr>
<td>
	<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF" width="100%">
	<tr>
		<td class="fubartitle" align="center">X</td>
		<td class="fubartitle" align="center">Uid</td>
		<td class="fubartitle" align="center">Online</td>
		<td class="fubartitle" align="center">&nbsp;</td>
	</tr>
	<!-- SUB: line -->
	<tr>
		<td class="fubar" align="center"><input type="checkbox" name="sel" value="{VAR:uid}"></td>
		<td class="fubar">{VAR:uid}</td>
		<td class="fubar">{VAR:online}</td>
		<td class="fubar">&nbsp;&nbsp;</td>
	</tr>
	<!-- END SUB: line -->
	<tr>
		<td class="fubar" align="center" colspan="4">
			<input type="button" value="Vali && Sulge aken" onClick="store_n_close()">
		</td>
	</tr>
	</table>
</td>
</tr>
</table>
</form>
</body>
</html>
