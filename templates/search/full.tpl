<form method="GET" action="orb.{VAR:ext}" name="searchform">
<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">
{VAR:toolbar}



		</td>
		</tr>
		</table>


	</td>
	</tr>
	</table>

</td>
</tr>
</table>

<script type="text/javascript">
var redir_targets = new Array();
<!-- SUB: redir_target -->
redir_targets[{VAR:clid}] = '{VAR:url}';
<!-- END SUB: redir_target -->
</script>

<script type="text/javascript">
function refresh_page(arg)
{
	if (!document.searchform.special)
	{
		return false;
	}
	if (!document.searchform.special.checked)
	{
		return false;
	}
	idx = arg.options[arg.selectedIndex].value;
	if (redir_targets[idx])
	{
		window.location = redir_targets[idx];
	};
}

function submit(val)
{
	document.resulttable.subaction.value=val;
	document.resulttable.submit();
}

function mk_group(text)
{
	res = prompt(text);
	if (res)
	{
		document.resulttable.subaction.value = 'mkgroup';
		document.resulttable.grpname.value = res;
		document.resulttable.submit();
	};
}

function assign_config()
{
	document.resulttable.subaction.value='assign_config';
	document.resulttable.action = '{VAR:baseurl}/automatweb/orb.{VAR:ext}';
	document.resulttable.submit();
}

</script>





<table border=0 cellspacing=1 cellpadding=2>
<!-- SUB: field -->
<tr>
	<td class="celltext">{VAR:caption}</td>
	<td class="celltext">{VAR:element}</td>
</tr>
<!-- END SUB: field -->
</table>
{VAR:reforb}
</form>
{VAR:table}
{VAR:treforb}
{VAR:ef}
