<script language="javascript">
function _delete(a)
{
foo.lgroup.value=a;
foo.subop.value="delete";
foo.submit();
};

function _rename(a)
{
foo.lgroup.value=a;
foo.gname.value=prompt("Sisesta grupi uus nimi:","");
foo.subop.value="rename";
foo.submit();
};

function _new()
{
foo.subop.value="new";
foo.submit();
};
</script>
<form action = 'reforb.{VAR:ext}' method=post name="foo">
<table cellpadding=0 cellspacing=0 border=0>
<tr><td width=100%>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0 width=100%>
<tr>
<td class="fcaption2">Nimi:</td><td class="fform" colspan="2"><input type='text' class='small_button' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption2">Kommentaar:</td><td class="fform" colspan="2"><input type='text' class='small_button' NAME='comment' VALUE='{VAR:comment}'></td>
</tr>
<tr>
<tr><td class="title" colspan="3">Muutujad</td></tr>
<!-- SUB: variable -->
<tr height="10"><td class="fform">{VAR:name}</td>
<td class="fform"><input type='checkbox' name="vars[]" value="{VAR:vid}" {VAR:checked}></td>
<td class="fform"><a href="{VAR:l_acl}">{VAR:acl}</a></td>
</tr>
<!-- END SUB: variable -->
</tr>

<tr>
<td class="fcaption2" colspan="3" align="right"><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
</td></tr>
<tr><td width="100%">
<table cellpadding=3 cellspacing=1 border=0 bgcolor="#CCCCCC" width=100%>
<tr><td class="title" colspan="3">Grupid</td></tr>
<!-- SUB: group -->
<tr>
<td class="fform">{VAR:name}</td>
<td class="fform"><a href="javascript:_delete('{VAR:lgroup}');">Kustuta</a></td>
<td class="fform"><a href="javascript:_rename('{VAR:lgroup}');">Nimeta ümber</a></td>
</tr>
<!-- END SUB: group -->
<tr>
<td class="fform"><input type="text" name="gname" class="small_button"></td>
<td class="fform" colspan="2"><a href="javascript:_new();">Uus</a></td>
</td>
</table>
</td></tr>
</table>
{VAR:reforb}
</form>
