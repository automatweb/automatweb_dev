<table border="0" width="100%">
<tr>
<td class="{VAR:style_caption}">
<small>
<strong>{VAR:path}</strong>
</small>
</td>
<td nowrap class="{VAR:style_caption}">
<!-- SUB: active_page -->
 <strong>[ {VAR:num} ]</strong>
<!-- END SUB: active_page -->
<!-- SUB: page -->
 <a href="{VAR:url}">{VAR:num}</a> 
<!-- END SUB: page -->
</td>
</tr>
<tr>
<td colspan="2" class="{VAR:style_caption}">
<strong>{VAR:name}</strong><br>
<small>{VAR:comment}</small>
</td>
</tr>
</table>
<table border="1" cellspacing="0" cellpadding="3" width="100%" style="border-collapse: collapse;">
<!-- SUB: COMMENT -->
<tr>
	<td align="center" width="20%" class="{VAR:style_comment_user}">{VAR:createdby}<br>{VAR:date}</td>
	<td valign="top" class="{VAR:style_comment_text}"><strong>{VAR:name}</strong><p><small>{VAR:commtext}</small></td>
</tr>
<!-- END SUB: COMMENT -->
</table>
<hr width="100%">
Lisa kommentaar
<form method="POST" action="{VAR:baseurl}/reforb.{VAR:ext}">
<table border="0">
<tr>
<td>Pealkiri</td>
<td><input type="text" name="name"></td>
</tr>
<tr>
<td>Sinu nimi</td>
<td><input type="text" name="uname"></td>
</tr>
<tr>
<td colspan="2">
Kommentaar:<br>
<textarea name="commtext" cols="40" rows="10"></textarea>
</td>
</tr>
<tr>
<td colspan="2">
<input type="submit" value="Lisa kommentaar">
</td>
</tr>
</table>
{VAR:reforb}
</form>


</table>
</form>

