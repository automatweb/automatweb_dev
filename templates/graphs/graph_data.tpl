<script language="JavaScript">
<!--
function uusAken()  
{
akn = window.open("/templates/graphs/graph_def.html", "mhh", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=470,height=500");}

var kuhu = 0;

function set_color(vrv) 
{
	document.data.elements[kuhu].value=vrv;
} 

function varvivalik(nr)
{
	kuhu=nr;
	aken=window.open("colorpicker.{VAR:ext}","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}
// -->
</script>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>{VAR:LC_GRAPH_GRAPH1}:&nbsp;<a href='{VAR:conf}'>{VAR:LC_GRAPH_TO_CONFIG}</a>
		|&nbsp;<a href='{VAR:preview}'>{VAR:LC_GRAPH_PREW}</a>	
		</b></td>
	</tr>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;{VAR:LC_GRAPH_NOTE1}</td>
	</tr>
	<tr><td>&nbsp;</tr></td>
	<tr><td>
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td bgcolor="#CCCCCC"><form method="post" action="reforb.aw" name="data">
	  <table width="100%" border="0" cellspacing=1>
	    <tr>
	      <td class="fcaption" colspan="5" align="center">{VAR:LC_GRAPH_TABLES} &quot;<B>{VAR:name}</B>&quot; {VAR:LC_GRAPH_SMALL_DATA}</td>
	    </tr>
	    <tr> 
	      <td class="fcaption" colspan="5"> 
		<p>{VAR:LC_GRAPH_INSERT_DATA_BY_SEP_COMMA} (ntx: X: &quot;Jan,Veb,Mar,...&quot; 
		  Y: &quot;12,12,34,56,...&quot;). {VAR:LC_GRAPH_CANT_INSERT_NEG_VALUES}.</p>
	      </td>
	    </tr>
	    <tr> 
	      <td class="fcaption" width="10%">X {VAR:LC_GRAPH_SMALL_DATA}</td>
	      <td class="fcaption" colspan="4" width="76%"> 
		<input type="text" name="arr[x]" size="70" value={VAR:xdata}>
	      </td>
	    </tr>
	<!-- SUB: LINE -->
	    <tr> 
	      <td class="fcaption" width="10%">Y_{VAR:y_nr} {VAR:LC_GRAPH_SMALL_DATA}</td>
	      <td class="fcaption"> 
		<input type="text" name="arr[y{VAR:y_nr}]" size="70" value={VAR:ydata}>
	      </td>
	      <td class="fcaption" > 
	      {VAR:LC_GRAPH_COLOR}:&nbsp; 
	      </td>
	      <td class="fcaption" > 
	      <input type="text" name="arr[yc{VAR:y_nr}]" size="6" value={VAR:ycolor}><a href="#" onclick="varvivalik({VAR:f_nr});">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
	      </td>
	    </tr>
	<!-- END SUB: LINE -->
	  </table>
	  <input type="submit" name="Submit" value="Salvesta">
	</td></tr></table>
	{VAR:reforb}
	</form>
	<tr><td>&nbsp;</td></tr>
	<tr><td>
		<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}'>
		<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
		<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
			<tr><td colspan=2 class="fcaption" >{VAR:LC_GRAPH_UPLOAD_CSV}:</td></tr>
			<tr>
			<td class="fcaption">{VAR:LC_GRAPH_CHOOSE_FILE}</td>
			<td class="fform"><input type="file" size="40" name="userfile"></td>
			</tr>
			<tr>
			<td class="fform" colspan="2" align="center">
			{VAR:upload}
			<input type="submit" value="Impordi">
			</td>
			</tr>
		</table>
		</form>
	</td></tr></table>
</table>