<br>
<form action=refcheck.{VAR:ext} method=post enctype='multipart/form-data'><input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr><td class="title" colspan=2>{VAR:LC_FORMS_IMPORT_FORMS}</td></tr>
<tr><td class="plain">{VAR:LC_FORMS_CHOOSE_FILE}:</td><td class="plain"><input class='small_button' type='file' name='fail'></td></tr>
<tr><td class="plain" align=right colspan=2><input class='small_button' type='submit' VALUE='Uploadi'></td></tr></table>
<input type='hidden' name=action value=import_forms>
<input type='hidden' NAME=parent value={VAR:parent}>
<input type='hidden' NAME=level VALUE=1>
</form>
