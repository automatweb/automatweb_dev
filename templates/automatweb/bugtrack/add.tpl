<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Kes</td>
	<td class="fcaption2"><strong>{VAR:uid} @ {VAR:now}</strong></td>
</tr>
<tr>
	<td class="fcaption2">Mille kohta</td>
	<td class="fcaption2"><strong><input type="text" name="url" size="30" value="{VAR:url}" ></strong></td>
</tr>
<tr>
	<td class="fcaption2">Pealkiri</td>
	<td class="fform"><input type="text" name="title" size="30"></td>
</tr>
<tr>
	<td class="fcaption2">Prioriteet</td>
	<td class="fform"><select name="pri">{VAR:prilist}</select></td>
</tr>
<tr>
	<td class="fcaption2">Kellele</td>
	<td class="fform"><select name="developer">{VAR:developerlist}</select></td>
</tr>
<tr>
	<td class="fcaption2">Tõsidus</td>
	<td class="fform"><select name="severity">{VAR:severitylist}</select></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Tekst</td>
	<td class="fform">
		<textarea name="text" cols="60" rows="10" wrap="soft"></textarea>
	</td>
</tr>
<tr>
	<td class="fcaption2">Valmis ajaks</td>
	<td class="fform">{VAR:time_fixed}</td>
</tr>
<tr>
	<td class="fform" colspan="2">
	Saada bug ka Dev listi? <input type="checkbox" name="maildev" checked>
	</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Kas soovite teadet, kui bugi on parandatud? (saadetakse aadressile {VAR:sendmail2_mail}) <input type='checkbox' NAME='sendmail2' value=1 CHECKED></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
	<input type="submit" value="Lisa">
	{VAR:reforb}
	</td>
</tr>
</table>
</form>