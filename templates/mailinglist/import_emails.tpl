<form action='reforb.{VAR:ext}' method=post ENCTYPE="multipart/form-data"> 
<INPUT TYPE="HIDDEN" name="MAX_FILE_SIZE" value="1000000">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td colspan="3" class="fcaption">{VAR:LC_MAILINGLIST_FILE}:</td><td class="fform"><input type='file' NAME='pilt'></td>
</tr>
<tr>
	<td colspan="2" class="fcaption">Vali muutujad</td>
	<td class="fcaption">J&auml;rjekord failis</td>
</tr>
<!-- SUB: V_LINE -->
<tr>
	<td class="fcaption">{VAR:var_name}</td>
	<td class="fcaption"><input type='checkbox' name='vars[{VAR:var_id}]' value='1'></td>
	<td class="fcaption"><input type='text' size='3' name='ord[{VAR:var_id}]' value='{VAR:ord}'></td>
</tr>
<!-- END SUB: V_LINE -->
<tr>
<td colspan="3" class="fcaption" colspan=2><input CLASS="small_button" type='submit' VALUE='{VAR:LC_MAILINGLIST_IMPORT}'></td>
</td>
{VAR:reforb}
</form>
