<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fgtext">Nimi:</td><td class="fform"><input type="text" NAME='name' SIZE=40 class='small_button' value="{VAR:name}"></td>
</tr>
<tr>
<td class="fgtext">Source tüüp</td>
<td class="fgtext"><select name="type">{VAR:types}</select>
</tr>
<tr>
<td class="fgtext">Andmete tüüp</td>
<td class="fgtext"><select name="datatype"><option>xml</option></select>
</tr>
{VAR:src}
<!-- SUB: localfile -->
<tr>
<td class="fgtext">Faili asukoht</td>
<td class="fgtext"><input type="text" name="fullpath" size="40" value="{VAR:fullpath}"></td>
</tr>
<!-- END SUB: localfile -->

<!-- SUB: http -->
<tr>
<td class="fgtext">Faili URL</td>
<td class="fgtext"><input type="text" name="url" size="40" value="{VAR:url}"></td>
</tr>
<!-- END SUB: http -->
<tr>
<td colspan="2" class="fgtext" align="center">
<input type="submit" value="Salvesta">
</td>
</tr>
</table>
{VAR:reforb}
</form>
