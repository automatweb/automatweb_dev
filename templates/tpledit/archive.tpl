<script language="JavaScript">
function activate()
{
	document.actform.submit();
};
</script>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width="100%">
<form name="actform" method="POST">
<tr>
<td colspan="4" class="fgtitle"><b>Arhiiv: {VAR:file}</b>
<a href="{VAR:edlink}">Muuda</a>
|
<a href="javascript:activate()"><b><font color="red">Salvesta</font></b></a>
</td>
</tr>
{VAR:table}
{VAR:reforb}
</form>
</table>
