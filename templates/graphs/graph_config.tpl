
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
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>GRAAFIK:&nbsp;<a href={VAR:prev}>Eelvaade</a>&nbsp;|&nbsp;<a href={VAR:meta}>Meta informatsioon</a>&nbsp;
		<!-- SUB: CHANGE -->
		|&nbsp;<a href='{VAR:userdata}'>&nbsp;Sisesta/Muuda Andmeid</a>
		<!-- END SUB: CHANGE -->
		
	</b></td>
	</tr>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;M‰rkus: Enne eelvaadet tuleks graafiku seaded salvestada</td>
	</tr>
	<tr><td>&nbsp;</tr></td>
<tr><td>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">
<form method="post" action="reforb.{VAR:ext}" name=ff>
<table border="0" cellspacing=1>
	<tr>
		<td class="fcaption" colspan=4 align=center>Graafiku: <b>"{VAR:name}"</b> seaded
	<tr>
	<tr>
		<td class="fcaption" colspan=4 align=center>&nbsp;
	<tr>

		<td class="fcaption">Graafiku pealkiri:</td>
		<td class="fcaption" colspan=3>
		<input type="text" name="setup[title]" size=40 value="{VAR:title}">
		</td>
	<tr>
		<td class="fcaption">Pealkirja v&auml;rv:</td>
		<td class="fcaption">#<input type="text" size=6 name="setup[title_col]" value="{VAR:title_col}"><a href="#" onclick="varvivalik(1);">&nbsp;Vali&nbsp;</a>
		</td>
		<td class="fcaption">Tausta v&auml;rv:</td>
		<td class="fcaption">#<input type="text" size=6 name="setup[back_col]" value="{VAR:back_col}"><a href="#" onclick="varvivalik(2);">&nbsp;Vali&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption">K&otilde;rgus: </td>
		<td class="fcaption"><input type="text" size=3 name="setup[heigth]" value="{VAR:gr_height}">
		</td>
		<td class="fcaption">Laius: </td>
		<td class="fcaption"><input type="text" size=3 name="setup[width]" value="{VAR:width}">
		</td>
	</tr>
	<tr>
		<td class="fcaption"> Raami laius: </td>
		<td class="fcaption"><input type="text" size=3 name="setup[frame]" value="{VAR:frame}">
		</td>
		<td class="fcaption"> Sisemine laius: </td>
		<td class="fcaption"><input type="text" size=3 name="setup[inside]" value="{VAR:inside}">
		</td>
	</tr>
	<tr>
		<td class="fcaption">Y telje text: </td>
		<td class="fcaption"><input type="text" size=20 name="setup[y_axis_text]" value="{VAR:y_axis_text}">
		</td>
		<td class="fcaption">Y telje teksti v&auml;rv:</td>
		<td class="fcaption">#<input type="text" size=6 name="setup[y_axis_col]" value="{VAR:y_axis_col}"><a href="#" onclick="varvivalik(8);">&nbsp;Vali&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption" colspan=2>N‰itan Y teljel max ja min v‰‰rtusi:&nbsp;</td>
		<td class="fcaption" colspan=2><input type="checkbox" name="setup[show_y_val]" {VAR:y}>
		</td>
	<tr>
		<td class="fcaption">X telje text: </td>
		<td class="fcaption"><input type="text" size=20 name="setup[x_axis_text]" value="{VAR:x_axis_text}">
		</td>
		<td class="fcaption">X telje teksti v&auml;rv:</td>
		<td class="fcaption">#<input type="text" size=6 name="setup[x_axis_col]" value="{VAR:x_axis_col}"><a href="#" onclick="varvivalik(11);">&nbsp;Vali&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption">Y telje &uuml;hikute arv:</td>
		<td class="fcaption"><input type="text" size=2 name="setup[y_grid]" value="{VAR:y_grid}">
		</td>
		<td class="fcaption">Y telje gridi v‰‰rtuste v&auml;rv:</td>
		<td class="fcaption">#<input type="text" size=6 name="setup[y_grid_col]" value="{VAR:y_grid_col}"><a href="#" onclick="varvivalik(13);">&nbsp;Vali&nbsp;</a>
		</td>
	</tr>
	<tr>
		<td class="fcaption" colspan=2>N&auml;itan gridil v&auml;&auml;rtusi:</td>
		<td class="fcaption" colspan=2><input type="checkbox" name="setup[show_grid_val]" {VAR:g}>
		</td>
	</tr>
	<tr>
		<td  class="fcaption">Andmete v‰rv:</td>
		<td colspan=3 class="fcaption">#<input type="text" size=6 name="setup[fir_col]" value="{VAR:fir_col}"><a href="#" onclick="varvivalik(15);">&nbsp;Vali&nbsp;</a> M‰rkus: Ei kasutata kasutaja andmetega graafiku puhul</td>
	</tr>
</table>
<table>
<tr>
	<td><input type="submit" value="Salvesta"></td>
</table>
{VAR:reforb}
</form>
</table>
</table>