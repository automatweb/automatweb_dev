<form action='reforb.{VAR:ext}' METHOD=post name="savexml">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption" colspan="5">
<a href="javascript:document.savexml.submit()">Salvesta</a>
|
<a href="{VAR:edurl}">Muuda väljundit</a>
</td>
</tr>
<!-- SUB: form -->
<tr>
<td class="fcaption"><b>Form:</b></td><td class="fcaption" colspan="4"><b>{VAR:fname}</b></td>
</tr>
<tr>
<td class="fcaption">Nimi</td>
<td class="fcaption">Tüüp</td>
<td class="fcaption">Jrk</td>
<td class="fcaption">Tag</td>
<td class="fcaption">Akt.</td>
</tr>
<!-- SUB: element -->
<tr>
<td class="fform">{VAR:name}</td>
<td class="fform">{VAR:type}</td>
<td class="fform"><input type="text" name="jrk[{VAR:id}]" size="2" maxlength="2" value="{VAR:jrk}"></td>
<td class="fform"><input type="text" name="tag[{VAR:id}]" size="20" maxlength="40" value="{VAR:tag}"></td>
<td class="fform" align="center"><input type="checkbox" name="active[{VAR:id}]" value="1" {VAR:checked}></td>
<input type="hidden" name="exists[{VAR:id}]" value="1">
</tr>
<!-- END SUB: element -->
<tr>
<td class="fcaption" colspan="5">
&nbsp;
</td>
</tr>
<!-- END SUB: form -->
<tr>
<td class="fcaption" colspan="5">
<input type="submit" value="Save">
</td>
</tr>
</table>
{VAR:reforb}
</form>
