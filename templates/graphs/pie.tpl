<script language="JavaScript">
<!--

function set_color(vrv) 
{
	document.ff.elements[element].value=vrv;
} 

function varvivalik(nr)
{
	element = nr
	aken=window.open("colorpicker.{VAR:ext}","varvivalik","HEIGHT=220,WIDTH=310");
 	aken.focus();
}
// -->
</script>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>GRAAFIK:&nbsp;
		<a href='{VAR:prev}'>Eelvaade</a>&nbsp;|&nbsp;<a href='{VAR:meta}'>Meta informatsioon</a>&nbsp;
		
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
		<FORM NAME=ff METHOD=POST ACTION="reforb.aw">
		<TABLE border=0>
		<TR>
			<TD colspan=2 class="fcaption" colspan=1>Graafiku <b>"{VAR:name}"</b> seaded:
		</TR>

		<TR>
			<TD class="fcaption" colspan=1>Graafiku pealkiri: 
			<TD class="fcaption"><INPUT TYPE="text" NAME="setup[title]" VALUE="{VAR:title}">
		</TR>
		<TR>
			<TD class="fcaption">Pealkirja v‰rv: 
			<TD class="fcaption"><INPUT TYPE="text" SIZE=6 NAME="setup[title_col]" VALUE="{VAR:title_col}">&nbsp;<a href="#" onclick="varvivalik(1);">&nbsp;Vali&nbsp;</a>
		</TR>
		<TR>
			<TD class="fcaption">Laius: <TD class="fcaption"><INPUT TYPE="text" NAME="setup[width]" VALUE="{VAR:width}">
		</TR>
		<TR>
			<TD class="fcaption">Kırgus: <TD class="fcaption"><INPUT TYPE="text" NAME="setup[height]" VALUE="{VAR:height}">
		</TR>
		<TR>
			<TD class="fcaption">Raadius: <TD class="fcaption"><INPUT TYPE="text" NAME="setup[radius]" VALUE="{VAR:radius}">
		</TR>
		<TR>
			<TD class="fcaption">Taustav‰rv: 
			<TD class="fcaption"><INPUT TYPE="text" SIZE=6 NAME="setup[bgcolor]" VALUE="{VAR:bgcolor}">&nbsp;<a href="#" onclick="varvivalik(5);">&nbsp;Vali&nbsp;</a>
		</TR>
		<TR>
			<TD class="fcaption">N‰itan piruka peal protsente: 
			<TD class="fcaption"><INPUT TYPE="checkbox" NAME="setup[percentage]" {VAR:percentage} >
		</TR>
		<TR>
			<TD class="fcaption">N‰itan kirjeldusi: 
			<TD class="fcaption"><INPUT TYPE="checkbox" NAME="setup[showlabels]" {VAR:showlabels}>
		</TR>

		</TABLE></table><BR>
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td bgcolor="#CCCCCC">
		<TABLE BORDER=0>
		<TR>
			<TD class="fcaption" colspan=2>Andmed: 
		</TR>
		<TR>
			<TD class="fcaption">V‰‰rtused:
			<TD class="fcaption"><INPUT SIZE=60 TYPE="text" NAME="data[data]" VALUE="{VAR:data}">
		</TR>
		<TR>
			<TD class="fcaption">Kirjeldused:
			<TD class="fcaption"><INPUT SIZE=60 TYPE="text" NAME="data[labels]" VALUE="{VAR:labels}">
		</TR>
		</TABLE>
		<input type="submit" name="Submit" value="Salvesta">
	</table>
		{VAR:reforb}
		</FORM>
</table>
