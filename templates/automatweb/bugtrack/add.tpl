<form method="POST" action="reforb.{VAR:ext}" name="foo">
{VAR:header}
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
<td class="fcaption2" colspan="2">
<input type="submit" value="Lisa" class="small_button">
<input type="button" value="Tagasi" class="small_button" OnClick="javascript:window.location='{VAR:backlink}'">
</td>
</tr>
<tr>
	<td class="fcaption2">Kes</td>
	<td class="fcaption2"><strong>{VAR:uid} @ {VAR:now}</strong></td>
</tr>
<tr>
	<td class="fcaption2">T&uuml;&uuml;p:</td>
	<td class="fcaption2">
		<!-- SUB: BUG_TYPE -->
		{VAR:name} <input type="radio" name="bug_type" value="{VAR:val}" {VAR:first}>
		<!-- END SUB: BUG_TYPE -->
	</td>
</tr>
<tr>
	<td class="fcaption2">Mille kohta</td>
	<td class="fcaption2"><input type="text" name="url" size="30" value="{VAR:url}" Style="width:50%"  class="small_button">
	<select id="millekohta"  OnChange="foo.url.value=foo.millekohta[foo.millekohta.selectedIndex].value" Style="width:48%"  class="small_button">{VAR:millekohta}</select></td>
</tr>
<tr>
	<td class="fcaption2">Prioriteet:</td>
	<td class="fform"><input type="text" name="pri" class="small_button" size="4"></td>
</tr>
<tr>
	<td class="fcaption2">Kellele</td>
	<td class="fform"><select name="developer[]" class="small_button">{VAR:developerlist}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Lisa aadress(e) kuhu tahad lisaks teate saada (eralda komaga): 
	<INPUT TYPE="text" NAME="mails" Value="{VAR:mails}" SIZE=40 class="small_button"></td>
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
	<td class="fcaption2">Pealkiri: </td>
	<td class="fcaption2"><input type="text" name="title" Style="width:60%"  class="small_button"></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Tekst</td>
	<td class="fform"><textarea name="text" cols="60" rows="10" class="small_button"></textarea>
	</td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Attachment:</td>
	<td class="fform"><input type="file" name="attach" class="small_button"></td>
</tr>
<tr>
<td class="fcaption2" colspan="2">
<input type="submit" value="Lisa" class="small_button">
<input type="button" value="Tagasi" class="small_button" OnClick="javascript:window.location='{VAR:backlink}'">
</td>
</tr>
</table>
{VAR:reforb}
</form>