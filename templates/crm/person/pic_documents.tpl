<!-- SUB: prevlink -->
<a href="{VAR:prevurl}">Eelmised</a>
<!-- END SUB: prevlink -->

<!-- SUB: nextlink -->
<a href="{VAR:nexturl}">Järgmised</a>
<!-- END SUB: nextlink -->
<table border="0">
<tr>
<td>{VAR:imgurl}</td>
</tr>
<tr>
<td class="text">
<h4>{VAR:name}</h4>
{VAR:phone}<br>
{VAR:email}
</td>
</tr>
<tr>
<td class="plain">
<!-- SUB: DOCLIST -->
Artiklid:
<table border="0" width="100%" cellpadding="2">
<!-- SUB: ITEM -->
<tr>
<td>{VAR:url}</td>
<td>{VAR:commcount}</td>
</tr>
<!-- END SUB: ITEM -->
</table>
<!-- END SUB: DOCLIST -->
</td>
</tr>
</table>
