<form method="POST" action="/index.{VAR:ext}">
<span style="font-family: Arial, Helvetica; font-size: 12px">
<strong></strong>
Sisestage oma kasutajanimi või e-posti aadress ning teile meilitakse link, millele klikkides pääsete parooli muutmise lehele.<br><br>
Probleemide puhul palun võtke ühendust aadressil <a href="mailto:{VAR:webmaster}">{VAR:webmaster}</a>.
</span>
<br><br>
<table border="0">
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td>
<input type="radio" name="type" value="email" checked>
</td>
<td>
E-posti aadress:
</td>
<td><input type="text" name="email" size="30">
</td>
</tr>
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td>
<input type="radio" name="type" value="uid">
</td>
<td>
Kasutajanimi:
</td>
<td><input type="text" name="uid" size="30">
</td>
</tr>
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td colspan="3" align="center">
<input type="submit" value="Soovin meeldetuletust">
</td>
</tr>
<tr style="font-family: Arial, Helvetica; font-size: 12px">
<td colspan="3" align="center">
<font color="red"><b>{VAR:status_msg}</b></font><br>
</td>
</tr>
</table>
{VAR:reforb}
</p>
</form>
