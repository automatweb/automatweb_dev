<form method="GET" action="pickobject.{VAR:ext}" name="searchdoc">


<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="2"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="29" BORDER=0 ALT=""></td>

<td valign="bottom">


<table border=0 cellpadding=0 cellspacing=0>
<tr>

<td class="tabsel"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom"><b>Search objects</b></td><td class="tabsel"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>


<td class="tab"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="bottom"><a href='pickobject.{VAR:ext}?type=search&docid={VAR:docid}'>List of objects</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>





</tr></table>



</td>
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




<table width="100%" cellspacing="0" cellpadding="5">
<tr><td class="tableborder">

	<table border="0" cellspacing="1" cellpadding="2" >
	<tr>
	<td>

		<table border="0" cellspacing="1" cellpadding="2">

		<tr>
			<td class="celltext">Search from name:</td>
			<td class="celltext" width=60% colspan="2"><input type="text" name="s_name" size="40" value='{VAR:s_name}' class="formtext"></td>
		</tr>
		<tr>
			<td class="celltext">Search from comments:</td>
			<td class="celltext" width=60% colspan="2"><input type="text" name="s_comment" size="40" value='{VAR:s_comment}' class="formtext"></td>
		</tr>
		<tr>
			<td class="celltext">Objects type:</td>
			<td class="celltext"><select name='s_type' class="formselect2">{VAR:types}</select></td>
			<td align="right"><input type="submit" value="Search" class="formbutton"></td>
		</tr>
		</table>

		<br>

		<table border="0" cellspacing="0" cellpadding="0" width=100%>
		<tr>
		<td bgcolor="#FFFFFF">



				<table border="0" cellspacing="1" cellpadding="2" width=100%>
				<tr class="aste05">


				<tr>
				<td class="celltext" colspan=8><b>Found objects:</b></td>
				</tr>
				<tr class="aste05">
				<td class="celltext">Name</td>
				<td class="celltext" nowrap>&nbsp;Type&nbsp;</td>
				<td class="celltext" nowrap align="center">&nbsp;Created&nbsp;</td>
				<td class="celltext" nowrap align="center">&nbsp;Creator&nbsp;</td>
				<td class="celltext" nowrap align="center">&nbsp;Changed&nbsp;</td>
				<td class="celltext" nowrap align="center">&nbsp;Changer&nbsp;</td>
				<td class="celltext" nowrap align="center">&nbsp;Parent&nbsp;</td>
				<td class="celltext" nowrap>&nbsp;</td>
			</tr>
			 <!-- SUB: LINE -->
			<tr class="aste07">
				<td class="celltext">{VAR:name}</td>
				<td class="celltext" nowrap>{VAR:type}</td>
				<td class="celltext" nowrap>{VAR:created}</td>
				<td class="celltext" nowrap>{VAR:createdby}</td>
				<td class="celltext" nowrap>{VAR:modified}</td>
				<td class="celltext" nowrap>{VAR:modifiedby}</td>
				<td class="celltext" nowrap>{VAR:parent_name}</td>
				<td class="celltext" nowrap>{VAR:pickurl}</td>
			</tr>
			<!-- END SUB: LINE -->
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

<input type='hidden' name='docid' value='{VAR:docid}'>
</form>
