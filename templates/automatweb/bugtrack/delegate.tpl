<form id="fr" action="reforb.{VAR:ext}" method="post">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr><td class="title"></td></tr>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width="100%">
<tr>
<td colspan="3" class="title">
Määra parandaja
</td></tr>
<tr><td class="ftitle2">Bug:</td><td class="fgtext">{VAR:id}: {VAR:title}</td></tr>
<tr><td class="ftitle2">Parandaja</td><td class="fgtext"><select class="small_button" name="developer[]" multiple>{VAR:userlist}</select></td></tr>

<tr><td class="ftitle2">Status:</td><td class="fgtext">
<select class="small_button" name="status" id="status">{VAR:statuslist}</select></td></tr>

<tr><td class="ftitle2" colspan="2" align="right">
<input class="small_button" type=submit value="määra"></td></tr>
</table>
</td></tr>
</table>
{VAR:reforb}
</form>
