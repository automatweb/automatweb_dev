<script language="javascript">
var sel_el;
function setLink(li,title)
{
	sel_el.value=li;
}
</script>

<form action='refcheck.{VAR:ext}' method=post name="b88">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="plain">Aadress, kuhu suunatakse p&auml;rast sisse logimist:</td>
<td class="plain"><input type='text' name='after_login' value='{VAR:after_login}'><a href="#" onclick="sel_el=document.b88.after_login;remote('no',500,400,'{VAR:search_doc}')">Saidi sisene link</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Aadress, kuhu suunatakse kui on vaja sisse logida:</td>
<td class="plain"><input type='text' name='mustlogin' value='{VAR:mustlogin}'><a href="#" onclick="sel_el=document.b88.mustlogin;remote('no',500,400,'{VAR:search_doc}')">Saidi sisene link</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Aadress, kuhu suunatakse kui tuleb veateade:</td>
<td class="plain"><input type='text' name='error_redirect' value='{VAR:error_redirect}'><a href="#" onclick="sel_el=document.b88.error_redirect;remote('no',500,400,'{VAR:search_doc}')">Saidi sisene link</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Vali kasutaja info form:</td>
<td class="plain"><select name='user_info_form'>{VAR:forms}</select></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Vali kasutaja info v&auml;ljund:</td>
<td class="plain"><select name='user_info_op'>{VAR:ops}</select></td>
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
