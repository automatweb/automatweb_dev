<html>
<head>
<title>{VAR:LC_PLANNER_CHOOSE_REPLAYS}</title>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<script language="JavaScript">
function recall()
{
	with (window.opener.document)
	{
		if (event.dayskip.value == 1)
		{
			document.repeater.dayskiptype[0].checked = true;
		}
		else
		{
			if (event.dayskip.value > 1)
			{
				document.repeater.dayskiptype[1].checked = true;
				document.repeater.dayskip.value = event.dayskip.value;
			}
			else
			{
				document.repeater.dayskiptype[2].checked = true;
				if (event.weekpwhen.value)
				{
					wdays = event.weekpwhen.value.split(',');
					for (i = 0; i < wdays.length; i++)
					{
						document.repeater.weekpwhen[wdays[i]-1].checked = true;
					};
				};
			};
		};

		if (event.weekskip.value == 1)
		{
			document.repeater.weekskiptype[0].checked = true;
		}
		else
		{
			if (event.weekskip.value > 1)
			{
				document.repeater.weekskiptype[1].checked = true;
				document.repeater.weekskip.value = event.weekskip.value;
			}
			else
			{
				if (event.monpwhen.value)
				{
					document.repeater.weekskiptype[2].checked = true;
					weeks = event.monpwhen.value.split(',');
					for (i = 0; i < weeks.length; i++)
					{
						document.repeater.monpwhen[weeks[i]-1].checked = true;
					};
				};
			};
		};
		
		if (event.monskip.value == 1)
		{
			document.repeater.monskiptype[0].checked = true;
		}
		else
		{
			if (event.monskip.value > 1)
			{
				document.repeater.monskiptype[1].checked = true;
				document.repeater.monskip.value = event.monskip.value;
			}
			else
			{
				if (event.yearpwhen.value)
				{
					document.repeater.monskiptype[2].checked = true;
					months = event.yearpwhen.value.split(',');
					for (i = 0; i < months.length; i++)
					{
						document.repeater.yearpwhen[months[i]-1].checked = true;
					};
				};
			};
		};

		if (event.yearskip.value == 1)
		{
			document.repeater.yearskiptype[0].checked = true;
		}
		else
		{
			if (event.yearskip.value > 1)
			{
				document.repeater.yearskiptype[1].checked = true;
				document.repeater.yearskip.value = event.yearskip.value;
			};
		};
		document.repeater.monpwhen2.value = event.monpwhen2.value;

		if (event.rep_forever.value > 0)
		{
			document.repeater.repeat[0].checked = true;
		}
		else
		{
			if (event.rep_type)
			{
				document.repeater.repeat[1].checked = true;
				document.repeater.rep_type.selectedIndex = event.rep_type.value - 1;
				document.repeater.rep_dur.value = event.rep_dur.value;
			};
		};
			
	
	}
}

