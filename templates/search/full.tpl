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


<table border="0" cellpadding="0" cellspacing="2">
<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:document.searchform.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Otsi" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:document.searchform.submit();">Otsi / Salvesta</a>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
</tr>
</table>


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
	idx = arg.options[arg.selectedIndex].value;
	if (redir_targets[idx])
	{
		window.location = redir_targets[idx];
	};
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
