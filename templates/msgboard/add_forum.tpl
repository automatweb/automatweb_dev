<!-- SUB: EDIT -->
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td class="fgtitle">
<b>FOORUM:</b>
<a href="{VAR:content_link}">Foorumi sisu</a>
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
<td class="fgtitle">Foorum URL:</td><td class="fform">{VAR:url}</td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
<td class="fgtitle">Template:</td>
<td class="fform"><select name="template">{VAR:template}</td>
</tr>
<tr>
<td class="fgtitle">Kommenteeritav:</td><td class="fform"><input type="checkbox" name="comments" value=1 {VAR:comments}></td>
</tr>
<tr>
<td class="fgtitle">Hinnatav:</td><td class="fform"><input type="checkbox" name="rated" value=1 {VAR:rated}></td>
</tr>
<tr>
<td class="fgtitle">Teemasid lehel:</td><td class="fform"><input type="text" name="topicsonpage" size="4" value="{VAR:topicsonpage}"></td>
</tr>
<tr>
<td class="fgtitle">Kommentaare lehel:</td><td class="fform"><input type="text" name="onpage" size="4" value="{VAR:onpage}"></td>
</tr>
<tr>
<td class="fgtitle" colspan=2 align=center><input type='submit' VALUE='{VAR:LC_MSGBOARD_SAVE}' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