function store_n_close()
{
	c = 0;
	wdx = new Array();
	mx = new Array();
	yx = new Array();
	dayskip = 0;
	weekskip = 0;
	monskip = 0;
	yearskip = 0;

	if (document.repeater.dayskiptype[0].checked)
	{
		dayskip = 1;
	};

	if (document.repeater.dayskiptype[1].checked)
	{
		dayskip = document.repeater.dayskip.value;
	};
			
	if (document.repeater.dayskiptype[2].checked)
	{
		dayskip = 0;
		for (i = 0; i <= document.repeater.weekpwhen.length; i++)
		{
			with(document.repeater)
			{
				if (weekpwhen[i] && weekpwhen[i].checked)
				{
					wdx[c] = document.repeater.weekpwhen[i].value;
					c = c + 1;
				};
			};
		};
	};

	if (document.repeater.weekskiptype[0].checked)
	{
		weekskip = 1;
	};

	if (document.repeater.weekskiptype[1].checked)
	{
		weekskip = document.repeater.weekskip.value;
	};

	c = 0;
	if (document.repeater.weekskiptype[2].checked)
	{
		weekskip = 0;
		for (i = 0; i <= document.repeater.monpwhen.length; i++)
		{
			with(document.repeater)
			{
				if (monpwhen[i] && monpwhen[i].checked)
				{
					mx[c] = monpwhen[i].value;
					c = c + 1;
				};
			};
		};
	};
	
	if (document.repeater.monskiptype[0].checked)
	{
		monskip = 1;
	};

	if (document.repeater.monskiptype[1].checked)
	{
		monskip = document.repeater.monskip.value;
	};

	c = 0;

	if (document.repeater.monskiptype[2].checked)
	{
		monskip = 0;
		for (i = 0; i <= document.repeater.yearpwhen.length; i++)
		{
			with(document.repeater)
			{
				if (yearpwhen[i] && yearpwhen[i].checked)
				{
					yx[c] = yearpwhen[i].value;
					c = c + 1;
				};
			};
		};
	};

	if (document.repeater.yearskiptype[0].checked)
	{
		yearskip = 1;
	}

	if (document.repeater.yearskiptype[1].checked)
	{
		yearskip = document.repeater.yearskip.value;
	};

	if (document.repeater.repeat[0].checked)
	{
		window.opener.document.event.rep_forever.value = 1;
	};

	if (document.repeater.repeat[1].checked)
	{
		window.opener.document.event.rep_forever.value = 0;
		window.opener.document.event.rep_dur.value = document.repeater.rep_dur.value;
		window.opener.document.event.rep_type.value = document.repeater.rep_type[document.repeater.rep_type.selectedIndex].value;
	};

	window.opener.document.event.dayskip.value = dayskip;
	window.opener.document.event.weekpwhen.value = wdx.join(',');
	window.opener.document.event.weekskip.value = weekskip;
	window.opener.document.event.monpwhen.value = mx.join(',');
	window.opener.document.event.monskip.value = monskip;
	window.opener.document.event.yearpwhen.value = yx.join(',');
	window.opener.document.event.monpwhen2.value = document.repeater.monpwhen2.value;
	window.opener.document.event.yearskip.value = yearskip;
	window.opener.document.event.repeater.checked = true;
	window.close();
}

