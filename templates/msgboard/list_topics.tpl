

<table width="100%" border="0" cellspacing="2" cellpadding="0" height="100" >
	<tr> 
		<td align="left" valign="top"> 
			<table width="98%" border="0" cellspacing="1" cellpadding="0" height="100%">
				<tr> 
					<td align="left" valign="top"> 
						<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
							<tr> 
								<td bgcolor="#efefef" colspan="4" height="14"> 
<center>
<table border="0" cellspacing="0" cellpadding="2" bgcolor="#ffffff">
<tr>
<td class="mboardtab">
	<a href="{VAR:newtopic_link}" class="mboardtab">{VAR:LC_MSGBOARD_NEW_SUBJECT}</a>  
</td>
<td>
	<img src="images/trans.gif" width="5">
</td>
<td class="mboardtab">
	<a href="{VAR:search_forum_link}" class="mboardtab">{VAR:LC_MSGBOARD_SEARCH} </a>  
</td>
<td>
	<img src="images/trans.gif" width="5">
</td>
<td class="mboardtab">
	<a href="{VAR:mark_all_read}" class="mboardtab">{VAR:LC_MSGBOARD_ALL_READ} </a> 
</td>
<td>
	<img src="images/trans.gif" width="5">
</td>
<td class="mboardtab">
	<a href="{VAR:props_link}" class="mboardtab">H‰‰lestamine </a>  
</td>
</tr>
</table>
<img src="images/mboard_joon.gif" border="0" width="100%" height="2" alt=""><br>

</center>
																			<!--
																			<a href="{VAR:topic_detail_link}" class="sisuteks">N‰ita detailselt </a> 
																			-->
									</div>
								</td>
							</tr>
							<tr bgcolor="#ffffff" class="mboard"> 
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
								<td class="mboardtexttopic" bgcolor="#eCeCeC" valign="top">{VAR:image}</td>
								<td class="mboardtexttopic" bgcolor="#eCeCeC" valign="top"><a href="{VAR:topic_link}">{VAR:topic}</a>
									<!-- SUB: NEW_MSGS -->
										<font color="#990000">{VAR:LC_MSGBOARD_NEW}!</font>
									<!-- END SUB: NEW_MSGS -->

									<!-- SUB: DELETE -->
										<br><a href=""></a>
									<!-- END SUB: DELETE -->
									
								</td>
								<td height="21" class="mboardtexttopic" align="center"> 
									{VAR:from}
								</td>
								<td height="21" class="mboardtexttopic" align="center"> 
									{VAR:created}
								</td>
							</tr>
							<!-- END SUB: TOPIC_EVEN -->

							<!-- SUB: TOPIC_ODD -->
							<tr bgcolor="#FFFFFF"> 
							<td class="mboardtexttopic" bgcolor="#FFFFFF" valign="top">{VAR:image} </td>
							<td class="mboardtexttopic" bgcolor="#FFFFFF" valign="top"><a href="{VAR:topic_link}" class="header4">{VAR:topic}</a>
										{VAR:DELETE}
										{VAR:NEW}
								</td>
								<td height="21" class="mboardtexttopic" align="center"> 
									{VAR:from}
								</td>
								<td height="21" class="mboardtexttopic" align="center"> 
									{VAR:created}
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
