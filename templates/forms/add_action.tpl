<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td><td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption" colspan=2>T&uuml;&uuml;p:</td>
</tr>
<tr>
<td class="fcaption"><input type='radio' NAME='type' VALUE='email' {VAR:email_selected}></td><td class="fform">Saada form e-mailile p&auml;rast t&auml;itmist</td>
</tr>
<!--<tr>
<td class="fcaption"><input type='radio' NAME='type' VALUE='move_filled' {VAR:move_filled_selected}></td><td class="fform">Liiguta formi sisestusi teise kategooriasse</td>
</tr>-->
<tr>
<td class="fcaption"><input type='radio' NAME='type' VALUE='join_list' {VAR:join_list_selected}></td><td class="fform">Liitu meilinglistiga</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Edasi'></td>
</tr>
</table>
{VAR:reforb}
</form>
