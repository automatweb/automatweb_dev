<form method="POST" ACTION="refcheck.{VAR:ext}">
<input type="submit" value="Salvesta oigused">

<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
<td class="ftitle2" colspan='{VAR:colspan}'><a href='editacl.{VAR:ext}?type=addgrp&oid={VAR:oid}&file={VAR:file}'>Muuda gruppe</a></td>
</tr>
<tr>
<td class="title">Objekt</td>
{VAR:header}
</tr>
<!-- SUB: line -->
<input type="hidden" name="facl[{VAR:oid}][{VAR:gid}][gid]" value="{VAR:gid}">
<tr>
<td class="fcaption2">{VAR:name}</td>
{VAR:cline}

<input type="hidden" name="facl[{VAR:oid}][{VAR:gid}][dummy]" VALUE="dummy">
</tr>
<!-- END SUB: line -->
</table>

<input type="hidden" name="oid" value="{VAR:oid}">
<input type="hidden" name="action" value="save_acl">
<input type="hidden" name="file" value="{VAR:file}">
<input type="submit" value="Salvesta oigused">

<!-- SUB: xfield -->
<input type="hidden" name="fields[{VAR:key}]" value="1">
<!-- END SUB: xfield -->
</form>
<font face="Verdana,Arial,Helvetica,sans-serif" size="-1">
<strong>
Legend:
</strong>
</font>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<!-- SUB: help -->
<tr>
<td class="ftitle2">
	{VAR:caption}
</td>
<td class="fcaption2">
	{VAR:help}
</td>
</tr>
<!-- END SUB: help -->
</table>
