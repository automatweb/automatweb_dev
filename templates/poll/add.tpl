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

<table border="0">
	<tr>
		<td class="celltext">ID:</td>
		<td colspan="3" class="celltext">{VAR:id} 
		<!-- SUB: CHANGE -->
			<a href='{VAR:translate}'>T&otilde;lgi</a>&nbsp;&nbsp;&nbsp;
			<a href='{VAR:clicks}'>Klikid</a>
		<!-- END SUB: CHANGE -->
		</td>
	</tr>
	<tr>
		<td class="celltext">Keel:&nbsp;&nbsp;</td>
		<td colspan="3" class="celltext"><b>{VAR:lang}</b></td>
	</tr>
	<tr>
		<td class="celltext">{VAR:LC_POLL_QUESTION}:</td>
		<td class="celltext"><input size="40" type='text' NAME='name[{VAR:lang_id}]' VALUE='{VAR:name}' class="formtext"></td>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext">Klikke:</td>
	</tr>
	<tr>
		<td class="celltext" valign="top">{VAR:LC_POLL_COMMENTARY}:</td>
		<td colspan="3" class="celltext"><textarea NAME='comment[{VAR:lang_id}]' cols=40 rows=5 class="formtext">{VAR:comment}</textarea></td>
	</tr>
	<tr>
		<td class="celltext" colspan="4">Vastused:</td>
	</tr>
	<!-- SUB: QUESTION -->
	<tr>
		<td colspan="2" align="right" class="celltext"><input size="40" type='text' NAME='answer[{VAR:lang_id}][{VAR:answer_id}]' VALUE='{VAR:answer}' class="formtext"></td>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext">&nbsp;{VAR:clicks} ({VAR:percent}%)</td>
	</tr>
	<!-- END SUB: QUESTION -->
	<tr>
		<td colspan="3" class="celltext" align="right"><b>Kokku:</b></td>
		<td class="celltext">&nbsp;<b>{VAR:sum}</b> ({VAR:percent}%)</td>
	</tr>
</table>

{VAR:reforb}
</form>

</td></tr></table>
