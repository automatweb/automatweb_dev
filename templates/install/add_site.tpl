<table border="1" cellpadding="1" cellspacing="1">
<form method="GET" action="orb.{VAR:ext}">
<tr>
	<td bgcolor="#DDDDDD" colspan="2">
	<strong>Uus sait...</strong>
	<font color="red">
	{VAR:message}
	</font>
	</td>
</tr>
<tr>
	<td>Baasi nimi (kasutatakse ka kataloogi tegemisel):</td>
	<td><input type="text" name="awdbname" size="30" value="{VAR:awdbname}"></td>
</tr>
<tr>
	<td>Baasi host:</td>
	<td><input type="text" name="awdbhost" size="30" value="{VAR:awdbhost}"></td>
</tr>
<tr>
	<td>Baasi kasutaja:</td>
	<td><input type="text" name="awdbuser" size="30" value="{VAR:awdbuser}"></td>
</tr>
<tr>
	<td>Baasi parool:</td>
	<td><input type="text" name="awdbpass" size="30" value="{VAR:awdbpass}"></td>
</tr>
<tr>
	<td>Default kasutaja:</td>
	<td><input type="text" name="default_user" size="30" value="{VAR:default_user}"></td>
</tr>
<tr>
	<td>Default parool:</td>
	<td><input type="text" name="default_pass" size="30" value="{VAR:default_pass}"></td>
</tr>
<tr>
	<td valign="top">Saidi tüüp:</td>
	<td>
	<input type="radio" name="type" value="1">
	Default<br>
	<font color="#cccccc">
	<input type="radio" name="type" value="2" disabled>
	Raamidega<br>
	<input type="radio" name="type" value="3" disabled>
	Ultralight (ainult tekst)<br>
	</font>
</td>
</tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" value="Lisa">
		{VAR:reforb}
	</td>
</tr>
</form>
</table>
