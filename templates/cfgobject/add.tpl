<form name="clform" method="POST" action="reforb.{VAR:ext}">
{VAR:toolbar}
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td class="fgtext">Nimi</td>
<td class="fgtext"><input type="text" name="name" size="40" value="{VAR:name}">
</td>
</tr>
<tr>
<td class="fgtext">Kommentaar</td>
<td class="fgtext"><input type="text" name="comment" size="40" value="{VAR:comment}">
</td>
</tr>
<tr>
<td class="fgtext">Konfivorm</td>
<td class="fgtext"><select name="cfgform">{VAR:cfgforms}</select></td>
</tr>
</table>
{VAR:reforb}
</form>
