<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
<td colspan="2" height="20" class="{VAR:style_new_topic_row}" colspan="2"><a href="{VAR:add_topic_url}"><img src="{VAR:baseurl}/automatweb/images/forum_add_new.gif" align="absmiddle" border="0" alt="Lisa uus teema"></a> <a href="{VAR:add_topic_url}">Lisa uus teema</a>
</td>
</tr>
<tr>
<!-- SUB: SHOW_PATH -->
<td class="{VAR:style_forum_yah}">
<strong>{VAR:path}</strong>
</td>
<!-- END SUB: SHOW_PATH -->
<td class="{VAR:style_caption}">
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
<table border="0" cellspacing="0" cellpadding="3" width="100%" style="border-collapse: collapse;">
<tr>
	<td colspan=2 align="center" class="{VAR:style_caption}">Teemad</td>
	<td align="center" class="{VAR:style_caption}">Vastuseid</td>
	<td align="center" class="{VAR:style_caption}">Autor</td>
	<td align="center" class="{VAR:style_caption}">Viimane vastus</td>
</tr>
<!-- SUB: SUBTOPIC -->
<tr>
	<td class="{VAR:style_topic_caption}"><center>
	<!-- SUB: ICON -->
	<img src="{VAR:icon_url}">
	<!-- END SUB: ICON -->
	</center></td>
	<td class="{VAR:style_topic_caption}">
	<!-- SUB: ADMIN_BLOCK -->
	<input type="checkbox" name="sel_topic[{VAR:topic_id}]" value="1" /> <input type="text" name="jrk[{VAR:topic_id}]" value="{VAR:jrk}" size="2" /> <a href="{VAR:add_faq_url}">[ lisa KKK ]</a>
	<!-- END SUB: ADMIN_BLOCK -->
	{VAR:jrk_text} <a href="{VAR:open_topic_url}">{VAR:name}</a>
	</td>
	<td align="center" class="{VAR:style_topic_replies}">{VAR:comment_count}</td>
	<td align="center" class="{VAR:style_topic_author}">{VAR:author}</td>
	<td align="center" class="{VAR:style_topic_last_post}">{VAR:last_date}<br>{VAR:last_createdby}</td>
</tr>
<!-- END SUB: SUBTOPIC -->
</table>
<!-- SUB: DELETE_ACTION -->
<input type="submit" name="delete_selected_topics" value="Kustuta valitud">
<input type="submit" name="save_jrk" value="Salvesta">
<!-- END SUB: DELETE_ACTION -->
