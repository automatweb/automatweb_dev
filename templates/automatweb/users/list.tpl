Page: 
<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} |
<!-- END SUB: SEL_PAGE -->

<!-- SUB: PAGE -->
<a href='{VAR:link}'>{VAR:from} - {VAR:to}</a> |
<!-- END SUB: PAGE -->
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>USERS:&nbsp;
<!-- SUB: ADD -->
<a href='{VAR:add}'>Add</a>
<!-- END SUB: ADD -->
</b></td>
</tr>
</table>
<br>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td align="center" class="title">&nbsp;UID&nbsp;</td>
<td align="center" class="title">&nbsp;logs&nbsp;</td>
<td align="center" class="title">&nbsp;Online&nbsp;</td>
<td align="center" class="title">&nbsp;Last login&nbsp;</td>
<td align="center" colspan="3" class="title">Actions</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td align="center" class="fgtext">&nbsp;{VAR:uid}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:logs}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:online}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:last}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:change}'>Change</a>
<!-- END SUB: CAN_CHANGE -->
</td>
<td class="fgtext2">&nbsp;
<a href='{VAR:settings}'>Properties</a>
</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_PWD -->
<a href='{VAR:change_pwd}'>Change password</a>
<!-- END SUB: CAN_PWD -->
</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DEL -->
<a href='{VAR:delete}'>Delete</a>
<!-- END SUB: CAN_DEL -->
</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
