<span class="fgtitle">
<!-- SUB: me -->
<strong>{VAR:mname}</strong>
<!-- END SUB: me -->
<!-- SUB: me1 -->
<a href="{VAR:self}?month={VAR:month}">{VAR:mname}</a>
<!-- END SUB: me1 -->
<br>
<!-- SUB: de -->
<strong>{VAR:day}</strong>
<!-- END SUB: de -->
<!-- SUB: de1 -->
<a href="{VAR:self}?month={VAR:month}&day={VAR:day}">{VAR:day}</a>
<!-- END SUB: de1 -->
<br>
<strong>Aktiivne periood</strong>: {VAR:activeperiod}</strong>
</span>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF" valign="top">
{VAR:left}
<!-- SUB: dayframe -->
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td colspan="2" class="fgtitle">Hitid päevade lõikes ({VAR:activeperiod})</td>
</tr>
<tr>
<td class="fgtitle">Päev</td>
<td class="fgtitle">Hitte</td>
<td class="fgtitle">&nbsp;</td>
<!-- SUB: dayhits -->
<tr>
<td class="fgtext"><a href="{VAR:self}?month={VAR:month}&day={VAR:dnum}">{VAR:dnum}</a></td>
<td class="fgtext" align="right">{VAR:dhits}</td>
<td class="fgtext"><img src="images/bar.gif" width="{VAR:width}" height="5"></td>
</tr>
<!-- END SUB: dayhits -->
</table>
<!-- END SUB: dayframe -->
<!-- SUB: hourframe -->
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td colspan="2" class="fgtitle">Hitid tundide lõikes ({VAR:activeperiod})</td>
</tr>
<tr>
<td class="fgtitle">Periood</td>
<td class="fgtitle">Hitte</td>
<td class="fgtitle">&nbsp;</td>
<!-- SUB: hourhits -->
<tr>
<td class="fgtext">{VAR:hour}:00-{VAR:hour}:59</td>
<td class="fgtext" align="right">{VAR:hits}</td>
<td class="fgtext"><img src="images/bar.gif" width="{VAR:width}" height="5"></td>
</tr>
<!-- END SUB: hourhits -->
</table>
<!-- END SUB: hourframe -->
</td>
<td valign="top">
<table border="0" cellspacing="1" cellpadding="2">
<form method="GET">
<tr>
<td colspan="2" class="fgtitle">Top <select name="count">{VAR:count}</select> <select name="type">{VAR:type}</select> selles perioodis</td>
</tr>
<tr>
<td class="fgtitle">IP</td>
<td class="fgtitle">Hitte</td>
</tr>
<!-- SUB: hosts -->
<tr>
<td class="fgtext">{VAR:ip}</td>
<td class="fgtext" align="right">{VAR:hits}</td>
</tr>
<!-- END SUB: hosts -->
</table>
</form>
</td>
</tr>
</table>
</td>
</tr>
</table>
