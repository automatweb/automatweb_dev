<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<script src="{VAR:baseurl}/automatweb/js/ftiens4.js"></script>




<script language=javascript>
document.write("<body bgcolor=#eeeeee><table border=0 width=\"100%\" cellspacing=\"0\" cellpadding=\"2\"><tr><td align=\"left\" class=\"yah\">&nbsp;{VAR:uid} @ {VAR:date}</td></tr></table><IMG SRC=\"{VAR:baseurl}/automatweb/images/trans.gif\" WIDTH=\"1\" HEIGHT=\"1\" BORDER=0 ALT=\"\"><br><table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=100%>			\
		<tr>																																																			\
			<td class=\"tableborder\">																																								\
				<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=100%>																		\
					<tr>																																																			\
						<td class=\"tableshadow\">																																								\
								<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=100%><tr><td class=\"tableinside\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">																		\
		<form action='menuedit.{VAR:ext}' method='get' name='pfft'>																								\
					<tr>																																																\
						<td class=\"tableinside\" height=\"20\" colspan=\"11\" align=center><select class='formselect' name='period'>{VAR:periods}</select></td><td class=\"tableinside\"><a href='javascript:document.pfft.submit()' onMouseOut=\"MM_swapImgRestore()\" onMouseOver=\"MM_swapImage('refresh','','images/blue/awicons/refresh_over.gif',1)\"><img name='refresh' alt='{VAR:LC_MENUEDIT_REFRESH}' border='0' SRC='{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif' width='25' height='25'></a><input type='hidden' name='type' value='folders'>															\
						</td>																																															\
					</tr>																																																\
				</form>																																																\
				</table></td></tr></table>																																															\
			</td>																																																		\
		</tr>																																																			\
			</table>																																																	\
			</td>																																																		\
		</tr>																																																			\
		</table>																																																	\
						<table border=0 cellpadding=0 cellspacing=0 width=100%>																		\
					<tr>																																																\
						<td align='center'><font face=verdana size=1><b></b></font></td>																	\
					</tr>																																																\
				</table>																																															\
		");

pr_{VAR:root} = gFld("<b>AutomatWeb</b>", "menuedit_right.{VAR:ext}?parent={VAR:root}","images/aw_ikoon.gif")

<!-- SUB: TREE -->
	pr_{VAR:id} = insFld(pr_{VAR:parent}, gFld("{VAR:name}", "{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: TREE -->
<!-- SUB: DOC -->
	pr_{VAR:id} = insDoc(pr_{VAR:parent}, gLnk("{VAR:name}", "{VAR:name}","{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: DOC -->


  if (doc.all) 
    browserVersion = 1 //IE4   
  else 
    if (doc.layers) 
      browserVersion = 2 //NS4 
    else 
      browserVersion = 0//other 
 
 pr_{VAR:root}.initialize(0, 1, "") 
 pr_{VAR:root}.display()
  
  if (browserVersion > 0) 
  { 
    doc.write("<layer top="+indexOfEntries[nEntries-1].navObj.top+">&nbsp;</layer></form>") 
 
    // close the whole tree 
    clickOnNode(0) 
	  // open the root folder 
    clickOnNode(0) 
  } 


</script>

</head>
</html>
