<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css">
<script src="{VAR:baseurl}/automatweb/js/ua.js"></script>
<script src="{VAR:baseurl}/automatweb/js/ftiens4.js"></script>
<script language="javascript">
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v3.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

USETEXTLINKS = 1
ICONPATH = '{VAR:baseurl}/automatweb/images/'
PERSERVESTATE = 0
LINKTARGET = 'list'
SHOWNODE = ''
HIGHLIGHT = 1;
HIGHLIGHT_COLOR = '#0000FF';
HIGHLIGHT_BG = '#EEEEEE';

pr_{VAR:root} = gFld("<b>AutomatWeb</b>", "{VAR:rooturl}", "{VAR:baseurl}/automatweb/images/aw_ikoon.gif")
<!-- SUB: TREE -->
	pr_{VAR:id} = insFld(pr_{VAR:parent}, gFld("{VAR:name}", "{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: TREE -->
<!-- SUB: DOC -->
	pr_{VAR:id} = insDoc(pr_{VAR:parent}, gLnk("R", "{VAR:name}","{VAR:url}","{VAR:iconurl}"));
<!-- END SUB: DOC -->

foldersTree = pr_{VAR:root};
</script>

</head>
<body bgcolor="#eeeeee" topmargin=0 marginheight=0>
<table border=0 width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left" class="yah">&nbsp;{VAR:uid} @ {VAR:date}</td></tr></table><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br><table border="0" cellspacing="0" cellpadding="1" width=100%>
	<tr>
	<td background="images/awmenueditor_iconbar_back.gif">
		<table background="images/awmenueditor_iconbar_back.gif" border="0" cellspacing="0" cellpadding="1" width=100%>
			<tr>
				<td background="images/awmenueditor_iconbar_back.gif">
					<table background="images/awmenueditor_iconbar_back.gif" border="0" cellspacing="0" cellpadding="0" width=100%>
						<tr>
							<td background="images/awmenueditor_iconbar_back.gif"><table background="images/awmenueditor_iconbar_back.gif" border="0" cellspacing="0" cellpadding="0">
                <form action='orb.{VAR:ext}' method='get' name='pfft'>
                <tr>
									<td height="20" colspan="11" background="images/awmenueditor_iconbar_back.gif" align=center><select class='formselect' name='period'>{VAR:periods}</select></td><td class="tableinside"><a href='javascript:document.pfft.submit()' onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','images/blue/awicons/refresh_over.gif',1)"><img name='refresh' alt='{VAR:LC_MENUEDIT_REFRESH}' border='0' SRC='{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif' width='25' height='25'></a><input type='hidden' name='action' value='folders'><input type='hidden' name='class' value='admin_folders'></td>
                </tr>
                </form>
								</table>
							</td>
						</tr>
					</table>
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>

<!-- Build the browser's objects and display default view of the
     tree. -->
<script>initializeDocument()</script>

</html>
