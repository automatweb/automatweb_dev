<form name="clform" method="POST" action="reforb.{VAR:ext}">
{VAR:toolbar}
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td class="fgtext">Nimi</td>
<td class="fgtext" colspan="2"><input type="text" name="name" size="40" value="{VAR:name}"></td>
</tr>
<tr>
<td class="fgtext">Kommentaar</td>
<td class="fgtext" colspan="2"><input type="text" name="comment" size="40" value="{VAR:comment}"></td>
</tr>
<tr>
<td class="fgtext" colspan="3"><strong><font color='red'>Vali väljad, mida see konfivorm sisaldab</font></strong></td>
</tr>
<!-- SUB: cline -->
<tr>
<td class="fgtext" colspan="3"><big><strong>{VAR:cname}</strong></big></td>
</tr>
<!-- END SUB: line -->
<!-- SUB: line -->
<tr>
<td>&nbsp;</td>
<td class="fgtext">{VAR:pname}</td>
<td class="fgtext"><input type="checkbox" name="properties[{VAR:clid}][{VAR:pkey}]" value="1" {VAR:checked}></td>
</tr>
<!-- END SUB: line -->
</table>
{VAR:reforb}
</form>
