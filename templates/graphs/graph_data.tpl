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


<td valign="bottom">
												<table border=0 cellpadding=0 cellspacing=0>
													<tr>

														<td class="tab"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="middle"><a href="{VAR:conf}">{VAR:LC_GRAPH_CONFIG}</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>

														
														<td class="tab"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="middle"><a href="{VAR:preview}">{VAR:LC_GRAPH_PREW}</a></td><td class="tab"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>

														<td class="tabsel"><IMG SRC="images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td>
														<td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom">{VAR:LC_GRAPH_INCH}</td><td class="tabsel"><IMG SRC="images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td>

														

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

<table border=0 cellpadding=5 bgcolor="#FFFFFF" cellspacing=1>

<tr>
	<td class="aste01">




<span class="celltext"><font color="red">{VAR:LC_GRAPH_NOTE1}</font></span>


	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td><form method="post" action="reforb.aw" name="data">

	  <table  border="0" cellspacing=1>
	    <tr>
	      <td class="celltext" colspan="5" align="center">{VAR:LC_GRAPH_TABLES} &quot;<B>{VAR:name}</B>&quot; {VAR:LC_GRAPH_SMALL_DATA}</td>
	    </tr>
	    <tr> 
	      <td class="celltext" colspan="5"> 
		<p>{VAR:LC_GRAPH_INSERT_DATA_BY_SEP_COMMA} (ntx: X: &quot;Jan,Veb,Mar,...&quot; 
		  Y: &quot;12,12,34,56,...&quot;). {VAR:LC_GRAPH_CANT_INSERT_NEG_VALUES}.</p>
	      </td>
	    </tr>
	    <tr> 
	      <td class="celltext">X {VAR:LC_GRAPH_SMALL_DATA}</td>
	      <td class="celltext" colspan="4"> 
		<input type="text" name="arr[x]" size="70" value="{VAR:xdata}" class="formtext">
	      </td>
	    </tr>
	<!-- SUB: LINE -->
	    <tr> 
	      <td class="celltext">Y_{VAR:y_nr} {VAR:LC_GRAPH_SMALL_DATA}</td>
	      <td class="celltext"> 
		<input type="text" name="arr[y{VAR:y_nr}]" size="70" value="{VAR:ydata}" class="formtext">
	      </td>
	      <td class="celltext" > 
	      {VAR:LC_GRAPH_COLOR}:&nbsp; 
	      </td>
	      <td class="celltext" > 
	      <input type="text" name="arr[yc{VAR:y_nr}]" size="6" value="{VAR:ycolor}" class="formtext"><a href="#" onclick="varvivalik({VAR:f_nr});">&nbsp;{VAR:LC_GRAPH_CHOOSE}&nbsp;</a>
	      </td>
	    </tr>
	<!-- END SUB: LINE -->
		<tr>
		<td></td>
		<td><input type="submit" name="Submit" value="Salvesta" class="formbutton"></td>
		<td></td>
		<td></td>
	  </table>

	

	{VAR:reforb}
	</form>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="aste06">
		<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}'>
		<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
		<table border=0 cellspacing=1 cellpadding=2 >
			<tr><td colspan=2 class="celltext" ><b>{VAR:LC_GRAPH_UPLOAD_CSV}:</b></td></tr>
			<tr>
			<td class="celltext">{VAR:LC_GRAPH_CHOOSE_FILE}</td>
			<td class="celltext"><input type="file" size="40" name="userfile" class="formfile"></td>
			</tr>
			<tr>
			<td></td>
			<td class="celltext">
			{VAR:upload}
			<input type="submit" value="Impordi" class="formbutton">
			</td>
			</tr>
		</table>
		</form>
	</td></tr></table>






</td>
</tr>
</table>

</td></tr></table>
<br>