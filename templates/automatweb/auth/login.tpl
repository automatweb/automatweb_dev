<table border="0" cellspacing="1" cellpadding="2">
<form method="POST" action="/index.{VAR:ext}">
<tr>
<td colspan="2">
<b>Selle ressursi kasutamiseks peate olema sisse logitud</b>
</td>
</tr>
<tr>
<td>
	Kasutajanimi:
</td>
<td>
	<input type="text" name="uid" size="40">
</td>
</tr>
<tr>
<td>
	Parool:
</td>
<td>
	<input type="password" name="password" size="40"">
	<img src='http://www.automatweb.com/img/logo_black.gif' height=1 width=1 onload="document.forms[0].elements['uid'].focus()">
</td>
</tr>
<td colspan="2" align="center">
{VAR:reforb}
<input type="submit" value="Logi sisse">
</td>
</tr>
</form>
</table>
