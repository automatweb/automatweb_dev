<html>
<head>
<title>Vali grupid</title>
</head>
<style type="text/css">
	.fubartitle {font-family: Verdana,sans-serif; font-size: 9px; font-weight: bold; background: #EEEEFF; }
	.fubar {font-family: Verdana,sans-serif; font-size: 9px;};
</style>
<script language="JavaScript">
names = new Array;
<!-- SUB: names -->
names[{VAR:gid}] = '{VAR:name2}';
<!-- END SUB: names -->

function store_n_close()
{
	res = new Array();
	gids = new Array();
	c = 0;
	with(document.picker)
	{
		for(i = 0; i < sel.length; i++)
		{
			if (sel[i].checked)
			{
				res[c] = names[sel[i].value];
				gids[c] = sel[i].value;
				c = c + 1;
			};
		};
	}

	with(window.opener.document)
	{
		if (writemessage.mtargets2.value.length == 0)
		{
			writemessage.mtargets2.value = res.join(',');
		}
		else
		{
			writemessage.mtargets2.value = writemessage.mtargets2.value + ',' + res.join(',');
		};
		if (writemessage.gids.value.length == 0)
		{
			writemessage.gids.value = gids.join(',');
		}
		else
		{
			writemessage.gids.value = writemessage.gids.value + ',' . gids.join(',');
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
		<td class="fubartitle" align="center">Gid</td>
		<td class="fubartitle" align="center">Nimi</td>
		<td class="fubartitle" align="center">Liikmeid</td>
		<td class="fubartitle" align="center">&nbsp;</td>
	</tr>
	<!-- SUB: line -->
	<tr>
		<td class="fubar" align="center"><input type="checkbox" name="sel" value="{VAR:gid}"></td>
		<td class="fubar">{VAR:gid}</td>
		<td class="fubar">{VAR:name}</td>
		<td class="fubar">{VAR:members}</td>
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
<input type="hidden" name="tmp" value="">
</form>
</body>
</html>
