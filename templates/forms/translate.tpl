<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform" colspan=10><input type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
<tr>
<td class="fform"></td>
<!-- SUB: LANGH -->
<td class="fform">{VAR:lang_name}:</td>
<!-- END SUB: LANGH -->
</tr>
<tr>
<td colspan=10 class="fform">{VAR:LC_FORMS_ELEMENTS_TEXTS}:</td>
</tr>
<!-- SUB: LROW -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL -->
<td class="fform"><input type='text' name='r[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL -->
</tr>
<!-- END SUB: LROW -->
<tr>
<td colspan=10 class="fform">{VAR:LC_FORMS_LISTBOXES_CONTENT}:</td>
</tr>
<!-- SUB: LROW1 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL1 -->
<td class="fform"><input type='text' name='l[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}][{VAR:item}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL1 -->
</tr>
<!-- END SUB: LROW1 -->
<tr>
<td colspan=10 class="fform">{VAR:LC_FORMS_MLISTBX_CONTENT}:</td>
</tr>
<!-- SUB: LROW2 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL2 -->
<td class="fform"><input type='text' name='m[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}][{VAR:item}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL2 -->
</tr>
<!-- END SUB: LROW2 -->
<tr>
<td colspan=10 class="fform">{VAR:LC_FORMS_SUBSCRIPTS_CONTENT}:</td>
</tr>
<!-- SUB: LROW3 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL3 -->
<td class="fform"><input type='text' name='s[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL3 -->
</tr>
<!-- END SUB: LROW3 -->
<tr>
<td colspan=10 class="fform">{VAR:LC_FORMS_DEFAULT_TEXTS_CONTENT}:</td>
</tr>
<!-- SUB: LROW4 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL4 -->
<td class="fform"><input type='text' name='d[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL4 -->
</tr>
<!-- END SUB: LROW4 -->
<tr>
<td colspan=10 class="fform">{VAR:LC_FORMS_ERRORS_CONTENT}:</td>
</tr>
<!-- SUB: LROW5 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL5 -->
<td class="fform"><input type='text' name='e[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL5 -->
</tr>
<!-- END SUB: LROW5 -->
<tr>
<td colspan=10 class="fform">Tähemärkide arvu kontrolli veateated:</td>
</tr>
<!-- SUB: LROW8 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL8 -->
<td class="fform"><input type='text' name='cl[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL8 -->
</tr>
<!-- END SUB: LROW8 -->
<tr>
<td colspan=10 class="fform">{VAR:LC_FORMS_BUTTONTS_TEXTS}:</td>
</tr>
<!-- SUB: LROW6 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL6 -->
<td class="fform"><input type='text' name='b[{VAR:row}][{VAR:col}][{VAR:lang_id}][{VAR:elid}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL6 -->
</tr>
<!-- END SUB: LROW6 -->

<tr>
<td colspan=10 class="fform">Elementide metadata:</td>
</tr>
<!-- SUB: LROW7 -->
<tr>
<td class="fform">{VAR:name}</td>
<!-- SUB: LCOL7 -->
<td class="fform"><input type='text' name='w[{VAR:row}][{VAR:col}][{VAR:elid}][{VAR:lang_id}][{VAR:mtk}]' value='{VAR:text}' class='small_button'></td>
<!-- END SUB: LCOL7 -->
</tr>
<!-- END SUB: LROW7 -->

<!-- do not use L*8, it's already in use
   .. and what the fsck is it with that naming scheme anyway? -->
<tr>
<td class="fform" colspan=10><input type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
