<form method="POST">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">
		Nimetus
	</td>
	<td class="fform">
		<input type="text" name="name" size="40">
	</td>
</tr>
<tr>
	<td class="fcaption2">
		URL
	</td>
	<td class="fform">
		<input type="text" name="url" size="40">
	</td>
</tr>
<tr>
	<td class="fcaption2">
		Kirjeldus
	</td>
	<td class="fform">
		<input type="text" name="desc" size="40">
	</td>
</tr>
<tr>
	<td class="fcaption2">Uues aknas</td>
	<td class="fform"><input type="checkbox" name="newwindow" value=1></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="Lisa link">
		<input type="hidden" name="docid" value="{VAR:docid}">
		<input type="hidden" name="op" value="addlink">
	</td>
</tr>
</table>
</form>
