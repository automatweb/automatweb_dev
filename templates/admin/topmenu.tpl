<html>
<head>
<title>AW Menu</title>
<link rel="stylesheet" href="/automatweb/css/top_frame.css">
<script language=javascript>
if (document.all)
	document.write("<style type='text/css'>#down { position: relative; visibility: visible; }</style>");
else
	document.write("<style type='text/css'>#down { position: absolute; visibility: visible; left: 10px; top: 56px; }</style>");
</script>
<style type="text/css">

#dd5  { position: absolute; visibility: visible; left: -3px; top: -8px; }
<!-- SUB: L1_MENU_LINE1 -->

#bob{VAR:menu_id} { position: relative; visibility: visible;}
#dip{VAR:menu_id} { position: absolute; visibility: hidden; left: 0px; top: 0px;}

#dd{VAR:menu_id}  { position: absolute; visibility: hidden; left: 10px; top: -6px;}
<!-- END SUB: L1_MENU_LINE1 -->

</style>
<script language=javascript>

function hideAll()
{
<!-- SUB: L1_MENU_LINE2 -->

	if (document.all)
	{
		document.all.dip{VAR:menu_id}.style.visibility = 'hidden';
		document.all.dd{VAR:menu_id}.style.visibility = 'hidden';
	}
	else
	{
		document.bob{VAR:menu_id}.document.dip{VAR:menu_id}.visibility = 'hidden';
		document.down.document.dd{VAR:menu_id}.visibility = 'hidden';
	}


<!-- END SUB: L1_MENU_LINE2 -->
}

function onOff(paren,lay,vis,low)
{
	hideAll();

	if (document.all)
	{
		eval("document.all."+lay+".style.visibility='"+vis+"'");
		eval("document.all."+low+".style.visibility='visible'");
	}
	else
	{
		eval("document."+paren+".document."+lay+".visibility='"+vis+"'");
		eval("document.down.document."+low+".visibility='visible'");
	}
}

</script>
</head>

<body bgcolor="#FFFFFF">
<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td><img src='/images/trans.gif' width=570 height=15 border=0></td>
<td rowspan=3 valign=bottom width=179><img src="images/logo.gif" align="" width="177" height="46" border="0" alt=""></td>
</tr>
<tr><td background="images/kast_taust_tyhi.gif" height="18">
<table border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td height="18" class="peaLingidText" background="images/kast_taust_tyhi.gif"><img src="images/transparent.gif" width="25" height="1" border="0"></td>


<!-- SUB: L1_MENU_LINE_3 -->

		<td height="18" class="peaLingidText">
			<span id="bob{VAR:menu_id}">
			<script language=javascript>
				if (document.all)
					document.write("<table border=0 cellpadding=0 cellspacing=0>");
				else
					document.write("<table align=left border=0 cellpadding=0 cellspacing=0>");
			</script><tr><td class="peaLingidText" height=18><img src="/automatweb/images/kast_ots_v.gif" width="15" height="18" align=absmiddle></td><td class="peaLingidText" height=18 background="/automatweb/images/kast_taust.gif"><a class="peaLingid" href="#" onClick="onOff('bob{VAR:menu_id}','dip{VAR:menu_id}','visible','dd{VAR:menu_id}');">{VAR:text}</a><img src="/automatweb/images/kast_ots_p.gif" width="15" height="18" align=absmiddle></td></tr></table>
				<span id="dip{VAR:menu_id}"><table border=0 cellpadding=0 cellspacing=0><tr><td class="peaLingidText" height=18><img src="/automatweb/images/kast_b_ots_v.gif" width="15" height="18" align=absmiddle></td><td  background="/automatweb/images/kast_b_taust.gif" height=18><a class="peaLingidText" href="#">{VAR:text}</a><img src="/automatweb/images/kast_b_ots_p.gif" width="15" height="18" align=absmiddle></td></tr></table></span>
			</span>
		</td>
<!-- END SUB: L1_MENU_LINE_3 -->


	</tr>
</table>
</td>
</tr>
<tr>
<td background="images/alamtaust.gif" height="17" class="alamLingidText">
<span id="down">
	<span id="dd5">
		<img src="images/alamtaust.gif" width=550 height=17 border=0>
	</span>
<!-- SUB: L1_MENU_LINE4 -->
	<span id="dd{VAR:menu_id}">
		<!-- SUB: L2_MENU_BEGIN -->
			<a href="{VAR:url}" class="alamLingid" target="main"><b>{VAR:text}</b></a>
		<!-- END SUB: L2_MENU_BEGIN -->

		<!-- SUB: L2_MENU -->

			&nbsp;|&nbsp;<a href="{VAR:url}" class="alamLingid" target="main"><b>{VAR:text}</b></a>
		<!-- END SUB: L2_MENU -->
	</span>

<!-- END SUB: L1_MENU_LINE4 -->
</span>
</td>
</tr>
</table>

</body>

</html>
