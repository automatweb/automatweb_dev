<form action='reforb.{VAR:ext}' method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='20000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td><td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">Programm:</td><td class="fform"><input type='text' NAME='programm'></td>
</tr>
<tr>
<td class="fcaption">Meie:</td><td class="fform"><input type='radio' NAME='kelle' VALUE='meie'></td>
</tr>
<tr>
<td class="fcaption">V&otilde;&otilde;ras:</td><td class="fform"><input type='radio' NAME='kelle' VALUE='nende'></td>
</tr>
<tr>
<td class="fcaption">Puhastatud:</td><td class="fform"><input type='checkbox' NAME='puhastatud' VALUE=1></td>
</tr>
<tr>
<td class="fcaption">Praht:</td><td class="fform"><input type='checkbox' NAME='praht' VALUE=1></td>
</tr>
<tr>
<td class="fcaption">M&auml;ki:</td><td class="fform"><input type='radio' NAME='opsys' VALUE="m2kk"></td>
</tr>
<tr>
<td class="fcaption">Winblowsi:</td><td class="fform"><input type='radio' NAME='opsys' VALUE="winblows"></td>
</tr>
<tr>
<td class="fcaption">L33noxi:</td><td class="fform"><input type='radio' NAME='opsys' VALUE="l33nox"></td>
</tr>
<tr>
<td class="fcaption">P&auml;ritolu:</td><td class="fform"><input type='text' NAME='p2rit'></td>
</tr>
<tr>
<td class="fcaption">M&auml;rks&otilde;nad (mis):</td><td class="fform"><input type='text' NAME='m2rks6nad' SIZE=60></td>
</tr>
<tr>
<td class="fcaption">M&auml;rks&otilde;nad (milleks):</td><td class="fform"><input type='text' NAME='m2rks6nad2' SIZE=60></td>
</tr>
<tr>
<td class="fcaption">Fail:</td><td class="fform"><input type='file' NAME='fail'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
{VAR:reforb}
</form>
