<FORM METHOD=POST ACTION="reforb.aw">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td bgcolor="#CCCCCC">
<TABLE border=0>
<!-- SUB: LINEG -->
<TR>
	<TD class="fcaption2">"{VAR:LC_GRAPH_CH_LINE} <b> {VAR:name}</b>" :
</TR>
<TR>
	<TD class="fcaption2"><SELECT NAME="datasrc">
			<OPTION VALUE=userdata>{VAR:LC_GRAPH_INSERT}</OPTION>
			<OPTION VALUE=stats_rows>{VAR:LC_GRAPH_BY_ROW}</OPTION>
			<OPTION VALUE=stats_bytes>{VAR:LC_GRAPH_BY_BITE}</OPTION>
			<OPTION VALUE=stats_words>{VAR:LC_GRAPH_BY_WORD}</OPTION>
		</SELECT>

</TR>
<!-- END SUB: LINEG -->
<!-- SUB: BARG -->
<TR>
	<TD class="fcaption2"> "{VAR:LC_GRAPH_CH_POST}<b> {VAR:name}</b>" :
</TR>
<TR>
	<TD class="fcaption2"><SELECT NAME="datasrc">
			<OPTION VALUE=userdata>{VAR:LC_GRAPH_INSERT}</OPTION>
			<OPTION VALUE=stats_all>{VAR:LC_GRAPH_BY_ALL}</OPTION>
		</SELECT>
</TR>
<!-- END SUB: BARG -->
<TR>
	<TD class="fcaption2">{VAR:LC_GRAPH_Y} <INPUT TYPE="text" NAME="ycount" VALUE="3" SIZE="2">.
</TR>
<TR>
	<TD class="fcaption2">{VAR:LC_GRAPH_NOTE}.
</TR>
	
</TABLE>
<input type="submit" name="Submit" value="{VAR:LC_GRAPH_FOR}">
</TABLE>
{VAR:reforb}
</FORM>
