<br>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>KATEGOORIAD: 
<!-- SUB: ADD_CAT -->
<a href='list.{VAR:ext}?type=add_var_cat&parent={VAR:parent}'>Lisa</a>
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
<td valign=center class="fgtext">&nbsp;<a href='list.{VAR:ext}?type=list_vars&parent={VAR:cat_id}{VAR:op}'>{VAR:cat_name}</a>&nbsp;</td>
</tr>
</table>
</td>

<td class="fgtext">&nbsp;{VAR:cat_comment}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='list.{VAR:ext}?type=change_var_cat&id={VAR:cat_id}&parent={VAR:parent}'>Metainfo</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda kategooriat 
kustutada?','list.{VAR:ext}?type=delete_var_cat&id={VAR:cat_id}&parent={VAR:parent}')">Kustuta</a>
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



Alati on ka defineeritud muutujad #nimi# , #email# ja #kuupaev# .
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>MUUTUJAD: 
&nbsp;<a href='list.{VAR:ext}?type=add_var&parent={VAR:parent}'>Lisa</a></b>
</td>
</tr>
<tr>
<td align=center class="title">&nbsp;Number&nbsp;</td>
<td align=center class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;Tegevus&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:var_id}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:var_name}&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_CHANGE -->
<a href='list.{VAR:ext}?type=change_var&id={VAR:var_id}&parent={VAR:parent}'>Muuda</a>
<!-- END SUB: V_CHANGE -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_DELETE -->
<a href='javascript:box2("Oled kindel et tahad kustutada muutujat {VAR:var_name}?","list.{VAR:ext}?type=delete_var&id={VAR:var_id}&parent={VAR:parent}")'>Kustuta</a>
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

