<form method="POST" action="reforb.{VAR:ext}" name="foo">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr><td class="fcaption2" colspan="2">
<input type="submit" value="Lisa" class="small_button">
<input type="button" value="Tagasi" class="small_button" OnClick="javascript:window.location='{VAR:backlink}'">
</td></tr>
<tr>
	<td class="fcaption2">Kes</td>
	<td class="fcaption2"><strong>{VAR:uid} @ {VAR:now}</strong></td>
</tr>
<tr>
	<td class="fcaption2">Mille kohta</td>
	<td class="fcaption2"><input type="text" name="url" size="30" value="{VAR:url}" Style="width:50%"  class="small_button">
	<select id="millekohta"  OnChange="foo.url.value=foo.millekohta[foo.millekohta.selectedIndex].value" Style="width:48%"  class="small_button">{VAR:millekohta}</select></td>
</tr>
<tr>
	<td class="fcaption2">Prioriteet: Pealkiri</td>
	<td class="fform"><select name="pri"  class="small_button">
	{VAR:prilist}
	</select>: <input type="text" name="title" Style="width:60%"  class="small_button"></td>
</tr>
<tr>
	<td class="fcaption2">Kellele</td>
	<td class="fform"><select name="developer[]" multiple class="small_button">{VAR:developerlist}</select></td>
</tr>
<tr>
	<td class="fcaption2">Tüüp / Tõsidus</td>
	<td class="fform">
	<!-- SUB: itypes -->
	<input type="radio" name="itype" value="{VAR:itype}"  OnClick="itypechg('{VAR:itype}');" {VAR:ischecked} class="small_button">{VAR:itypename}&nbsp;
	<!-- END SUB: itypes -->
	<input type="hidden" name="severity">
	<span id="severitylist0" valign="top"><select name="severity0" OnChange="foo.severity.value=foo.severity0.value;"  class="small_button">{VAR:severitylist0}</select></span>
	<span id="severitylist1" valign="top"><select name="severity1" OnChange="foo.severity.value=foo.severity1.value;" class="small_button">{VAR:severitylist1}</select></span>
	<span id="severitylist2" valign="top"><select name="severity2" OnChange="foo.severity.value=foo.severity2.value;" class="small_button">{VAR:severitylist2}</select></span>
	</td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Tekst</td>
	<td class="fform">
		<textarea name="text" cols="60" rows="10" class="small_button"></textarea>
	</td>
</tr>
<tr>
	<td class="fcaption2">Valmis ajaks</td>
	<td class="fform">{VAR:time_fixed}</td>
</tr>
<tr>
	<td class="fform" colspan="2">
	<input type="checkbox" name="maildev" checked>
	Saada bug ka Dev listi
	</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>
	 <input type='checkbox' NAME='sendmail2' value=1 CHECKED>Kas soovite teadet, kui bugi on parandatud? (saadetakse aadressile {VAR:sendmail2_mail})</td>
</tr>
</table>
{VAR:reforb}
<input type="submit" value="Lisa" class="small_button">
<input type="button" value="Tagasi" class="small_button" OnClick="javascript:window.location='{VAR:backlink}'">
<script language="JAVAscRIPt">
function n2ita(m,n)
{
if (n)
{
 if (document.all)
 {
  eval("document.all."+m+".style.display='';");
 } else
 {
  eval("document.all."+m+".style.display='';");
 };
} else
{
 if (document.all)
 {
  eval("document.all."+m+".style.display='none';")
 } else
 {
  eval("document.all."+m+".style.display='none';");
 };

};
};

function itypechg(seln)
{
for (i=0;i<{VAR:sizeofitypelist};i++)
{
	n2ita("severitylist"+i,0);
};
n2ita("severitylist"+seln,1);
};

itypechg(0);
</script>

</form>