<br>
<form action='config.{VAR:ext}' METHOD="GET">
<input type='hidden' NAME='type' VALUE='sel_icon'>
<input type='hidden' NAME='rtype' VALUE='{VAR:rtype}'>
<input type='hidden' NAME='rid' VALUE='{VAR:rid}'>
<input type='hidden' NAME='search' VALUE='1'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='sstring' VALUE='{VAR:sstring}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td><td class="fform"><input type='text' NAME='sstring2' VALUE='{VAR:sstring2}'></td>
</tr>
<tr>
<td class="fcaption">Programm:</td><td class="fform"><input type='text' NAME='programm' VALUE='{VAR:programm}'></td>
</tr>
<tr>
<td class="fcaption">Meie:</td><td class="fform"><input type='radio' NAME='kelle' VALUE='meie' {VAR:meie}></td>
</tr>
<tr>
<td class="fcaption">V&otilde;&otilde;ras:</td><td class="fform"><input type='radio' NAME='kelle' VALUE='nende' {VAR:nende}></td>
</tr>
<tr>
<td class="fcaption">Puhastatud:</td><td class="fform"><input type='checkbox' NAME='puhastatud' VALUE=1 {VAR:puhastatud}></td>
</tr>
<tr>
<td class="fcaption">Praht:</td><td class="fform"><input type='checkbox' NAME='praht' VALUE=1 {VAR:praht}></td>
</tr>
<tr>
<td class="fcaption">M&auml;ki:</td><td class="fform"><input type='radio' NAME='opsys' VALUE="m2kk" {VAR:m2kk}></td>
</tr>
<tr>
<td class="fcaption">Winblowsi:</td><td class="fform"><input type='radio' NAME='opsys' VALUE="winblows" {VAR:winblows}></td>
</tr>
<tr>
<td class="fcaption">L33noxi:</td><td class="fform"><input type='radio' NAME='opsys' VALUE="l33nox" {VAR:l33nox}></td>
</tr>
<tr>
<td class="fcaption">P&auml;ritolu:</td><td class="fform"><input type='text' NAME='p2rit' VALUE='{VAR:p2rit}'></td>
</tr>
<tr>
<td class="fcaption">M&auml;rks&otilde;nad (mis):</td><td class="fform"><input type='text' NAME='m2rks6nad' SIZE=60 VALUE='{VAR:m2rks6nad}'></td>
</tr>
<tr>
<td class="fcaption">M&auml;rks&otilde;nad (milleks):</td><td class="fform"><input type='text' NAME='m2rks6nad2' SIZE=60 VALUE='{VAR:m2rks6nad2}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Otsi'></td>
</tr>
</table>
<Br>
</form>

<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>TULEMUSED:&nbsp;<a href='config.{VAR:ext}?type=add_icon'>Lisa ikoon</a></b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kommentaar&nbsp;</td>
<td align="center" class="title">&nbsp;Ikoon&nbsp;</td>
<td align="center" colspan="1" class="title">Vali</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:comment}&nbsp;</td>
<td class="fgtext">&nbsp;<img src='{VAR:url}'>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='config.{VAR:ext}?type={VAR:rtype}&id={VAR:rid}&icon_id={VAR:id}'>Vali</a>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
<Br><br>