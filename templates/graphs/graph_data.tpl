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
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>GRAAFIK:&nbsp;<a href='{VAR:conf}'>Konfima</a>
		|&nbsp;<a href='{VAR:preview}'>Eelvaade</a>	
		</b></td>
	</tr>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;M‰rkus: Enne eelvaadet tuleks graafiku seaded salvestada</td>
	</tr>
	<tr><td>&nbsp;</tr></td>
	<tr><td>
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td bgcolor="#CCCCCC"><form method="post" action="reforb.aw" name="data">
	  <table width="100%" border="0" cellspacing=1>
	    <tr>
	      <td class="fcaption" colspan="5" align="center">Tabeli &quot;<B>{VAR:name}</B>&quot; andmed</td>
	    </tr>
	    <tr> 
	      <td class="fcaption" colspan="5"> 
		<p>Sisesta andmed eraldades need komaga (ntx: X: &quot;Jan,Veb,Mar,...&quot; 
		  Y: &quot;12,12,34,56,...&quot;). Sisestada ei tohiks negatiivseid v‰‰rtusi.</p>
	      </td>
	    </tr>
	    <tr> 
	      <td class="fcaption" width="10%">X andmed</td>
	      <td class="fcaption" colspan="4" width="76%"> 
		<input type="text" name="arr[x]" size="70" value={VAR:xdata}>
	      </td>
	    </tr>
	<!-- SUB: LINE -->
	    <tr> 
	      <td class="fcaption" width="10%">Y_{VAR:y_nr} andmed</td>
	      <td class="fcaption"> 
		<input type="text" name="arr[y{VAR:y_nr}]" size="70" value={VAR:ydata}>
	      </td>
	      <td class="fcaption" > 
	      V‰rv:&nbsp; 
	      </td>
	      <td class="fcaption" > 
	      <input type="text" name="arr[yc{VAR:y_nr}]" size="6" value={VAR:ycolor}><a href="#" onclick="varvivalik({VAR:f_nr});">&nbsp;Vali&nbsp;</a>
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
			<tr><td colspan=2 class="fcaption" >Uploadi CSV file graafiku andmeteks:</td></tr>
			<tr>
			<td class="fcaption">Vali fail</td>
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