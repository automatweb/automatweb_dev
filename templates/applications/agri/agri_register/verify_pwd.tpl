<form action="{VAR:baseurl}/reforb.aw" method="POST">
<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="text">
			{VAR:utype} <br>
			&auml;riregistrikood: {VAR:regcode} <br>
			Sisestage palun oma parool: <input type="password" name="pass" size="12"> 
		</td>
	</tr>
	<tr>
		<td span class="text">
			<input class='aw04formbutton' type="submit" value="Sisene">
		</td>
	</tr>
</table>
{VAR:reforb}
</form>