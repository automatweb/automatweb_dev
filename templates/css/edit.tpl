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
<style>
{VAR:styl}
</style>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>CSS Editor:&nbsp;
<b><a href="javascript:document.cssedit.submit()"><font color="red">{VAR:LC_CSS_SAVE}</font></b>
</td>
</tr>
</table>
<br>
<table border="0" cellspacing="2" cellpadding="2" width="100%">
<tr>
<td valign="top" rowspan="2">
<table border="0" cellspacing="0" cellpadding="0">
<form method="POST" action="{VAR:baseurl}/automatweb/reforb.{VAR:ext}" name="cssedit">
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td class="fgtext">{VAR:LC_CSS_NAME}:</td>
<td class="fgtext"><input type="text" name="name" size="30" value="{VAR:name}"></td>
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
<td class="fgtext">{VAR:LC_CSS_STYLE}</td>
<td class="fgtext">
<input type="checkbox" name="italic" value="1" {VAR:italic}><i>Italic</i> |
<input type="checkbox" name="bold" value="1" {VAR:bold}><b>Bold</b> |
<input type="checkbox" name="underline" value="1" {VAR:underline}><u>Underline</u>
</td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_CSS_HEIGHT}</td>
<td class="fgtext"><input type="text" name="size" size="4" maxlength="4" value="{VAR:size}">
<select name="units">
{VAR:units}
</select>
</td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_CSS_TEXT_COLOR}</td>
<td class="fgtext"><input type="text" name="fgcolor" size="7" maxlength="7" value="{VAR:fgcolor}">
&nbsp;
<a href="javascript:varvivalik('fgcolor')">{VAR:LC_CSS_CHOOSE}</a></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_CSS_BACK_COLOR}</td>
<td class="fgtext"><input type="text" name="bgcolor" size="7" maxlength="7" value="{VAR:bgcolor}">
&nbsp;
<a href="javascript:varvivalik('bgcolor')">{VAR:LC_CSS_CHOOSE}</a>
</td>
</tr>
<tr>
<td class="fgtext">Lingi stiil:</td>
<td class="fgtext"><select name='a_style'>{VAR:a_style}</select></td>
</tr>
<tr>
<td class="fgtext">Lingi stiil (hover):</td>
<td class="fgtext"><select name='a_hover_style'>{VAR:a_hover_style}</select></td>
</tr>
<tr>
<td class="fgtext">Lingi stiil (visited):</td>
<td class="fgtext"><select name='a_visited_style'>{VAR:a_visited_style}</select></td>
</tr>
<tr>
<td class="fgtext">Lingi stiil (active):</td>
<td class="fgtext"><select name='a_active_style'>{VAR:a_active_style}</select></td>
</tr>

</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
</td>
<form>
<td valign="top" bgcolor="#CCCCCC" align="center">
<font color="#FFFFFF"><big><b>PREVIEW</b></big></font>
</td>
</tr>
<tr>
<td valign="top" class="demo">
Sample text here and sample form elements below<p>
<a href="#" class="demo">{VAR:LC_CSS_SLINK}</a><p>
<input type="text" class="demo" size="30" width="30"><br>
<input class="demo" type="checkbox">&nbsp;&nbsp;
<input class="demo" type="checkbox">&nbsp;&nbsp;
<input class="demo" type="checkbox">
<p>
<input class="demo" type="radio">&nbsp;&nbsp;
<input class="demo" type="radio">&nbsp;&nbsp;
<input class="demo" type="radio">&nbsp;&nbsp;
<p>
<input type="button" class="demo" value=" Nupp ">
<p>
<select class="demo" size="3">
<option>one
<option>two
<option>three
</select>
</td>
</tr>
</form>
</table>
