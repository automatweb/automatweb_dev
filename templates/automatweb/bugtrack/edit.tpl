<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
<td class="fcaption2" colspan="2">
<input type="submit" value="Salvesta" class="small_button">
<input type="button" value="Tagasi" class="small_button" OnClick="javascript:window.location='{VAR:backlink}'">
</td>
</tr>
<tr>
	<td class="fcaption2">Kes</td>
	<td class="fcaption2"><strong>{VAR:uid} @ {VAR:now}</strong></td>
</tr>
<tr>
	<td class="fcaption2">Mille kohta</td>
	<td class="fcaption2"><strong>{VAR:url}</strong></td>
</tr>
<tr>
	<td class="fcaption2">Prioriteet: Pealkiri</td>
	<td class="fcaption2"><select name="pri"  class="small_button">
	{VAR:prilist}
	</select>
	<input type="text" name="title" value="{VAR:title}" size="60" class="small_button"></td>
</tr>
<tr>
	<td class="fcaption2">Staatus</td>
	<td class="fform">
		<select name="status" class="small_button">
			{VAR:statuslist}
		</select>
	</td>
</tr>
<tr>
	<td class="fcaption2">Kellele</td>
	<td class="fform">
		<select name="developer[]" multiple class="small_button">
			{VAR:developerlist}
		</select></td>
</tr>
<tr>
	<td class="fcaption2">Tõsidus</td>
	<td class="fform"><select name="severity" class="small_button">{VAR:severitylist}</select></td>
</tr>
<tr>
	<td class="fcaption2">Valmis ajaks</td>
	<td class="fform">{VAR:time_fixed}</td>
</tr>
<tr>
	
	<td class="fcaption2" colspan=2><input type='checkbox' NAME='sendmail2' value=1 {VAR:sendmail2} class="small_button">Kas soovite ülesande täitmisest teadet aadressile "{VAR:sendmail2_mail}" 
	 </td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Lisa aadress(e) kuhu tahad teate saada (eralda komaga): 
	<INPUT TYPE="text" NAME="mails" Value="{VAR:mails}" SIZE=40 class="small_button"></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Tekst</td>
	<td class="fcaption2">
	<!-- SUB: text -->
	<textarea name="text" cols="60" rows="10" class="small_button">{VAR:txt}</textarea>
	<input type="hidden" name="savetext" value="1">
	<!-- END SUB: text -->
	</td>
</tr>
<tr>
	<td class="fcaption2">Järeldus</td>
	<td class="fform"><select name="resol" class="small_button">{VAR:resollist}</select></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Parandaja märkus:</td>
	<td class="fcaption2"><textarea name="text_result" cols="60" rows="10" class="small_button">{VAR:text_result}</textarea>
	</td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Kommentaarid:</td>
	<td class="fcaption2"> <a href='comments.aw?section=bug_{VAR:id}'>Kommenteeri siin</a>
	</td>
</tr>
<tr>
	<td class="fform"  colspan="2">
	<input type="submit" value="Salvesta" class="small_button">
	<input type="button" value="Tagasi" class="small_button" OnClick="javascript:window.location='{VAR:backlink}'">

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
