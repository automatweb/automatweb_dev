<form method="POST" action="reforb.{VAR:ext}" name="foo">
{VAR:header}
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
	<td class="fcaption2">T&uuml;&uuml;p:</td>
	<td class="fcaption2">{VAR:BUG_TYPE}</td>
</tr>
<tr>
	<td class="fcaption2">Mille kohta</td>
	<td class="fcaption2">{VAR:url}</td>
</tr>
<tr>
	<td class="fcaption2">Prioriteet:</td>
	<td class="fform">{VAR:priority}</td>
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
	<td class="fform">{VAR:severity}</td>
</tr>
<tr>
	<td class="fcaption2">Valmis ajaks</td>
	<td class="fform">{VAR:time_fixed}</td>
</tr>
<tr>
	<td class="fcaption2">Mitu tundi kulub:</td>
	<td class="fform"><input size="4" type='text' name='hours' value='{VAR:hours}' class="small_button"></td>
</tr>
<tr>
	<td class="fcaption2">Percent completed:</td>
	<td class="fform"><input size="4" type='text' name='percent' value='{VAR:percent}' class="small_button"></td>
</tr>
<tr>
	<td class="fcaption2">Staatus:</td>
	<td class="fform"><select class="small_button" name='status'>{VAR:statuses}</select></td>
</tr>
<tr>
	<td class="fcaption2">Pealkiri: </td>
	<td class="fcaption2">{VAR:title}</td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Tekst:</td>
	<td class="fcaption2">{VAR:m_text}</td>
</tr>
<!-- SUB: COMMENT -->
<tr>
	<td class="fcaption2">{VAR:m_uid} @ {VAR:m_date}</td>
	<td class="fcaption2">{VAR:m_text}</td>
</tr>
<!-- END SUB: COMMENT -->
<tr>
	<td class="fcaption2" valign="top">Kommenteeri:</td>
	<td class="fform"><textarea name="text" cols="120" rows="10" class="small_button"></textarea>
	</td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Attachment:</td>
	<td class="fform"><input type="file" name="attach" class="small_button"></td>
</tr>
<tr>
<td class="fcaption2" colspan="2">
<input type="submit" value="Salvesta" class="small_button">
<input type="button" value="Tagasi" class="small_button" OnClick="javascript:window.location='{VAR:backlink}'">
</td>
</tr>
</table>
{VAR:reforb}
</form>