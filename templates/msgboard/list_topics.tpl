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
<!-- SUB: DELETE -->
<input type="checkbox" name="check[]" value="{VAR:id}">
<!-- END SUB: DELETE -->
<form name="topicform" method="POST" action="reforb.{VAR:ext}">




            <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#e2e2e2">
              <tr> 
                <td>

					<!--2-->
			{VAR:TABS}


				  <!--5-->
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb" height="20">
                    <tr> 
		      <td width="9">&nbsp;</td>
                      <td><b class="text"><img src="{VAR:baseurl}/img/trans.gif" width="5" height="10"><span class="menuboxpealkirisinine">Pealkiri</span></b></td>
                      <td><b class="text">Vastuseid</b></td>
		      <!--
                      <td><b class="text">Postitas</b></td>
		      -->
                      <td><div align="center"><b class="text">Alustatud</b></div>
                      </td>
                      <td><div align="center"><b class="text">Vastatud</b></div>
                      </td>
                      <td class="rightpromotext">
		      </td>
                    </tr>

<!-- SUB: TOPIC_EVEN -->
                    <tr valign="top" bgcolor="#ffffff"> 
                      <td width="6">
					<!-- SUB: NEW_MSGS -->
					<font face="Arial" size="1" color="red">uus</font>&nbsp;
					<!-- END SUB: NEW_MSGS -->
					</td>
                      <td><span class="text"><b><a href="{VAR:threaded_topic_link2}">{VAR:topic}</a></b></span></td>
                      <td class="text"> 
                        <div align="center">{VAR:cnt}</div>
                      </td>
		      <!--
                      <td class="text">{VAR:createdby}</td>
		      -->
                      <td nowrap align="center" class="text"> 
                        {VAR:created_date}
                      </td>
                      <td nowrap align="center" class="text"> 
                        {VAR:lastmessage}
                      </td>
                      <td> 
		      	{VAR:DEL_TOPIC}
                        <!--<div align="center" class="text">{VAR:rate}</div>-->
                      </td>
                    </tr>
                   
<!-- END SUB: TOPIC_EVEN -->


<!-- SUB: TOPIC_ODD -->									
                    <tr valign="top"> 
                      <td width="6">{VAR:NEW_MSGS}</td>
                      <td><span class="text"><b><a href="{VAR:threaded_topic_link2}">{VAR:topic}</a></b></span></td>
                      <td class="text"> 
                        <div align="center">{VAR:cnt}</div>
                      </td>
		      <!--
                      <td class="text">{VAR:from}</td>
		      -->
                      <td nowrap align="center" class="text"> 
                        {VAR:created_date}
                      </td>
                      <td nowrap align="center"  class="text"> 
                        {VAR:lastmessage}
                      </td>
                      <td> 
		      	{VAR:DEL_TOPIC}
                        <!-- <div align="center" class="text">{VAR:rate}</div> -->
                      </td>
                    </tr>
                   
<!-- END SUB: TOPIC_ODD -->

<!-- SUB: actions -->
<tr><td colspan="7" class="text" align="right">
<input type="submit" class='doc_button' value="Kustuta valitud teemad" onClick="if (confirm('Oled kindel, et soovid valitud teemad kustutada?')) {document.topicform.submit()} ;return false;">
{VAR:reforb}
</td>
</tr>
<!-- END SUB: actions -->
</table>

                 


                  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#3e5f94" height="1">
                    <tr> 
                      <td><img src="{VAR:baseurl}/img/trans.gif" width="435" height="1"></td>
                    </tr>
                  </table>


<TABLE width="100%" border="0"cellspacing="0" cellpadding="0">
			<TR>
			<TD align="left" class="text">

			<!-- SUB: PAGES -->
			Vali lehekülg:&nbsp;

			<!-- SUB: PAGE -->
			<a href='{VAR:pagelink}'>{VAR:linktext}</a>&nbsp;&nbsp;
			<!-- END SUB: PAGE -->
			<!-- SUB: SEL_PAGE -->
			<a href='{VAR:pagelink}'><b>&gt;{VAR:linktext}&lt;</b></a>&nbsp;&nbsp;
			<!-- END SUB: SEL_PAGE -->
		
			<!-- END SUB: PAGES -->


			</TD>
			<TD align="right">
			<!--
			<input type="submit" value=" Hinda " class="mboardtextsmall">
			<input type="hidden" name="action" value="submit_votes"></TD>
			-->&nbsp;
			</TR>
			</TABLE>

                  


                </td>
              </tr>
            </table>
			<!--end 1-->

			<br>


</form>
