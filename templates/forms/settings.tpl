<script language='javascript'>

function varv(vrv) 
{
	document.ffrm.bgcolor.value="#"+vrv;
} 

function varvivalik() 
{
  aken=window.open("/vv.html","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}
</script>

<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Taustav&auml;rv:</td>
<td class="fform"><input type='text' NAME='bgcolor' VALUE='{VAR:form_bgcolor}'> <a href="#" onclick="varvivalik();">vali v&auml;rv</a></td>
</tr>
<tr>
<td class="fform">Serva laius:</td>
<td class="fform"><input type='text' NAME='border' VALUE='{VAR:form_border}'></td>
</tr>
<tr>
<td class="fform">cellpadding:</td>
<td class="fform"><input type='text' NAME='cellpadding' VALUE='{VAR:form_cellpadding}'></td>
</tr>
<tr>
<td class="fform">cellspacing:</td>
<td class="fform"><input type='text' NAME='cellspacing' VALUE='{VAR:form_cellspacing}'></td>
</tr>
<tr>
<td class="fform">K&otilde;rgus:</td>
<td class="fform"><input type='text' NAME='height' VALUE='{VAR:form_height}'></td>
</tr>
<tr>
<td class="fform">Laius:</td>
<td class="fform"><input type='text' NAME='width' VALUE='{VAR:form_width}'></td>
</tr>
<tr>
<td class="fform">Hspace:</td>
<td class="fform"><input type='text' NAME='hspace' VALUE='{VAR:form_hspace}'></td>
</tr>
<tr>
<td class="fform">Vspace:</td>
<td class="fform"><input type='text' NAME='vspace' VALUE='{VAR:form_vspace}'></td>
</tr>
<tr>
<td class="fform">Default stiil:</td>
<td class="fform"><select NAME='def_style'><option VALUE=''>{VAR:def_style}</select>
</td>
</tr>
<tr>
<td class="fform" colspan=2>Kas formi &uuml;&uuml;ritatakse t&auml;ita kasutaja liitumisel sisestatud andmetega: &nbsp;<input type='checkbox' name='try_fill' value=1 {VAR:try_fill}></td>
</tr>
<tr>
<td class="fform" colspan=2>Kataloog kuhu salvestatakse formi sisestatud info:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select name='ff_folder' class='small_button'>{VAR:ff_folder}</select></td>
</tr>
<!-- SUB: NOSEARCH -->
<tr>
<td class="fform" colspan=2>P&auml;rast t&auml;itmist:</td>
</tr>
<tr>
<td class="fform"><input type='radio' NAME='after_submit' VALUE='1' {VAR:as_1}>muuda sisestust</td>
<td class="fform">&nbsp;</td>
</tr>
<tr>
<td class="fform"><input type='radio' NAME='after_submit' VALUE='3' {VAR:as_3}>mine aadressile:</td>
<td class="fform"><input type='text' NAME='after_submit_link' value='{VAR:after_submit_link}'></td>
</tr>
<!-- END SUB: NOSEARCH -->

<!-- SUB: SEARCH -->
<tr>
<td class="fform" colspan=2>Kas otsingu tulemusi n&auml;idatakse tabelina? <input type='checkbox' NAME='show_table' value='1' {VAR:show_table_checked}></td>
</tr>
<tr>
<td class="fform">Vali tabel:</td>
<td class="fform"><select name='table'>{VAR:tables}</select></td>
</tr>
<!-- END SUB: SEARCH -->
<tr>
<td class="fform" colspan=2>Vali element mille sisu pannakse formi sisestuse objekti nimeks</td>
</tr>
<tr>
<td colspan=2 class="fform"><select NAME='entry_name_el'>{VAR:els}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' NAME='save_form_settings' VALUE='Salvesta form'></td>
</table>
{VAR:reforb}
</form>
  
