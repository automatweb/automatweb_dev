<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Kataloog:</td><td class="fform"><select name='folder'>{VAR:folders}</select>&nbsp;&nbsp;<input type='checkbox' NAME='search_subs' VALUE=1 CHECKED>&nbsp;ka alamkataloogidest</td>
</tr>
<tr>
<td class="fcaption">Kellelt:</td><td class="fform"><input type='text' NAME='from' VALUE=''></td>
</tr>
<tr>
<td class="fcaption">Kellele:</td><td class="fform"><input type='text' NAME='to' VALUE=''></td>
</tr>
<tr>
<td class="fcaption">Teema:</td><td class="fform"><input type='text' NAME='subject' VALUE=''></td>
</tr>
<tr>
<td class="fcaption" valign=top>Sisu:</td><td class="fform"><input type='text' NAME='content' VALUE=''></td>
</tr>
<tr>
<td class="fcaption" valign=top>Saabunud enne:</td><td class="fform"><input type='checkbox' VALUE=1 NAME='use_e_date'>&nbsp;<select name='e_day'>{VAR:e_day}</select> / <select name='e_month'>{VAR:e_month}</select> / <select name='e_year'>{VAR:e_year}</select></td>
</tr>
<tr>
<td class="fcaption" valign=top>Saabunud p&auml;rast:</td><td class="fform"><input type='checkbox' VALUE=1 NAME='use_s_date'>&nbsp;<select name='s_day'>{VAR:s_day}</select> / <select name='s_month'>{VAR:s_month}</select> / <select name='s_year'>{VAR:s_year}</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Otsi!'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='search_mail'>
</form>
