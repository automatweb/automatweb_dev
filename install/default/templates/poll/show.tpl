
<img src="/img/trans.gif" width="1" height="15" border="0" alt=""><br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <!--begin KESKMISE TEKSTI OSA-->
    <td width="100%" valign="top">
			<span class="textPealkiri">Päeva küsimus</span><br>
			<img src="/img/joon.gif" width="370" height="1" border="0" alt="">
			<span class="text"><b>{VAR:question}</b></span><br>
			<br>

			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<!-- SUB: ANSWER -->
				<tr>
					<td class="textSmall"><b>{VAR:answer}</b></td>
				</tr>
				<tr>
					<td class="textSmall"><img src="/img/ruut.gif" width="{VAR:width}" height="5" border="0" alt=""> ({VAR:percent}%)</td>
				</tr>
				<tr>
					<td><img src="/img/trans.gif" border="0" width="1" height="5" alt=""></td>
				</tr>
				<!-- END SUB: ANSWER -->
			</table>
			<br>
			<img src="/img/trans.gif" width="1" height="20" border="0" alt=""><br>


			<img src="img/joon.gif" width="370" height="1" border="0" alt=""><br>
			<img src="img/trans.gif" width="1" height="10" border="0" alt=""><br>
			{VAR:addcomment}

			<!--begin ARHIIV-->
			<br>
			<table border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td class="text"><b>Küsitluse arhiiv</b></td>
			    <td><img src="/img/trans.gif" width="10" height="13" border="0" alt=""></td>
			  </tr>
			</table>

			<img src="/img/joon.gif" width="370" height="1" border="0" alt=""><br>
			<img src="/img/trans.gif" width="1" height="10" border="0" alt=""><br>

			<!--kysimus-->
			<!-- SUB: QUESTION -->
			<span class="textSmall">: <b><a href="/poll.{VAR:ext}?id={VAR:poll_id}" class="linkSin">{VAR:question}</a></b></span>

			<span class="textSmall"><a href="/comments.{VAR:ext}?section={VAR:poll_id}" class="link2"> {VAR:num_comments}</a></span><br>
			<img src="/img/trans.gif" width="1" height="10" border="0" alt=""><br>
			<!-- END SUB: QUESTION -->
			<!--end ARHIIV-->
			<br>
    </td>
    <!--end KESKMISE TEKSTI OSA-->
  </tr>
</table>


