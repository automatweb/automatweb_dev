{VAR:tabs}
<!-- SUB: TBC -->
{VAR:toolbar}
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<form action='reforb.{VAR:ext}' method="POST" name="add">
<tr><td class="awmenuedittableborder">
{VAR:table}
{VAR:reforb}
</td>
</form>
</tr>
</table>
<script language="javascript">
function del()
{
	document.add.is_del.value="1";
	document.add.submit();
}
</script>
<!-- END SUB: TBC -->
