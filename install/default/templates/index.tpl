<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> automatweb | com</TITLE>
<link REL="icon" HREF="/favicon.ico" TYPE="image/x-icon">
<meta http-equiv="Content-Type" content="text/html; charset={VAR:charset}">
<META NAME="Generator" CONTENT="AutomatWeb&trade;">
<META NAME="Author" CONTENT="Struktuur Meedia">

		<meta name="Keywords" content="{VAR:keywords}">
		<meta name="Description" content="{VAR:description}">


		<link rel=stylesheet href="{VAR:baseurl}/css/styles.css" type="text/css">


		<link rel=stylesheet href="{VAR:baseurl}/css/site.css" type="text/css">


		<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
		<!--
function remote(toolbar,width,height,file) {
	self.name = "root";
	var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=0, "+
	"menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	openwindow = window.open(file,"remote",wprops);
}

		function box2(caption,url){
		var answer=confirm(caption)
		if (answer)
		window.location=url
		}

function gimme()
{
}

// AW Javascript functions

// see on nö "core" funktsioon popuppide kuvamiseks. Interface juures on soovitav kasutada
// jargnenaid funktsioone
function _aw_popup(file,name,toolbar,location,status,menubar,scrollbars,resizable,width,height)
{
	 var wprops = 	"toolbar=" + toolbar + "," + 
	 		"location= " + location + "," +
			"directories=0," + 
			"status=" + status + "," +
	        	"menubar=" + menubar + "," +
			"scrollbars=" + scrollbars + "," +
			"resizable=" + resizable + "," +
			"width=" + width + "," +
			"height=" + height;

	openwindow = window.open(file,name,wprops);
};

function aw_popup(file,name,width,height)
{
	_aw_popup(file,name,0,1,0,0,0,1,width,height);
}

function aw_popup_s(file,name,width,height)
{
	_aw_popup(file,name,0,1,0,0,1,1,width,height);
};

function aw_popup_scroll(file,name,width,height)
{
	_aw_popup(file,name,0,0,0,0,1,1,width,height);
};

		//-->
		</SCRIPT>

<script language="javascript">
<!--
var Open = ""
var Closed = ""

function preload(){
if(document.images){
	Open = new Image(16,13)    
	Closed = new Image(16,13)
	Open.src = "{VAR:baseurl}/img/nool1down.gif"
	Closed.src = "{VAR:baseurl}/img/nool1.gif"
}}


function showhide(what,what2)
{
	if (what && what.style && what2)
	{
		if (what.style.display=='none')
		{
			what.style.display='';
			what2.src="{VAR:baseurl}/img/nool1down.gif";
		}
		else
		{
			what.style.display='none'
			what2.src="{VAR:baseurl}/img/nool1.gif";
		}
	}
}

function nothing()
{
	return false;
}
-->
</script>
<script language="Javascript">
<!--

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

// -->
</script>

<script language="JavaScript">
		<!-- Hide JavaScript
		 if (navigator.appName.toUpperCase().match(/NETSCAPE/) != null) {
			document.write('<link rel="stylesheet" href="{VAR:baseurl}/css/form_ns.css">')}
		 else {
			document.write('<link rel="stylesheet" href="{VAR:baseurl}/css/form_ie.css">')}
		//-->
		</script>

</head>

<BODY bgcolor="#FFFFFF" marginwidth="20" marginheight="0" leftmargin="0" topmargin="0" onLoad="gimme()">
<center>


	{VAR:content}


</center>



</body>
</html>













