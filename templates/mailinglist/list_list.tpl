
<br>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>KATEGOORIAD: 
<!-- SUB: ADD_CAT -->
<a href='list.{VAR:ext}?type=add_cat&parent={VAR:parent}'>Lisa</a>
<!-- END SUB: ADD_CAT -->
</b>
</td>
</tr>
<tr>
<td height="15" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kirjeldus&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;Tegevus&nbsp;</td>
</tr>

<!-- SUB: C_LINE -->
<tr>
<td height="15" class="fgtext">
<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
<tr>
<td>{VAR:space_images}{VAR:image}</td>
<td valign=center class="fgtext">&nbsp;<a href='list.{VAR:ext}?parent={VAR:cat_id}{VAR:op}'>{VAR:cat_name}</a>&nbsp;</td>
</tr>
</table>
</td>

<td class="fgtext">&nbsp;{VAR:cat_comment}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='list.{VAR:ext}?type=change_cat&id={VAR:cat_id}&parent={VAR:parent}'>Metainfo</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda kategooriat 
kustutada?','list.{VAR:ext}?type=delete_cat&id={VAR:cat_id}&parent={VAR:parent}')">Kustuta</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:cat_id}&file=list_cat.xml'>ACL</a>
<!-- END SUB: CAN_ACL -->
&nbsp;</td>
</tr>
<!-- END SUB: C_LINE -->
</table>
</td>
</tr>
</table>
<br><br>



<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>LISTID:&nbsp;
<!-- SUB: ADD_LIST -->
<a href='list.{VAR:ext}?type=add_list&parent={VAR:parent}'>Lisa</a>
<!-- END SUB: ADD_LIST -->
</b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;ID&nbsp;</td>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kommentaar&nbsp;</td>
<td align="center" colspan="7" class="title">Tegevus</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td align="center" class="fgtext">&nbsp;{VAR:list_id}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:list_name}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:list_comment}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: L_CHANGE -->
<a href='list.{VAR:ext}?type=change_list&id={VAR:list_id}'>Muuda</a>
<!-- END SUB: L_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;<a href='list.{VAR:ext}?type=change_list_vars&id={VAR:list_id}&parent={VAR:parent}'>Muutujad</a>&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: L_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda listi kustutada?','list.{VAR:ext}?type=delete_list&id={VAR:list_id}')">Kustuta</a>
<!-- END SUB: L_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: L_ACL -->
<a href="editacl.{VAR:ext}?oid={VAR:list_id}&file=list.xml">ACL</a>
<!-- END SUB: L_ACL -->
&nbsp;</td>
<td class="fgtext2">&nbsp;<a href='list.{VAR:ext}?type=list_inimesed&id={VAR:list_id}'>Listi liikmed</a>&nbsp;</td>
<td class="fgtext2">&nbsp;<a href='list.{VAR:ext}?type=list_mails&id={VAR:list_id}'>Meilid</a>&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: L_IMPORT -->
<a href='list.{VAR:ext}?type=import_file&id={VAR:list_id}'>Impordi aadresse</a>
<!-- END SUB: L_IMPORT -->
&nbsp;</td>
<!-- END SUB: LINE -->
</tr>
</table>
</td></tr></table>