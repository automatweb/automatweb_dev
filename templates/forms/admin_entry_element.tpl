<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width=100%>
<tr>
	<td class="fgtext">T&uuml;&uuml;p:</td>
	<td class="fgtext"><select class="small_button" NAME='{VAR:cell_id}_type'>
    <option  VALUE=''>Tavaline tekst
    <option  VALUE=''>---------
    <option {VAR:type_active_textbox} VALUE='textbox'>Tekstiboks
    <option {VAR:type_active_textarea} VALUE='textarea'>Mitmerealine tekst
    <option {VAR:type_active_checkbox} VALUE='checkbox'>Checkbox
    <option {VAR:type_active_radiobutton} VALUE='radiobutton'>Radiobutton
    <option {VAR:type_active_listbox} VALUE='listbox'>Listbox
    <option {VAR:type_active_multiple} VALUE='multiple'>Multiple listbox
    <option {VAR:type_active_file} VALUE='file'>Faili lisamine
    <option {VAR:type_active_link} VALUE='link'>H&uuml;perlink
    <option {VAR:type_active_submit} VALUE='submit'>Submit nupp
    <option {VAR:type_active_reset} VALUE='reset'>Reset nupp
    <option {VAR:type_active_price} VALUE='price'>Hind
    <option {VAR:type_active_date} VALUE='date'>Kuup&auml;ev
<!-- SUB: CAN_DELETE -->
    <option VALUE='delete'>Kustuta see element
<!-- END SUB: CAN_DELETE -->
    </select></td>
	<td class="fgtext">Nimi:</td>
	<td class="fgtext"><input type='text' class="small_button" NAME='{VAR:cell_id}_name' VALUE='{VAR:cell_name}'></td>
</tr>
<!-- SUB: LISTBOX_ITEMS -->
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><input class="small_button" type='text' NAME='{VAR:listbox_item_id}' VALUE='{VAR:listbox_item_value}'>&nbsp;<input type='radio' NAME='{VAR:listbox_radio_name}' VALUE='{VAR:listbox_radio_value}' {VAR:listbox_radio_checked}></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: LISTBOX_ITEMS -->

<!-- SUB: MULTIPLE_ITEMS -->
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><input CLASS="small_button" type='text' NAME='{VAR:multiple_item_id}' VALUE='{VAR:multiple_item_value}'>&nbsp;<input CLASS="small_button" type='checkbox' NAME='{VAR:multiple_check_name}' VALUE='{VAR:multiple_check_value}' {VAR:multiple_check_checked}></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: MULTIPLE_ITEMS -->

<!-- SUB: TEXTAREA_ITEMS -->
<tr>
<td class="fgtext">Suurus:</td>
<td class="fgtext">&nbsp;Laius:&nbsp;<input CLASS="small_button" SIZE=3 type='text' NAME='{VAR:textarea_cols_name}' VALUE='{VAR:textarea_cols}'>K&otilde;rgus:&nbsp;<input CLASS="small_button" SIZE=3 type='text' NAME='{VAR:textarea_rows_name}' VALUE='{VAR:textarea_rows}'></td>
<td valign=top class="fgtext">Algne tekst:</td>
<td class="fgtext"><input type=text CLASS="small_button"  SIZE=45 NAME='{VAR:default_name}' VALUE='{VAR:default}'></td>
</tr>
<!-- END SUB: TEXTAREA_ITEMS -->

<!-- SUB: DATE_ITEMS -->
<tr>
<td class="fgtext">Mis aastast mis aastani:</td>
<td class="fgtext">&nbsp;<input CLASS="small_button" SIZE=5 type='text' NAME='{VAR:cell_id}_from_year' VALUE='{VAR:from_year}'>&nbsp;-&nbsp;<input type=text CLASS="small_button"  SIZE=5 NAME='{VAR:cell_id}_to_year' VALUE='{VAR:to_year}'></td>
<td valign=top class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: DATE_ITEMS -->

<!-- SUB: FILE_ITEMS -->
<tr>
<td class="fgtext">Kuvamine:</td>
<td class="fgtext"><input CLASS="small_button" type='radio' NAME='{VAR:cell_id}_filetype' VALUE='1' {VAR:ftype_image_selected}> pildina <input CLASS="small_button" type='radio' NAME='{VAR:cell_id}_filetype' VALUE='2' {VAR:ftype_file_selected}> lingitud failina</td>
<td class="fgtext">Lingi tekst:</td>
<td class="fgtext"><input CLASS="small_button" type='text' NAME='{VAR:cell_id}_file_link_text' VALUE='{VAR:file_link_text}'></td>
</tr>
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><input type='radio' NAME='{VAR:cell_id}_file_show' VALUE=1 {VAR:file_show}> kuvan kohe <input type='radio' NAME='{VAR:cell_id}_file_show' VALUE=0 {VAR:file_alias}> teen aliase</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: FILE_ITEMS -->

