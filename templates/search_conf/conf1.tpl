<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Vali rubriigid:</td><td class="fform"><SELECT NAME='section[]' SIZE=20 MULTIPLE>{VAR:section}</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='Salvesta' CLASS="small_button"></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='admin_search_conf'>
<input type='hidden' NAME='level' VALUE='0'>
</form>
