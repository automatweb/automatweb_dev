<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
<td height="20" style="font: Bold 10px Verdana, Arial, Sans-Serif; padding-left:10px;" colspan="2">
<a href="#kommentaar"><img src="{VAR:baseurl}/automatweb/images/forum_add_comment.gif" align="absmiddle" border="0" alt="" />Lisa kommentaar</a>
</td>
</tr>
<tr>
<td class="{VAR:style_forum_yah}">
<strong>{VAR:path}</strong>
</td>
<td nowrap class="{VAR:style_caption}">
<!-- SUB: PAGER -->
<!-- SUB: active_page -->
 <strong>[ {VAR:num} ]</strong>
<!-- END SUB: active_page -->
<!-- SUB: page -->
 <a href="{VAR:url}">{VAR:num}</a> 
<!-- END SUB: page -->
<!-- END SUB: PAGER -->
</td>
</tr>
</table>

<table border="1" cellspacing="0" cellpadding="3" width="100%" style="border-collapse: collapse;">
<tr>
	<td align="center" width="20%" class="{VAR:style_comment_creator}"><div class="{VAR:style_comment_user}">{VAR:createdby}</div>{VAR:date}
<!-- SUB: ADMIN_TOPIC -->
<br />
<img src="{VAR:baseurl}/automatweb/images/icons/forum_admin.gif" alt="Administrator" title="Administrator ---- ">
<!-- END SUB: ADMIN_TOPIC -->
</td>
	<td valign="top" class="{VAR:style_comment_count}"><strong>{VAR:name}</strong><p>{VAR:comment}</td>
</tr>
<!-- SUB: COMMENT -->
<tr>
	<td align="center" width="20%" class="{VAR:style_comment_time}"><div class="{VAR:style_comment_user}">{VAR:uname}</div><div class="">{VAR:date}</div>
<!-- SUB: ADMIN_POST -->
<img src="{VAR:baseurl}/automatweb/images/icons/forum_admin.gif" alt="Administrator" title="Administrator">
<!-- END SUB: ADMIN_POST -->
</td>
	<td valign="top" class="{VAR:style_comment_text}">
		<!-- SUB: ADMIN_BLOCK -->
		<div align="right">
		<strong>IP: {VAR:ip}</strong><br />
		<input type="checkbox" name="del[]" value="{VAR:id}" />
		</div>
		<!-- END SUB: ADMIN_BLOCK -->
	<strong>{VAR:name}</strong><p>{VAR:commtext}
	</td>
</tr>
<!-- END SUB: COMMENT -->
</table>
<!-- SUB: DELETE_ACTION -->
<div align="right" style="background: #FFFFFF;">
<input type="button" name="delete_comments" value="Kustuta valitud kommentaarid" onClick="if(confirm('Kustutada?')){document.changeform.action.value='delete_comments';document.changeform.submit();};"/>
</div>
<!-- END SUB: DELETE_ACTION -->
