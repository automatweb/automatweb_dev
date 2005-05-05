<table border="0" cellspacing="1" cellpadding="2">
<form name=login method="POST" action="{VAR:baseurl}/index.{VAR:ext}">
<tr>
<td colspan="2">
<b>Selle ressursi kasutamiseks peate olema sisse logitud</b>
</td>
</tr>
							<!-- SUB: SERVER_PICKER -->
						  <tr> 
                            <td align="left" valign="top" width="123"><b>Server</b>:</td>
                            <td align="left" valign="top" width="123"><select name="server">{VAR:servers}</select></td>
                          </tr>
							<!-- END SUB: SERVER_PICKER -->
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
</td>
</tr>
<td colspan="2" align="center">
{VAR:reforb}
<input type="submit" value="Logi sisse">
<script language="Javascript">
	document.login.uid.focus();
</script>
</td>
</tr>
</form>
</table>
