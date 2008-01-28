<center>

<style>
.logintable {
	font-family:  Arial, sans-serif;
	font-size: 11px;
	color: #000000;
	text-decoration: none;
	background-color: #ffffff;
	border: 0px;
}
.logintable a {
	color: #0467a0;
	text-decoration:none;
	font-size: 11px;
}
.logintable a:hover {
	color: #0075B8;
	text-decoration:underline;
	font-size: 11px;
}
.logintable .loginbt {
	font-family:  Verdana, Arial, sans-serif;
	font-size: 11px;
	font-weight: bold;
	color: #FFFFFF;
	text-decoration: none;
	background: #0075B8;
	border-color: #79B3D5;
}
.logintable .note {
	color: #c3162f;
	font-weight: bold;
	text-align: center;
	padding-bottom: 13px;
	font-size: 13px;
}
.logintable .caption {
	font-weight: bold;
	text-align: right;
}
.logintable .element {
	font-weight: bold;
	text-align: left;
}
.logintable .lingid {
	font-size: 11px;
	font-weight: bold;
	text-align: right;
}
.logintable .logo {
	text-align: left;
	padding-bottom: 23px;
}
.logintable .footer {
	text-align: center;
	border-top: 2px solid #0075B8;
	padding-top: 7px;
}
.logintable .textbox {
	background-color: #FFFFFF;
	font-family:  Verdana, Arial, sans-serif;
	border: 1px solid #0075B8;
	padding: 2px 5px 2px 5px;
	margin: 0 0 0 0;
	width: 250px;
}
.logintable .select {
	background-color: #FFFFFF;
	font-family:  Verdana, Arial, sans-serif;
	font-size: 11px;
	border: 1px solid #0075B8;
	margin: 0 0 0 0;
}
</style>

<table cellspacing="0" cellpadding="13" style="border: 2px solid #0075B8; margin-top: 50px;">
<tr><td>

<table border="0" cellspacing="1" cellpadding="2" class="logintable">
<form name=login method="POST" action="{VAR:baseurl}/reforb.{VAR:ext}">
	<tr>
		<td colspan="2" class="logo">
			<img src='http://www.struktuur.ee/img/aw_logo.gif' border='0'>
		</td>
	</tr>

	<tr>
		<td colspan="2" class="note">
			<b>Selle ressursi kasutamiseks peate olema sisse logitud!</b>
		</td>
	</tr>
	
	<!-- SUB: SERVER_PICKER -->
	<tr> 
                            <td  class="caption">Server:</td>
                            <td class="element"><select name="server" class="select">{VAR:servers}</select></td>
	</tr>
	<!-- END SUB: SERVER_PICKER -->

	<tr>
		<td class="caption">
			Kasutajanimi:
		</td>
		<td class="element">
			<input type="text" name="uid" size="40" class="textbox">
		</td>
	</tr>

	<tr>
		<td class="caption">
			Parool:
		</td>
		<td class="element">
			<input type="password" name="password" size="40" class="textbox">
		</td>
	</tr>

	<tr>
		<td class="caption">
		</td>
		<td align="left">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td  width="50%">
						{VAR:reforb}
						<input type="submit" value="Sisene" class="loginbt">
						<script language="Javascript">
							document.login.uid.focus();
						</script>
					</td>
					<td class="lingid" width="50%" align="right">
						<a href="{VAR:baseurl}?class=users&action=send_hash">Unustasid parooli?</a><br>
						<a target="new" href="http://support.automatweb.com">Abikeskkond</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td height="43">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td class="footer" colspan="2">
			O� Struktuur Meedia<br>Aadress: P�rnu mnt. 154, 11317, Tallinn<br>Infotelefon: 655 8336<br>E-mail: <a href="mailto:info@struktuur.ee">info@struktuur.ee</a>
		</td>
	</tr>

</form>
</table>

</td></tr></table>
</center>
