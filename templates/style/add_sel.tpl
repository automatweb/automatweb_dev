<form action='reforb.{VAR:ext}' METHOD=post>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption">Nimi:</td>
		<td class="fform"><input type="text" name="name" VALUE=''></td>
	</tr>
	<tr>
		<td class="fcaption">Kommentaar:</td>
		<td class="fform"><textarea name=comment cols=50 rows=5></textarea></td>
	</tr>
	<tr>
		<td class="fcaption" colspan=2>Valu t&uuml;p:</td>
	</tr>
	<tr>
		<td class="fcaption">Tabeli stiil</td>
		<td class="fform"><input type="radio" name="type" VALUE='0' CHECKED></td>
	</tr>
	<tr>
		<td class="fcaption">Celli stiil</td>
		<td class="fform"><input type="radio" name="type" VALUE='1'></td>
	</tr>
<!--	<tr>
		<td class="fcaption">Elemendi stiil</td>
		<td class="fform"><input type="radio" name="type" VALUE='2'></td>
	</tr>-->
	<tr>
		<td class="fform" colspan=2><input type="submit" VALUE='Salvesta' class='small_button'></td>
	</tr>
</table>
{VAR:reforb}
</form>
								