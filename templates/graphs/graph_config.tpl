
<script language="JavaScript">
<!--
function uusAken()  
{
akn = window.open("graph_def", "mhh", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=470,height=500");}

var kuhu = 0;
function set_color(vrv) 
{
	document.ff.elements[kuhu].value=vrv;
} 

function varvivalik(nr)
{
	kuhu = nr;
  aken=window.open("orb.aw?class=css&action=colorpicker","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}
// -->
</script>


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
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="middle"><a href="{VAR:prev}">{VAR:LC_GRAPH_PREW}</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>


														<td class="tab"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="middle"><a href={VAR:meta}>{VAR:LC_GRAPH_META}</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>


														<!-- SUB: CHANGE -->
														<td class="tab"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="middle"><a href='{VAR:userdata}'>&nbsp;{VAR:LC_GRAPH_INCH}</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>
														<!-- END SUB: CHANGE -->
														

														<!-- SEL_PAGE
														<td class="tabsel"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom">{VAR:page}</td><td class="tabsel"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>
														END SEL_PAGE -->
													</tr>
												</table>
</td>
<td class="celltext">
&nbsp;&nbsp;&nbsp;

</td>


</tr></table>

</td></tr></table>
</td></tr></table>
<span class="celltext"><font color="darkred">{VAR:LC_GRAPH_NOTE1}</font></span></td>
</tr></table>


<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr>
<td bgcolor="#FFFFFF">


<table border="0" cellspacing="0" cellpadding="2" width="100%">
<tr>
<td class="aste01">

<table border="0" cellspacing="5" cellpadding="2">
	<tr>
		<td class="celltext" colspan=4 align=center> <b>"{VAR:name}"</b> {VAR:LC_GRAPH_CONF} </td>
	</tr>

	
	<tr>

		<td class="celltext" align="right">{VAR:LC_GRAPH_TITLE}:</td>
		<td class="celltext" colspan=3><input type="text" name="setup[title]" size=40 value="{VAR:title}" class="formtext"></td>
	</tr>
	<tr>
		<td class="celltext" align="right">{VAR:LC_GRAPH_TITLE_COLOR}:</td>
		<td class="celltext">#<input type="text" size=6 name="setup[title_col]" value="{VAR:title_col}" class="formtext"><a href="#" onclick="varvivalik(1);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</td>
		<td class="celltext" align="right">{VAR:LC_GRAPH_BACK_COLOR}:</td>
		<td class="celltext">#<input type="text" size=6 name="setup[back_col]" value="{VAR:back_col}" class="formtext"><a href="#" onclick="varvivalik(2);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="celltext" align="right">{VAR:LC_GRAPH_HIGH}: </td>
		<td class="celltext"><input type="text" size=3 name="setup[heigth]" value="{VAR:gr_height}" class="formtext">
		</td>
		<td class="celltext" align="right">{VAR:LC_GRAPH_WIDTH}: </td>
		<td class="celltext"><input type="text" size=3 name="setup[width]" value="{VAR:width}" class="formtext">
		</td>
	</tr>
	<tr>
		<td class="celltext" align="right"> {VAR:LC_FRAME_BLAA}: </td>
		<td class="celltext"><input type="text" size=3 name="setup[frame]" value="{VAR:frame}" class="formtext">
		</td>
		<td class="celltext" align="right"> {VAR:LC_GRAPH_INS_BLAA}: </td>
		<td class="celltext"><input type="text" size=3 name="setup[inside]" value="{VAR:inside}" class="formtext">
		</td>
	</tr>
	<tr>
		<td class="celltext" align="right">Y {VAR:LC_GRAPH_AX_TEXT}: </td>
		<td class="celltext"><input type="text" size=20 name="setup[y_axis_text]" value="{VAR:y_axis_text}" class="formtext"></td>
		<td class="celltext" align="right">Y {VAR:LC_GRAPH_AX_COLOR}:</td>
		<td class="celltext">#<input type="text" size=6 name="setup[y_axis_col]" value="{VAR:y_axis_col}" class="formtext"><a href="#" onclick="varvivalik(8);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a></td>
	</tr>
	<tr>
		<td class="celltext" colspan=4>{VAR:LC_GRAPH_Y_MAXMIN}:&nbsp;<input type="checkbox" name="setup[show_y_val]" {VAR:y}></td>
	</tr>
	<tr>
		<td class="celltext" align="right">X {VAR:LC_GRAPH_AX_TEXT}: </td>
		<td class="celltext"><input type="text" size=20 name="setup[x_axis_text]" value="{VAR:x_axis_text}" class="formtext"></td>
		<td class="celltext" align="right">X {VAR:LC_GRAPH_AX_COLOR}:</td>
		<td class="celltext">#<input type="text" size=6 name="setup[x_axis_col]" value="{VAR:x_axis_col}" class="formtext"><a href="#" onclick="varvivalik(11);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a></td>
	</tr>
	<tr>
		<td class="celltext" align="right">{VAR:LC_GRAPH_Y_INCH}:</td>
		<td class="celltext"><input type="text" size=2 name="setup[y_grid]" value="{VAR:y_grid}" class="formtext"></td>
		<td class="celltext" align="right">{VAR:LC_GRAPH_GRID_COLOR}:</td>
		<td class="celltext">#<input type="text" size=6 name="setup[y_grid_col]" value="{VAR:y_grid_col}" class="formtext"><a href="#" onclick="varvivalik(13);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a></td>
	</tr>
	<tr>
		<td class="celltext" colspan=4>{VAR:LC_GRAPH_GRID_INCH}:&nbsp;<input type="checkbox" name="setup[show_grid_val]" {VAR:g}></td>
	</tr>
	<tr>
		<td  class="celltext" align="right">{VAR:LC_GRAPH_DATA_COLOR}:</td>
		<td colspan=3 class="celltext">#<input type="text" size=6 name="setup[fir_col]" value="{VAR:fir_col}" class="formtext"><a href="#" onclick="varvivalik(15);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;&nbsp;&nbsp;</a> <font color="darkred">{VAR:LC_GRAPH_NOTE2}</font></td>
	</tr>
	<!--<tr>
	     <td></td>
			<td class="celltext"><input type="submit" value="{VAR:LC_GRAPH_SAVE}"></td>
	</tr>-->

</table>




{VAR:reforb}

</td></tr></table>
</td></tr></form></table>
