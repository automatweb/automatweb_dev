<h6>object: {VAR:name}<h6>
<center>
{VAR:abix}
	<table width=500 border=1 cellpadding=0 cellspacing=0>
	<tr>
		<td colspan=3>
			<i>
			<b>{VAR:nms}
				<!-- SUB: tee -->
				&nbsp;/&nbsp;<a href={VAR:link}>{VAR:name}</a>
				<!-- END SUB: tee -->
			</b>
			</i> <small><small>{VAR:total}</small></small>
		</td>
	</tr>
	<tr>
			{VAR:tulbad}
			<!-- SUB: tulp -->
			<td valign=top>{VAR:dirs}</td>
			<!-- END SUB: tulp -->

			<!-- SUB: dir -->
			&nbsp;&nbsp;&nbsp;<b><a href={VAR:link}>{VAR:name}</a></b><br />
			<!-- END SUB: dir -->
	</tr>	

	<!-- SUB: links -->	
	<tr>
		<td>&nbsp;&nbsp;&nbsp;{VAR:l_name}&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;&nbsp;<small><u>{VAR:l_url}</u>&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;&nbsp;<i>{VAR:l_comment}</i></small>&nbsp;&nbsp;</td>
	</tr>
	<!-- END SUB: links -->
	
	
	<tr>
		<td colspan=3>
			<hr>statusbar:  
			&nbsp;linke kataloogis:	{VAR:total2}
		</td>
	</tr>
	</table>
</center>
