<html>
<head>
<title>{VAR:LC_SYSLOG_COMPARE_PERIODS}</title>
<link rel="stylesheet" href="css/site.css">
</head>
<body bgcolor="#FFFFFF">
<table border="0" cellspacing="0" cellpadding="2" width="100%" bgcolor="#CCCCCC">
<form method="POST" name="cform" action="{VAR:self}">
<tr>
<td>
	<table border="0" cellspacing="1" cellpadding="2" width="100%" bgcolor="#CCCCCC">
	<tr>
	<td colspan="3" class="fgtitle"><b><a href="javascript:document.cform.submit()">{VAR:LC_SYSLOG_SHOW_GRAPH}</a></b></a>
	</tr>
	<tr>
	<td colspan="3" class="fgtext">{VAR:LC_SYSLOG_PERIOD}: <b>{VAR:LC_SYSLOG_DAYS}</b><input type="radio" name="period" checked></td>
	</tr>
	<tr>
	<td class="fgtitle">{VAR:LC_SYSLOG_DAY} (pp-kk-aaaa)</td>
	<td colspan="2" align="right" class="fgtitle">{VAR:LC_SYSLOG_COLOR}</td>
	</tr>
	<!-- SUB: line -->
	<tr>
	<td class="fgtext">
	<input type="text" name="day[{VAR:cnt}]" size="10" maxlength="10" value="{VAR:day}">
	</td>
	<td bgcolor="{VAR:color}">
	&nbsp;
	</td>
	<td class="fgtext">
	<input type="text" name="color[{VAR:cnt}]" size="7" maxlength="7" value="{VAR:color}">
	</td>
	</tr>
	<!-- END SUB: line -->
	</table>
</td>
</tr>
<input type="hidden" name="display" value="compare">
<input type="hidden" name="showgraph" value="1">
</form>
</table>
<!-- SUB: graph -->
{VAR:LC_SYSLOG_GRAPH}:<br>
<img src="{VAR:self}?display=cgraph">
<!-- END SUB: graph -->
</body>
</html>
