<form method="POST" action="reforb.{VAR:ext}" name="foo">

<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr><td class="fcaption2" colspan=2><a href="{VAR:backlink}">tagasi</a></td></tr>
<tr>
	<td class="fcaption2">Kes</td>
	<td class="fcaption2"><strong>{VAR:uid} @ {VAR:now}</strong></td>
</tr>
<tr>
	<td class="fcaption2">Mille kohta</td>
	<td class="fcaption2"><input type="text" name="url" size="30" value="{VAR:url}" Style="width:50%">
	<select id="millekohta"  OnChange="foo.url.value=foo.millekohta[foo.millekohta.selectedIndex].value" Style="width:48%">{VAR:millekohta}</select></td>
</tr>
<tr>
	<td class="fcaption2">Pealkiri : Prioriteet</td>
	<td class="fform"><input type="text" name="title" Style="width:60%">:<input type="text" name="pri" value="1"></td>
</tr>
<tr>
	<td class="fcaption2">Kellele</td>
	<td class="fform"><select name="developer[]" multiple>{VAR:developerlist}</select></td>
</tr>
<tr>
	<td class="fcaption2">Tüüp / Tõsidus</td>
	<td class="fform">
	<!-- SUB: itypes -->
	<input type="radio" name="itype" value="{VAR:itype}"  OnClick="itypechg('{VAR:itype}');">{VAR:itypename}&nbsp;
	<!-- END SUB: itypes -->
	<input type="hidden" name="severity">
	<span id="severitylist0" valign="top"><select name="severity0" OnChange="foo.severity.value=foo.severity0.value;">{VAR:severitylist0}</select></span>
	<span id="severitylist1" valign="top"><select name="severity1" OnChange="foo.severity.value=foo.severity1.value;">{VAR:severitylist1}</select></span>
	<span id="severitylist2" valign="top"><select name="severity2" OnChange="foo.severity.value=foo.severity2.value;">{VAR:severitylist2}</select></span>
	</td>
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
	<input type="checkbox" name="maildev" checked>
	Saada bug ka Dev listi
	</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>
	 <input type='checkbox' NAME='sendmail2' value=1 CHECKED>Kas soovite teadet, kui bugi on parandatud? (saadetakse aadressile {VAR:sendmail2_mail})</td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
	<input type="submit" value="Lisa">
	{VAR:reforb}
	</td>
</tr>
</table>
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