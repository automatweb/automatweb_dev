<form method="POST" action="reforb.{VAR:ext}" name='b88' enctype="multipart/form-data">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2" colspan=2>{VAR:image}</td>
</tr>
<tr>
	<td class="fcaption2">Nimi:</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Kommentaar:</td>
	<td class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Kuhu:</td>
</tr>
<tr>
	<td colspan=2 class="fform"><select name='parent'>{VAR:parent}</select></td>
</tr>
<tr>
	<td class="fcaption2">Asukoht:</td>
	<td class="fform"><select multiple name='grp[]'>{VAR:grp}</select></td>
</tr>
<tr>
	<td class="fcaption2">Klient:</td>
	<td class="fform"><select multiple name='buyer[]'>{VAR:buyer}</select></td>
</tr>
<tr>
	<td class="fcaption2">URL, kuhu suunatakse:</td>
	<td class="fform"><input type="text" name="url" value="{VAR:url}"></td>
</tr>
<tr>
	<td class="fcaption2">Aktiivne:</td>
	<td class="fform"><input type="checkbox" name="act" value="1" {VAR:act}></td>
</tr>
<tr>
	<td class="fcaption2">N&auml;itamise t&otilde;en&auml;osus:</td>
	<td class="fform"><input type="text" name="probability" size=2 value="{VAR:probability}">%&nbsp;&nbsp;(Kui k&otilde;ikidel &uuml;he kliendi banneritel on 0% t&otilde;en&auml;osus, siis n&auml;idatakse k&otilde;iki sama tihti, kuid kui m&otilde;nel on suurem kui null, siis neid, millel on 0% ignoreeritakse.</td>
</tr>
<!-- SUB: CHANGE -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:periods}'>Vali aktiivsuse perioodid</a></td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
	<td class="fcaption2">Mitu korda bannerit n&auml;idata:</td>
	<td class="fform"><input type='text' name='max_views' value='{VAR:max_views}' size=10></td>
</tr>
<tr>
	<td class="fcaption2">Mitu klikki banneril:</td>
	<td class="fform"><input type='text' name='max_clicks' value='{VAR:max_clicks}' size=10></td>
</tr>
<tr>
	<td class="fcaption2">Mitu korda bannerit n&auml;idata &uuml;hele kasutajale:</td>
	<td class="fform"><input type='text' name='max_views_user' value='{VAR:max_views_user}' size=10></td>
</tr>
<tr>
	<td class="fcaption2">Mitu klikki banneril &uuml;kas kasutaja teha v&otilde;ib:</td>
	<td class="fform"><input type='text' name='max_clicks_user' value='{VAR:max_clicks_user}' size=10></td>
</tr>
<tr>
	<td class="fcaption2">Profiilid:</td>
	<td class="fform"><select multiple name='profiles[]'>{VAR:profiles}</select></td>
</tr>
<tr>
	<td class="fcaption2">Pilt:</td>
	<td class="fform"><input type="hidden" name="MAX_FILE_SIZE" value="1000000"><input type="file" name="fail"></td>
</tr>
<tr>
	<td class="fcaption2">Pildi url:</td>
	<td class="fform"><input type="text" name="b_url" value="{VAR:b_url}"></td>
</tr>
<tr>
	<td class="fcaption2">Flash:</td>
	<td class="fform"><input type="file" name="flash"></td>
</tr>
<tr>
	<td class="fcaption2">HTML:</td>
	<td class="fform"><textarea name="html">{VAR:html}</textarea></td>
</tr>
<tr>
	<td class="fcaption2">N&auml;itamisi:</td>
	<td class="fform">{VAR:views}</td>
</tr>
<tr>
	<td class="fcaption2">Klikke:</td>
	<td class="fform">{VAR:clics}</td>
</tr>
<tr>
	<td class="fcaption2">Click-through ratio:</td>
	<td class="fform">{VAR:ctr}%</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:stats}'>Detailne statistika</a></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="Salvesta">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
