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
		<td class="plain">DB host:</td>
		<td class="plain"><input type="text" name="db_host" class="small_button" VALUE="{VAR:db_host}"></td>
	</tr>
	<tr>
		<td class="plain">Baasi nimi:</td>
		<td class="plain"><input type="text" name="db_base" class="small_button" VALUE="{VAR:db_base}"></td>
	</tr>
	<tr>
		<td class="plain">Baasi kasutaja:</td>
		<td class="plain"><input type="text" name="db_name" class="small_button" VALUE="{VAR:db_name}"></td>
	</tr>
	<tr>
		<td class="plain">Baasi parool:</td>
		<td class="plain"><input type="text" name="db_pass" class="small_button" VALUE="{VAR:db_pass}"></td>
	</tr>
	<tr>
		<td class="plain">Default user:</td>
		<td class="plain"><input type="text" name="default_user" class="small_button" VALUE="{VAR:default_user}"></td>
	</tr>
	<tr>
		<td class="plain">Default pass:</td>
		<td class="plain"><input type="text" name="default_pass" class="small_button" VALUE="{VAR:default_pass}"></td>
	</tr>
	<tr>
		<td class="plain" colspan="2"><input type="submit" class="small_button" value="Salvesta"></td>
	</tr>
</table>
{VAR:reforb}
</form>

