<table border="0" width="100%">
<tr>
<td>
<small>
<strong>{VAR:path}</strong>
</small>
</td>
<td>
<!-- SUB: active_page -->
 <strong>[ {VAR:num} ]</strong>
<!-- END SUB: active_page -->
<!-- SUB: page -->
 <a href="{VAR:url}">{VAR:num}</a> 
<!-- END SUB: page -->
</td>
</tr>
</table>
<strong>{VAR:name}</strong><br>
<small>{VAR:comment}</small>
<table border="1" cellspacing="0" cellpadding="3" width="100%" style="border-collapse: collapse;">
<!-- SUB: COMMENT -->
<tr>
	<td align="center" width="20%">{VAR:createdby}<br>{VAR:date}</td>
	<td valign="top"><strong>{VAR:name}</strong><p><small>{VAR:commtext}</small></td>
</tr>
<!-- END SUB: COMMENT -->

</table>
