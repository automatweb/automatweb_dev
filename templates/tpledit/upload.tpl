<form enctype="multipart/form-data" method=POST action='images.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="100000">
<table border="0" cellspacing="0" cellpadding="0" width="400" bgcolor="#CCCCCC">
<tr>
<td>
<table border="0" cellspacing="1" cellpadding="2" width="100%" bgcolor="#FFFFFF">
<tr>
<tr>
<td class="fgtitle">Vali fail:</td>
<td class="fgtext"><input type="file" name="template" size="40"></td>
</tr>
<tr>
<td class="fgtitle">Nimi</td>
<td class="fgtext"><input type="text" name="name" size="40"></td>
</tr>
<tr>
<td class="fgtitle">Valikud</td>
<td class="fgtext">Aktiveerida <input type="checkbox" name="activate" value="1"></td>
</tr>
<tr>
<td class="fgtitle" colspan="2" align="center">
<input type="submit" value="Salvesta">
</td>
</tr>
{VAR:reforb}
</table>
</td>
</tr>
</table>
