<form action="reforb.aw" method="POST" name="foo">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>

<tr>
<td class="fgtitle">Nimi:</td>
<td class="fgtext" colspan="2"><input type="text" name="name" class="small_button" value="{VAR:name}" Style="width:100%"></td>
</tr>

<tr><td class="ftitle2" colspan="3">Eeldused:</td></tr>

<tr>
<td class="fgtitle"><input type="checkbox" name="tm_inlist" {VAR:tm_inlist}>Listis:</td>
<td class="fgtext" colspan="2"><select name="t_inlist" class="small_button">{VAR:t_inlist}</select></td>
</tr>

<tr>
<td class="fgtitle"><input type="checkbox" name="tm_mailsent" {VAR:tm_mailsent}>Saadetud meil:</td>
<td class="fgtext" colspan="2"><input type="text" name="t_mailsent" class="small_button" value="{VAR:t_mailsent}">
<input type="button" name="böö!" class="small_button" Value="vali" OnClick="JavaScript:remote(0,700,600,'{VAR:l_vali}&el=t_mailsent')"></td>
</tr>

<tr>
<td class="fgtitle"><input type="checkbox" name="tm_mailsubj" {VAR:tm_mailsubj}>Subjekt:</td>
<td class="fgtext" colspan="2"><input name="t_mailsubj" type="text" class="small_button" Style="width:100%" value="{VAR:t_mailsubj}"></td>
</tr>

<tr>
<td class="fgtitle"><input type="checkbox" name="tm_mailsentat" {VAR:tm_mailsentat}>Saatmise ajavahemik:</td>
<td class="fgtext" colspan="2">{VAR:t_mailsentat}&nbsp;<br>{VAR:t_mailsentat2}</td>
</tr>

<tr>
<td class="fgtitle"><input type="checkbox" name="tm_usedvars" {VAR:tm_usedvars}>Kasutatud muutujad:</td>
<td class="fgtext" colspan="2"><select name="t_usedvars[]" multiple Style="width:100%" >{VAR:t_usedvars}</select></td>
</tr>

<tr>
<td class="fgtitle"><input type="checkbox" name="tm_entry" {VAR:tm_entry}>Liikme andmed:</td>
<td class="fgtext" colspan="2">{VAR:formparse}</td>
</tr>

<tr><td class="ftitle2" colspan="3">Tegevus:</td></tr>

<tr>
<td class="fgtitle" colspan="3"><input type="checkbox" name="dynamic" {VAR:dynamic}>Dünaamiline</td>
</tr>


<tr>
<td class="fgtitle"><input type="radio" name="actionx" value="addlist" {VAR:a_addlist}>Lisa listi</td>
<td class="fgtext" colspan="2"><select name="addlist" class="small_button">{VAR:addlist}</select></td>
</tr>

<tr>
<td class="fgtitle"><input type="radio" name="actionx" value="dellist" {VAR:a_dellist}>Eemalda listist</td>
<td class="fgtext" colspan="2"><select name="dellist" class="small_button">{VAR:dellist}</select></td>
</tr>

<tr>
<td class="fgtitle"><input type="radio" name="actionx" value="dontsend" {VAR:a_dontsend}>Ära saada meili</td>
<td class="fgtext" colspan="2"><input type="text" name="dontsend" class="small_button" value="{VAR:dontsend}">
<input type="button" name="böö!" class="small_button" Value="vali" OnClick="JavaScript:remote(0,700,600,'{VAR:l_vali}&el=dontsend')"></td>
</tr>

<tr>
<td class="fgtitle" colspan="3"><input type="radio" name="actionx" value="delete" {VAR:a_delete}>Kustuta</td>
</tr>

<tr>
<td class="fgtitle" colspan="3" align="right">
<input type="button" value="Otsi" class="small_button" OnClick="javascript:DoTheThing('search')">
<input type="submit" value="Salvesta" class="small_button">
</td>
</tr>
{VAR:reforb}


<script language="javascript">
function DoTheThing(aa)
{
	document.foo.subaction.value=aa;
	document.foo.submit();
};
</script>

<tr>
<td class="ftitle2" colspan="2">
Otsing:</td>
<td class="ftitle2" align="right">
<!-- SUB: taida -->
<input type="button" value="TÄIDA" class="small_button" Style="align:right;" OnClick="javascript:DoTheThing('execute')">
<!-- END SUB: taida -->
</td></tr>
<tr>
<td class="title"><a name="searcha"></a><a>#</a></td>
<td class="title"><a>Nimi</a></td>
<td class="title"><a>Vali</a></td>
</tr>

<!-- SUB: rida -->
<tr>
<td class="fgtext">{VAR:id}</td>
<td class="fgtext">{VAR:name}</td>
<td class="fgtext"><input type="checkbox" name="mids[]" value="{VAR:id}"></td>
</tr>
<!-- END SUB: rida -->
</table>
</form>