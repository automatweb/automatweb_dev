<div class="{VAR:style_form_caption}">
Lisa teema
</div>
<form method="POST" action="{VAR:baseurl}/reforb.{VAR:ext}">
<table border="0">
<tr>
<td class="{VAR:style_form_text}">Autori nimi</td>
<td><input type="text" name="author_name"></td>
</tr>
<tr>
<td class="{VAR:style_form_text}">Teema</td>
<td><input type="text" name="name"></td>
</tr>
<tr>
<td colspan="2" class="{VAR:style_form_text}">Sisu</td>
</tr>
<tr>
<td colspan="2"><textarea name="comment" cols="40" rows="10"></textarea></td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Lisa teema"></td>
</tr>
{VAR:reforb}
</table>
</form>
