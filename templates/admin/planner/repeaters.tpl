<html>
<head>
<title>Vali kordused</title>
<link rel="stylesheet" href="/automatweb/css/site.css">
<link rel="stylesheet" href="/automatweb/css/fg_menu.css">
<script language="JavaScript">
function recall()
{
	with (window.opener.document)
	{
		if (event.repeat_value.value)
		{
			val = event.repeat_value.value;
		}
		else
		{
			val = 6;
		};
		document.repeater.repeat_value.value = val;
		document.repeater.dayskiptype[event.dayskip_type.value - 1].checked = true;
		document.repeater.repeat[event.repeat.value - 1].checked = true;
		document.repeater.repeat_type.selectedIndex = event.repeat_type.value - 1;
		dstype = event.dayskip_type.value;
		if (dstype)
		{
			dsval = event.dayskip_value.value;
		}
		else
		{
			dsval = 2;
		};
		wdays = event.wd.value.split(',');
		for (i = 0; i < wdays.length; i++)
		{
			document.repeater.wd[wdays[i]-1].checked = true;
		}
		document.repeater.dayskip.value = dsval;
		
	}
}

function store_n_close()
{
	c = 0;
	wdx = new Array();
	rep = 0;
	with (document.repeater)
	{
		for (i = 0; i < dayskiptype.length; i++)
		{
			if (dayskiptype[i].checked)
			{
				dstype = dayskiptype[i].value;
			}
		};

		for (i = 0; i < repeat.length; i++)
		{
			if (repeat[i].checked)
			{
				rep = repeat[i].value;
			};
		};

		for(i = 0; i < wd.length; i++)
                {
                        if (wd[i].checked)
                        {
                                wdx[c] = wd[i].value;
                                c = c + 1;
                        };
                };
        }		

	with (window.opener.document)
	{
		event.dayskip_value.value = document.repeater.dayskip.value;
		event.wd.value = wdx.join(',');
		event.repeat.value = rep;
		event.dayskip_type.value = dstype;
		event.repeater.checked = true;
		event.repeat_value.value = document.repeater.repeat_value.value;
		event.repeat_type.value = document.repeater.repeat_type[document.repeater.repeat_type.selectedIndex].value;
	};
	window.close();
}

function update_dayskiptype(field)
{
	window.opener.document.event.dayskip_type.value = field.value;
};

</script>
</head>
<body>
<form name="repeater" method="post">
<table border="1" cellspacing="1" cellpadding="2" width="100%">
<tr>
<td class="header1" valign="top">
Päevad
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="dayskiptype" value="1" checked">Iga päev
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="dayskiptype" value="2"> Iga <input type="text" name="dayskip" size="2" value="2"> päeva tagant
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="dayskiptype" value="3"> Nendel päevadel
<br>
<input type="checkbox" name="wd" value="1"> esmaspäev<br>
<input type="checkbox" name="wd" value="2"> teisipäev<br>
<input type="checkbox" name="wd" value="3"> kolmapäev<br>
<input type="checkbox" name="wd" value="4"> neljapäev<br>
<input type="checkbox" name="wd" value="5"> reede<br>
<input type="checkbox" name="wd" value="6"> laupäev<br>
<input type="checkbox" name="wd" value="7"> pühapäev<br>
</td>
</tr>
<tr>
<td class="header1" valign="top">Nädalad</td>
<td class="fgtitle" valign="top">
<input type="radio" name="weekskiptype">Iga nädal<br>
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="weekskiptype">Iga <input type="text" size="2" maxlength="2" value="2"> nädala tagant.
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="weekskiptype">kuu<br>
<input type="checkbox">1. nädalal<br>
<input type="checkbox">2. nädalal<br>
<input type="checkbox">3. nädalal<br>
<input type="checkbox">4. nädalal<br>
</td>
</tr>
<tr>
<td valign="top" class="header1">
Kuud
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="monskiptype">Iga kuu
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="monskiptype">Iga <input type="text" size="2" maxlength="2" value="2"> kuu tagant.
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="monskiptype">Kuud<br>
<input type="checkbox">jaanuaris<br>
<input type="checkbox">veebruaris<br>
<input type="checkbox">märtsis<br>
<input type="checkbox">aprillis<br>
<input type="checkbox">mais<br>
<input type="checkbox">juunis<br>
<input type="checkbox">juulis<br>
<input type="checkbox">augustis<br>
<input type="checkbox">septembris<br>
<input type="checkbox">oktoobris<br>
<input type="checkbox">novembris<br>
<input type="checkbox">detsembris<br>
</td>
</tr>
<tr>
<td valign="top" class="header1">
Aastad
</td>
<td class="fgtitle" valign="top">
<input type="radio" class="fgtitle" name="yearskiptype"><b>Igal aastal</b><br>
</td>
<td class="fgtitle" valign="top" colspan="2">
<input type="radio" class="fgtitle" name="yearskiptype">Iga <input type="text" size="2" maxlength="2" value="2"> aasta tagant
</td>
</tr>
<td valign="top" class="header1">
<b>Kordub</b>
</td>
<td class="fgtitle">
<input type="radio" name="repeat" value="1" checked>Igavesti
</td>
<td class="fgtitle" colspan="2">
<input type="radio" name="repeat" value="2"><input type="text" name="repeat_value" size="2" maxlength="2" value="6">
<select name="repeat_type">
<option value="1">päeva</option>
<option value="2">nädalat</option>
<option value="3">kuud</option>
<option value="4">aastat</option>
</select>
</td>
</table>
<hr size="1">
<input type="button" value="Vali && Sulge aken" onClick="store_n_close()">
</form>
<script language="Javascript">
recall();
</script>
</body>
</html>

