<script language='javascript'>

el = "fgcolor";

function varv(vrv) 
{
	if (el == "fgcolor")
		document.cssedit.fgcolor.value="#"+vrv;
	else
	if (el == "bgcolor")
		document.cssedit.bgcolor.value="#"+vrv;
} 

function varvivalik(which) 
{
	el = which;
	aken=window.open("/vv.html","varvivalik","HEIGHT=220,WIDTH=310")
 	aken.focus()
}
</script>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>CSS Editor:&nbsp;
<b><a href="{VAR:link_sys_styles}">Süsteemsed stiilid</a> | <a href="{VAR:link_my_styles}">Minu stiilid</a> |
<a href="javascript:document.cssedit.submit()"><font color="red">Salvesta</font></b>
</td>
</tr>
</table>
<br>
<table border="0" cellspacing="0" cellpadding="0">
<form method="POST" action="{VAR:baseurl}/automatweb/reforb.{VAR:ext}" name="cssedit">
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td class="fgtext">Nimi:</td>
<td class="fgtext"><input type="text" name="name" size="30" value="{VAR:name}"</td>
</tr>
<tr>
<td class="fgtext" valign="top">Font</td>
<td class="fgtext">
<table border="0" cellspacing="0" cellpadding="2" width="100%">
<!-- SUB: family -->
<tr>
<td class="fgtext" align="center"><input type="radio" name="ffamily" value="{VAR:ffamily}" {VAR:fchecked}></td>
<td style="font-family: {VAR:family}; font-size: 12px">{VAR:family}</td>
</tr>
<!-- END SUB: family -->
</table>
</td>
</tr>
<tr>
<td class="fgtext">Stiil</td>
<td class="fgtext">
<input type="checkbox" name="italic" value="1" {VAR:italic}><i>Italic</i> |
<input type="checkbox" name="bold" value="1" {VAR:bold}><b>Bold</b> |
<input type="checkbox" name="underline" value="1" {VAR:underline}><u>Underline</u>
</td>
</tr>
<tr>
<td class="fgtext">Suurus</td>
<td class="fgtext"><input type="text" name="size" size="4" maxlength="4" value="{VAR:size}">
<select name="units">
{VAR:units}
</select>
</td>
</tr>
<tr>
<td class="fgtext">Teksti värv</td>
<td class="fgtext"><input type="text" name="fgcolor" size="7" maxlength="7" value="{VAR:fgcolor}">
&nbsp;
<a href="javascript:varvivalik('fgcolor')">Vali</a></td>
</tr>
<tr>
<td class="fgtext">Tausta värv</td>
<td class="fgtext"><input type="text" name="bgcolor" size="7" maxlength="7" value="{VAR:bgcolor}">
&nbsp;
<a href="javascript:varvivalik('bgcolor')">Vali</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
