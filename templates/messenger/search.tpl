{VAR:menu}
<br>
<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="0" cellpadding="0">
<!-- SUB: sline -->
<tr>
<td class="text" align="center" width="20">
{VAR:num}
</td>
<td class="text">
<select name="field[{VAR:idx}]">
{VAR:fieldlist}
</select>
</td>
<td class="text">
<input type="text" name="search[{VAR:idx}]" size="30" value="{VAR:value}">
</td>
<td> &nbsp; </td>
</tr>
<!-- END SUB: sline -->
<!-- SUB: connline -->
<tr>
<td colspan="3" align="right" class="text">
&nbsp;
</td>
<td class="text">
<select name="connector[{VAR:idx}]">
{VAR:connlist}
</td>
</tr>
<!-- END SUB: connline -->
</table>
<input type="submit" value="Otsi" class="formbutton">
<br><br>
<span class="text">
{VAR:msg_search_remark}:<br>
<!-- SUB: line -->
<input type="checkbox" {VAR:checked} name="folders[{VAR:id}]">{VAR:name}<br>
<!-- END SUB: line -->
<p>
</span>
<input type="submit" value="Otsi" class="formbutton">
{VAR:reforb}
</form>
