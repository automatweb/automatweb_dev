<!-- SUB: SEARCH_DEFS -->
<script language="javascript">
var elements = new Array();
// elements = array(form_id, el_id,el_text);

<!-- SUB: ELDEFS -->
elements[{VAR:el_num}] = new Array({VAR:form_id},{VAR:el_id},"{VAR:el_text}");
<!-- END SUB: ELDEFS -->

</script>
<!-- END SUB: SEARCH_DEFS -->

<!-- SUB: TABLE_DEFS -->
<script language="javascript">
var elements = new Array();

<!-- SUB: TBL -->
elements[{VAR:tbl_num}] = new Array("{VAR:table_name}","{VAR:col_name}","{VAR:col_name}");
<!-- END SUB: TBL -->

</script>
<!-- END SUB: TABLE_DEFS -->

<!-- SUB: SEARCH_SCRIPT -->
<script language="javascript">

function clearList(list)
{
	var listlen = list.length;

	for(i=0; i < listlen; i++)
		list.options[0] = null;
}

function ch(el, f_el,suf)
{
	var sf = f_el.options[f_el.selectedIndex].value;

	clearList(el);
	for (i=0; i < elements.length; i++)
	{
		if (elements[i][0] == sf)
		{
			el.options[el.length] = new Option(elements[i][2],""+elements[i][1],false,false);
		}
	}
}

function setsel(el,val)
{
	for (i=0; i < el.length; i++)
	{
		if (el.options[i].value==val)
		{
			el.options[i].selected = true;
			return;
		}
	}
}

function toggle_file_link_newwin()
{
	alert(document.f1.{VAR:cell_id}_filetype);
}
</script>
<!-- END SUB: SEARCH_SCRIPT -->

<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width=100%>
<!-- SUB: SEARCH_LB -->
<tr>
	<td class="fgtext">{VAR:LC_FORMS_FORM_WHERE_ELEMENT_IS_TAKEN}:</td>
	<td class="fgtext"><select class='small_button' NAME='{VAR:cell_id}_form' onChange="ch(document.f1.{VAR:cell_id}_element, this)">{VAR:forms}</select></td>
	<td class="fgtext">{VAR:LC_FORMS_ELEMENT_FROM_FORM}:</td>
	<td class="fgtext"><select class='small_button' NAME='{VAR:cell_id}_element'><option value=''></select>
	<script language="javascript">
		ch(document.f1.{VAR:cell_id}_element, document.f1.{VAR:cell_id}_form);
		setsel(document.f1.{VAR:cell_id}_element,"{VAR:linked_el}");
	</script>
	</td>
</tr>
<!-- END SUB: SEARCH_LB -->

<!-- SUB: TABLE_LB -->
<tr>
	<td class="fgtext">Tabel:</td>
	<td class="fgtext"><select class='small_button' NAME='{VAR:cell_id}_table' onChange="ch(document.f1.{VAR:cell_id}_tbl_col, this)">{VAR:tables}</select></td>
	<td class="fgtext">Tulp tabelis:</td>
	<td class="fgtext"><select class='small_button' NAME='{VAR:cell_id}_tbl_col'><option value=''></select>
	<script language="javascript">
		ch(document.f1.{VAR:cell_id}_tbl_col, document.f1.{VAR:cell_id}_table);
		setsel(document.f1.{VAR:cell_id}_tbl_col,"{VAR:table_col}");
	</script>
	</td>
</tr>
<!-- END SUB: TABLE_LB -->

<!-- SUB: FILTER_PART_LB -->
<tr>
<td class="fgtext">Elemendiga seotud filtri osa:</td>
<td class="fgtext" colspan="3"><select class="small_button" name='{VAR:cell_id}_part'>{VAR:parts}</select></td>
</tr>
<!-- END SUB: FILTER_PART_LB -->
<tr>
	<td class="fgtext">T&uuml;&uuml;p:</td>
	<td class="fgtext"><select class="small_button" NAME='{VAR:cell_id}_type'>
    <option  VALUE=''>{VAR:LC_FORMS_ORDINARY_TEXT}
    <option  VALUE=''>---------
    <option {VAR:type_active_textbox} VALUE='textbox'>{VAR:LC_FORMS_TEXTBOX}
    <option {VAR:type_active_textarea} VALUE='textarea'>{VAR:LC_FORMS_MULTILINE_TEXT}
    <option {VAR:type_active_checkbox} VALUE='checkbox'>Checkbox
    <option {VAR:type_active_radiobutton} VALUE='radiobutton'>Radiobutton
    <option {VAR:type_active_listbox} VALUE='listbox'>Listbox
    <option {VAR:type_active_multiple} VALUE='multiple'>Multiple listbox
    <option {VAR:type_active_file} VALUE='file'>{VAR:LC_FORMS_ADDING_FILE}
    <option {VAR:type_active_link} VALUE='link'>{VAR:LC_FORMS_HYPERLINK}
    <option {VAR:type_active_button} VALUE='button'>{VAR:LC_FORMS_BUTTON}
    <option {VAR:type_active_price} VALUE='price'>{VAR:LC_FORMS_PRICE}
    <option {VAR:type_active_date} VALUE='date'>{VAR:LC_FORMS_DATE}
