<form name='q' method="POST" action='reforb.{VAR:ext}'>
<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">


<table border="0" cellpadding="0" cellspacing="0">
<tr><td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>




<td class="celltitle">&nbsp;<b><a href='{VAR:settings}'>Saidi export</a> | Ruulid | <a href='{VAR:gen_url}'>Ekspordi</a> &nbsp;</td>
<td align="left"></td>
</tr></table>


		</td></tr></table>
	</td></tr></table>
</td></tr></table>


<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
	<tr class="aste05">
		<td colspan="3" class="celltext"><a href='{VAR:add}'>Lisa</a></td>
	</tr>
	<tr class="aste05">
		<td class="celltext">Nimi</td>
		<td class="celltext">Muuda</td>
		<td class="celltext">Kustuta</td>
	</tr>
<!-- SUB: LINE -->
	<tr class="aste05">
		<td class="celltext">{VAR:name}</td>
		<td class="celltext"><a href='{VAR:change}'>Muuda</a></td>
		<td class="celltext"><a href='{VAR:delete}'>Kustuta</a></td>
	</tr>
<!-- END SUB: LINE -->
</table>

</td>
</tr>
</table>
{VAR:reforb}
</form>