<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Baasklass</td>
<td class="fform">{VAR:baseclass}</td>
<tr>
<td class="fform">Name:</td><td class="fform"><input type='text' NAME='name' size="40" value="{VAR:name}"></td>
</tr>
<tr>
<td class="fform">Komment:</td><td class="fform"><input type='comment' NAME='comment' size="40" value="{VAR:comment}"></td>
</tr>
<!-- SUB: confline -->
<tr>
<td class="fform">{VAR:name}</td>
<td class="fform">{VAR:element}</td>
</tr>
<!-- END SUB: confline -->
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
{VAR:reforb}
</form>