<!-- SUB: HLINK_ITEMS -->
<tr>
<td class="fgtext">Lingi tekst:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_link_text' VALUE='{VAR:link_text}'></td>
<td class="fgtext">Kirjelduse tekst:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_link_address' VALUE='{VAR:link_address}'></td>
</tr>
<!-- END SUB: HLINK_ITEMS -->

<!-- SUB: RADIO_ITEMS -->
<tr>
<td class="fgtext">Grupp:</td>
<td class="fgtext"><input class="small_button" type='text' SIZE=1 NAME='{VAR:cell_id}_group' VALUE='{VAR:cell_group}'></td>
<td class="fgtext">Algselt valitud:</td>
<td class="fgtext"><input type='checkbox' NAME='{VAR:default_name}' VALUE='1' {VAR:default_checked}></td>
</tr>
<!-- END SUB: RADIO_ITEMS -->

<!-- SUB: DEFAULT_TEXT -->
<tr>
<td class="fgtext">Algne tekst:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:default_name}' VALUE='{VAR:default}'></td>
<td class="fgtext">Pikkus:</td>
<td class="fgtext"><input type='text' CLASS="small_button" SIZE=3 NAME='{VAR:cell_id}_length' VALUE='{VAR:length}'></td>
</tr>
<!-- END SUB: DEFAULT_TEXT -->

<!-- SUB: BUTTON_ITEMS -->
<tr>
<td class="fgtext">Tekst nupul:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_btext' VALUE='{VAR:button_text}'></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: BUTTON_ITEMS -->

<!-- SUB: CHECKBOX_ITEMS -->
<tr>
<td class="fgtext">Algselt valitud:</td>
<td class="fgtext"><input type='checkbox' NAME='{VAR:default_name}' VALUE='1' {VAR:default_checked}></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: CHECKBOX_ITEMS -->
<!-- SUB: PRICE_ITEMS -->
<tr>
<td class="fgtext">Default hind:</td>
<td class="fgtext"><input class='small_button' type='text' NAME='{VAR:cell_id}_price' VALUE='{VAR:price}'></td>
<td class="fgtext">Pikkus:</td>
<td class="fgtext"><input type='text' CLASS="small_button" SIZE=3 NAME='{VAR:cell_id}_length' VALUE='{VAR:length}'></td>
</tr>
<!-- END SUB: PRICE_ITEMS -->
<tr>
<!-- SUB: EL_NOHLINK -->
<td valign=top class="fgtext">Tekst:</td>
<td valign=top class="fgtext"><input class="small_button" type='text' NAME='{VAR:cell_id}_text' VALUE="{VAR:cell_text}">&nbsp;Kaugus elemendist:&nbsp;<input class="small_button" type='text' NAME='{VAR:cell_id}_dist' size=3 VALUE='{VAR:cell_dist}'>&nbsp;pix</td>
<!-- END SUB: EL_NOHLINK -->

<!-- SUB: EL_HLINK -->
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<!-- END SUB: EL_HLINK -->
</tr>
<tr>
<td valign=top class="fgtext"><small>Subskript:</font></small></td>
<td class="fgtext" >
	<input type=text class="small_button" size=45 NAME='{VAR:cell_id}_info' value='{VAR:cell_info}'>
</td>
<Td class="fgtext">&nbsp;</td>
<Td class="fgtext">&nbsp;</td>
</tr>
<tr>
<td valign=top class="fgtext">Teksti asukoht:</td>
<td valign=top class="fgtext"><input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='up' {VAR:text_pos_up}>&nbsp;&Uuml;leval&nbsp;<input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='down' {VAR:text_pos_down}>&nbsp;All&nbsp;<input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='left' {VAR:text_pos_left}>&nbsp;Vasakul&nbsp;<input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='right' {VAR:text_pos_right}>&nbsp;Paremal&nbsp;</td>

<td valign=top class="fgtext"><a href='{VAR:changepos}'>Muuda elemendi asukohta</a></td>
<td valign=top class="fgtext">&nbsp;</td>
</tr>
<tr>
<td class="fgtext"><img src='/images/transa.gif' height=1 width=85><br>P&auml;rast elementi:</td>
<td class="fgtext" colspan=1><img src='/images/transa.gif' height=1 width=275><br><input class="small_button" type='radio' NAME='{VAR:cell_id}_separator_type' VALUE='1' {VAR:sep_enter_checked}>reavahetus&nbsp;&nbsp;
<input class="small_button" type='radio' NAME='{VAR:cell_id}_separator_type' VALUE='2' {VAR:sep_space_checked}>&nbsp;<input class="small_button" type='text' NAME='{VAR:cell_id}_sep_pixels' MAXLENGTH=10 SIZE=10 VALUE='{VAR:cell_sep_pixels}'>&nbsp;pikslit</td>
<td class="fgtext"><img src='/images/transa.gif' height=1 width=85><br>J&auml;rjekord:</td>
<td class="fgtext"><input class="small_button" type='text' size=2 NAME='{VAR:cell_id}_order' VALUE='{VAR:cell_order}'></td>
</tr>
</table>
