{VAR:menu}


<form method="POST"  action='reforb.{VAR:ext}'>
<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#EEEEEE">
<tr>
<td>
<table width="100%" bgcolor="#FFFFFF" border="0" cellspacing="1" cellpadding="2">
<tr>
	<td class="text" colspan="2">
	<strong>{VAR:title}</strong>
<HR size="1" width="100%" color="#C8C8C8">
	</td>
</tr>
<tr>
	<td class="textsmall" width="50%"><strong>Kui väli</strong></td>
	<td class="textsmall"><select name="field">{VAR:field_list}</select></td>
</tr>
<tr>
	<td class="textsmall"><strong>sisaldab teksti</strong>
	<td class="textsmall"><input type="text" name="rule" size="30" value="{VAR:rule}"></td>
</tr>
<tr>
	<td class="textsmall"><input type="radio" name="delivery" value="fldr" {VAR:folder_checked}><strong>Paiguta see folderisse</strong>
	<td class="textsmall"><select name="folder">{VAR:folder_list}</select></td>
</tr>
<tr>
	<td class="textsmall"><input type="checkbox" name="set_priority" value="1" {VAR:set_pri_checked}>
	<strong>Kehtesta prioriteet</strong></td>
	<td class="textsmall"><select name="set_priority_to">{VAR:pri_list}</select></td>
</tr>
<!--
<tr>
	<td class="textsmall"><input type="radio" name="delivery" value="mail" {VAR:addr_checked}><strong>Saada aadressile</strong>
	<td class="textsmall"><input type="text" name="addr" size="30" value="{VAR:addr}"></td>
</tr>
-->
<tr>
	<td class="textsmall" align="center" colspan="3">
		<input class="lefttab" type="submit" value="{VAR:btn_cap}" class="formbutton">
	</td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>

