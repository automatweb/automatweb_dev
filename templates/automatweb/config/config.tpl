<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="plain">Aadress, kuhu suunatakse p&auml;rast sisse logimist:</td>
<td class="plain"><input type='text' name='after_login' value='{VAR:after_login}'></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain" colspan=2><input type='submit' value='Salvesta'></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='config.{VAR:ext}?type=icon_db'>Ikoonide baas</a></td>
<td class="plain"><a href='config.{VAR:ext}?type=import_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='config.{VAR:ext}?type=class_icons'>Klasside ikoonid</a></td>
<td class="plain"><a href='config.{VAR:ext}?type=import_class_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='config.{VAR:ext}?type=file_icons'>Failit&uuml;&uuml;pide ikoonid</a></td>
<td class="plain"><a href='config.{VAR:ext}?type=import_file_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='config.{VAR:ext}?type=program_icons'>Programmide ikoonid</a></td>
<td class="plain"><a href='config.{VAR:ext}?type=import_program_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='config.{VAR:ext}?type=other_icons'>Muud ikoonid</a></td>
<td class="plain"><a href='config.{VAR:ext}?type=import_other_icons'>Impordi</a></td>
</tr>
</table>
<input type='hidden' name='action' value='submit_loaginaddr'>
</form>