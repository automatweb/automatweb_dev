



















<font color="red">{VAR:status_msg}</font>
<form action='reforb.{VAR:ext}' method="POST" name='selsrch'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
	<tr>
		<td class="plain">Saidi nimi:</td>
		<td class="plain"><input type="text" name="site_name" class="small_button" VALUE="{VAR:site_name}"></td>
	</tr>
	<tr>
		<td class="plain">Saidi ID:</td>
		<td class="plain"><input type="text" name="site_id" class="small_button" size="3" VALUE="{VAR:site_id}"></td>
	</tr>
	<tr>
		<td class="plain">Kataloogi nimi:</td>
		<td class="plain"><input type="text" name="site_folder" class="small_button" VALUE="{VAR:site_folder}"></td>
	</tr>
	<tr>
		<td class="plain">Saidi URL:</td>
		<td class="plain"><input type="text" name="site_url" class="small_button" VALUE="{VAR:site_url}"></td>
	</tr>
	<tr>
		<td class="plain" colspan="2"><input type="submit" class="small_button" value="Salvesta"></td>
	</tr>
</table>
{VAR:reforb}
</form>

