<script language="javascript">
function setLink(li,title)
{
	document.b88.url.value=li;
	document.b88.elements[2].value=title; // nime element
}

function toggle_javascript()
{
	if (document.b88.use_javascript.checked)
	{
		with (document.b88)
		{
			newwinwidth.disabled = false;
			newwinheight.disabled = false;
			newwintoolbar.disabled = false;
		};
	}
	else
	{
		with (document.b88)
		{
			newwinwidth.disabled = true;
			newwinheight.disabled = true;
			newwintoolbar.disabled = true;
		};
	};
};
</script>
<form method="POST" action="reforb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">URL</td>
	<td class="fform"><input type="text" name="url" size="40" value='{VAR:url}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><input type='hidden' name='type' value='ext'><a href="javascript:remote('no',500,400,'{VAR:search_doc}')">{VAR:LC_EXTLINKS_INSIDE_WEB}</a></td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_EXTLINKS_NAME}</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_EXTLINKS_COMMENT_TO_LC}</td>
</tr>					
<tr>
	<td colspan=2 class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_EXTLINKS_NEW_WINDOW}</td>
	<td class="fform"><input type="checkbox" name="newwindow" value=1 {VAR:newwindow}></td>
</tr>
<tr>
	<td class="fcaption2">Kasutada lingi loomisel Javascripti?</td>
	<td class="fform"><input type="checkbox" name="use_javascript" value=1 onClick="toggle_javascript()" {VAR:use_javascript}></td>
</tr>
<tr>
	<td class="fcaption2">Uue akna mõõtmed</td>
	<td class="fform"><input type="text" name="newwinwidth" value="{VAR:newwinwidth}" size="4"> x 
	<input type="text" name="newwinheight" value="{VAR:newwinheight}" size="4"></td>
</tr>
<tr>
	<td class="fcaption2">Uue akna sätungid</td>
	<td class="fform">
	Toolbar: <input type="checkbox" name="newwintoolbar" value=1 {VAR:newwintoolbar}> |
	Address bar: <input type="checkbox" name="newwinlocation" value=1 {VAR:newwinlocation}> |
	Menyyd: <input type="checkbox" name="newwinmenu" value=1 {VAR:newwinmenu}> 

</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_EXTLINKS_DOC_LC}?</td>
	<td class="fform"><input type="checkbox" name="doclinkcollection" value=1 {VAR:doclinkcollection}></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_EXTLINKS_CHOOSE_CATALOGUE}</td>
</tr>
<tr>
	<td class="fform" colspan=2><select class='small_button' name='parent'>{VAR:parent}</select></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="{VAR:LC_EXTLINKS_ADD} {VAR:LC_EXTLINKS_LINK}">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
<script language="JavaScript">
toggle_javascript();
</script>
