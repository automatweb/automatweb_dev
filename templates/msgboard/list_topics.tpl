<table width="100%" border="0" cellspacing="14" cellpadding="0" height="100" class="sisuteks">
	<tr> 
		<td align="left" valign="top"> 
			<table width="98%" border="0" cellspacing="1" cellpadding="0" height="100%" class="sisuteks">
				<tr> 
					<td align="left" valign="top" class="maintext"> 
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="sisuteks" align="center">
							<tr> 
								<td colspan="4" height="14" class="header3"> 
									<div align="center">
																			<a href="{VAR:newtopic_link}" class="sisuteks">{VAR:LC_MSGBOARD_NEW_SUBJECT}</a> | 
																			<a href="{VAR:search_link}" class="sisuteks">{VAR:LC_MSGBOARD_SEARCH} </a> | 
																			<a href="{VAR:mark_all_read}" class="sisuteks">{VAR:LC_MSGBOARD_ALL_READ} </a> |
																			<a href="{VAR:props_link}" class="sisuteks">H‰‰lestamine </a> | 
									</div>
								</td>
							</tr>
							<tr bgcolor="#CCCCCC" class="header4"> 
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
								<td class="header5" bgcolor="#CCCCCC" valign="top">{VAR:image}</td>
								<td class="header5" bgcolor="#CCCCCC" valign="top"><a href="{VAR:topic_link}">{VAR:topic}</a>
									<!-- SUB: NEW -->
										<font color="#990000">{VAR:LC_MSGBOARD_NEW}!</font>
									<!-- END SUB: NEW -->

									<!-- SUB: DELETE -->
										<br><a href=""></a>
									<!-- END SUB: DELETE -->
									
								</td>
								<td height="21" class="header5"> 
									<div class="header5" align="center">{VAR:from}</div>
								</td>
								<td height="21" class="header5"> 
									<div class="header5" align="center">{VAR:created}</div>
								</td>
							</tr>
							<!-- END SUB: TOPIC_EVEN -->

							<!-- SUB: TOPIC_ODD -->
							<tr> 
							<td class="foorum_cont" bgcolor="#FFFFFF" valign="top">{VAR:image} </td>
							<td class="header4" bgcolor="#FFFFFF" valign="top"><a href="{VAR:topic_link}" class="header4">{VAR:topic}</a>
										{VAR:DELETE}
										{VAR:NEW}
								</td>
								<td height="21" class="header4"> 
									<div class="header4" align="center">{VAR:from}</div>
								</td>
								<td height="21" class="header4"> 
									<div class="header4" align="center">{VAR:created}</div>
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
			<a href='{VAR:pagelink}' class="links"><b><font color="#FF9900">{VAR:linktext}</font></b></a>&nbsp;&nbsp;
			<!-- END SUB: PAGE -->
			<!-- SUB: SEL_PAGE -->
			<a href='{VAR:pagelink}' class="links"><b><font color="#FF9900">&gt;{VAR:linktext}&lt;</font></b></a>&nbsp;&nbsp;
			<!-- END SUB: SEL_PAGE -->
			</font><br>
			<!-- END SUB: PAGES -->
		</td>
	</tr>
</table>
