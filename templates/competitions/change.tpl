<span class="text">
<b><a href='{VAR:list}'>Nimekiri</a></b>
</span><br>
<form action=reforb.aw method=POST>

<table border="0" cellpadding="2" cellspacing="2">
<tr>
<td class="textsmall" valign="top" align="right">Nimi:</td>
<td><input type='text' name='name' value='{VAR:name}' size="35"></td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">Sisu:</td>
<td><textarea name='content' cols=30 rows=10>{VAR:content}</textarea></td>
</tr>
<!-- SUB: IS_ADMIN -->
<tr>
<td class="textsmall" valign="top" align="right">Algab:</td>
<td>{VAR:date_start}</td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">H&auml;&auml;letamine algab:</td>
<td>{VAR:date_end}</td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">H&auml;&auml;letamine l&otilde;peb:</td>
<td>{VAR:date_vote_end}</td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">Raskusaste:</td>
<td><select name="raskus">
<option value="1"{VAR:kergem_sel}>Kergem</option>
<option value="2"{VAR:raskem_sel}>Raskem</option>
</select></td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">Aktsepteeritud:</td>
<td><input type='checkbox' name='confirmed' value='1' {VAR:confirmed}></td>
</tr>
<!-- END SUB: IS_ADMIN -->

<!-- SUB: NO_ADMIN -->
<tr>
<td class="textsmall" valign="top" align="right">Algab:</td>
<td>{VAR:start}</td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">H&auml;&auml;letamine algab:</td>
<td>{VAR:end}</td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">H&auml;&auml;letamine l&otilde;peb:</td>
<td>{VAR:vote_end}</td>
</tr>
<tr>
<td class="textsmall" valign="top" align="right">Aktsepteeritud:</td>
<td>{VAR:accepted}</td>
</tr>
<!-- END SUB: NO_ADMIN -->
<tr>
<td></td>
<td><input type="submit" value="Salvesta"></td>
</tr>
</table>
{VAR:reforb}
</table>
