<form action='reforb.{VAR:ext}' method=post>
<font color="red">{VAR:error}</font>

<style>
.tablehead {
font-family: Verdana;
font-size: 10px;
font-weight: bold;
background-color: #00988F;
text-align: center;
}

.tablehead a {color: #FFFFFF; text-decoration:none;}
.tablehead a:hover {color: #FFFFFF; text-decoration:underline;}

.tabletext {
font-family: Verdana;
font-size: 10px;
background-color: #FFFFFF;
}
.tabletext a {color: #191F58; text-decoration:underline;}
.tabletext a:hover {color: #00988F; text-decoration:underline;}

.tabletext2 {
font-family: Verdana;
font-size: 10px;
background-color: #EFEFEF;
}
.tabletext2 a {color: #191F58; text-decoration:underline;}
.tabletext2 a:hover {color: #00988F; text-decoration:underline;}
</style>

<table bgcolor="#A5DAD8" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="tabletext">E-mail:</td>
<td class="tabletext"><input type="text" name="email" VALUE='{VAR:email}'></td>
</tr>
<tr>
<td class="tabletext">Parool:</td><td class="tabletext"><input type='password' NAME='pwd' ></td>
</tr>
<tr>
<td class="tabletext">Parool uuesti:</td><td class="tabletext"><input type='password' NAME='pwd2' ></td>
</tr>
<tr>
<td class="tabletext">Saada liitumismeil uuesti:</td><td class="tabletext"><input type="checkbox" name="send_welcome_mail" value="1"></td>
</tr>
<tr>
<td class="tabletext" colspan=2><input type="image" value="submit" src="/automatweb/images/formbutton_salvesta-andmed.gif" border="0"></td>
</tr>
</table>
{VAR:reforb}
</form>
