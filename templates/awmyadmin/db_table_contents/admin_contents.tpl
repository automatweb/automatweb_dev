{VAR:pages}
<!-- SUB: TBL -->
{VAR:toolbar}
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<form action='reforb.{VAR:ext}' method="POST" name='add'>
<tr><td class="awmenuedittableborder">
{VAR:table}
</td>
</tr>
{VAR:reforb}
</form>
</table>
<!-- END SUB: TBL -->

<script language='javascript'>
var chk_status = true;

function selall()
{
	len = document.add.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.add.elements[i].name == "sel[]")
		{
			document.add.elements[i].checked=chk_status;
			window.status = ""+i+" / "+len;
		}
	}
	chk_status = !chk_status;
}

function ddel()
{
	document.add.is_del.value=1;
	document.add.submit();
}
</script>