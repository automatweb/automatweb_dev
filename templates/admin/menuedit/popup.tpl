<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<script src="{VAR:baseurl}/automatweb/js/ftiens4.js"></script>
<script language=javascript>
document.write("<body bgcolor=#eeeeee>");

pr_{VAR:root} = gFld("<i>Site</i>", "","images/ftv2doc.gif")

<!-- SUB: TREE -->
	pr_{VAR:id} = insFld(pr_{VAR:parent}, gFld("{VAR:name}", "menuedit.{VAR:ext}?type=popup_objects&parent={VAR:id}&tpl={VAR:tpl}","{VAR:iconurl}"));
<!-- END SUB: TREE -->
<!-- SUB: DOC -->
	pr_{VAR:id} = insDoc(pr_{VAR:parent}, gLnk("{VAR:name}", "{VAR:name}","menuedit.aw?type=popup_objects&parent={VAR:id}&tpl={VAR:tpl}","{VAR:iconurl}"));
<!-- END SUB: DOC -->


  if (doc.all) 
    browserVersion = 1 //IE4   
  else 
    if (doc.layers) 
      browserVersion = 2 //NS4 
    else 
      browserVersion = 0 //other 

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
