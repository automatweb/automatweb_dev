<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={VAR:charset}" />
<title>{VAR:title_action}{VAR:uid}@AutomatWeb</title>
<link href="{VAR:baseurl}/automatweb/css/stiil.css" rel="stylesheet" type="text/css" />
<!--[if lt IE 7]>
    <link rel="stylesheet" type="text/css" href="{VAR:baseurl}/automatweb/css/iefix.css" />
<![endif]-->
<link href="{VAR:baseurl}/automatweb/css/sisu.css" rel="stylesheet" type="text/css" />
<link href="{VAR:baseurl}/automatweb/css/aw06.css" rel="stylesheet" type="text/css" />


<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/browserdetect.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/ajax.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/CalendarPopup.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/popup_menu.js"></script>


<script type="text/JavaScript">
<!--
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function generic_loader()
{
	// don't do anything. screw you.
}
	
function check_generic_loader()
{
	if (generic_loader)
	{
		generic_loader();
	}
};
		
function generic_unloader()
{
	// don't do anything. screw you.
}
							
function check_generic_unloader()
{
	if (generic_unloader)
	{
		generic_unloader();
	}
};
//-->
</script>

</head>

<body  onBeforeUnload="check_generic_unloader();" onLoad="check_generic_loader();">
<!-- päis -->
		<!-- SUB: YAH -->
<div id="pais">
	<div class="logo">
		<span>{VAR:prod_family}</span>
		<a href="{VAR:baseurl}/automatweb/" title="AutomatWeb"><img src="{VAR:baseurl}/automatweb/images/aw06/aw_logo.gif" alt="AutomatWeb.com" width="183" height="34" border="0" /></a>
	</div>
	<div class="top-left-menyy"><a href="{VAR:cur_p_url}">{VAR:cur_p_name}</a> | <a href="{VAR:cur_co_url}">{VAR:cur_co_name}</a> | {VAR:cur_class} | <a href="{VAR:cur_obj_url}">{VAR:cur_obj_name}</a></div>
<!--	<div class="top-left-menyy">{VAR:cur_p_url} | {VAR:cur_co_url} | {VAR:cur_class} | <a href="{VAR:cur_obj_url}">{VAR:cur_obj_name}</a></div>-->
	<div class="top-right-menyy">
		{VAR:lang_pop}
		{VAR:settings_pop}

		<a href="{VAR:baseurl}/orb.aw?class=users&action=logout" class="logout">Logi välja</a>
	</div>
	<div class="olekuriba">Asukoht:
		{VAR:site_title}
	</div>
		<!-- END SUB: YAH -->

	{VAR:content}
<!-- //sisu -->
<!-- jalus -->
<!-- SUB: YAH2 -->
	<div id="jalus">
		AutomatWeb&reg; on Struktuur Meedia registreeritud kaubam&auml;rk. K&otilde;ik &otilde;igused kaitstud, &copy; 1999-2006. <br />
		Palun k&uuml;lasta meie kodulehek&uuml;lgi: <a href="http://www.struktuur.ee">Struktuur Meedia</a>, <a href="http://www.automatweb.com">AutomatWeb</a>.
	</div>
<!-- END SUB: YAH2 -->
<!--//jalus -->
</body>
</html>
