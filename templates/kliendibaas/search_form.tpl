<button onclick="document.getElementById('make_search').value = '1'">Otsi</button>

<!-- SUB: search_field_textbox -->
{VAR:label}
<input type="text" id="sfield[{VAR:id}]" name="sfield[{VAR:id}]" value="{VAR:value}" /> välistav:<input name="exclude[{VAR:id}]"
type="checkbox" {VAR:exclude}><br />

<!-- END SUB: search_field_textbox -->

<!-- SUB: search_field_select -->
{VAR:label}
<select id="sfield[{VAR:id}]" name="sfield[{VAR:id}]">
{VAR:options}
</select>
välistav:
<input name="exclude[{VAR:id}]"
type="checkbox" {VAR:exclude}><br />

<!-- END SUB: search_field_select -->