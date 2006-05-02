<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> AutomatWeb&reg; {VAR:site_title}</TITLE>
<link REL="icon" HREF="/favicon.gif" TYPE="image/gif">
<META NAME="Generator" CONTENT="AutomatWeb&reg;">
<META NAME="Author" CONTENT="Struktuur Meedia">

		<meta name="Keywords" content="{VAR:keywords}">
		<meta name="Description" content="{VAR:description}">


		<link rel=stylesheet href="{VAR:baseurl}/css/styles.css" type="text/css">
		<link rel=stylesheet href="{VAR:baseurl}/css/site.css" type="text/css">


<script language="JavaScript">
		<!-- Hide JavaScript
		 if (navigator.appName.toUpperCase().match(/NETSCAPE/) != null) {
			document.write('<link rel="stylesheet" href="{VAR:baseurl}/css/form_ns.css">')}
		 else {
			document.write('<link rel="stylesheet" href="{VAR:baseurl}/css/form_ie.css">')}
		//-->
		</script>

</head>

<BODY bgcolor="#FFFFFF" marginwidth="20" marginheight="0" leftmargin="20" topmargin="0" >


<span class="textpealkiri"><font color="#000000">{VAR:title}</font></span><br>
<IMG src="{VAR:baseurl}/img/trans.gif" WIDTH="1" HEIGHT="10" BORDER=0 ALT=""><br>
<span class="text2">{VAR:lead}
<br>
{VAR:text}</span><br>

<!-- SUB: image -->
<table border=0 cellpadding=0 cellspacing=0 {VAR:align}>
	<tr>
		<td><img src='{VAR:imgref}'></td>
	</tr>
	<tr>
		<td class="text">{VAR:imgcaption}</td>
	</tr>
<!-- END SUB: image -->
<!-- SUB: image_linked -->
<table border=0 cellpadding=0 cellspacing=0 {VAR:align}>
	<tr>
		<td><a href='{VAR:plink}'><img border=0 src='{VAR:imgref}'></a></td>
	</tr>
	<tr>
		<td class="text">{VAR:imgcaption}</td>
	</tr>
<!-- END SUB: image_linked -->

<!-- SUB: link -->
<a {VAR:target} href='{VAR:url}'>{VAR:caption}</a>
<!-- END SUB: link -->

</center>
</span><br>
</body>
</html>
