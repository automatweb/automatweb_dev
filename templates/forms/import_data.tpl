<br>
<form action=refcheck.{VAR:ext} method=post enctype='multipart/form-data'><input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr><td class="title" colspan=2>{VAR:LC_FORMS_UPLOAD_FILE}</td></tr>
<tr><td class="plain">{VAR:LC_FORMS_FILE_TYPE}:</td><td class="plain"><input type='radio' NAME='ftype' VALUE='1' CHECKED>{VAR:LC_FORMS_TAB_DELIMITED}</td></tr>
<tr><td class="plain">&nbsp;</td><td class="plain"><input type='radio' NAME='ftype' VALUE='2' >{VAR:LC_FORMS_ENTER_DELIMITED}</td></tr>
<tr><td class="plain">{VAR:LC_FORMS_HOW_MANY_ROWS_ADD}:</td><td class="plain"><input class='small_button' type='text' NAME='numrows' VALUE='1'></td></tr>
<tr><td class="plain">{VAR:LC_FORMS_CHOOSE_FILE}:</td><td class="plain"><input class='small_button' type='file' name='fail'></td></tr>
<tr><td class="plain" align=right colspan=2><input class='small_button' type='submit' VALUE='Uploadi'></td></tr></table>
<input type='hidden' name=action value=import_data>
<input type='hidden' NAME=id value={VAR:form_id}>
<input type='hidden' NAME=step VALUE=1>
</form>
