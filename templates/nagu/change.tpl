<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>N&Auml;OD:&nbsp;<a href='nagu.{VAR:ext}?type=add&id={VAR:id}'>Lisa</a> | <a href="nagu.{VAR:ext}?type=texts&id={VAR:id}">Tekstid</a> | <a href="nagu.{VAR:ext}?type=change_ooc&id={VAR:id}">Tegevused</a>
</b></td>
</tr>
</table>
</td>
</tr>
</table>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action = 'refcheck.{VAR:ext}' method=post enctype="multipart/form-data">
<input type="hidden" NAME="MAX_FILE_SIZE" VALUE="100000">
<tr>
<td class="fcaption">Eesnimi:</td><td class="fform"><input type='text' NAME='forname' VALUE='{VAR:eesnimi}'></td>
</tr>
<tr>
<td class="fcaption">Kesknimi:</td><td class="fform"><input type='text' NAME='midname' VALUE='{VAR:kesknimi}'></td>
</tr>
<tr>
<td class="fcaption">Perenimi:</td><td class="fform"><input type='text' NAME='surname' VALUE='{VAR:perenimi}'></td>
</tr>
<tr>
<td class="fcaption">Sugu:</td><td class="fform"><input {VAR:man} type='radio' NAME='gender' VALUE='m'>mees&nbsp;<input type='radio' NAME='gender' {VAR:woman} VALUE='n'>naine</td>
</tr>
<tr>
<td class="fcaption">Eestlane:</td><td class="fform"><input type="checkbox" name="estonian" VALUE="1" {VAR:estonian}></td>
</tr>
<tr>
<td class="fcaption">S&uuml;nni:</td><td class="fform"><input type='text' size=4 NAME='byear' VALUE='{VAR:byear}'>aasta&nbsp;<select NAME='bmonth'>{VAR:bmonth}</select>kuu&nbsp;<select NAME='bday'>{VAR:bday}</select>p&auml;ev&nbsp;</td>
</tr>
<tr>
<td class="fcaption">Tegevusala:</td><td class="fform"><select MULTIPLE NAME='occ[]'>{VAR:occ}</select>&nbsp;<a href='nagu.{VAR:ext}?type=change_ooc&id={VAR:id}'>Muuda</a></td>
</tr>
<tr>
<td class="fcaption">Pilt:</td><td class="fform" valign=center><img src="{VAR:imgurl}"><Br><input type='file' NAME='img'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_nagu'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='fid' VALUE='{VAR:fid}'>
</form>
