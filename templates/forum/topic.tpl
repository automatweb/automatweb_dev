<table border="0" width="100%">
<tr>
<td class="{VAR:style_caption}">
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
<tr>
<td colspan="2" class="{VAR:style_caption}">
<strong>{VAR:name}</strong><br>
{VAR:comment}
</td>
</tr>
</table>
<table border="1" cellspacing="0" cellpadding="3" width="100%" style="border-collapse: collapse;">
<!-- SUB: COMMENT -->
<tr>
	<td align="center" width="20%"><div class="{VAR:style_comment_user}">{VAR:createdby}</div><div class="{VAR:style_comment_time}">{VAR:date}</div></td>
	<td valign="top" class="{VAR:style_comment_text}"><strong>{VAR:name}</strong><p>{VAR:commtext}</td>
</tr>
<!-- END SUB: COMMENT -->
</table>


