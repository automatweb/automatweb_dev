<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Vali, milliste rubriikide all kasti n&auml;idatakse:</td><td class="fform"><SELECT NAME='section[]' SIZE=20 MULTIPLE>{VAR:section}</select></td>
</tr>
<tr>
<td class="fcaption">Pealkiri:</td><td class="fform"><input type='text' NAME='title' VALUE='{VAR:title}'></td>
</tr>
<tr>
<td class="fcaption">Link:</td><td class="fform"><input type='text' NAME='link' VALUE='{VAR:link}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2>Kasti tyyp:</td>
</tr>
<tr>
<td class="fcaption">Paremal:</td><td class="fform"><input type='radio' NAME='right' VALUE='1' {VAR:right_sel}></td>
</tr>
<tr>
<td class="fcaption">Vasakul:</td><td class="fform"><input type='radio' NAME='right' VALUE='0' {VAR:left_sel}></td>
</tr>
<tr>
<td class="fcaption">Template (muutmiseks)</td><td class="fform">
<select name="tpl_edit">
{VAR:tpl_edit}
</select>
</td>
</tr>
</tr>
<tr>
<td class="fcaption">Template (n&auml;itamiseks)</td><td class="fform">
<select name="tpl_lead">
<option value="0">Default</option>
{VAR:tpl_lead}
</select>
</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='Salvesta' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
