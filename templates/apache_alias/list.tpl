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




<td class="celltitle">&nbsp;<b>Aliased&nbsp;</td>
<td align="left"><!--add--><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:add}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img
name="new" alt="Lisa" title="Lisa" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a>
<!-- SUB: page -->
<a href="{VAR:pagelink}">{VAR:pagetitle}</a>&nbsp;
<!-- END SUB: page -->

<!-- SUB: act_page -->
<b>{VAR:pagetitle}</b>
<!-- END SUB: act_page -->
</td>
</tr></table>


		</td></tr></table>
	</td></tr></table>
</td></tr></table>









<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">


<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr class="aste05">

		<td align=center class="celltext">&nbsp;ID&nbsp;</td>
		<td align=center class="celltext">&nbsp;Alias&nbsp;</td>
		<td align=center class="celltext">&nbsp;Kataloog serveris&nbsp;</td>
		<td align=center class="celltext">&nbsp;Loodud&nbsp;</td>
		<td align=center class="celltext">&nbsp;Autor&nbsp;</td>
		<td align=center class="celltext">&nbsp;Muudetud&nbsp;</td>
		<td align=center class="celltext">&nbsp;Muutja&nbsp;</td>
		<td align=center class="celltext" colspan="2">&nbsp;Tegevus&nbsp;</td>
	</tr>
<!-- SUB: LINE -->
<tr class="aste07"	>
<td class="celltext" align=center>&nbsp;{VAR:id}&nbsp;</td>
<td class="celltext" align=center>&nbsp;{VAR:alias}&nbsp;</td>
<td class="celltext" align=center>&nbsp;{VAR:dir}&nbsp;</td>
<td class="celltext" align=center>&nbsp;{VAR:created}&nbsp;</td>
<td class="celltext" align=center>&nbsp;{VAR:createdby}&nbsp;</td>
<td class="celltext" align=center>&nbsp;{VAR:modified}&nbsp;</td>
<td class="celltext" align=center>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td class="celltext" align=center><a href='{VAR:change}'><IMG SRC="{VAR:baseurl}/automatweb/images/blue/obj_edit.gif" WIDTH="16" HEIGHT="16" BORDER=0 ALT="Muuda" title="Muuda"></a></td>
<td class="celltext" align=center><a href="javascript:box2('Kustutada see alias?','{VAR:remove}')"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/obj_delete.gif" WIDTH="16" HEIGHT="16" BORDER=0 ALT="Kustuta" title="Kustuta"></a></td>
</tr>
<!-- END SUB: LINE -->
</table>
</td></tr></table>
{VAR:reforb}
</form>
This gets written into the apache conf file
<pre>
{VAR:conf}
</pre>
