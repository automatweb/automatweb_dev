<form action = 'refcheck.{VAR:ext}' method=post >
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">ID</td>
<td class="fform">Nimi</td>
<td class="fform">J&auml;rjekord</td>
<td class="fform">&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fform">{VAR:num}</td>
<td class="fform"><input type="text" name="text_{VAR:num}" VALUE="{VAR:text}"></td>
<td class="fform"><input type="text" name="ord_{VAR:num}" VALUE="{VAR:ord}" size=2></td>
<td class="fform"><a href="nagu.{VAR:ext}?type=delete_ooc&nid={VAR:num}&id={VAR:id}">Kustuta</a></td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class="fcaption" colspan=4><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_nagu_ooc'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
