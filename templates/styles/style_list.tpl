<form action='refcheck.{VAR:ext}' method=post>
<table border=0 cellspacing=1 bgcolor=#cccccc cellpadding=2>
<tr>
<td class=title>{VAR:LC_STYLE_NAME}</td>
<td class=title colspan=3 align=center>{VAR:LC_STYLE_ACTIVITY}</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class=plain>{VAR:style_name}</td>
<td class=plain>
<!-- SUB: CAN_CHANGE -->
<a href='styles.{VAR:ext}?type=change_style&id={VAR:style_id}&parent={VAR:parent}'>{VAR:LC_STYLE_CHANGE}</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class=plain>
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('{VAR:LC_STYLE_ARE_YOU_SURE}?','styles.{VAR:ext}?type=delete_style&id={VAR:style_id}&parent={VAR:parent}')">{VAR:LC_STYLE_DELETE}</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class=plain align=center>
<!-- SUB: CAN_EXPORT -->
<input class='chkbox' type='checkbox' NAME='style_{VAR:style_id}' VALUE='1'>
<!-- END SUB: CAN_EXPORT -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class=plain colspan=3 align=center>
<!-- SUB: CAN_ADD -->
<a href='styles.{VAR:ext}?type=add_style&parent={VAR:parent}'>{VAR:LC_STYLE_ADD}</a>
<!-- END SUB: CAN_ADD -->
&nbsp;</td>
<td class=plain align=center>
<!-- SUB: CAN_EXPORT_B -->
<input class='small_button' type='submit' NAME='s' VALUE='{VAR:LC_STYLE_EXPORT}Ekspordi'>
&nbsp;</td>
<!-- END SUB: CAN_EXPORT_B -->
</tr>
</table>
<br>
<input type='hidden' NAME='action' VALUE='export_styles'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>

<!-- SUB: CAN_IMPORT -->
<form action='refcheck.{VAR:ext}' method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE=1000000>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr><td class="title" colspan=2>{VAR:LC_STYLE_IMPORT_STYLES}:</td></tr>
<tr><td class="plain">{VAR:LC_STYLE_FILE}:</td><td class="plain"><input class='small_button' type=file NAME=file></td></tr>
<tr><td class="plain" colspan=2 align=right><input class='small_button' type=submit name=upload value='{VAR:LC_STYLE_IMPORT}'></td></tr>
</table>
<input type='hidden' NAME='action' VALUE='import_styles'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>
<!-- END SUB: CAN_IMPORT -->