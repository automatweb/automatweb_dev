<form action="refcheck.{VAR:ext}" method=post>
<table border=0 cellspacing=1 cellpadding=1 width=600>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g"><select MULTIPLE SIZE=20 name="sections[]">{VAR:sections}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g"><input type='submit' class='small_button' value='Salvesta'></td>
	</tr>
</table>
<input type='hidden' name='action' value='save_doc_brother'>
<input type='hidden' name='docid' value='{VAR:docid}'>
</form>