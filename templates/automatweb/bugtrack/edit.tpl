<form method="POST" action="reforb.{VAR:ext}">
<a href="{VAR:backlink}">tagasi</a>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Kes</td>
	<td class="fcaption2"><strong>{VAR:uid} @ {VAR:now}</strong></td>
</tr>
<tr>
	<td class="fcaption2">Mille kohta</td>
	<td class="fcaption2"><strong>{VAR:url}</strong></td>
</tr>
<tr>
	<td class="fcaption2">Pealkiri</td>
	<td class="fcaption2">{VAR:title}</td>
</tr>
<tr>
	<td class="fcaption2">Prioriteet</td>
	<td class="fform">
		<select name="pri">
			{VAR:prilist}
		</select>
         </td>
</tr>
<tr>
	<td class="fcaption2">Staatus</td>
	<td class="fform">
		<select name="status">
			{VAR:statuslist}
		</select>
	</td>
</tr>
<tr>
	<td class="fcaption2">Kellele</td>
	<td class="fform">
		<select name="developer">
			{VAR:developerlist}
		</select></td>
</tr>
<tr>
	<td class="fcaption2">Tõsidus</td>
	<td class="fform"><select name="severity">{VAR:severitylist}</select></td>
</tr>
<tr>
	<td class="fcaption2">Valmis ajaks</td>
	<td class="fform">{VAR:time_fixed}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Kas soovite teadet emailiga, kui bugi on parandatud? Saadetakse aadressile "{VAR:sendmail2_mail}" 
	<input type='checkbox' NAME='sendmail2' value=1 {VAR:sendmail2}> </td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Lisa aadress(e) kuhu tahad teate saada (eralda komaga): 
	<INPUT TYPE="text" NAME="mails" Value="{VAR:mails}" SIZE=40></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Tekst</td>
	<td class="fcaption2">{VAR:text}
	</td>
</tr>
<tr>
	<td class="fcaption2">Järeldus</td>
	<td class="fform"><select name="resol">{VAR:resollist}</select></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Parandaja märkus:</td>
	<td class="fcaption2"><textarea name="text_result" cols="60" rows="10" wrap="soft">{VAR:text_result}</textarea>
	</td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Kommentaarid:</td>
	<td class="fcaption2"> <a href='comments.aw?section=bug_{VAR:id}'>Kommenteeri siin</a>
	</td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
	<a href="{VAR:backlink}">tagasi</a>
	<input type="submit" value="Salvesta">
	{VAR:reforb}
	</td>
</tr>
<tr>
<td width="100%" colspan="2">
<iframe src="{VAR:iframesrc}" Style="width:100%;height:500">
</td>
</tr>
</table>
</form>
