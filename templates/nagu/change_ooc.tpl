<form action = 'refcheck.{VAR:ext}' method=post >
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">ID</td>
<td class="fform">{VAR:LC_NAGU_NAME}</td>
<td class="fform">{VAR:LC_NAGU_ORDER}</td>
<td class="fform">&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fform">{VAR:num}</td>
<td class="fform"><input type="text" name="text_{VAR:num}" VALUE="{VAR:text}"></td>
<td class="fform"><input type="text" name="ord_{VAR:num}" VALUE="{VAR:ord}" size=2></td>
<td class="fform"><a href="nagu.{VAR:ext}?type=delete_ooc&nid={VAR:num}&id={VAR:id}">{VAR:LC_NAGU_DELETE}</a></td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class="fcaption" colspan=4><input class='small_button' type='submit' VALUE='{VAR:LC_NAGU_SAVE}'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_nagu_ooc'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
