{VAR:menubar}
<span class="header1">{VAR:menudef}</a>
<br>
<font color="red"><b>{VAR:status_msg}</b></font>
{VAR:navigator}
</span>
<table border=1 cellspacing=0 cellpadding=1 width="100%">
<tr>
<td class="lefttab" colspan="2">
<center>
<table border="0" cellspacing="2" cellpadding="0">
<tr>
<td class="header1" align="center">
<strong><a href="{VAR:self}?{VAR:prev}">&lt;&lt;</a></strong>
</td>
<td class="header1">&nbsp;</td>
<td class="header1" align="center"><strong>{VAR:caption}</strong></td>
<td class="header1">&nbsp;</td>
<td class="header1" align="center">
<strong><a href="{VAR:self}?{VAR:next}">&gt;&gt;</a></strong>
</td>
</tr>
<form method="POST" action="reforb.{VAR:ext}">
<tr>
<td class="header1" align="center" colspan="5">
<select name="month">{VAR:mlist}</select>
<select name="year">{VAR:ylist}</select>
<input type="submit" value="{VAR:LC_PLANNER_SHOW}">
{VAR:mreforb}
</td>
</tr>
</form>
</table>
</center>
</td>
</tr>
<tr>
<td colspan="5">
{VAR:content}
</td>
</tr>
</table>
