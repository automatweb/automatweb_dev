<br>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>{VAR:LC_MAILINGLIST_BIG_CATEGORIES}: 
<!-- SUB: ADD_CAT -->
<a href='{VAR:add_link}'>{VAR:LC_MAILINGLIST_ADD}</a>
<!-- END SUB: ADD_CAT -->
</b>
</td>
</tr>
<tr>
<td height="15" class="title">&nbsp;{VAR:LC_MAILINGLIST_NAME}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MAILINGLIST_DESCRIPTION}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MAILINGLIST_CHANGER}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MAILINGLIST_CHANGED}&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;{VAR:LC_MAILINGLIST_ACTION}&nbsp;</td>
</tr>

<!-- SUB: C_LINE -->
<tr>
<td height="15" class="fgtext">
<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
<tr>
<td>{VAR:space_images}{VAR:image}</td>
<td valign=center class="fgtext">&nbsp;<a href='{VAR:open_link}'>{VAR:cat_name}</a>&nbsp;</td>
</tr>
</table>
</td>

<td class="fgtext">&nbsp;{VAR:cat_comment}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:change_link}'>Metainfo</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('{VAR:LC_MAILINGLIST_WANT_TO_DEL_CAT}?','{VAR:delete_link}')">{VAR:LC_MAILINGLIST_DELETE}</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:cat_id}&file=default.xml'>ACL</a>
<!-- END SUB: CAN_ACL -->
&nbsp;</td>
</tr>
<!-- END SUB: C_LINE -->
</table>
</td>
</tr>
</table>
<br><br>



{VAR:LC_MAILINGLIST_ALWAYS_DEFINED} #{VAR:LC_MAILINGLIST_SMALL_NAME}# , #email# ja #{VAR:LC_MAILINGLIST_DATE}# .
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>{VAR:LC_MAILINGLIST_BIG_VARIABLES}: 
&nbsp;<a href='{VAR:add_var}'>{VAR:LC_MAILINGLIST_ADD}</a></b>
</td>
</tr>
<tr>
<td align=center class="title">&nbsp;{VAR:LC_MAILINGLIST_NUMBER}&nbsp;</td>
<td align=center class="title">&nbsp;{VAR:LC_MAILINGLIST_NAME}&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;{VAR:LC_MAILINGLIST_ACTION}&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:var_id}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:var_name}&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_CHANGE -->
<a href='{VAR:change_var}'>{VAR:LC_MAILINGLIST_CHANGE}</a>
<!-- END SUB: V_CHANGE -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_DELETE -->
<a href='javascript:box2("{VAR:LC_MAILINGLIST_WANT_TO_DEL_VARIABLE} {VAR:var_name}?","{VAR:delete_var}")'>{VAR:LC_MAILINGLIST_DELETE}</a>
<!-- END SUB: V_DELETE -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:var_id}&file=variable.xml'>ACL</a>
<!-- END SUB: V_ACL -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>

