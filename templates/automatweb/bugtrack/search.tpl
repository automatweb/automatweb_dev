<form action='reforb.{VAR:ext}' method=POST name="foo">
{VAR:header}
<table>
<tr>
	<td colspan="2" class="fgtext"><a href='{VAR:list}'>Tagasi nimekirja</a></td>
</tr>
<tr>
	<td class="fgtext">ID:</td>
	<td class="fgtext"><input type='text' name='id' size='3' value='{VAR:id}' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">T&uuml;&uuml;p:</td>
	<td class="fgtext"><select name='type[]' multiple class='small_button'>{VAR:types}</select></td>
</tr>
<tr>
	<td class="fgtext">Mille kohta:</td>
	<td class="fgtext"><input type='text' name='url' value='{VAR:url}' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">Prioriteet:</td>
	<td class="fgtext"><input type='text' name='pri' value='{VAR:pri}' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">Kes:</td>
	<td class="fgtext"><select multiple name='from[]' class='small_button'>{VAR:from}</select></td>
</tr>
<tr>
	<td class="fgtext">Kellele:</td>
	<td class="fgtext"><select multiple name='developer[]' class='small_button'>{VAR:developer}</select></td>
</tr>
<tr>
	<td class="fgtext">CC:</td>
	<td class="fgtext"><input type='text' name='cc' value='{VAR:cc}' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">T&otilde;sidus:</td>
	<td class="fgtext"><select multiple name='severity[]' class='small_button'>{VAR:severity}</select></td>
</tr>
<tr>
	<td class="fgtext">Mitu tundi:</td>
	<td class="fgtext"><input type='text' name='hours' value='{VAR:hours}' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">Staatus:</td>
	<td class="fgtext"><select multiple name='status[]' class='small_button'>{VAR:status}</select></td>
</tr>
<tr>
	<td class="fgtext">Pealkiri:</td>
	<td class="fgtext"><input type='text' name='title' value='{VAR:title}' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">Tekst:</td>
	<td class="fgtext"><input type='text' name='text' value='{VAR:text}' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">Filtriks salvestamine:</td>
	<td class="fgtext"><input type='checkbox' name='save_as_filter' value='1'></td>
</tr>
<tr>
	<td class="fgtext">Filtri nimi:</td>
	<td class="fgtext"><input type='text' name='filter_name' value='' class='small_button'></td>
</tr>
<tr>
	<td class="fgtext">Filtrite grupp:</td>
	<td class="fgtext"><select class='small_button' name='filter_group'>{VAR:filter_groups}</select></td>
</tr>

<tr>
	<td colspan="2" class="fgtext"><input type='submit' value='Otsi'></td>
</tr>
</table>
{VAR:reforb}</form>
{VAR:table}


