<html>
<head>
<title>{VAR:LC_SYSLOG_STATISTICS}</title>
<link rel="stylesheet" href="css/site.css">
<script language="Javascript">
function ipexplorer(ip)
{
 var windowprops = "toolbar=0,location=1,directories=0,status=0, "+
"menubar=0,scrollbars=1,resizable=1,width=400,height=500";

OpenWindow = window.open("ipexplorer.{VAR:ext}?ip=" + ip, "remote", windowprops);
}
function compare()
{
 var wprops = "toolbar=0,location=1,directories=0,status=0,"+
 	"menubar=0,scrollbars=1,resizable=1,width=500,height=300";
	CWindow = window.open("{VAR:self}?display=compare","compare",wprops);
}
</script>
</head>
<body bgcolor="#FFFFFF" marginwidth="0" marginheight="0">
<table border="0" cellspacing="1" cellpadding="2" width="100%">
<form name="stat" action="{VAR:self}" method="POST">
<tr>
<td class="fgtitle">
<b>
<a href="{VAR:self}">DR. ONLINE</a> |
<a href="{VAR:today}">{VAR:LC_SYSLOG_TODAY}</a> |
<a href="{VAR:thisweek}">{VAR:LC_SYSLOG_THIS_WEEK}</a> |
<a href="{VAR:thismonth}">{VAR:LC_SYSLOG_THIS_MONTH}</a> |
<a href="#" onClick="javascript:compare()">{VAR:LC_SYSLOG_COMPARE_PERIODS1}</a>
</b><br>
{VAR:LC_SYSLOG_FROM} (pp-kk-aaaa):
<input type="text" size="10" maxlength="10" name="from" value="{VAR:from}">
{VAR:LC_SYSLOG_TILL} (pp-kk-aaaa):
<input type="text" size="10" maxlength="10" name="to" value="{VAR:to}">
<input type="submit" value="{VAR:LC_SYSLOG_SHOW}">
<input type="hidden" name="display" value="stat">
</td>
</tr>
<tr>
<td>
<small>
{VAR:parts}
</small>
</td>
</tr>
</form>
</table>

<table border="0" cellspacing="0" cellpadding="2" width="100%">
<tr>
<td valign="top" width="50%">
	{VAR:left}
</td>
<td rowspan=2 width="50%" valign="top">
	{VAR:right}
</td>
</tr>
<tr>
<td valign="top" width="50%">
	{VAR:left1}
</td>
</tr>
</table>
</body>
</html>
