<table width="100%" border="0" cellspacing="14" cellpadding="0" height="100" class="sisuteks">
	<tr> 
		<td align="left" valign="top"> 
			<table width="98%" border="0" cellspacing="1" cellpadding="0" height="100%" bgcolor="#FFCC66" class="sisuteks">
				<tr> 
					<td align="left" valign="top" class="maintext"> 
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="sisuteks" align="center">
							<tr> 
								<td colspan="4" height="14"> 
									<div align="center"><a href="#" class="sisuteks">{VAR:LC_MSGBOARD_GO_UP}</a> | 
																			<a href="{VAR:baseurl}/comments.aw?action=addtopic" class="sisuteks">{VAR:LC_MSGBOARD_NEW_SUBJECT}</a> | 
																			<a href="{VAR:baseurl}/comments.aw?action=topics" class="sisuteks">{VAR:LC_MSGBOARD_SHOW_SIMPLE}</a> | 
																			<a href="{VAR:baseurl}/comments.{VAR:ext}?action=search" class="sisuteks">{VAR:LC_MSGBOARD_SEARCH} </a> | 
																			<a href="{VAR:baseurl}/comments.{VAR:ext}?action=markallread" class="sisuteks">{VAR:LC_MSGBOARD_ALL_READ} </a>
									</div>
								</td>
							</tr>
							<tr bgcolor="#CCCCCC"> 
								<td colspan=2>   
									<div align="left"><b>{VAR:LC_MSGBOARD_TITLE}</b></div>
								</td>
								<td> 
									<div align="center"><b> {VAR:LC_MSGBOARD_POSTED_BY}</b></div>
								</td>
								<td> 
									<div align="center"><b> {VAR:LC_MSGBOARD_POSTED}</b></div>
								</td>
							</tr>
							<!-- SUB: TOPIC_EVEN -->
							<tr> 
								<td class="sisuteks" bgcolor="#CCCCCC" valign="top">{VAR:image}</td>
								<td class="sisuteks" bgcolor="#CCCCCC" valign="top"><a href="comments.aw?section={VAR:topic_id}&msg={VAR:msg_id}" class="sisuteks">{VAR:topic}</a>
									<!-- SUB: NEW -->
										<font color="#990000">{VAR:LC_MSGBOARD_NEW}!</font>
									<!-- END SUB: NEW -->

									<!-- SUB: DELETE -->
										<br><a href="comments.aw?action=delete_topic&id={VAR:topic_id}">{VAR:LC_MSGBOARD_DEL_SUBJ}</a>
									<!-- END SUB: DELETE -->
									
								</td>
								<td height="21" bgcolor="#CCCCCC"> 
									<div class="sisuteks" align="center">{VAR:from}</div>
								</td>
								<td height="21" bgcolor="#CCCCCC"> 
									<div class="sisuteks" align="center">{VAR:created}</div>
								</td>
							</tr>
							<!-- END SUB: TOPIC_EVEN -->

							<!-- SUB: TOPIC_ODD -->
							<tr> 
							<td class="foorum_cont" bgcolor="#FFFFFF" valign="top">{VAR:image} </td>
							<td class="foorum_cont" bgcolor="#FFFFFF" valign="top"><a href="comments.aw?section={VAR:topic_id}&msg={VAR:msg_id}" class="sisuteks">{VAR:topic}</a>
										{VAR:DELETE}
										{VAR:NEW}
								</td>
								<td height="21" bgcolor="#FFFFFF"> 
									<div class="sisuteks" align="center">{VAR:from}</div>
								</td>
								<td height="21" bgcolor="#FFFFFF"> 
									<div class="sisuteks" align="center">{VAR:created}</div>
								</td>
							</tr>
							<!-- END SUB: TOPIC_ODD -->
							
						</table>
					</td>
				</tr>
			</table>
			<!-- SUB: PAGES -->
			<font color="#FFFFFF" face="tahoma, arial" size="1">or&nbsp;select page:&nbsp;
			<!-- SUB: PAGE -->
			<a href='/comments.{VAR:ext}?action=topics&page={VAR:pagenum}' class="links"><b><font color="#FF9900">{VAR:ltext}</font></b></a>&nbsp;&nbsp;
			<!-- END SUB: PAGE -->
			<!-- SUB: SEL_PAGE -->
			<a href='/comments.{VAR:ext}?action=topics&page={VAR:pagenum}' class="links"><b><font color="#FF9900">&gt;{VAR:ltext}&lt;</font></b></a>&nbsp;&nbsp;
			<!-- END SUB: SEL_PAGE -->
			</font><br>
			<!-- END SUB: PAGES -->
		</td>
	</tr>
</table>
