<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td class="title" height="15">Otsi: 
<!-- SUB: SHOWALL -->
&nbsp;|&nbsp;<a href="{VAR:l_showall}">Näita kõiki<a/>
<!-- END SUB: SHOWALL -->
</td></tr>
<tr>
<td class="fgtext">
{VAR:SEARCHFORM}
</td>
</tr>

<tr><td class="fgtext" height="20"></td></tr>
<td class="title" height="15">Tulemused: </td></tr>

<tr>
<td bgcolor="#CCCCCC">

<script Language="JavaScript">

function SFolder(what,fel)
{
 for (i=0; i<foo.elements.length; i++)
 if (foo.elements[i].style.jura == what)
 {
  foo.elements[i].checked= eval("foo."+fel+".checked");
 };
};
</script>
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>
<table border="0" cellspacing="1" cellpadding="0" width=100%>


<tr>
<td height="15" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" class="title">&nbsp;Tüüp&nbsp;</td>
<td align="center" class="title">&nbsp;Vali&nbsp;</td>
</tr>

<!-- SUB: FOLDER -->
<tr>
<td height="15" class="fgtext2" colspan="3">&nbsp;{VAR:name}&nbsp;</td>
<td align="center" class="fgtext2" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="checkbox" OnClick="javascript:SFolder('{VAR:pid}','F_{VAR:pid}')" NAME="F_{VAR:pid}">&nbsp;</td>
</tr>
{VAR:LINEX}
<!-- END SUB: FOLDER -->
<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">&nbsp;<a href="{VAR:chlink}" target="_blank">{VAR:name}</a>&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:changedby}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="checkbox" NAME="sel[]" VALUE="{VAR:id}" ID="_{VAR:pid}_{VAR:id}" Style="jura={VAR:pid};">&nbsp;</td>
</tr>
<!-- END SUB: LINE -->


</table>
</td>
</tr>
<tr>
<td>
<!-- SUB: SUBMIT -->
<input type="SUBMIT" VALUE="Lisa" class="small_button">
<!-- END SUB: SUBMIT -->
</td>
</tr>
{VAR:reforb}
</form>
</table>

