<form action='reforb.{VAR:ext}' method=post name="polladd">


<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="2">
<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.polladd.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_POLL_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:this.document.polladd.submit();">{VAR:LC_POLL_SAVE}</a>
</td></tr>
</table>


		</td>
		</tr>
		</table>


	</td>
	</tr>
	</table>

</td>
</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="5">
<tr><td>

<table  cellpadding=2 cellspacing=1 border=0>
<tr>
<td class="celltext" align="right">ID:</td><td class="celltext">{VAR:id}</td>
</tr>
<tr>
<!-- SUB: POLL -->
<td>
<table border="0">
<tr>
<td class="celltext">Keel:&nbsp;&nbsp;<b>{VAR:lang}</b></td>
</tr>
<tr>
<td class="celltext">{VAR:LC_POLL_QUESTION}:<br><input size="40" type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
</tr>
<tr>
<td colspan="2">
<table border='0' cellspacing='1' cellpadding='3' width='100%'>
<tr>
<td class="celltext" valign="top">{VAR:LC_POLL_COMMENTARY}:<br><textarea NAME='comment' cols=40 rows=5 class="formtext">{VAR:comment}</textarea></td>
</tr>

<!-- SUB: QUESTION -->
<tr>
<td>
<input size="40" type='text' NAME='answer[{VAR:lang_id}][{VAR:answer_id}]' VALUE='{VAR:answer}' class="formtext">
</td>
</tr>
<!-- END SUB: QUESTION -->
</table>
</td>
<!-- END SUB: POLL -->
<td>
<table border="1">
<tr>
<td rowspan="3">
&nbsp;
</td>
<td class="celltext">{VAR:clicks}</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="celltext" align="right"><b>Kokku:</b></td>
<td class="celltext" align="center"><b>{VAR:sum}</b></td>
<td class="celltext" align="center"><b>100%</b></td>
</tr>
</table>
</td>
</tr>
<!-- END SUB: EDIT -->

</table>
{VAR:reforb}
</form>

</td></tr></table>
