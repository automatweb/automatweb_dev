<script language="JavaScript">
<!--

function set_color(vrv) 
{
	document.ff.elements[element].value=vrv;
} 

function varvivalik(nr)
{
	element = nr
	aken=window.open("orb.aw?class=css&action=colorpicker","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}
// -->
</script>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>{VAR:LC_GRAPH_GRAPH1}:&nbsp;
		<a href='{VAR:prev}'>{VAR:LC_GRAPH_PREW}</a>&nbsp;|&nbsp;<a href='{VAR:meta}'>{VAR:LC_GRAPH_META}</a>&nbsp;
		
	</b></td>
	</tr>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;{VAR:LC_GRAPH_NOTE1}</td>
	</tr>
	<tr><td>&nbsp;</tr></td>
<tr><td>

<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td bgcolor="#CCCCCC">
		<FORM NAME=ff METHOD=POST ACTION="reforb.aw">
		<TABLE border=0>
		<TR>
			<TD colspan=2 class="fcaption2" colspan=1> <b>"{VAR:name}"</b> {VAR:LC_GRAPH_CONF}:
		</TR>

		<TR>
			<TD class="fcaption2" colspan=1>{VAR:LC_GRAPH_TITLE}: 
			<TD class="fcaption2"><INPUT TYPE="text" NAME="setup[title]" VALUE="{VAR:title}">
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_TITLE_COLOR}: 
			<TD class="fcaption2"><INPUT TYPE="text" SIZE=6 NAME="setup[title_col]" VALUE="{VAR:title_col}">&nbsp;<a href="#" onclick="varvivalik(1);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_WIDTH}: <TD class="fcaption2"><INPUT TYPE="text" NAME="setup[width]" VALUE="{VAR:width}">
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_HIGH}: <TD class="fcaption2"><INPUT TYPE="text" NAME="setup[height]" VALUE="{VAR:height}">
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_RADIUS}: <TD class="fcaption2"><INPUT TYPE="text" NAME="setup[radius]" VALUE="{VAR:radius}">
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_BACK_COLOR}: 
			<TD class="fcaption2"><INPUT TYPE="text" SIZE=6 NAME="setup[bgcolor]" VALUE="{VAR:bgcolor}">&nbsp;<a href="#" onclick="varvivalik(5);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_PIEPRO}: 
			<TD class="fcaption2"><INPUT TYPE="checkbox" NAME="setup[percentage]" {VAR:percentage} >
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_SHREPRE}: 
			<TD class="fcaption2"><INPUT TYPE="checkbox" NAME="setup[showlabels]" {VAR:showlabels}>
		</TR>

		</TABLE></table><BR>
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td bgcolor="#CCCCCC">
		<TABLE BORDER=0>
		<TR>
			<TD class="fcaption2" colspan=2>{VAR:LC_GRAPH_DATA}: 
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_VALUES}:
			<TD class="fcaption2"><INPUT SIZE=60 TYPE="text" NAME="data[data]" VALUE="{VAR:data}">
		</TR>
		<TR>
			<TD class="fcaption2">{VAR:LC_GRAPH_REPRE}:
			<TD class="fcaption2"><INPUT SIZE=60 TYPE="text" NAME="data[labels]" VALUE="{VAR:labels}">
		</TR>
		</TABLE>
		<input type="submit" name="Submit" value="{VAR:LC_GRAPH_{VAR:LC_GRAPH_SAVE}}">
	</table>
		{VAR:reforb}
		</FORM>
</table>
