 <form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fgtext">Vali vorm, kust meiliaadresse võtta:</td>
<td class="fform"><select name="srcform">{VAR:srcforms}</select></td>
</tr>
<tr>
<td class="fgtext">Vormi element, milles meiliaadress(id) asuvad:</td>
<td class="fform"><select name="srcfield">{VAR:srcfields}</select></td>
</tr>
<tr>
<td class="fgtext">Millist väljundit kasutada:</td>
<td class="fform"><select name="output">{VAR:outputs}</select></td>
</tr>
<tr>
<td class="fgtext">Bind to submit button:</td>
<td class="fform"><select name="sbt_bind">{VAR:sbt_binds}</select></td>
</tr>
<tr>
<td class="fgtext">Kirja subjekt:</td>
<td class="fform"><input type="text" name="subject" value="{VAR:subject}" size="30"></td>
</tr>
<tr>
<td class="fgtext">From:</td>
<td class="fform"><input type="text" name="from" value="{VAR:from}" size="30"></td>
</tr>
<tr>
<td class="fgtext" colspan=2><input type='submit' NAME='save_form_actions' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
