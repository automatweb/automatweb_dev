<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_FORM_NAME}:</td><td class="fform"><input type='text' NAME='name'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORM_COMM}:</td><td class="fform"><textarea cols=50 rows=5 NAME=comment></textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORM_TYPE}:</td><td class="fform"><select  NAME=type><option VALUE='entry'>{VAR:LC_FORM_INPUT}
<option VALUE='search'>{VAR:LC_FORM_SEARCH}
<option VALUE='rating'>{VAR:LC_FORM_RESEARCH}
</select>
</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
{VAR:reforb}
</form>
