<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{var:LC_MENUEDIT_PROMO_WHERE}</td><td class="fform"><SELECT NAME='section[]' SIZE=20 MULTIPLE>{VAR:section}</select></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_PROMO_ALL}</td><td class="fform"><input type='checkbox' NAME='all_menus' VALUE='1' {VAR:all_menus}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_HEADLINE}</td><td class="fform"><input type='text' NAME='title' VALUE='{VAR:title}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_LINK}</td><td class="fform"><input type='text' NAME='link' VALUE='{VAR:link}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2>{VAR:LC_MENUEDIT_TYPE}</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_RIGHT}</td><td class="fform"><input type='radio' NAME='right' VALUE='1' {VAR:right_sel}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_LEFT}</td><td class="fform"><input type='radio' NAME='right' VALUE='0' {VAR:left_sel}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_PROMO_SCROLL}</td><td class="fform"><input type='radio' NAME='right' VALUE='scroll' {VAR:scroll_sel}></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_TEMPL_EDIT}</td><td class="fform">
<select name="tpl_edit">
{VAR:tpl_edit}
</select>
</td>
</tr>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MENUEDIT_TEMPL_SHOW}</td><td class="fform">
<select name="tpl_lead">
<option value="0">Default</option>
{VAR:tpl_lead}
</select>
</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='{VAR:LC_MENUEDIT_SAVE}' CLASS="small_button"></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='admin_promo'>
<input type='hidden' NAME='id' VALUE='{VAR:promo_id}'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='interface' VALUE='{VAR:interface}'>
</form>
