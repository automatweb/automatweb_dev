<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Kas salvestada formi andmed m6nda teise tabelisse:</td>
<td class="fform"><input type='checkbox' NAME='save_table' VALUE='1' {VAR:save_table}></td>
</tr>
<tr>
<td colspan=2 class="fform">Vali tabelid, millesse saab andmeid salvestada:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select class="small_button" name="tables[]" size=20 multiple>{VAR:tables}</select></td>
</tr>
<tr>
<td colspan="2" class="fform">Vali tabelite indeks-elemendid</td>
</tr>
<tr>
<td class="fform">Tabel</td>
<td class="fform">Element</td>
</tr>
<!-- SUB: TABLE -->
<tr>
<td class="fform">{VAR:table_name}</td>
<td class="fform"><select name="indexes[{VAR:table_name}]">{VAR:cols}</select></td>
</tr>
<!-- END SUB: TABLE -->
<tr>
<td class="fform" colspan=2><input type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
