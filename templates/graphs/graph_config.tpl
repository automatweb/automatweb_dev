
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
  aken=window.open("colorpicker.{VAR:ext}","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}
// -->
</script>

<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>{VAR:LC_GRAPH_GRAPH1}:&nbsp;<a href={VAR:prev}>{VAR:LC_GRAPH_PREW}</a>&nbsp;|&nbsp;<a href={VAR:meta}>{VAR:LC_GRAPH_META}</a>&nbsp;
		<!-- SUB: CHANGE -->
		|&nbsp;<a href='{VAR:userdata}'>&nbsp;{VAR:LC_GRAPH_INCH}</a>
		<!-- END SUB: CHANGE -->
		
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
<form method="post" action="reforb.{VAR:ext}" name=ff>
<table border="0" cellspacing=1>
	<tr>
		<td class="fcaption2" colspan=4 align=center> <b>"{VAR:name}"</b> {VAR:LC_GRAPH_CONF}
	<tr>
	<tr>
		<td class="fcaption2" colspan=4 align=center>&nbsp;
	<tr>

		<td class="fcaption2">{VAR:LC_GRAPH_TITLE}:</td>
		<td class="fcaption2" colspan=3>
		<input type="text" name="setup[title]" size=40 value="{VAR:title}">
		</td>
	<tr>
		<td class="fcaption2">{VAR:LC_GRAPH_TITLE_COLOR}:</td>
		<td class="fcaption2">#<input type="text" size=6 name="setup[title_col]" value="{VAR:title_col}"><a href="#" onclick="varvivalik(1);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</td>
		<td class="fcaption2">{VAR:LC_GRAPH_BACK_COLOR}:</td>
		<td class="fcaption2">#<input type="text" size=6 name="setup[back_col]" value="{VAR:back_col}"><a href="#" onclick="varvivalik(2);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption2">{VAR:LC_GRAPH_HIGH}: </td>
		<td class="fcaption2"><input type="text" size=3 name="setup[heigth]" value="{VAR:gr_height}">
		</td>
		<td class="fcaption2">{VAR:LC_GRAPH_WIDTH}: </td>
		<td class="fcaption2"><input type="text" size=3 name="setup[width]" value="{VAR:width}">
		</td>
	</tr>
	<tr>
		<td class="fcaption2"> {VAR:LC_FRAME_BLAA}: </td>
		<td class="fcaption2"><input type="text" size=3 name="setup[frame]" value="{VAR:frame}">
		</td>
		<td class="fcaption2"> {VAR:LC_GRAPH_INS_BLAA}: </td>
		<td class="fcaption2"><input type="text" size=3 name="setup[inside]" value="{VAR:inside}">
		</td>
	</tr>
	<tr>
		<td class="fcaption2">Y {VAR:LC_GRAPH_AX_TEXT}: </td>
		<td class="fcaption2"><input type="text" size=20 name="setup[y_axis_text]" value="{VAR:y_axis_text}">
		</td>
		<td class="fcaption2">Y {VAR:LC_GRAPH_AX_COLOR}:</td>
		<td class="fcaption2">#<input type="text" size=6 name="setup[y_axis_col]" value="{VAR:y_axis_col}"><a href="#" onclick="varvivalik(8);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption2" colspan=2>{VAR:LC_GRAPH_Y_MAXMIN}:&nbsp;</td>
		<td class="fcaption2" colspan=2><input type="checkbox" name="setup[show_y_val]" {VAR:y}>
		</td>
	<tr>
		<td class="fcaption2">X {VAR:LC_GRAPH_AX_TEXT}: </td>
		<td class="fcaption2"><input type="text" size=20 name="setup[x_axis_text]" value="{VAR:x_axis_text}">
		</td>
		<td class="fcaption2">X {VAR:LC_GRAPH_AX_COLOR}:</td>
		<td class="fcaption2">#<input type="text" size=6 name="setup[x_axis_col]" value="{VAR:x_axis_col}"><a href="#" onclick="varvivalik(11);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption2">{VAR:LC_GRAPH_Y_INCH}:</td>
		<td class="fcaption2"><input type="text" size=2 name="setup[y_grid]" value="{VAR:y_grid}">
		</td>
		<td class="fcaption2">{VAR:LC_GRAPH_GRID_COLOR}:</td>
		<td class="fcaption2">#<input type="text" size=6 name="setup[y_grid_col]" value="{VAR:y_grid_col}"><a href="#" onclick="varvivalik(13);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption2" colspan=2>{VAR:LC_GRAPH_GRID_INCH}:</td>
		<td class="fcaption2" colspan=2><input type="checkbox" name="setup[show_grid_val]" {VAR:g}>
		</td>
	</tr>
	<tr>
		<td  class="fcaption2">{VAR:LC_GRAPH_DATA_COLOR}:</td>
		<td colspan=3 class="fcaption2">#<input type="text" size=6 name="setup[fir_col]" value="{VAR:fir_col}"><a href="#" onclick="varvivalik(15);">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a> {VAR:LC_GRAPH_NOTE2}</td>
	</tr>
</table>
<table>
<tr>
	<td class="fcaption2"><input type="submit" value="{VAR:LC_GRAPH_SAVE}"></td>
</table>
{VAR:reforb}
</form>
</table>
</table>