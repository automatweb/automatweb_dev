<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Nimi:</td><td colspan=2 class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fform">Kommentaar:</td><td colspan=2 class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fform">Kas t&auml;idetakse &uuml;hekordselt:</td><td colspan=2 class="fform"><input type='checkbox' NAME='fillonce' VALUE='1' {VAR:fillonce}></td>
</tr>
<tr>
<td class="fform">Vali formid:</td><td colspan=2 class="fform"><select name='forms[]' multiple size=10>{VAR:forms}</select></td>
</tr>
<tr>
<td class="fform">Nimi</td><td class="fform">J&auml;rjekord</td><td class="fform">P&auml;rast t&auml;itmist mine edasi</td>
</tr>
<!-- SUB: FORM -->
<tr>
<td class="fform"><input type='text' name='fname[{VAR:form_id}]' value='{VAR:fname}' size=50 class='small_button'></td>
<td class="fform"><input type='text' name='fjrk[{VAR:form_id}]' value='{VAR:fjrk}' size=3 class='small_button'></td>
<td class="fform"><input type='checkbox' name='fgoto[{VAR:form_id}]' value='1' {VAR:fgoto} class='small_button'></td>
</tr>
<!-- END SUB: FORM -->
<tr>
<td class="fcaption" colspan=3><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
<tr>
<td class="fform" colspan=3><a href='{VAR:import}'>Impordi sisestusi</a></td>
</tr>
<tr>
<td class="fform" colspan=3><a href='{VAR:entries}'>Sisestused</a></td>
</tr>
</table>
{VAR:reforb}
</form>
