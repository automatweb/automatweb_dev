<script language="javascript">
function box2(caption,url)
{
	var answer=confirm(caption);
	if (answer)
	{
		window.location=url
	}
}
</script>
<form method="POST" name="commform" action="reforb.{VAR:ext}">







			<!--1-->
            <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#e2e2e2">
              <tr> 
                <td>

					<!--2-->
			{VAR:TABS}

					<!--4-->
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#3e5f94" height="1">
                    <tr> 
                      <td><img src="{VAR:baseurl}/img/trans.gif" width="435" height="1"></td>
                    </tr>
                  </table>
				  <!--end 4-->

<table width="100%" cellpadding="10" cellspacing="0" border="0">
<!-- SUB: TOPIC -->
<tr> 
<td bgcolor="#ECECEC" class="text">

<!--topic-->
<a href="{VAR:threaded_topic_link}"><b>{VAR:topic}</b></a><br>
Autor: <b>{VAR:from}</b>  ({VAR:created})<br>
<img src="{VAR:baseurl}/img/trans.gif" border="0" width="1" height="10" alt=""><br>
{VAR:text}<br>
</td>
</tr>


<!--<tr><td bgcolor="#ECECEC" class="text"><img
src="/img/new/nool_hall.gif">&nbsp;&nbsp;<a href="#comments">Loe selle teema arvamusi</a>&nbsp;&nbsp;&nbsp;Hinne: {VAR:rate}&nbsp;</td></tr>-->

<!--jooneke
<tr><td align="right"><img src="{VAR:baseurl}/img/forum_joon2.gif" border="0" width="100%" height="2" alt=""></td></tr>-->
							
<!-- END SUB: TOPIC -->
</table>





<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">

<tr> 
<!--<td height="18" align="left" class="text"></td>-->

			<TD align="right" class="textesileht">

			<!-- SUB: PAGES -->
			Vali lehekülg:&nbsp;

			<!-- SUB: PAGE -->
			<a href='/comments.{VAR:ext}?action=topics&page={VAR:pagenum}&forum_id={VAR:forum_id}'>{VAR:ltext}</a>&nbsp;&nbsp;
			<!-- END SUB: PAGE -->
			<!-- SUB: SEL_PAGE -->
			<a href='/comments.{VAR:ext}?action=topics&page={VAR:pagenum}&forum_id={VAR:forum_id}'><b>&gt;{VAR:ltext}&lt;</b></a>&nbsp;&nbsp;
			<!-- END SUB: SEL_PAGE -->
		
			<!-- END SUB: PAGES -->


			</TD>
			<!--<TD align="right">
			
			<input type="submit" value=" Hinda " class="mboardtextsmall">
			<input type="hidden" name="action" value="submit_votes">
			&nbsp;
			</td>-->
			</TR>
			</TABLE>

                  


                </td>
              </tr>
            </table>
			<!--end 1-->






<img src='{VAR:baseurl}/img/trans.gif' width="1" height="5" alt="" border="0"><br>

<a name="comments"></a>

<!--begin komment-->

<!-- SUB: message -->
<table width=100% border=0 cellpadding=0 cellspacing=0 class="text">
<tr>
<td width=1><img src='{VAR:baseurl}/img/trans.gif' width="{VAR:level}" height="1" alt="" border="0"></td>
<td>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="text">
  <tr>
    <td class="text" bgcolor="#CCCCCC"><!--<a name="c{VAR:id}" style="text-decoration: none;">-->Kes: <a href="mailto:{VAR:email}"><b>{VAR:from}</b></a> @ {VAR:time}</td>
    </tr>
  <tr>
    <td height="18" valign="top" class="text" bgcolor="{VAR:color}"><b>{VAR:subj}</b></td>
  </tr>
  <tr>
    <td bgcolor="{VAR:color}"><span class="text">{VAR:comment}</span></td>
	</tr>
	<tr>
<td align="right" height="18" bgcolor="{VAR:color}"><span class="text">
		<!-- SUB: REPLY -->
		<img src="/img/mboard_nool_hall.gif">&nbsp;<a href="{VAR:reply_link}"><b>Vasta</b></a>
		<!-- END SUB: REPLY -->

		<!-- SUB: KUSTUTA -->
		<img src="/img/mboard_nool_hall.gif">&nbsp;<b>Vali:</b> <input type='checkbox' name='check[]' value='{VAR:id}'>
		<!-- END SUB: KUSTUTA -->
		</span>
		</td>
</tr>
</table>
</td>
</tr>

</table>
<!-- END SUB: message -->
<!--end komment-->
<!-- SUB: actions -->
<table width="100%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td align='right'>
<input type="submit" class='doc_button' value="Kustuta valitud kommentaarid" onClick="if (confirm('Kustutada valitud kommentaarid?')) {document.commform.submit()} ;return false;">
{VAR:reforb}
</td>
</tr>
</table>
<!-- END SUB: actions -->







<table width="100%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td class="text">


{VAR:PAGES}




</td>
</tr>
</table>
</form>

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td bgcolor="#ECECEC" class="text"><b>Uus</b></td>
	</tr>
</table>

