<form name="foo">
<script Language="JavaScript">
function pane(a)
{
	opener.document.foo.{VAR:el}.value=a;
	window.close();
};
</script>

<table border="0" cellspacing="1" cellpadding="2" width="100%" bgcolor="#CCCCCC">
<tr>
<td colspan="11" class="title">
<strong>Vali meil : </strong>
</td>

</tr>
<tr>
<td class="title" align="center"><a><b>#</b></a></td>

<td class="title" align="center"><a><b>Kellelt</b></a></td>

<td class="title" align="center"><a><b>Teema</b></a></td>

<td class="title" align="center"><a><b>Teade</b></a></td>

<td class="title" align="center"><b>Vali</b></td>
</tr>

<!-- SUB: rida -->
<tr>
<td class="fgtext" align="center">{VAR:id}</td>
<td class="fgtext" align="center">{VAR:mfrom}</td>
<td class="fgtext" align="center">{VAR:subject}</td>
<td class="fgtext" align="center">{VAR:message}</td>
<td class="fgtext" align="center"><input type="radio" OnClick="JavaScript:pane('{VAR:id}')"></td>

</tr>
<!-- END SUB: rida -->

</table>

</form>