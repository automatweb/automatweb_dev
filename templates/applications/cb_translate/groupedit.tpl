<style>
body {
	font-family: Arial,sans-serif;
	font-size: 13px;
}
</style>
<div style="background-color: #EEE; font-size: 14px; font-weight: bold; font-family: Arial,sans-serif; padding: 5px;">{VAR:groupname}</div>
<form id="changeform" method="POST" action="{VAR:baseurl}/reforb.{VAR:ext}">
<table border='1' width='100%'>
<!-- SUB: PROPERTY_TRANSLATE -->
<tr>
<td colspan='2' bgcolor='#eeeeee'><strong>{VAR:property_name} [{VAR:property_type}]</strong></td>
</tr>
<td>Nimi</td>
<td>
<input type="text" name="caption[{VAR:property_id}]" value="{VAR:property_name}" size="40">
</td>
</tr>
<tr>
<td>Kommentaar</td>
<td><textarea name="comment[{VAR:property_id}]" cols="40">{VAR:property_comment}</textarea></td>
</tr>
<tr>
<td>Abitekst</td>
<td><textarea name="help[{VAR:property_id}]" cols="40"></textarea></td>
</tr>
<!-- END SUB: PROPERTY_TRANSLATE -->
</table>
{VAR:reforb}
</form>
<script type="text/javascript">
function submit_changeform() 
{
	document.getElementById('changeform').submit();
}
</script>