<!-- SUB: CAN_DELETE -->
    <option VALUE='delete'>{VAR:LC_FORMS_DELETE_THIS_ELEMENT}
<!-- END SUB: CAN_DELETE -->
    </select>
<!-- SUB: HAS_SUBTYPE -->
&nbsp;Alamt&uuml;&uuml;p:&nbsp;
<select name='{VAR:cell_id}_subtype' class="small_button">{VAR:subtypes}</select>
<!-- END SUB: HAS_SUBTYPE -->
		</td>
	<td class="fgtext">{VAR:LC_FORMS_NAME}:</td>
	<td class="fgtext"><input type='text' class="small_button" NAME='{VAR:cell_id}_name' VALUE='{VAR:cell_name}'></td>
</tr>
<tr>
	<td class="fgtext">{VAR:LC_FORMS_IGNORE_TEXT}:</td>
	<td class="fgtext"><input type='checkbox' name='{VAR:cell_id}_ignore_text' {VAR:ignore_text}></td>
	<td class="fgtext">{VAR:LC_FORMS_TYPE_NAME}:</td>
	<td class="fgtext"><input type='text' class="small_button" NAME='{VAR:cell_id}_type_name' VALUE='{VAR:cell_type_name}'></td>
</tr>
<!-- SUB: RELATION_LB -->
<tr>
	<td class="fgtext">Seose form:</td>
	<td class="fgtext"><select class='small_button' NAME='{VAR:cell_id}_rel_form' onChange="ch(document.f1.{VAR:cell_id}_rel_element, this)">{VAR:rel_forms}</select></td>
	<td class="fgtext">Seose element:</td>
	<td class="fgtext"><select class='small_button' NAME='{VAR:cell_id}_rel_element'><option value=''></select>
	<script language="javascript">
		ch(document.f1.{VAR:cell_id}_rel_element, document.f1.{VAR:cell_id}_rel_form);
		setsel(document.f1.{VAR:cell_id}_rel_element,"{VAR:rel_el}");
	</script>
	</td>
</tr>
<!-- END SUB: RELATION_LB -->

<!-- SUB: SEARCH_RELATION -->
<tr>
	<td class="fgtext">Ainult unikaalsed:</td>
	<td class="fgtext">&nbsp;<input type='checkbox' class='small_button' value='1' name='{VAR:cell_id}_unique' {VAR:unique}></td>
	<td class="fgtext">&nbsp;</td>
	<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: SEARCH_RELATION -->

<!-- SUB: LISTBOX_SORT -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_SORTING}:</td>
<td class="fgtext"><input class="small_button" type='checkbox' NAME='{VAR:cell_id}_sort_order' value='1' {VAR:sort_by_order}>&nbsp;{VAR:LC_FORMS_BY_ORDER} <input type='checkbox' NAME='{VAR:cell_id}_sort_alpha' VALUE='1'  {VAR:sort_by_alpha}>&nbsp;{VAR:LC_FORMS_BY_ALPHABET}</td>
<td class="fgtext">{VAR:LC_FORMS_IMPORT}Impordi:</td>
<td class="fgtext"><input type='file' name='{VAR:cell_id}_import' class='small_button'></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_FORMS_IS_MARKED_ELEMENTS}:</td>
<td class="fgtext"><input class="small_button" type='radio' NAME='{VAR:cell_id}_lbitems_dowhat' value='del' >&nbsp;{VAR:LC_FORMS_WILL_BE_DELETED} <input type='radio' NAME='{VAR:cell_id}_lbitems_dowhat' VALUE='add'>&nbsp;{VAR:LC_FORMS_ADDING_NEW}</td>
<td class="fgtext">{VAR:LC_FORMS_SIZE}:</td>
<td class="fgtext"><input type="text" name="{VAR:cell_id}_lb_size" size=3 class='small_button' value='{VAR:lb_size}'></td>
</tr>
<!-- END SUB: LISTBOX_SORT -->

