<script language=javascript>
window.parent.frames[1].frames[0].location.href='mail.{VAR:ext}?parent={VAR:parent}';
window.parent.frames[1].frames[1].location.href='mail.{VAR:ext}?type=show_mail';
</script>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>KATALOOGID: 
<!-- SUB: CAN_ADD -->
<a href='mail.{VAR:ext}?type=add_folder&parent={VAR:parent}'>Lisa</a> | 
<!-- END SUB: CAN_ADD -->
<!-- SUB: CAN_CHANGE -->
<a href='mail.{VAR:ext}?type=del_folder&id={VAR:parent}'>Kustuta</a> | 
<a href='mail.{VAR:ext}?type=mod_folder&id={VAR:parent}'>Muuda</a>
<!-- END SUB: CAN_CHANGE -->
</b></td>
</tr>

<!-- SUB: C_LINE -->
<tr>
<td height="15" class="fgtext">
<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
<tr>
<td>{VAR:space_images}{VAR:image}</td>
<td valign=center class="fgtext">&nbsp;<a href='mail.{VAR:ext}?type=folders&parent={VAR:cat_id}{VAR:op}'>{VAR:cat_name}</a>&nbsp;</td>
</tr>
</table>
</td>
</tr>
<!-- END SUB: C_LINE -->
</table>