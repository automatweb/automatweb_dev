<form method=POST action='reforb.{VAR:ext}'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td colspan=3 class="fform"><input checked type='radio' name='type' value='add'>&nbsp;Lisa uus element</td>
</tr>
<tr>
	<td class="fform">&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="fform">Vali kataloog, kuhu element lisada:</td>
	<td class="fform"><select name='parent' class='small_button'>{VAR:folders}</select></td>
</tr>
<tr>
	<td class="fform">&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="fform">Elemendi nimi:</td>
	<td class="fform"><input type="text" name="name"></td>
</tr>
<tr>
	<td colspan=3 class="fform"><input type='radio' name='type' value='select'>&nbsp;Lisa olemasolev element</td>
</tr>
<tr>
	<td class="fform">&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="fform">Vali element:</td>
	<td class="fform"><select class='small_button' name='el'>{VAR:elements}</select></td>
</tr>
<tr>
	<td class="fform" colspan="3" align="center">
	{VAR:reforb}
	<input type="submit" class='small_button' value="Lisa">
	</td>
</tr>
</table>
</form>
