<table border="0" width="100%">
<tr>
<td class="{VAR:style_caption}">
<strong>{VAR:path}</strong>
</td>
<td class="{VAR:style_caption}">
<!-- SUB: active_page -->
 <strong>[ {VAR:num} ]</strong>
<!-- END SUB: active_page -->
<!-- SUB: page -->
 <a href="{VAR:url}">{VAR:num}</a> 
<!-- END SUB: page -->
</td>
</tr>
<tr>
<td colspan="2" class="{VAR:style_caption}"><a href="{VAR:add_topic_url}">Lisa teema</a></td>
</tr>
</table>
<table border="1" cellspacing="0" cellpadding="3" width="100%" style="border-collapse: collapse;">
<tr>
	<td colspan=2 class="{VAR:style_caption}">Teemad</td>
	<td class="{VAR:style_caption}">Vastuseid</td>
	<td class="{VAR:style_caption}">Autor</td>
	<td class="{VAR:style_caption}">Viimane vastus</td>
</tr>
<!-- SUB: SUBTOPIC -->
<tr>
	<td class="{VAR:style_topic_caption}"><center><big>*</big></center></td>
	<td class="{VAR:style_topic_caption}"><a href="{VAR:open_topic_url}">{VAR:name}</a></td>
	<td class="{VAR:style_topic_replies}">{VAR:comment_count}</td>
	<td class="{VAR:style_topic_author}">{VAR:author}</td>
	<td class="{VAR:style_topic_last_post}">{VAR:last_date}<br>{VAR:last_createdby}</td>
</tr>
<!-- END SUB: SUBTOPIC -->
</table>
