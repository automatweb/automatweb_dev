<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="title">Number</td>
<td class="title">Pilt</td>
<td class="title">Kommentaar</td>
<td class="title">Link</td>
<td  class="title" colspan=2 align=center>Tegevus</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="plain">{VAR:image_number}</td>
<td class="plain"><img src='{VAR:image_url}' height=20 width=20></td>
<td class="plain">{VAR:image_comment}</td>
<td class="plain">{VAR:image_link}</td>
<td class="plain">
<!-- SUB: CAN_CHANGE -->
<a href='images.{VAR:ext}?type=change_image&id={VAR:image_id}&parent={VAR:parent}'>Muuda</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="plain">
<!-- SUB: CAN_DELETE -->
<a href='images.{VAR:ext}?type=delete_image&id={VAR:image_id}&parent={VAR:parent}'>Kustuta</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td  class="fcaption" colspan=6 align=center>
<!-- SUB: CAN_ADD -->
<a href='images.{VAR:ext}?type=add_image&parent={VAR:parent}'>Lisa</a>
<!-- END SUB: CAN_ADD -->
&nbsp;</td>
</tr>
</table>
