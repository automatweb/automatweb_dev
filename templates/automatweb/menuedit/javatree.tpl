
<table border=0 width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left" class="yah">&nbsp;{VAR:uid} @ {VAR:date}</td>
	</tr>
</table>
<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
<applet codebase="/automatweb/java/javatree" code="menuTree.class" width="100%" height="98%">
<param name="background_color" value="#FCFCF4">
<param name="mouseover_color" value="#CCCCCC">
<param name="selected_color" value="#B58D47">
<param name="text_color" value=" #0018AF">
<param name="active_text_color" value="#424242">
<param name="top_color" value="#E0E2E5">
<param name="font_size" value="11">
<param name="font" value="Times New Roman">
<param name="perioodiline" value="ON"><!-- ON/OFF , kui on, siis puul perioodide valimise võimalus-->
<param name="deemon" value="OFF"><!-- ON/OFF , kui off, siis puul refresh nupp-->
<param name="server" value="{VAR:demon_server}"><!-- deemoni asukoha server -->
<param name="port" value="{VAR:demon_port}">
<param name="sait" value="{VAR:site_id}"><!-- saidi ID -->
<param name="session" value="{VAR:session}">
<param name="url" value="{VAR:baseurl}"> <!-- saidi url -->
<param name="rootmenu" value="{VAR:rootmenu}"><!--puu alguse oid-->
</applet>