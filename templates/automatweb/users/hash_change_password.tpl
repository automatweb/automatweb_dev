<font color="red"><b>{VAR:status_msg}</b></font>
<form method="POST" action="/index.{VAR:ext}">
Kasutajanimi: {VAR:uid}<br>
Uus parool: <input type="password" name="pass1" size="30"><br>
Uus parool 2x: <input type="password" name="pass2" size="30"><br>
<input type="submit" value="Vaheta parool">
<br>
{VAR:reforb}
</form>
