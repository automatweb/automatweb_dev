<table border=0 cellspacing=1 cellpadding=2>
<!-- SUB: search_field_textbox -->
<tr >
	<td class='chformleftcol'  width='160' nowrap>
{VAR:caption}
	</td>
	<td class='chformrightcol'>
<input type="text" id="sfield[{VAR:id}]" name="sfield[{VAR:id}]" value="{VAR:value}" />
{VAR:br}	
	</td>
</tr>

<!-- <input name="exclude[{VAR:id}]"
type="checkbox" {VAR:exclude} /> -->

<!-- END SUB: search_field_textbox -->

<!-- SUB: search_field_select -->
<tr>
	<td class='chformleftcol' width='160' nowrap>
{VAR:caption}
	</td>
	<td class='chformrightcol'>
<select id="sfield[{VAR:id}]" name="sfield[{VAR:id}]" {VAR:multiple}>
{VAR:options}
</select>
välistav:
<input name="exclude[{VAR:id}]"
type="checkbox" {VAR:exclude}><br />
	</td>
</tr>
<!-- END SUB: search_field_select -->
	<td class='chformleftcol' width='160' nowrap>	
	</td>
	<td class='chformrightcol'>

<input type="hidden" id="make_search" name="make_search" value="0" />
<input type="hidden" id="search_type" name="search_type" value="org" />
<input type="submit" value="Otsi" onclick="document.getElementById('make_search').value = '1'" />
	</td>
</tr>
</table>