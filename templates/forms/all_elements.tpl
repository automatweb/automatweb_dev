<form action='reforb.{VAR:ext}' method=POST>
<table bgcolor=#b0b0b0 cellpadding=2>
<tr>
<td>&nbsp;</td>
<!-- SUB: HE -->
<td align="center">{VAR:col1}</td>
<!-- END SUB: HE -->
</tr>
<!-- SUB: LINE -->
<tr>
<td>{VAR:row1}</td>
<!-- SUB: COL -->
<td bgcolor=#ffffff valign=bottom align=left colspan={VAR:colspan} rowspan={VAR:rowspan}>
<!-- SUB: SOME_ELEMENTS -->

<table bgcolor=#f0f0f0 width=100% height=100% border=0>
<tr>
<td align=left class='fgen_text' colspan=3><a href='javascript:remote("no","200","200","pickstyle.{VAR:ext}?id={VAR:form_id}&col={VAR:col}&row={VAR:row}")'>{VAR:LC_FORMS_CHOOSE_STYLE}</a>&nbsp;<input type='checkbox' name='chk[{VAR:row}][{VAR:col}]' value=1>&nbsp;{VAR:style_name}</td>
</tr>

<tr>
	<td bgcolor=#ffffff class='fgen_text'><b>{VAR:LC_FORMS_NAME}</b></td>
	<td bgcolor=#ffffff class='fgen_text'><b>{VAR:LC_FORMS_TYPE}</b></td>
	<td bgcolor=#ffffff class='fgen_text'><b>{VAR:LC_FORMS_TEXT}</b></td>
</tr>
<!-- SUB: ELEMENT -->
<tr>
	<td bgcolor=#ffffff class='fgen_text'>{VAR:el_name}</td>
	<td bgcolor=#ffffff class='fgen_text'>{VAR:el_type}</td>
	<td bgcolor=#ffffff class='fgen_text'>{VAR:el_text} &nbsp;&nbsp;<input type='checkbox' name='selel[]' value='{VAR:element_id}'></td>
</tr>
<!-- END SUB: ELEMENT -->
</table>
<!-- END SUB: SOME_ELEMENTS -->
</td>
<!-- END SUB: COL -->
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:LC_FORMS_CHOOSE_STYLE}:<select name='setstyle' class='small_button'>{VAR:styles}</select><br>
{VAR:LC_FORMS_CHOOSE_CALALOGUE_WHERE_MOVE_ELEMENT}:<select name='setfolder' class='small_button'>{VAR:folders}</select><br>
{VAR:LC_FORMS_CHOOSE_ELEMENT_TYPE_WHAT_ADD}:<select name='addel' class='small_button'>{VAR:types}</select><br>
<input type='submit' value='{VAR:LC_FORMS_SAVE}' class='small_button'>&nbsp;&nbsp;
<input type='submit' name='diliit' value='Kustuta' class='small_button'>
{VAR:reforb}
</form>