<!-- SUB: LISTBOX_ITEMS -->
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><input class="small_button" type='text' NAME='{VAR:listbox_item_id}' VALUE='{VAR:listbox_item_value}'>&nbsp;<input type='radio' NAME='{VAR:listbox_radio_name}' VALUE='{VAR:listbox_radio_value}' {VAR:listbox_radio_checked}>&nbsp;<input type='text' name='{VAR:listbox_order_name}' value='{VAR:listbox_order_value}' class='small_button' size=4>&nbsp;<input type='checkbox' name='{VAR:cell_id}_sel[{VAR:num}]' value='1'></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: LISTBOX_ITEMS -->

<!-- SUB: MULTIPLE_ITEMS -->
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><input CLASS="small_button" type='text' NAME='{VAR:multiple_item_id}' VALUE='{VAR:multiple_item_value}'>&nbsp;<input CLASS="small_button" type='checkbox' NAME='{VAR:multiple_check_name}' VALUE='{VAR:multiple_check_value}' {VAR:multiple_check_checked}>&nbsp;<input type='text' name='{VAR:multiple_order_name}' value='{VAR:multiple_order_value}' class='small_button' size=4>&nbsp;<input type='checkbox' name='{VAR:cell_id}_sel[{VAR:num}]' value='1'></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;
</tr>
<!-- END SUB: MULTIPLE_ITEMS -->

<!-- SUB: TEXTAREA_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_SIZE}:</td>
<td class="fgtext">&nbsp;{VAR:LC_FORMS_WITDH}:&nbsp;<input CLASS="small_button" SIZE=3 type='text' NAME='{VAR:textarea_cols_name}' VALUE='{VAR:textarea_cols}'>{VAR:LC_FORMS_HIGHT}:&nbsp;<input CLASS="small_button" SIZE=3 type='text' NAME='{VAR:textarea_rows_name}' VALUE='{VAR:textarea_rows}'></td>
<td valign=top class="fgtext">{VAR:LC_FORMS_ORIGINAL_TEXT}:</td>
<td class="fgtext"><input type=text CLASS="small_button"  SIZE=45 NAME='{VAR:default_name}' VALUE='{VAR:default}'></td>
</tr>
<!-- END SUB: TEXTAREA_ITEMS -->

