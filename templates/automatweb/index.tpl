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

<!-- ma ei tea kas neid kõiki vaja läheb, aga las nad olla siin -->
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/browserdetect.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/ajax.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/CalendarPopup.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/popup_menu.js"></script>
<!-- // ma ei tea kas neid kõiki vaja läheb, aga las nad olla siin -->

</head>

<body onResize="document.execCommand('Refresh')">
<!-- päis -->
<div id="pais">
	<div class="logo">
		<span>{VAR:prod_family}</span>
		<a href="{VAR:baseurl}/automatweb/" title="AutomatWeb"><img src="{VAR:baseurl}/automatweb/images/aw06/aw_logo.gif" alt="AutomatWeb.com" width="183" height="34" border="0" /></a>
	</div>
	<div class="top-left-menyy">{VAR:cur_p_url} | {VAR:cur_co_url} | {VAR:cur_class} | <a href="{VAR:cur_obj_url}">{VAR:cur_obj_name}</a></div>
	<div class="top-right-menyy">
		{VAR:lang_pop}
		{VAR:settings_pop}

		<a href="{VAR:baseurl}/orb.aw?class=users&action=logout" class="logout">Logi välja</a>
	</div>
	<div class="olekuriba">Asukoht:
		<!-- SUB: YAH -->
		{VAR:site_title}
		<!-- END SUB: YAH -->
	</div>

	{VAR:content}
<!-- //sisu -->
<!-- jalus -->
	<div id="jalus">
		AutomatWeb&reg; on Struktuur Meedia registreeritud kaubamärk. Kõik õigused kaitstud, &copy; 1999-2006. <br />
		Palun külasta meie kodulehekülgi: <a href="http://www.struktuur.ee">Struktuur Meedia</a>, <a href="http://www.automatweb.com">AutomatWeb</a>.
	</div>
<!--//jalus -->
<img src="{VAR:baseurl}/automatweb/images/aw06/blank.gif" alt="#" width="1010" height="1" />
</body>
</html>
