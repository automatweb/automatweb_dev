<table border=0 width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left" class="yah">&nbsp;{VAR:uid} @ {VAR:date}</td>
	</tr>
</table>
<applet codebase="/automatweb/java/" code="menuThread2.class" width="100%" height="98%">
<param name="background_color" value="#EEEEEE">
<param name="mouseover_color" value="#8AABBE">
<param name="selected_color" value="#BDD2DE">
<param name="text_color" value="#000000">
<param name="top_color" value="#DBE8EE">
<param name="font" value="Times New Roman">
<param name="perioodiline" value="OFF"><!-- ON/OFF , kui on, siis puul perioodide valimise võimalus-->
<param name="deemon" value="ON"><!-- ON/OFF , kui off, siis puul refresh nupp-->
<param name="server" value="{VAR:demon_server}"><!-- deemoni asukoha server -->
<param name="port" value="{VAR:demon_port}">
<param name="sait" value="{VAR:site_id}"><!-- saidi ID -->
<param name="session" value="{VAR:session}">
<param name="url" value="{VAR:baseurl}"> <!-- saidi url -->
<param name="rootmenu" value="{VAR:rootmenu}"><!--puu alguse oid-->
</applet>