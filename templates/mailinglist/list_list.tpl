
<br>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">


<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr class="aste05">
<td height="15" colspan="11" class="celltext">&nbsp;<b>{VAR:LC_MAILINGLIST_BIG_CATEGORIES}: 
<!-- SUB: ADD_CAT -->
<a href='{VAR:add_link}'>{VAR:LC_MAILINGLIST_ADD}</a>
<!-- END SUB: ADD_CAT -->
</b>
</td>
</tr>
<tr class="aste05">
<td height="15" class="celltext">&nbsp;{VAR:LC_MAILINGLIST_NAME}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MAILINGLIST_DESCRIPTION}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MAILINGLIST_CHANGER}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MAILINGLIST_CHANGED}&nbsp;</td>
<td align="center" colspan="3" class="celltext">&nbsp;{VAR:LC_MAILINGLIST_ACTION}&nbsp;</td>
</tr>

<!-- SUB: C_LINE -->
<tr class="aste07">
<td height="15" class="celltext">
<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
<tr class="aste07">
<td>{VAR:space_images}{VAR:image}</td>
<td valign=center class="celltext">&nbsp;<a href='{VAR:open_link}'>{VAR:cat_name}</a>&nbsp;</td>
</tr>
</table>
</td>

<td class="celltext">&nbsp;{VAR:cat_comment}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:modified}&nbsp;</td>
<td class="celltext">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:change_link}'>Metainfo</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="celltext">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('{VAR:LC_MAILINGLIST_WANT_TO_DEL_CAT}?','{VAR:delete_link}')">{VAR:LC_MAILINGLIST_DELETE}</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class="celltext">&nbsp;
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
<form method="POST" name="listform">
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="celltext">&nbsp;<b>{VAR:LC_MAILINGLIST_BIG_LISTS}:&nbsp;
<!-- SUB: ADD_LIST -->
<a href='list.{VAR:ext}?type=add_list&parent={VAR:parent}'>{VAR:LC_MAILINGLIST_ADD}</a> |
<a href='#' onClick='document.listform.submit()'>{VAR:LC_MAILINGLIST_SAVE}</a>
<!-- END SUB: ADD_LIST -->
</b></td>
</tr>
<tr>
<td align="center" class="celltext">&nbsp;ID&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MAILINGLIST_NAME}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_MAILINGLIST_COMMENTARY}&nbsp;</td>
<td align="center" colspan="7" class="celltext">{VAR:LC_MAILINGLIST_ACTION}</td>
<td align="center" class="celltext">&nbsp;Default&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td align="center" class="celltext">&nbsp;{VAR:list_id}&nbsp;</td>
<td class="celltext">&nbsp;{VAR:list_name}&nbsp;</td>
<td class="celltext">&nbsp;{VAR:list_comment}&nbsp;</td>
<td class="celltext">&nbsp;
<!-- SUB: L_CHANGE -->
<a href='list.{VAR:ext}?type=change_list&id={VAR:list_id}'>{VAR:LC_MAILINGLIST_CHANGE}</a>
<!-- END SUB: L_CHANGE -->
&nbsp;</td>
<td class="celltext">&nbsp;<a href='list.{VAR:ext}?type=change_list_vars&id={VAR:list_id}&parent={VAR:parent}'>{VAR:LC_MAILINGLIST_SMALL_VARIABLES}</a>&nbsp;</td>
<td class="celltext">&nbsp;
<!-- SUB: L_DELETE -->
<a href="javascript:box2('{VAR:LC_MAILINGLIST_WANT_TO_DEL_LIST}?','list.{VAR:ext}?type=delete_list&id={VAR:list_id}')">{VAR:LC_MAILINGLIST_DELETE}</a>
<!-- END SUB: L_DELETE -->
&nbsp;</td>
<td class="celltext">&nbsp;
<!-- SUB: L_ACL -->
<a href="editacl.{VAR:ext}?oid={VAR:list_id}&file=list.xml">ACL</a>
<!-- END SUB: L_ACL -->
&nbsp;</td>
<td class="celltext">&nbsp;<a href='list.{VAR:ext}?type=list_inimesed&id={VAR:list_id}'>{VAR:LC_MAILINGLIST_LIST_MEMBERS}</a>&nbsp;</td>
<td class="celltext">&nbsp;<a href='list.{VAR:ext}?type=list_mails&id={VAR:list_id}'>{VAR:LC_MAILINGLIST_SMALL_MAILS}</a>&nbsp;</td>
<td class="celltext">&nbsp;
<!-- SUB: L_IMPORT -->
<a href='list.{VAR:ext}?type=import_file&id={VAR:list_id}'>{VAR:LC_MAILINGLIST_IMPORT_ADDRESS}</a>
<!-- END SUB: L_IMPORT -->
&nbsp;</td>
<td class="celltext" align="center"><input type="radio" name="default" value="{VAR:list_id}" {VAR:checked}>
</td>
<!-- END SUB: LINE -->
</tr>
</table>
<input type="hidden" name="type" value="submit_default_list">
<input type="hidden" name="parent" value="{VAR:parent}">
</form>
</td></tr></table>
