<form action='reforb.{VAR:ext}' method=POST name='q'>

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




<td class="celltitle">&nbsp;<b>{VAR:title}&nbsp;</td>
<td align="left"><!--add--><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:document.q.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img
name="save" alt="Salvesta" title="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a>
</td>
</tr></table>


		</td></tr></table>
	</td></tr></table>
</td></tr></table>









<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td class="aste05">
ID:
</td>
<td class="aste05">
{VAR:id}
</td>
</tr>
<tr>
<td class="aste05">
Alias:
</td>
<td class="aste05">
<input type="text" name="alias" size="50" value="{VAR:alias}">
</td>
</tr>
<tr>
<td class="aste05">
Kataloog serveris:
</td>
<td class="aste05">
<input type="text" name="dir" size="50" value="{VAR:dir}">
</td>
</tr>
</table>
{VAR:reforb}
</form>
