<form method="GET" action="orb.{VAR:ext}">

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





<td class="celltext">
<b>
Search objects 
</b>
</td></tr></table>

		</td></tr></table>
	</td></tr></table>
</td></tr></table>

<table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr>
<td class="aste01" colspan="2">

<table border=0 cellspacing=0 cellpadding=5>
<tr>
	<td class="celltext">Search from name:</td>
	<td class="celltext"><input type="text" name="s_name" size="40" value='{VAR:s_name}' class="formtext"></td>
</tr>
<tr>
	<td class="celltext">Search from comments:</td>
	<td class="celltext"><input type="text" name="s_comment" size="40" value='{VAR:s_comment}' class="formtext"></td>
</tr>
<tr>
	<td class="celltext">Objects type:</td>
	<td class="celltext"><select name='s_type' class="formselect2">{VAR:types}</select></td>
</tr>
<tr>
    <td></td>
	<td class="celltext"><input type="submit" value="Search" class="formbutton"></td>
</tr>
</table>
<br>

<table border="0" cellspacing="0" cellpadding="0"  width=100%>
<tr><td bgcolor="#FFFFFF">

<table border="0" cellspacing="1" cellpadding="5"  width=100%>
<tr class="aste03">
	<td class="celltext" colspan=8><b>Found objects:</b></td>
</tr>
<tr class="aste05">
	<td class="celltext">Name</td>
	<td class="celltext" nowrap>&nbsp;Type&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Created&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Creator&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Changed&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Changer&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Parent&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr class="aste01">
	<td class="celltext">{VAR:name}</td>
	<td class="celltext" nowrap>{VAR:type}</td>
	<td class="celltext" nowrap>{VAR:created}</td>
	<td class="celltext" nowrap>{VAR:createdby}</td>
	<td class="celltext" nowrap>{VAR:modified}</td>
	<td class="celltext" nowrap>{VAR:modifiedby}</td>
	<td class="celltext" nowrap>{VAR:parent_name}</td>
	<td class="celltext" nowrap><a href="{VAR:pick_url}">Pick this</a></td>
</tr>
<!-- END SUB: LINE -->
</table>
</td></tr></table>
</td></tr></table>
</td></tr></table>
<input type='hidden' name='docid' value='{VAR:docid}'>
<input type='hidden' name='class' value='aliasmgr'>
<input type='hidden' name='action' value='search'>
</form>