<!-- SUB: DATE_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_FROM_YEAR_TO_YEAR}:</td>
<td class="fgtext">&nbsp;<input CLASS="small_button" SIZE=5 type='text' NAME='{VAR:cell_id}_from_year' VALUE='{VAR:from_year}'>&nbsp;-&nbsp;<input type=text CLASS="small_button"  SIZE=5 NAME='{VAR:cell_id}_to_year' VALUE='{VAR:to_year}'></td>
<td valign=top class="fgtext" colspan="2">
<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td class="fgtext">&nbsp;Aasta:</td>
<td class="fgtext"><input type='checkbox' name='{VAR:cell_id}_has_year' value='1' {VAR:has_year}></td>
<td class="fgtext">&nbsp;<input type='text' name='{VAR:cell_id}_year_ord' value='{VAR:year_ord}' size="2" class="small_button"></td>
</tr>
<tr>
<td class="fgtext">&nbsp;Kuu:</td>
<td class="fgtext"><input type='checkbox' name='{VAR:cell_id}_has_month' value='1' {VAR:has_month}></td>
<td class="fgtext">&nbsp;<input type='text' name='{VAR:cell_id}_month_ord' value='{VAR:month_ord}' size="2" class="small_button"></td>
</tr>
<tr>
<td class="fgtext">&nbsp;P&auml;ev:</td>
<td class="fgtext"><input type='checkbox' name='{VAR:cell_id}_has_day' value='1' {VAR:has_day}></td>
<td class="fgtext">&nbsp;<input type='text' name='{VAR:cell_id}_day_ord' value='{VAR:day_ord}' size="2" class="small_button"></td>
</tr>
<tr>
<td class="fgtext">&nbsp;Tund:</td>
<td class="fgtext"><input type='checkbox' name='{VAR:cell_id}_has_hr' value='1' {VAR:has_hr}></td>
<td class="fgtext">&nbsp;<input type='text' name='{VAR:cell_id}_hr_ord' value='{VAR:hr_ord}' size="2" class="small_button"></td>
</tr>
<tr>
<td class="fgtext">&nbsp;Minut:</td>
<td class="fgtext"><input type='checkbox' name='{VAR:cell_id}_has_minute' value='1' {VAR:has_minute}></td>
<td class="fgtext">&nbsp;<input type='text' name='{VAR:cell_id}_minute_ord' value='{VAR:minute_ord}' size="2" class="small_button"></td>
</tr>
<tr>
<td class="fgtext">&nbsp;Sekund:</td>
<td class="fgtext"><input type='checkbox' name='{VAR:cell_id}_has_second' value='1' {VAR:has_second}></td>
<td class="fgtext">&nbsp;<input type='text' name='{VAR:cell_id}_second_ord' value='{VAR:second_ord}' size="2" class="small_button"></td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="fgtext">Kuup&auml;eva formaat n&auml;itamisel:</td>
<td class="fgtext"><input type='text' name='{VAR:cell_id}_date_format' VALUE='{VAR:date_format}' class='small_button'></td>
<td class="fgtext" colspan="2">&nbsp;
</td>
</tr>
<td class="fgtext">Default kuup&auml;ev:</td>
<td class="fgtext" align="right">
&nbsp;<input type="radio" name="{VAR:cell_id}_def_date_type" VALUE="rel" {VAR:date_rel_checked}> Kuup&auml;ev elemendist 
<select name='{VAR:cell_id}_def_date_rel' class='small_button'>{VAR:date_rel_els}</select>&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="{VAR:cell_id}_def_date_type" VALUE="now" {VAR:date_now_checked}> Kellaaeg </td>
<td class="fgtext" colspan=2>pluss&nbsp;<input type="text" class="small_button" size="5" name="{VAR:cell_id}_def_date_num" value="{VAR:def_date_num}">&nbsp;<select name='{VAR:cell_id}_def_date_add_type' class="small_button">{VAR:add_types}</select>&nbsp;</td>
</tr>
<!-- END SUB: DATE_ITEMS -->

<!-- SUB: FILE_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_DISPLAYING}:</td>
<td class="fgtext"><input CLASS="small_button" type='radio' NAME='{VAR:cell_id}_filetype' VALUE='1' {VAR:ftype_image_selected}> {VAR:LC_FORMS_AS_A_PICTURE} <input CLASS="small_button" type='radio' NAME='{VAR:cell_id}_filetype' VALUE='2' {VAR:ftype_file_selected}> {VAR:LC_FORMS_LINKED_AS_FILE} failina
<input type='checkbox' name='{VAR:cell_id}_file_newwin' value=1 {VAR:file_new_win}> Link uues aknas
<input type="button" onClick="toggle_file_link_newwin()">
</td>
<td class="fgtext">{VAR:LC_FORMS_LINK_TEXT}:</td>
<td class="fgtext"><input CLASS="small_button" type='text' NAME='{VAR:cell_id}_file_link_text' VALUE='{VAR:file_link_text}'></td>
</tr>
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><input type='radio' NAME='{VAR:cell_id}_file_show' VALUE=1 {VAR:file_show}> {VAR:LC_FORMS_DISPLAY_NOW} <input type='radio' NAME='{VAR:cell_id}_file_show' VALUE=0 {VAR:file_alias}> {VAR:LC_FORMS_MAKING_ALIAS}</td>
<td class="fgtext">
&nbsp;
</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: FILE_ITEMS -->

<!-- SUB: HLINK_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_LINK_TEXT}:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_link_text' VALUE='{VAR:link_text}'></td>
<td class="fgtext">{VAR:LC_FORMS_DESCRIBE_TEXT}:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_link_address' VALUE='{VAR:link_address}'></td>
</tr>
<!-- END SUB: HLINK_ITEMS -->

<!-- SUB: RADIO_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_GROUP}:</td>
<td class="fgtext"><input class="small_button" type='text' SIZE=1 NAME='{VAR:cell_id}_group' VALUE='{VAR:cell_group}'></td>
<td class="fgtext">Algselt valitud:</td>
<td class="fgtext"><input type='checkbox' NAME='{VAR:default_name}' VALUE='1' {VAR:default_checked}></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_FORMS_RADIO_VALUE}</td>
<td class="fgtext"><input type='text' name='{VAR:cell_id}_ch_value' value='{VAR:ch_value}' class='small_button'></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: RADIO_ITEMS -->

