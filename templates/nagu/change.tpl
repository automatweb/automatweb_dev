<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>{VAR:LC_NAGU_BIG_FACE}:&nbsp;<a href='nagu.{VAR:ext}?type=add&id={VAR:id}'>{VAR:LC_NAGU_ADD}</a> | <a href="nagu.{VAR:ext}?type=texts&id={VAR:id}">{VAR:LC_NAGU_TEXTS}</a> | <a href="nagu.{VAR:ext}?type=change_ooc&id={VAR:id}">{VAR:LC_NAGU_ACTIONS}</a>
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
<td class="fcaption">{VAR:LC_NAGU_FORNAME}:</td><td class="fform"><input type='text' NAME='forname' VALUE='{VAR:eesnimi}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_MIDDLE_NAME}:</td><td class="fform"><input type='text' NAME='midname' VALUE='{VAR:kesknimi}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_SURNAME}:</td><td class="fform"><input type='text' NAME='surname' VALUE='{VAR:perenimi}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_SEX}:</td><td class="fform"><input {VAR:man} type='radio' NAME='gender' VALUE='m'>{VAR:LC_NAGU_MAN}&nbsp;<input type='radio' NAME='gender' {VAR:woman} VALUE='n'>{VAR:LC_NAGU_WOMAN}</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_ESTONIAN}:</td><td class="fform"><input type="checkbox" name="estonian" VALUE="1" {VAR:estonian}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_BIRTH}S&uuml;nni:</td><td class="fform"><input type='text' size=4 NAME='byear' VALUE='{VAR:byear}'>{VAR:LC_NAGU_YEAR}&nbsp;<select NAME='bmonth'>{VAR:bmonth}</select>{VAR:LC_NAGU_MONTH}&nbsp;<select NAME='bday'>{VAR:bday}</select>{VAR:LC_NAGU_DAY}&nbsp;</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_OCCUPITION}:</td><td class="fform"><select MULTIPLE NAME='occ[]'>{VAR:occ}</select>&nbsp;<a href='nagu.{VAR:ext}?type=change_ooc&id={VAR:id}'>{VAR:LC_NAGU_CHANGE}</a></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_IMAGE}:</td><td class="fform" valign=center><img src="{VAR:imgurl}"><Br><input type='file' NAME='img'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_NAGU_SAVE}'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_nagu'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='fid' VALUE='{VAR:fid}'>
</form>
