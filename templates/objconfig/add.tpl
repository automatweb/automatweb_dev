<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Name:</td><td class="fform"><input type='text' NAME='name' size="40"></td>
</tr>
<tr>
<td class="fform">Komment:</td><td class="fform"><input type='comment' NAME='name' size="40"></td>
</tr>
<tr>
<td class="fform" colspan=2>Baasklass:</td>
</tr>
<!-- SUB: classlist -->
<tr>
<td class="fform" align="center">
<input type="radio" name="baseclass" value="{VAR:clid}" {VAR:selected}>
</td>
<td class="fform">
{VAR:name}
</td>
</tr>
<!-- END SUB: classlist -->
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='Edasi&gt;&gt;'></td>
</tr>
</table>
{VAR:reforb}
</form>
