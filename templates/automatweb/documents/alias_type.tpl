<br>
<form ACTION='refcheck.{VAR:ext}' METHOD=POST>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="fcaption" colspan=2>{VAR:LC_DOCUMENT_CHOOSE_FORM_ENTRY}:</td>
</tr>
<td class="fform" ><input type="radio" NAME='type' value="change" CHECKED></td>
<td class="fcaption">{VAR:LC_DOCUMENT_EDIT_INPUT}</td>
</tr>
</tr>
<td class="fform" ><input type="radio" NAME='type' value="show"></td>
<td class="fcaption">{VAR:LC_DOCUMENT_ENTRY_OUTPUT_STYLE}:&nbsp;<select name=output>{VAR:op_sel}</select></td>
</tr>
<tr>
<td class="fform" colspan="2">
<input type="submit" class="small_button" value="{VAR:LC_DOCUMENT_SAVE}">
</td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='alias_type'>
<input type='hidden' NAME='docid' VALUE='{VAR:docid}'>
<input type='hidden' NAME='alias' VALUE='{VAR:alias}'>
<input type='hidden' NAME='form_id' VALUE='{VAR:form_id}'>
</form>
