
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="textSmall"><img src="{VAR:baseurl}/img/trans.gif" width="5" height="1" border="0" alt=""><b>Otsi kommentaare</b></td>
    <td>&nbsp;</td>
  </tr>
</table>

<img src="/img/joon.gif" width="370" height="1" border="0" alt=""><br>
<img src="/img/trans.gif" width="1" height="10" border="0" alt=""><br>

<table align="center" border="0" cellspacing="4" cellpadding="0">
	<form method="get" action="{VAR:baseurl}/automatweb/comments.{VAR:ext}">
	<tr>
		<td align="right" class="textSmall">Nimi:&nbsp;<input type='checkbox' value=1 name='s_from'></td>
		<td><input type="text" name="from" size="25"></td>
	</tr>

	<tr>
		<td align="right" class="textSmall">E-mail:&nbsp;<input type='checkbox' value=1 name='s_email'></td>
		<td><input type="text" NAME="email" size="25"></td>
	</tr>

	<tr>
		<td align="right" class="textSmall">Subjekt:&nbsp;<input type='checkbox' value=1 name='s_subj'></td>
		<td><input type="text" NAME="subj" size="25" ></td>
	</tr>
	
	<tr>
		<td align="right" valign="top" class="textSmall">Komment:&nbsp;<input type='checkbox' value=1 name='s_comment'></td>
		<td valign="top"><textarea  cols="30" name="comment" rows="8" wrap="virtual"></textarea></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td align="right"><span class="textSmall">K&otilde;ikide kommentaaride hulgast?&nbsp;</span><input type='checkbox' value=1 name='s_all'>&nbsp;<input type="submit" value="Otsi" class="textSmall"></td>
		<input type='hidden' NAME='action' VALUE='search_comments'>
		<input type='hidden' NAME='section' VALUE='{VAR:section}'>
	</form>
	</tr>
</table>
<img src="/img/trans.gif" width="1" height="10" border="0" alt=""><br>
