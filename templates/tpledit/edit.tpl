<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<form name="edit" method="POST">
<tr>
<td colspan="2" class="fgtitle"><b>Editing {VAR:file}
|
<a href="javascript:document.edit.submit()"><font color="red">Salvesta</font>
</b>
</td>
</tr>
<tr>
<td class="fgtext">
<textarea name="source" cols="60" rows="60">
{VAR:source}
</textarea>
</td>
<td valign="top" class="fgtext">
<IFRAME src="{VAR:rawlink}" width="400" height="500"
              scrolling="auto" frameborder="1">
</IFRAME>
</td>
</tr>
{VAR:reforb}
</form>
</table>
