<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<form name="edit" method="POST">
<tr>
<td colspan="2" class="fgtitle"><b>Editing {VAR:file}
|
<a href="#">Eelvaade</a>
|
<a href="{VAR:arclink}">Arhiiv</a>
|
<a href="javascript:document.edit.submit()"><font color="red">Salvesta</font>
</b>
</td>
</tr>
<tr>
<td class="fgtext">Nimi</td>
<td class="fgtext"><input type="text" name="name" size="40" value="{VAR:name}">
</tr>
<tr>
<td class="fgtext">Kommentaar</td>
<td class="fgtext"><input type="text" name="comment" size="40" value="{VAR:comment}">
</tr>
<tr>
<td class="fgtext">M‰‰rangud</td>
<td class="fgtext">Arhiiv: <input type="checkbox" name="archive" value="1" {VAR:archive}>
</tr>
<tr>
<td class="fgtext" colspan="2">
<textarea name="source" cols="100" rows="60">
{VAR:source}
</textarea>
</td>
<!--
<td valign="top" class="fgtext">
<IFRAME src="{VAR:rawlink}" width="400" height="500"
              scrolling="auto" frameborder="1">
</IFRAME>
</td>
-->
</tr>
{VAR:reforb}
</form>
</table>
