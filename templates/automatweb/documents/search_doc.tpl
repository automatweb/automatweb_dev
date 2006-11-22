<br><br><br><br>
<form method="GET" action="orb.{VAR:ext}">

<table border=0 cellspacing=0 cellpadding=5>
<tr>
<td class="aste01" colspan="2">
		<table border=0 cellspacing=0 cellpadding=2>
		<tr class="aste01">
			<td class="celltext" align="right">{VAR:LC_DOCUMENT_SEARCH_NAME}:</td>
			<td class="celltext"><input type="text" name="s_name" size="40" value='{VAR:s_name}' class="formtext"></td>
		</tr>
		<tr class="aste01">
			<td class="celltext" align="right">{VAR:LC_DOCUMENT_SEARCH_CONTENT}:</td>
			<td class="celltext"><input type="text" name="s_content" size="40" value='{VAR:s_content}' class="formtext"></td>
		</tr>
		<tr class="aste01">
			<td>&nbsp;</td>
			<td class="celltext"><input type="submit" value="{VAR:LC_DOCUMENT_SEARCH}" class="formbutton"></td>
		</tr>
		<tr class="aste01">
			<td class="celltext" colspan=2>&nbsp;</td>
		</tr>
		</table>
</td></tr>
<tr class="aste06"><td colspan="2" class="celltext">{VAR:LC_DOCUMENT_FOUND_DOCS}:</td></tr>
<!-- SUB: LINE -->
<tr class="aste01">
	<td class="celltext"><a target="_blank" href='{VAR:change}'>{VAR:name}</a></td>
	<td class="celltext"><a href='{VAR:brother}'>{VAR:LC_DOCUMENT_DO_BROTHER}</a></td>
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:reforb}
</form>
