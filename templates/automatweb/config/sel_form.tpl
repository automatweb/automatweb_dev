<br>
<form action='reforb.{VAR:ext}' method=POST>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="title">ID</td>
<td class="title">Nimi</td>
<td class="title">Kommentaar</td>
<td class="title">Vali</td>
<td class="title">Peab t&auml;itma</td>
<td class="title">Grupp</td>
<td class="title">J&auml;rjekord men&uuml;&uuml;s</td>
<td class="title">Nimi men&uuml;&uuml;s</td>
<td class="title">V&auml;ljundi stiil</td>
<td class="title">Teine v&auml;ljundi stiil</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td class="plain">{VAR:form_id}</td>
<td class="plain"><a href='{VAR:change}'>{VAR:form_name}</a></td>
<td class="plain">{VAR:form_comment}</td>
<td class="plain"><input type='checkbox' name='sf[{VAR:form_id}]' value=1 {VAR:checked}></td>
<td class="plain">&nbsp;
<!-- SUB: MUSTFILL -->
<input type='checkbox' name='mf[{VAR:form_id}]' value=1 {VAR:mf}>
<!-- END SUB: MUSTFILL -->
</td>
<td class="plain">&nbsp;
<!-- SUB: GROUP -->
<input type='text' size=10 name='fg[{VAR:form_id}]' value="{VAR:group}">
<!-- END SUB: GROUP -->
</td>
<td class="plain">&nbsp;
<!-- SUB: ORDER -->
<input type='text' size=2 name='fo[{VAR:form_id}]' value="{VAR:order}">
<!-- END SUB: ORDER -->
</td>
<td class="plain">&nbsp;
<!-- SUB: NAME -->
<input type='text' size=20 name='fn[{VAR:form_id}]' value="{VAR:name}">
<!-- END SUB: NAME -->
</td>
<td class="plain">&nbsp;
<!-- SUB: OPS -->
<select name='fp[{VAR:form_id}]'>{VAR:jops}</select>
<!-- END SUB: OPS -->
</td>
<td class="plain">&nbsp;
<!-- SUB: OPS2 -->
<select name='fp2[{VAR:form_id}]'>{VAR:jops2}</select>
<!-- END SUB: OPS2 -->
</td>
</tr>
<!-- END SUB: LINE -->
</table>
<input type='submit' VALUE='Salvesta'>
{VAR:reforb}
</form>