<!-- SUB: DEFAULT_TEXT -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_ORIGINALLY_SELECTED}:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:default_name}' VALUE='{VAR:default}'></td>
<td class="fgtext">{VAR:LC_FORMS_LENGTH}:</td>
<td class="fgtext"><input type='text' CLASS="small_button" SIZE=3 NAME='{VAR:cell_id}_length' VALUE='{VAR:length}'></td>
</tr>
<!-- END SUB: DEFAULT_TEXT -->

<!-- SUB: BUTTON_SUB_URL -->
<tr>
<td class="fgtext">URL:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_burl' VALUE='{VAR:button_url}'></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: BUTTON_SUB_URL -->

<!-- SUB: BUTTON_CONFIRM_TYPE -->
<tr>
<td class="fgtext">Folder kuhu liigutada:</td>
<td colspan="3" class="fgtext"><select name='{VAR:cell_id}_confirm_moveto' class='small_button'>{VAR:folders}</select></td>
</tr>
<tr>
<td class="fgtext">Url kuhu suunata:</td>
<td colspan="3" class="fgtext"><input type='text' name='{VAR:cell_id}_confirm_redirect' class='small_button' value='{VAR:redirect}'></td>
</tr>
<!-- END SUB: BUTTON_CONFIRM_TYPE -->

<!-- SUB: BUTTON_SUB_OP -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_OUTPUT}:</td>
<td class="fgtext"><select CLASS="small_button" NAME='{VAR:cell_id}_bop'>{VAR:bops}</select></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: BUTTON_SUB_OP -->

<!-- SUB: BUTTON_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_TEXT_ON_BUTTON}:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_btext' VALUE='{VAR:button_text}'></td>
<td class="fgtext">Kas p&auml;rjas ei minda edasi:</td>
<td class="fgtext"><input type="checkbox" name="{VAR:cell_id}_chain_forward" value="1" {VAR:chain_forward}></td>
</tr>
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">Kas p&auml;rjas minnakse tagasi:</td>
<td class="fgtext"><input type="checkbox" name="{VAR:cell_id}_chain_backward" value="1" {VAR:chain_backward}></td>
</tr>
<!-- END SUB: BUTTON_ITEMS -->

<!-- SUB: CHECKBOX_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_ORIGINALLY_SELECTED}:</td>
<td class="fgtext"><input type='checkbox' NAME='{VAR:default_name}' VALUE='1' {VAR:default_checked}></td>
<td class="fgtext">{VAR:LC_FORMS_CHECKBOX_VALUE}</td>
<td class="fgtext"><input type='text' name='{VAR:cell_id}_ch_value' value='{VAR:ch_value}' class='small_button'></td>
</tr>
<tr>
<td class="fgtext">Grupp:</td>
<td class="fgtext"><input class='small_button' type='text' NAME='{VAR:cell_id}_ch_grp' VALUE='{VAR:ch_grp}'></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: CHECKBOX_ITEMS -->

<!-- SUB: PRICE_ITEMS -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_DEFAULT_PRICE}:</td>
<td class="fgtext"><input class='small_button' size=7 type='text' NAME='{VAR:cell_id}_price' VALUE='{VAR:price}'></td>
<td class="fgtext">{VAR:LC_FORMS_LENGTH}:</td>
<td class="fgtext"><input type='text' CLASS="small_button" SIZE=3 NAME='{VAR:cell_id}_length' VALUE='{VAR:length}'></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_FORMS_WHICH_CURRENCY_PRICE_SAVED}:</td>
<td class="fgtext"><select CLASS="small_button" NAME='{VAR:cell_id}_price_cur'>{VAR:price_cur}</select></td>
<td class="fgtext">{VAR:LC_FORMS_WHICH_CURRENCY_SHOW_PRICE}:</td>
<td class="fgtext"><select multiple CLASS="small_button" NAME='{VAR:cell_id}_price_show[]'>{VAR:price_show}</select></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_FORMS_PRICE_SEPARATOR}:</td>
<td class="fgtext"><input type='text' class='small_button' name='{VAR:cell_id}_price_sep' value='{VAR:price_sep}'></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
</tr>
<!-- END SUB: PRICE_ITEMS -->
<tr>
<!-- SUB: EL_NOHLINK -->
<td valign=top class="fgtext">{VAR:LC_FORMS_TEXT}:</td>
<td valign=top class="fgtext"><input class="small_button" type='text' NAME='{VAR:cell_id}_text' VALUE="{VAR:cell_text}">&nbsp;{VAR:LC_FORMS_DISTANCE_FROM_ELEMENT}:&nbsp;<input class="small_button" type='text' NAME='{VAR:cell_id}_dist' size=3 VALUE='{VAR:cell_dist}'>&nbsp;pix</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<!-- END SUB: EL_NOHLINK -->

