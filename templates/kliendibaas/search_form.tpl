<!-- SUB: search_field_textbox -->
{VAR:caption}
<input type="text" id="sfield[{VAR:id}]" name="sfield[{VAR:id}]" value="{VAR:value}" /> v�listav:<input name="exclude[{VAR:id}]"
type="checkbox" {VAR:exclude}><br />

<!-- END SUB: search_field_textbox -->

<!-- SUB: search_field_select -->
{VAR:caption}
<select id="sfield[{VAR:id}]" name="sfield[{VAR:id}]" {VAR:multiple}>
{VAR:options}
</select>
v�listav:
<input name="exclude[{VAR:id}]"
type="checkbox" {VAR:exclude}><br />
<!-- END SUB: search_field_select -->


