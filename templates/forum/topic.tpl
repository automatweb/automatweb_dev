<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
<td height="20" style="font: Bold 10px Verdana, Arial, Sans-Serif; padding-left:10px;" colspan="2"><a href="#kommentaar"><img src="{VAR:baseurl}/automatweb/images/forum_add_comment.gif" align="absmiddle" border="0" alt="Lisa kommentaar"></a> <a href="#kommentaar">Lisa kommentaar</a>
</td>
</tr>
<tr>
<td class="{VAR:style_forum_yah}">
<strong>{VAR:path}</strong>
</td>
<td nowrap class="{VAR:style_caption}">
<!-- SUB: active_page -->
 <strong>[ {VAR:num} ]</strong>
<!-- END SUB: active_page -->
<!-- SUB: page -->
 <a href="{VAR:url}">{VAR:num}</a> 
<!-- END SUB: page -->
</td>
</tr>
<!-- <tr>
<td colspan="2" class="{VAR:style_caption}">
<strong>{VAR:name}</strong>
</td>
</tr>
<tr>
<td class="{VAR:style_comment_count}">
{VAR:comment}
</td>
</tr> -->
</table>

<table border="0" cellspacing="0" cellpadding="3" width="100%" style="border-collapse: collapse;">
<tr>
	<td align="center" width="20%" class="{VAR:style_comment_creator}"><div class="{VAR:style_comment_user}">{VAR:createdby}</div>{VAR:date}</td>
	<td valign="top" class="{VAR:style_comment_count}"><strong>{VAR:name}</strong><p>{VAR:comment}</td>
</tr>
<!-- SUB: COMMENT -->
<tr>
	<td align="center" width="20%" class="{VAR:style_comment_time}"><div class="{VAR:style_comment_user}">{VAR:createdby}</div><div class="">{VAR:date}</div></td>
	<td valign="top" class="{VAR:style_comment_text}"><strong>{VAR:name}</strong><p>{VAR:commtext}</td>
</tr>
<!-- END SUB: COMMENT -->
</table>


