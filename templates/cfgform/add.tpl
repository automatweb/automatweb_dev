<form name="clform" method="POST" action="reforb.{VAR:ext}">
{VAR:toolbar}
<fieldset>
<legend class="fgtext"><b>Objekti andmed</b><legend>
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td class="fgtext">Nimi</td>
<td class="fgtext" colspan="2"><input type="text" name="name" size="40" value="{VAR:name}"></td>
</tr>
<tr>
<td class="fgtext">Kommentaar</td>
<td class="fgtext" colspan="2"><input type="text" name="comment" size="40" value="{VAR:comment}"></td>
</tr>
</table>
</fieldset>
<fieldset>
<legend class="fgtext"><font color='red'><strong>Vali väljad, mida see konfivorm sisaldab</strong></font></legend>
<!-- SUB: class_container -->
<fieldset>
<legend class="fgtext"><b>{VAR:cname}</b></legend>
<table border="0" cellspacing="1" cellpadding="2">
<!-- SUB: line -->
<tr>
<td class="fgtext">{VAR:pname}</td>
<td class="fgtext"><input type="checkbox" name="properties[{VAR:clid}][{VAR:pkey}]" value="1" {VAR:checked}></td>
</tr>
<!-- END SUB: line -->
</table>
</fieldset>
<!-- END SUB: class_container -->
</fieldset>
{VAR:reforb}
</form>
