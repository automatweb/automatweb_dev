<form method="GET" action="orb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">{VAR:LC_DOCUMENT_SEARCH_NAME}:</td>
	<td class="fform"><input type="text" name="s_name" size="40" value='{VAR:s_name}'></td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_DOCUMENT_SEARCH_CONTENT}:</td>
	<td class="fform"><input type="text" name="s_content" size="40" value='{VAR:s_content}'></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="{VAR:LC_DOCUMENT_SEARCH}"></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_DOCUMENT_FOUND_DOCS}:</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2"><a target="_blank" href='{VAR:change}'>{VAR:name}</a></td>
	<td class="fform"><a href='{VAR:brother}'>{VAR:LC_DOCUMENT_DO_BROTHER}</a></td>
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:reforb}
</form>
