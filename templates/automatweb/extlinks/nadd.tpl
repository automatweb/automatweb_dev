<script language="javascript">
function setLink(li,title)
{
	document.b88.url.value=li;
}
</script>

<form method="POST" action="reforb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">URL</td>
	<td class="fform"><input type="text" name="url" size="40" value='{VAR:url}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><input type='hidden' name='type' value='ext'><a href="javascript:remote('no',500,400,'{VAR:search_doc}')">Saidi sisene link</a></td>
</tr>
<tr>
	<td class="fcaption2">Nimetus</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Kommentaar lingikogusse</td>
</tr>
<tr>
	<td colspan=2 class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2">Uues aknas</td>
	<td class="fform"><input type="checkbox" name="newwindow" value=1 {VAR:newwindow}></td>
</tr>
<tr>
	<td class="fcaption2">Dokumendi lingikogus?</td>
	<td class="fform"><input type="checkbox" name="doclinkcollection" value=1 {VAR:doclinkcollection}></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali kataloog, kuhu link salvestatakse</td>
</tr>
<tr>
	<td class="fform" colspan=2><select class='small_button' name='parent'>{VAR:parent}</select></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="Lisa link">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
