<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_PROMO_CHOOSE_SECTION}:</td><td class="fform"><SELECT NAME='section[]' SIZE=20 class='small_button' MULTIPLE>{VAR:section}</select></td>
</tr>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='title' VALUE='{VAR:title}' size="40"></td>
</tr>
<tr>
<td class="fcaption">Pealkiri:</td><td class="fform"><input type='text' NAME='comment' VALUE='{VAR:comment}' size="40"></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_PROMO_LINK}:</td><td class="fform"><input type='text' NAME='link' VALUE='{VAR:link}' size="40"></td>
</tr>
<tr>
<td class="fcaption">Lingi kirjeldus:</td><td class="fform"><input type='text' NAME='link_caption' VALUE='{VAR:link_caption}' size="40"></td>
</tr>
<tr>
<td class="fcaption" colspan=2>{VAR:LC_PROMO_BOX_TYPE}:</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_PROMO_AT_RIGHT}:</td><td class="fform"><input type='radio' NAME='right' VALUE='1' {VAR:right_sel}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_PROMO_AT_LEFT}:</td><td class="fform"><input type='radio' NAME='right' VALUE='0' {VAR:left_sel}></td>
</tr>
<tr>
<td class="fcaption">&Uuml;leval:</td><td class="fform"><input type='radio' NAME='right' VALUE='2' {VAR:up_sel}></td>
</tr>
<tr>
<td class="fcaption">All:</td><td class="fform"><input type='radio' NAME='right' VALUE='3' {VAR:down_sel}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_PROMO_WHTOUT_TITLE}:</td><td class="fform"><input type='checkbox' NAME='no_title' VALUE='1' {VAR:no_title}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_PROMO_TEMPLATE_FOR_CHANGE}</td><td class="fform"><select name="tpl_edit">{VAR:tpl_edit}</select></td>
</tr>
<tr>
	<td class="fcaption">Vali grupid kellele promo kasti n&auml;idatakse:</td>
	<td class="fform"><select name="groups[]" class="small_button" multiple size="10">{VAR:groups}</select></td>
</tr>

<tr>
<td class="fcaption">{VAR:LC_PROMO_TEMPLATE_FOR_SHOW}</td><td class="fform">
<select name="tpl_lead">
<option value="0">Default</option>
{VAR:tpl_lead}
</select>
</td>
</tr>
<tr>
	<td colspan=2 class="fcaption">Vali men&uuml;d, mille alt viimaseid dokumente n&auml;idatakse:</td>
</tr>
<tr>
	<td colspan=2 class="fcaption"><select size="20" name="last_menus[]" multiple class="small_button">{VAR:last_menus}</select></td>
</tr>
<tr>
	<td class="fcaption">Mitu viimast dokumenti:</td>
	<td class="fcaption"><input type="text" size="2" class="small_button" name="num_last" value='{VAR:num_last}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='{VAR:LC_PROMO_SHOW}' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
