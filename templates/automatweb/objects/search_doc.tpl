<br>
<form method="GET" action="pickobject.{VAR:ext}">
<table border="0" cellspacing="0" cellpadding="0" >
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
	<td colspan=2 class="title">Search objects | <a href='pickobject.{VAR:ext}?type=search&docid={VAR:docid}'>List of objects</a></td>
</tr>
<tr>
	<td class="fcaption2">Search from name:</td>
	<td class="fform" width=70%><input type="text" name="s_name" size="40" value='{VAR:s_name}'></td>
</tr>
<tr>
	<td class="fcaption2">Search from comments:</td>
	<td class="fform" width=70%><input type="text" name="s_comment" size="40" value='{VAR:s_comment}'></td>
</tr>
<tr>
	<td class="fcaption2">Objects type:</td>
	<td class="fform" width=70%><select name='s_type'>{VAR:types}</select></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Search"></td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="2"  width=100%>
<tr>
	<td class="fcaption2" colspan=8>Found objects:</td>
</tr>
<tr>
	<td class="fcaption2">Name</td>
	<td class="fcaption2" nowrap>&nbsp;Type&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Created&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Creator&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Changed&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Changer&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Parent&nbsp;</td>
	<td class="fform" nowrap>&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2">{VAR:name}</td>
	<td class="fcaption2" nowrap>{VAR:type}</td>
	<td class="fcaption2" nowrap>{VAR:created}</td>
	<td class="fcaption2" nowrap>{VAR:createdby}</td>
	<td class="fcaption2" nowrap>{VAR:modified}</td>
	<td class="fcaption2" nowrap>{VAR:modifiedby}</td>
	<td class="fcaption2" nowrap>{VAR:parent_name}</td>
	<td class="fform" nowrap>{VAR:pickurl}</td>
</tr>
<!-- END SUB: LINE -->
</table>
<input type='hidden' name='docid' value='{VAR:docid}'>
</form>
