<table width="100%" border="0" cellpadding="5" cellspacing="0">
<tr><td class="tableborder">

<table border=0 cellpadding=2 bgcolor="#FFFFFF" cellspacing=1>

<tr>
	<td align=center class="aste01">




<FORM METHOD=POST ACTION="reforb.aw">

<table border="0" cellspacing="0" cellpadding="2" width="100%">
<tr>
<td class="aste01">

<table border="0" cellspacing="5" cellpadding="2">


<!-- SUB: LINEG -->
<TR>
	<TD class="celltext">"{VAR:LC_GRAPH_CH_LINE} <b> {VAR:name}</b>" :
</TR>
<TR>
	<TD class="celltext"><SELECT NAME="datasrc" class="formselect2">
			<OPTION VALUE=userdata>{VAR:LC_GRAPH_INSERT}</OPTION>
			<OPTION VALUE=stats_rows>{VAR:LC_GRAPH_BY_ROW}</OPTION>
			<OPTION VALUE=stats_bytes>{VAR:LC_GRAPH_BY_BITE}</OPTION>
			<OPTION VALUE=stats_words>{VAR:LC_GRAPH_BY_WORD}</OPTION>
		</SELECT>

</TR>
<!-- END SUB: LINEG -->
<!-- SUB: BARG -->
<TR>
	<TD class="celltext"> "{VAR:LC_GRAPH_CH_POST}<b> {VAR:name}</b>" :
</TR>
<TR>
	<TD class="celltext"><SELECT NAME="datasrc" class="formselect2">
			<OPTION VALUE=userdata>{VAR:LC_GRAPH_INSERT}</OPTION>
			<OPTION VALUE=stats_all>{VAR:LC_GRAPH_BY_ALL}</OPTION>
		</SELECT>
</TR>
<!-- END SUB: BARG -->
<TR>
	<TD class="celltext">{VAR:LC_GRAPH_Y} <INPUT TYPE="text" NAME="ycount" VALUE="3" SIZE="2" class="formtext">.
</TR>
<TR>
	<TD class="celltext">{VAR:LC_GRAPH_NOTE}.
</TR>
	
</TABLE>
<input type="submit" name="Submit" value="{VAR:LC_GRAPH_FOR}" class="formbutton">
</TABLE>
{VAR:reforb}
</FORM>





</td>
</tr>
</table>

</td></tr></table>
<br>