</script>
</head>
<body>
<form name="repeater" method="post">
<table border="1" cellspacing="1" cellpadding="2" width="100%">
<tr>
<td class="header1" valign="top">
{VAR:LC_PLANNER_DAYS}
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="dayskiptype" value="1" checked">{VAR:LC_PLANNER_EVERY_DAY}
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="dayskiptype" value="2"> {VAR:LC_PLANNER_EVERY} <input type="text" name="dayskip" size="2" value="2"> {VAR:LC_PLANNER_AFTER_DAY}
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="dayskiptype" value="3"> {VAR:LC_PLANNER_THOSE_DAYS}
<br>
<input type="checkbox" name="weekpwhen" value="1"> {VAR:LC_PLANNER_MON}<br>
<input type="checkbox" name="weekpwhen" value="2"> {VAR:LC_PLANNER_TUE}<br>
<input type="checkbox" name="weekpwhen" value="3"> {VAR:LC_PLANNER_WEN}<br>
<input type="checkbox" name="weekpwhen" value="4"> {VAR:LC_PLANNER_THU}<br>
<input type="checkbox" name="weekpwhen" value="5"> {VAR:LC_PLANNER_FRI}<br>
<input type="checkbox" name="weekpwhen" value="6"> {VAR:LC_PLANNER_SAT}<br>
<input type="checkbox" name="weekpwhen" value="7"> {VAR:LC_PLANNER_SUN}<br>
</td>
</tr>
<tr>
<td class="header1" valign="top">{VAR:LC_PLANNER_WEEKS}</td>
<td class="fgtitle" valign="top">
<input type="radio" name="weekskiptype">{VAR:LC_PLANNER_EVERY_WWEK}<br>
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="weekskiptype">{VAR:LC_PLANNER_EVERY} <input type="text" size="2" maxlength="2" name="weekskip" value="2"> {VAR:LC_PLANNER_AFTER_WEEK}.
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="weekskiptype">{VAR:LC_PLANNER_MONTH}<br>
<input type="checkbox" name="monpwhen" value="1">1. {VAR:LC_PLANNER_ON_WEEK}<br>
<input type="checkbox" name="monpwhen" value="2">2. {VAR:LC_PLANNER_ON_WEEK}<br>
<input type="checkbox" name="monpwhen" value="3">3. {VAR:LC_PLANNER_ON_WEEK}<br>
<input type="checkbox" name="monpwhen" value="4">4. {VAR:LC_PLANNER_ON_WEEK}<br>
</td>
</tr>
<tr>
<td valign="top" class="header1" rowspan="2">
{VAR:LC_PLANNER_MONTHS}
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="monskiptype">{VAR:LC_PLANNER_EVERY_MONTH}
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="monskiptype">{VAR:LC_PLANNER_EVERY} <input type="text" size="2" name="monskip" maxlength="2" value="2">{VAR:LC_PLANNER_AFTER_MONTH}.
</td>
<td class="fgtitle" valign="top">
<input type="radio" name="monskiptype">{VAR:LC_PLANNER_MONTHS}<br>
<input type="checkbox" name="yearpwhen" value="1">{VAR:LC_PLANNER_JAN}<br>
<input type="checkbox" name="yearpwhen" value="2">{VAR:LC_PLANNER_VEB}<br>
<input type="checkbox" name="yearpwhen" value="3">{VAR:LC_PLANNER_MAR}<br>
<input type="checkbox" name="yearpwhen" value="4">{VAR:LC_PLANNER_APR}<br>
<input type="checkbox" name="yearpwhen" value="5">{VAR:LC_PLANNER_MAI}<br>
<input type="checkbox" name="yearpwhen" value="6">{VAR:LC_PLANNER_JUN}<br>
<input type="checkbox" name="yearpwhen" value="7">{VAR:LC_PLANNER_JUL}<br>
<input type="checkbox" name="yearpwhen" value="8">{VAR:LC_PLANNER_AUG}<br>
<input type="checkbox" name="yearpwhen" value="9">{VAR:LC_PLANNER_SEPT}<br>
<input type="checkbox" name="yearpwhen" value="10">{VAR:LC_PLANNER_OKT}<br>
<input type="checkbox" name="yearpwhen" value="11">{VAR:LC_PLANNER_NOV}<br>
<input type="checkbox" name="yearpwhen" value="12">{VAR:LC_PLANNER_DETS}<br>
</td>
</tr>
<tr>
<td class="fgtitle" valign="top">{VAR:LC_PLANNER_DAYS_IN_MONTH}(ntx 3,17)</td>
<td class="fgtitle" colspan="2"><input type="text" name="monpwhen2" value=""></td>
</tr>
<tr>
<td valign="top" class="header1">
{VAR:LC_PLANNER_YEARS}
</td>
<td class="fgtitle" valign="top">
<input type="radio" class="fgtitle" name="yearskiptype"><b>{VAR:LC_PLANNER_EVERY_YEAR}</b><br>
</td>
<td class="fgtitle" valign="top" colspan="2">
<input type="radio" class="fgtitle" name="yearskiptype">{VAR:LC_PLANNER_EVERY} <input name="yearskip" type="text" size="2" maxlength="2" value="2"> {VAR:LC_PLANNER_AFTER_YEAR}
</td>
</tr>
<td valign="top" class="header1">
<b>{VAR:LC_PLANNER_REPEATS}</b>
</td>
<td class="fgtitle">
<input type="radio" name="repeat" value="1" checked>{VAR:LC_PLANNER_FOREVER}
</td>
<td class="fgtitle" colspan="2">
<input type="radio" name="repeat" value="2"><input type="text" name="rep_dur" size="2" maxlength="2" value="6">
<select name="rep_type">
<option value="1">{VAR:LC_PLANNER_DAYS}</option>
<option value="2">{VAR:LC_PLANNER_WWEEK}</option>
<option value="3">{VAR:LC_PLANNER_MMONTH}</option>
<option value="4">{VAR:LC_PLANNER_YYEAR}</option>
</select>
</td>
</table>
<hr size="1">
<input type="button" value="{VAR:LC_PLANNER_CHOOSE_CLOSE}" onClick="store_n_close()">
</form>
<script language="Javascript">
recall();
</script>
</body>
</html>

