<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td><td class="fform"><textarea cols=50 rows=5 NAME=comment></textarea></td>
</tr>
<tr>
<td class="fcaption">T&uuml;&uuml;p:</td><td class="fform"><select  NAME=type><option VALUE='entry'>Sisestus
<option VALUE='search'>Otsingu
<option VALUE='rating'>Reitimis
</select>
</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
{VAR:reforb}
</form>
