<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<script src="{VAR:baseurl}/automatweb/js/ftiens4.js"></script>
<script language=javascript>
document.write("<body bgcolor=#eeeeee><table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=100%>			\
		<tr>																																																			\
			<td bgcolor=\"#000000\">																																								\
				<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=100%>																		\
		<form action='menuedit.{VAR:ext}' method='get' name='pfft'>																								\
					<tr>																																																\
						<td height=\"20\" colspan=\"11\" class=\"fgtitle_new\" align=center><span class=\"fgtitle_text\"><select class='small_button' name='period'>{VAR:periods}</select><a class='fgtitle_link' href='javascript:document.pfft.submit()'><font size=1 face=arial>&nbsp;{VAR:LC_MENUEDIT_REFRESH}</font></a><input type='hidden' name='type' value='folders'></span>															\
						</td>																																															\
					</tr>																																																\
				</form>																																																\
				</table>																																															\
			</td>																																																		\
		</tr>																																																			\
		<tr>																																																			\
			<td bgcolor='#CCCCCC'>																																									\
				<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>																		\
					<tr>																																																\
						<td class=\"title\" align='center'>[{VAR:uid} @ {VAR:date}]</td>																	\
					</tr>																																																\
				</table>																																															\
			</td>																																																		\
		</tr>																																																			\
		</table>																																																	\
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
