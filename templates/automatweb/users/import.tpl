<form action='reforb.{VAR:ext}' method="POST" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="3000000">
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td bgcolor="#CCCCCC">
		<table border="0" cellspacing="1" cellpadding="2" width=100%>
			<tr>
				<td height="15" class="fgtext">&nbsp;Uploadi .csv fail:&nbsp;</td>
				<td height="15" class="fgtext">&nbsp;<input type="file" name="imp"></td>
			</tr>
			<tr>
				<td height="15" class="fgtext">&nbsp;Esimeses reas on tulpade pealkirjad?:&nbsp;</td>
				<td height="15" class="fgtext">&nbsp;<input type="checkbox" name="first_colheaders" value="1"></td>
			</tr>
			<tr>
				<td height="15" class="fgtext" colspan=2>&nbsp;<input class='small_button' type='submit' value='Impordi'>&nbsp;</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
{VAR:reforb}
</form>
Uploaditav fail peab sisaldama j&auml;rgmisi tulpi:<br>
1) kasutajanimi<br>
2) parool<br>
&uuml;lej&auml;&auml;nud ei ole kohustuslikud:<br>
3) nimi<br>
4) e-mail<br>
5) aktiivne kuni<br>
6) aktiivne alates<br>
