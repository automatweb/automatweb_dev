<form action='reforb.{VAR:ext}' method=POST>
<table bgcolor=#b0b0b0 cellpadding=2>
<!-- SUB: LINE -->
<tr>
<!-- SUB: COL -->
<td bgcolor=#ffffff valign=bottom align=left colspan={VAR:colspan} rowspan={VAR:rowspan}>
<!-- SUB: SOME_ELEMENTS -->

<table bgcolor=#f0f0f0 width=100% height=100% border=0>
<tr>
<td align=left class='fgen_text' colspan=3><a href='javascript:remote("no","200","200","pickstyle.{VAR:ext}?id={VAR:form_id}&col={VAR:col}&row={VAR:row}")'>Vali stiil</a>&nbsp;<input type='checkbox' name='chk[{VAR:row}][{VAR:col}]' value=1>&nbsp;{VAR:style_name}</td>
</tr>

<tr>
	<td bgcolor=#ffffff class='fgen_text'><b>Nimi</b></td>
	<td bgcolor=#ffffff class='fgen_text'><b>T&uuml;&uuml;p</b></td>
	<td bgcolor=#ffffff class='fgen_text'><b>Tekst</b></td>
</tr>
<!-- SUB: ELEMENT -->
<tr>
	<td bgcolor=#ffffff class='fgen_text'>{VAR:el_name}</td>
	<td bgcolor=#ffffff class='fgen_text'>{VAR:el_type}</td>
	<td bgcolor=#ffffff class='fgen_text'>{VAR:el_text}</td>
</tr>
<!-- END SUB: ELEMENT -->
</table>
<!-- END SUB: SOME_ELEMENTS -->
</td>
<!-- END SUB: COL -->
</tr>
<!-- END SUB: LINE -->
</table>
Vali stiil:<select name='setstyle' class='small_button'>{VAR:styles}</select><br>
Vali kataloog, kuhu elemendid liigutada:<select name='setfolder' class='small_button'>{VAR:folders}</select><br>
<input type='submit' value='Salvesta' class='small_button'>
{VAR:reforb}
</form>