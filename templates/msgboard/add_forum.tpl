<!-- SUB: EDIT -->
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td class="fgtitle">
<b>{VAR:LC_MSGBOARD_BIG_FORUM}:</b>
<a href="{VAR:content_link}">{VAR:LC_MSGBOARD_FORUM_CONTENT}</a>
</td>
</tr>
</table>
<!-- END SUB: EDIT -->


<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fgtitle">{VAR:LC_MSGBOARD_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fgtitle">{VAR:LC_MSGBOARD_COMMENTARY}:</td><td class="fform"><input type='text' NAME='comment' VALUE='{VAR:comment}'></td>
</tr>
<!-- SUB: CHANGE -->
<tr>
<td class="fgtitle">{VAR:LC_MSGBOARD_BIG_FORUM} URL:</td><td class="fform">{VAR:url}</td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
<td class="fgtitle">Template:</td>
<td class="fform"><select name="template">{VAR:template}</td>
</tr>
<tr>
<td class="fgtitle">{VAR:LC_MSGBOARD_COMABLE}:</td><td class="fform"><input type="checkbox" name="comments" value=1 {VAR:comments}></td>
</tr>
<tr>
<td class="fgtitle">{VAR:LC_MSGBOARD_RATEABLE}:</td><td class="fform"><input type="checkbox" name="rated" value=1 {VAR:rated}></td>
</tr>
<tr>
<td class="fgtitle">{VAR:LC_MSGBOARD_SUBJECTS_ON_PAGE}:</td><td class="fform"><select name="topicsonpage">{VAR:topicsonpage}</select> </td>
</tr>
<tr>
<td class="fgtitle">{VAR:LC_MSGBOARD_COM_ON_PAGE}:</td><td class="fform"><select name="onpage">{VAR:onpage}</select></td>
</tr>
<tr>
<td class="fgtitle" colspan=2 align=center><input type='submit' VALUE='{VAR:LC_MSGBOARD_SAVE}' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
