<form action = 'reforb.{VAR:ext}' method=post name="foo">
<table cellpadding=0 cellspacing=0 border=0>
<tr><td width=100%>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0 width=100%>
<tr>
	<td class="fcaption2">Konfiguratsioon:</td>
	<td class="fform" ><select name='conf'>{VAR:conf}</select></td>
</tr>
<tr>
	<td class="fcaption2">From:</td>
	<td class="fform" ><input type="text" name="mfrom" class="small_button" value='{VAR:mfrom}'></td>
</tr>
<tr>
	<td class="fcaption2">Kellele (muutuja):</td>
	<td class="fform" ><input type="text" name="mtargets1" class="small_button" value='{VAR:mtargets1}'></td>
</tr>
<tr>
	<td class="fcaption2">Subject:</td>
	<td class="fform" ><input type="text" name="subject" class="small_button" value='{VAR:subject}'></td>
</tr>

<tr>
	<td class="fcaption2">Muutujad:</td>
	<td class="fform" >{VAR:elements}</td>
</tr>
<tr>
	<td class="fcaption2">Sisu:</td>
	<td class="fform" ><textarea name='message' cols="60" rows="20">{VAR:message}</textarea></td>
</tr>

<tr>
	<td class="fcaption2" colspan="2" align="right"><a href='{VAR:l_queue}'>Queue</a> | <a href='{VAR:l_saada}'>Saada</a> | <input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
</td></tr>
</table>
{VAR:reforb}
</form>
