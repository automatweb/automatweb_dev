<script language="javascript">
var sel_el;
function setLink(li,title)
{
	sel_el.value=li;
}
</script>

<form action='reforb.{VAR:ext}' method=post name="b88" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" VALUE="1000000">
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
<td class="plain">Vali bugtracki kasutajate nimekirja grupp:</td>
<td class="plain"><select name='bt_gid'>{VAR:bt_gid}</select></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Vali bugtracki adminnide grupp:</td>
<td class="plain"><select name='bt_adm'>{VAR:bt_adm}</select></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Uploadi "favorites icon":</td>
<td class="plain">{VAR:favicon} <input type="file" name="favicon"></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Kas p&auml;rast lisamist logitakse kasutaja sisse:</td>
<td class="plain"><input type="checkbox" name="autologin" value='1' {VAR:autologin}></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain" colspan=2><input type='submit' value='Salvesta'></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='orb.{VAR:ext}?class=icons&action=icon_db'>Ikoonide baas</a></td>
<td class="plain"><a href='orb.{VAR:ext}?class=icons&action=import_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=class_icons'>Klasside ikoonid</a></td>
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=import_class_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=file_icons'>Failit&uuml;&uuml;pide ikoonid</a></td>
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=import_file_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=program_icons'>Programmide ikoonid</a></td>
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=import_program_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=other_icons'>Muud ikoonid</a></td>
<td class="plain"><a href='orb.{VAR:ext}?class=config&action=import_other_icons'>Impordi</a></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><a href='{VAR:cfgform_link}'>Klasside konfiguratsioonivormid</a></td>
<td class="plain">&nbsp;</td>
</tr>
</table>
{VAR:reforb}
</form>
