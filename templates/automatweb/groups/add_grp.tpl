<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Name:</td><td class="fform"><input type='text' NAME='name' ></td>
</tr>
<tr>
<td class="fform" colspan=2>Type:</td>
</tr>
<tr>
<td class="fform" colspan=2><input type='radio' name='type' value=0>Normal group</td>
</tr>
<tr>
<td class="fform" colspan=2><input type='radio' name='type' value=2>Dyn. Group</td>
</tr>
<tr>
<td class="fform">Search form for dyn. group:</td><td class="fform"><select name='search_form'>{VAR:search_forms}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
{VAR:reforb}
</form>
