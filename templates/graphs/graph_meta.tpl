<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<form method="post" action="reforb.{VAR:ext}" name=ff>
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">


<table border="0" cellpadding="0" cellspacing="0">
<tr><td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="29" BORDER=0 ALT=""></td>




<td width="30" class="celltext">
<b>
{VAR:LC_GRAPH_GRAPH1}:&nbsp;
</b>
</td>

<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:document.ff.submit()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MENUEDIT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""></td>

<td valign="bottom">
												<table border=0 cellpadding=0 cellspacing=0>
													<tr>
														
														


														

														<td class="tab"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="middle"><a href='{VAR:conf}'>{VAR:LC_GRAPH_CONFIG}</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>




														<td class="tab"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="middle"><a href='{VAR:prev}'>{VAR:LC_GRAPH_PREW}</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>


														<td class="tabsel"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom">{VAR:LC_GRAPH_META}</td><td class="tabsel"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>


														

														
														
													</tr>
												</table>







</td>
<td class="celltext">
&nbsp;&nbsp;&nbsp;

</td>


</tr></table>

</td></tr></table>
</td></tr></table>
</td></tr></table>



<table width="100%" border="0" cellpadding="5" cellspacing="0">
<tr><td class="tableborder">

<table border=0 cellpadding=2 bgcolor="#FFFFFF" cellspacing=1>

<tr>
	<td align=center class="aste01">


<table border="0" cellspacing="5" cellpadding="2">
	<tr>
	<TD class="celltext" align="right">{VAR:LC_GRAPH_NAME}:</td>
	<TD class="celltext"><input type="text" name="name" value="{VAR:name}" class="formtext"></td>
	</tr>
<TR>
	<TD class="celltext" align="right">{VAR:LC_GRAPH_COMM}:</td>
	<TD class="celltext"><textarea name="comment" COLS=50 ROWS=5 wrap='soft' class="formtext">{VAR:comment}</textarea>
</tr>
<TR>
	<TD class="celltext" align="right">{VAR:LC_GRAPH_TYPE}:</td>
	<td class="celltext">&nbsp;{VAR:type}&nbsp;</td>
</tr>
<TR>
	<TD class="celltext" align="right">{VAR:LC_GRAPH_DATA}:</td>
	<td class="celltext">&nbsp;{VAR:andmed}&nbsp;</td>
</tr>
	</TABLE>   
<!--<input type="submit" name="Submit" value="{VAR:LC_GRAPH_SAVE}">-->

{VAR:reforb}
</form>




</td>
</tr>
</table>

</td></tr></table>
<br>