<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css">
<script type="text/javascript">

function activate_window()
{
        if (window != window.top)
        {
                if (top.document.getElementById('status') == '[object HTMLDivElement]') //et siis kontrollime kas aken on desktopis
                {
                        top.winMakeActive2((window.name.indexOf('frei') == 1) ? window.name : parent.window.name);
                }
        }
}

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
</script>
</head>
<body bgcolor="#eeeeee">
<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left" class="yah">&nbsp;{VAR:uid} @ {VAR:date}</td>
	</tr>
	<!-- SUB: has_toolbar -->
	<tr>
		<form action='orb.{VAR:ext}' method='get' name='pform'>
		<td>{VAR:toolbar}</td>
		</form>
	</tr>
	<!-- END SUB: has_toolbar -->
</table>

{VAR:TREE}




</html>
