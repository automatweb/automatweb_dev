<script type="text/javascript">
function show_search()
{
	window.location = '{VAR:search_url}';
};
</script>
<form name="clform" method="POST" action="reforb.{VAR:ext}">
{VAR:toolbar}
<fieldset>
<legend class="fgtext"><strong>Objekti andmed</strong></legend>
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
<table border="0" cellspacing="1" cellpadding="2">
<!-- SUB: line -->
<tr>
<td class="fgtext">{VAR:pname}</td>
<td class="fgtext"><input type='checkbox' name='properties[{VAR:clid}][{VAR:pkey}]' value="1" {VAR:checked}></td>
</tr>
<!-- END SUB: line -->
</table>
</fieldset>
<!-- END SUB: class_container -->
<table>
<tr>
<td class="fgtext" colspan="2">
Objektid, millele see konfiobjekt kehtib
</td>
</tr>
<!-- SUB: oline -->
<tr>
<td class="fgtext">{VAR:oid}</td>
<td class="fgtext">{VAR:name}</td>
</tr>
<!-- END SUB: oline -->
</table>
</fieldset>
{VAR:reforb}
</form>
