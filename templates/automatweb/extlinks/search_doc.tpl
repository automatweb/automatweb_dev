<script language="javascript">
function ss(li,title)
{
	window.opener.setLink(li,title);
	window.close();
}
</script>
<form method="GET" action="orb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">{VAR:LC_EXTLINKS_SEARCH_FROM_NAME}</td>
	<td class="fform"><input type="text" name="s_name" size="40" value='{VAR:s_name}'></td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_EXTLINKS_SEARCH_FROM_CONTENT}</td>
	<td class="fform"><input type="text" name="s_content" size="40" value='{VAR:s_content}'></td>
</tr>
<tr>
	<td colspan=2 class="fcaption2">{VAR:LC_EXTLINKS_SEARCHING}: <input type='radio' name='s_class_id' value='doc' {VAR:doc_sel}>{VAR:LC_EXTLINKS_SEARCH_DOCUMENTS} <input type='radio' name='s_class_id' value='item' {VAR:item_sel}> {VAR:LC_EXTLINKS_SEARCH_FROM_HEADING}</td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="{VAR:LC_EXTLINKS_SEARCH}"></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_EXTLINKS_RESULT}:</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2"><a target="_blank" href='{VAR:baseurl}/{VAR:index_file}.{VAR:ext}?section={VAR:id}'>{VAR:name}</a></td>
	<td class="fform"><a href='javascript:ss("{VAR:url}","{VAR:name}")'>{VAR:LC_EXTLINKS_CHOOSE}</a></td>
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:reforb}
</form>
