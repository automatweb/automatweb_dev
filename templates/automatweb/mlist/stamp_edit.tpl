<form action="reforb.aw" method="POST">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="ftitle2">Nimi (ilma #):</td></tr>
<tr>
<td class="fgtext"><input type="text" name="name" value="{VAR:name}"  class="small_button"></td>
</tr>

<tr>
<td class="ftitle2">Sisu:</td></tr>
<tr>
<td class="fgtext"><textarea name="content" cols=40 rows=15>{VAR:content}</textarea></td>
</tr>

<tr><td class="ftitle2"><input type="submit" value="salvesta" class="small_button"></td></tr>
</table>
{VAR:reforb}
</form>