<script type="text/javascript">
function show_search()
{
	window.location = '{VAR:search_url}';
};
</script>
<form name="clform" method="POST" action="reforb.{VAR:ext}">
{VAR:toolbar}
<fieldset>
<legend accesskey=x class="fgtext"><strong>Objekti andmed</strong></legend>
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td class="fgtext">Nimi</td>
<td class="fgtext"><input type="text" name="name" size="40" value="{VAR:name}">
</td>
</tr>
<tr>
<td class="fgtext">Kommentaar</td>
<td class="fgtext"><input type="text" name="comment" size="40" value="{VAR:comment}">
</td>
</tr>
<tr>
<td class="fgtext">Prioriteet</td>
<td class="fgtext"><input type="text" name="priority" size="4" value="{VAR:priority}">
</td>
</tr>
</table>
</fieldset>
<!-- SUB: class_container -->
<fieldset>
<legend class="fgtext"><strong>{VAR:clname}<strong></legend>
<table border="0" cellspacing="0" cellpadding="0"
<tr>
<td valign="top" width="300">
<table border="0" cellspacing="1" cellpadding="2">
<!-- SUB: line -->
<tr>
<td class="fgtext" width="250">{VAR:pname}</td>
<td class="fgtext">
	{VAR:el}
</td>
</tr>
<!-- END SUB: line -->
</table>
</td>
<td width="50">
&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;
</td>
<td valign="top">
<!-- objektide tabel algab -->
<table border="0" cellspacing="1" width="400" cellpadding="2" bgcolor="#CCCCCC">
<!-- SUB: objline -->
<tr>
<td class="fgtext">
{VAR:oid}
</td>
<td class="fgtext" width="190">
{VAR:name}
</td>
<td class="fgtext">
{VAR:modified}
</td>
<td class="fgtext">
{VAR:modifiedby}
</td>
</tr>
<!-- END SUB: objline -->
</table>
<!-- objektide tabel lõpeb -->
</td>
</tr>
</table>
</fieldset>
<!-- END SUB: class_container -->
</fieldset>
{VAR:reforb}
</form>
