<form method="POST" action="/index.{VAR:ext}">
<table border="0">
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td colspan="2">Kasutajanimi: <strong>{VAR:uid}</strong></td>
</tr>
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td>Parool:</td>
<td><input type="password" name="pass1" size="30"</td>
</tr>
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td>Parool veel kord:</td>
<td><input type="password" name="pass2" size="30"</td>
</tr>
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td colspan="2" align="center">
<input type="submit" value="Vaheta parool">
{VAR:reforb}
</td>
</tr>
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td colspan="2" align="center">
<span style="color: red"><strong>{VAR:status_msg}</strong></span>
</td>
</tr>
</table>
</form>