<!-- SUB: EL_HLINK -->
<td class="fgtext">Vali v&auml;ljund:</td>
<td class="fgtext"><select name='{VAR:cell_id}_link_op' class='small_button'>{VAR:ops}</select></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<!-- END SUB: EL_HLINK -->
</tr>
<tr>
<td valign=top class="fgtext"><small>Subskript:</font></small></td>
<td class="fgtext" >
	<input type=text class="small_button" size=45 NAME='{VAR:cell_id}_info' value='{VAR:cell_info}'>
</td>
<Td class="fgtext">Selectrow grupp:</td>
<Td class="fgtext"><input type='text' name='{VAR:cell_id}_srow_grp' size=15 class='small_button' value='{VAR:srow_grp}'></td>
</tr>
<tr>
<td valign=top class="fgtext">{VAR:LC_FORMS_TEXT_POSITION}:</td>
<td valign=top class="fgtext"><input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='up' {VAR:text_pos_up}>&nbsp;{VAR:LC_FORMS_BIG_UP}&nbsp;<input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='down' {VAR:text_pos_down}>&nbsp;{VAR:LC_FORMS_BIG_DOWN}&nbsp;<input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='left' {VAR:text_pos_left}>&nbsp;{VAR:LC_FORMS_IN_LEFT}&nbsp;<input class="small_button" type='radio' NAME='{VAR:cell_id}_text_pos' VALUE='right' {VAR:text_pos_right}>&nbsp;{VAR:LC_FORMS_IN_RIGHT}&nbsp;</td>

<td valign=top class="fgtext"><a href='{VAR:changepos}'>{VAR:LC_FORMS_CHANGE_ELEMENT_POSITION}</a></td>
<td valign=top class="fgtext">&nbsp;</td>
</tr>
<tr>
<td class="fgtext"><img src='/images/transa.gif' height=1 width=85><br>{VAR:LC_FORMS_AFTER_ELEMENT}:</td>
<td class="fgtext" colspan=1><img src='/images/transa.gif' height=1 width=275><br><input class="small_button" type='radio' NAME='{VAR:cell_id}_separator_type' VALUE='1' {VAR:sep_enter_checked}>{VAR:LC_FORMS_ROW_EXCHANGE}&nbsp;&nbsp;
<input class="small_button" type='radio' NAME='{VAR:cell_id}_separator_type' VALUE='2' {VAR:sep_space_checked}>&nbsp;<input class="small_button" type='text' NAME='{VAR:cell_id}_sep_pixels' MAXLENGTH=10 SIZE=10 VALUE='{VAR:cell_sep_pixels}'>&nbsp;{VAR:LC_FORMS_PIXELS}</td>
<td class="fgtext"><img src='/images/transa.gif' height=1 width=85><br>{VAR:LC_FORMS_ORDER}:</td>
<td class="fgtext"><input class="small_button" type='text' size=2 NAME='{VAR:cell_id}_order' VALUE='{VAR:cell_order}'></td>
</tr>
<tr>
<td class="fgtext">Aktiivne alates:</td>
<td class="fgtext">{VAR:act_from}</td>
<td class="fgtext">Aktiivne kuni:</td>
<td class="fgtext">{VAR:act_to}</td>
</tr>
<!-- SUB: HAS_SIMPLE_CONTROLLER -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_SHOULD_BE_FILLED}:</td>
<td class="fgtext"><input type='checkbox' CLASS="small_button" NAME='{VAR:cell_id}_must_fill' VALUE='1' {VAR:must_fill_checked}></td>
<td class="fgtext">{VAR:LC_FORMS_ERROR_NOTE}:</td>
<td class="fgtext"><input type='text' CLASS="small_button" NAME='{VAR:cell_id}_must_error' VALUE='{VAR:must_error}'></td>
</tr>
<!-- END SUB: HAS_SIMPLE_CONTROLLER -->
</table>
