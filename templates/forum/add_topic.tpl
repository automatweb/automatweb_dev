<form method="POST" action="{VAR:baseurl}/reforb.{VAR:ext}">
<table border="0" width="100%">
<tr>
<td colspan="2" class="{VAR:style_form_caption}">Lisa teema</td>
</tr>
<tr>
<td class="{VAR:style_form_text}" nowrap width="16%">Autori nimi:</td>
<td><input type="text" name="author_name" class="{VAR:style_form_element}"></td>
</tr>
<tr>
<td class="{VAR:style_form_text}" nowrap width="16%">Teema:</td>
<td><input type="text" name="name" class="{VAR:style_form_element}"></td>
</tr>
<tr>
<td colspan="2" class="{VAR:style_form_text}">Sisu</td>
</tr>
<tr>
<td colspan="2"><textarea name="comment" cols="40" rows="10" class="{VAR:style_form_element}"></textarea></td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Lisa teema"></td>
</tr>
{VAR:reforb}
</table>
</form>
