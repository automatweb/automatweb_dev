<script language=javascript>


if (document.all)
	document.write("<style type='text/css'>#down { position: relative; visibility: visible; }</style>");
else
	document.write("<style type='text/css'>#down { position: absolute; visibility: visible; left: 10px; top: 56px; }</style>");
</script>
<style type="text/css">

#dd5  { position: absolute; visibility: visible; left: -3px; top: -8px; }

<!-- L1_MENU_LINE1 -->
#bob1284 { position: relative; visibility: visible;}
#dip1284 { position: absolute; visibility: hidden; left: 0px; top: 0px;}

#dd1284  { position: absolute; visibility: hidden; left: 0px; top: -1px;}
<!--  L1_MENU_LINE1 -->

<!-- L1_MENU_LINE1 -->
#bob1285 { position: relative; visibility: visible;}
#dip1285 { position: absolute; visibility: hidden; left: 0px; top: 0px;}

#dd1285  { position: absolute; visibility: hidden; left: 0px; top: -1px;}
<!-- L1_MENU_LINE1 -->


<!-- L1_MENU_LINE1 -->
#bob1290 { position: relative; visibility: visible;}
#dip1290 { position: absolute; visibility: hidden; left: 0px; top: 0px;}

#dd1290  { position: absolute; visibility: hidden; left: 0px; top: -1px;}
<!--  L1_MENU_LINE1 -->

<!-- L1_MENU_LINE1 -->
#bob1291 { position: relative; visibility: visible;}
#dip1291 { position: absolute; visibility: hidden; left: 0px; top: 0px;}

#dd1291  { position: absolute; visibility: hidden; left: 0px; top: -1px;}
<!-- L1_MENU_LINE1 -->


<!-- L1_MENU_LINE1 -->
#bob1293 { position: relative; visibility: visible;}
#dip1293 { position: absolute; visibility: hidden; left: 0px; top: 0px;}

#dd1293  { position: absolute; visibility: hidden; left: 0px; top: -1px;}
<!-- L1_MENU_LINE1 -->


<!-- L1_MENU_LINE1 -->
#bob1294 { position: relative; visibility: visible;}
#dip1294 { position: absolute; visibility: hidden; left: 0px; top: 0px;}

#dd1294  { position: absolute; visibility: hidden; left: 0px; top: -1px;}
<!-- L1_MENU_LINE1 -->

</style>
<script language=javascript>


var element = 0;
function set_color(vrv) 
{  
	document.menuinfo.elements[element].value=vrv;
} 

function varvivalik(nr)
{  
	element = nr;  
	aken=window.open("colorpicker.aw","varvivalik","HEIGHT=220,WIDTH=310");  
	aken.focus();
}


function hideAll()
{
<!-- L1_MENU_LINE2 -->
	if (document.all)
	{
		document.all.dip1284.style.visibility = 'hidden';
		document.all.dd1284.style.visibility = 'hidden';
	}
	else
	{
		document.bob1284.document.dip1284.visibility = 'hidden';
		document.down.document.dd1284.visibility = 'hidden';
	}
<!-- L1_MENU_LINE2 -->

<!-- L1_MENU_LINE2 -->
	if (document.all)
	{
		document.all.dip1285.style.visibility = 'hidden';
		document.all.dd1285.style.visibility = 'hidden';
	}
	else
	{
		document.bob1285.document.dip1285.visibility = 'hidden';
		document.down.document.dd1285.visibility = 'hidden';
	}
<!-- L1_MENU_LINE2 -->









<!-- L1_MENU_LINE2 -->
	if (document.all)
	{
		document.all.dip1290.style.visibility = 'hidden';
		document.all.dd1290.style.visibility = 'hidden';
	}
	else
	{
		document.bob1290.document.dip1290.visibility = 'hidden';
		document.down.document.dd1290.visibility = 'hidden';
	}
<!-- L1_MENU_LINE2 -->


<!-- L1_MENU_LINE2 -->
	if (document.all)
	{
		document.all.dip1291.style.visibility = 'hidden';
		document.all.dd1291.style.visibility = 'hidden';
	}
	else
	{
		document.bob1291.document.dip1291.visibility = 'hidden';
		document.down.document.dd1291.visibility = 'hidden';
	}
<!-- L1_MENU_LINE2 -->



<!-- L1_MENU_LINE2 -->
	if (document.all)
	{
		document.all.dip1293.style.visibility = 'hidden';
		document.all.dd1293.style.visibility = 'hidden';
	}
	else
	{
		document.bob1293.document.dip1293.visibility = 'hidden';
		document.down.document.dd1293.visibility = 'hidden';
	}
<!-- L1_MENU_LINE2 -->


<!-- L1_MENU_LINE2 -->
	if (document.all)
	{
		document.all.dip1294.style.visibility = 'hidden';
		document.all.dd1294.style.visibility = 'hidden';
	}
	else
	{
		document.bob1294.document.dip1294.visibility = 'hidden';
		document.down.document.dd1294.visibility = 'hidden';
	}
<!-- L1_MENU_LINE2 -->


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




var ops=new Array()
<!-- SUB: FORM -->
ops_{VAR:form_id} = new Array();
<!-- SUB: FORM_OP -->
ops_{VAR:form_id}[{VAR:cnt}] = new Array({VAR:op_id},"{VAR:op_name}");
<!-- END SUB: FORM_OP -->

<!-- END SUB: FORM -->

function clearList(list)
{
	var listlen = list.length;

	for(i=0; i < listlen; i++)
		list.options[0] = null;
}

function addItem(list, arr)
{
	list.options[list.length] = new Option(arr[1],""+arr[0],false,false);
}

function populate_list(el,arr)
{
	clearList(el);
	for (i = 0; i < arr.length; i++)
		addItem(el,arr[i]);
}

var sel_form;
sel_form = 0;

var cur_arr;
cur_arr = 0;

function mk_ops()
{
	if (cur_arr != sel_form)
	{
		cur_arr = sel_form;
		if (eval("typeof(ops_"+sel_form+")") != "undefined")
		{
			eval("far = ops_"+sel_form);
			populate_list(menuinfo.ftpl_lead, far);
			populate_list(menuinfo.ftpl_view, far);
		}
		else
		{
			clearList(menuinfo.ftpl_view);
			clearList(menuinfo.ftpl_lead);
		}
	}
}

function idxforvalue(el,val)
{
	for (i=0; i < el.options.length; i++)
	{
		if (el.options[i].value == val)
		{
			return i;
		}
	}
	return 0;
}


</script>





<script language="Javascript">
function savemenu() {
	document.menuinfo.submit();
}
</script>

<!--<body onLoad="javascript:onOff('bob1284','dip1284','visible','dd1284');">-->


<form action='reforb.{VAR:ext}' name="menuinfo" method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>

<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">


	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="1">
	<tr><td class="tableshadow">

		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td class="tableinside" width="29" height="29"><a
		href="javascript:savemenu()"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" WIDTH="25" HEIGHT="25" BORDER=0 ALT="SAVE"></a></td><td class="tableinside" valign="bottom">


		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
	
		
		<!-- 1 general -->
		<td height="29" class="tableinside" valign="bottom">
			<span id="bob1284">
			<script language=javascript>
				if (document.all)
					document.write("<table border=0 cellpadding=0 cellspacing=0>");
				else
					document.write("<table align=left border=0 cellpadding=0 cellspacing=0>");
			</script><tr><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="19" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="bottom"><a href="#" onClick="onOff('bob1284','dip1284','visible','dd1284');">{VAR:LC_MENUEDIT_MENU_GENERAL}</a></td><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="19" BORDER=0 ALT=""></td></tr></table>		
			

				<span id="dip1284"><table border=0 cellpadding=0 cellspacing=0><tr><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom"><a href="#">{VAR:LC_MENUEDIT_MENU_GENERAL}</a></td><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td></tr></table></span>
			</span>
		</td>
		<!-- 1 general -->

		<!-- 2 templates -->
		<td height="29" class="tableinside" valign="bottom">
			<span id="bob1285">
			<script language=javascript>
				if (document.all)
					document.write("<table border=0 cellpadding=0 cellspacing=0>");
				else
					document.write("<table align=left border=0 cellpadding=0 cellspacing=0>");
			</script><tr><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="19" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="bottom"><a href="#" onClick="onOff('bob1285','dip1285','visible','dd1285');">{VAR:LC_MENUEDIT_DISPLAY}</a></td><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="19" BORDER=0 ALT=""></td></tr></table>		
			

				<span id="dip1285"><table border=0 cellpadding=0 cellspacing=0><tr><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom"><a href="#">{VAR:LC_MENUEDIT_DISPLAY}</a></td><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td></tr></table></span>
			</span>
		</td>
		<!-- 2 templates -->


	
	

	


	


		<!-- 6 PICTURE -->
		<td height="29" class="tableinside" valign="bottom">
			<span id="bob1290">
			<script language=javascript>
				if (document.all)
					document.write("<table border=0 cellpadding=0 cellspacing=0>");
				else
					document.write("<table align=left border=0 cellpadding=0 cellspacing=0>");
			</script><tr><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="19" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="bottom"><a href="#" onClick="onOff('bob1290','dip1290','visible','dd1290');">{VAR:LC_MENUEDIT_PICTURE}</a></td><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="19" BORDER=0 ALT=""></td></tr></table>		
			

				<span id="dip1290"><table border=0 cellpadding=0 cellspacing=0><tr><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom"><a href="#">{VAR:LC_MENUEDIT_PICTURE}</a></td><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td></tr></table></span>
			</span>
		</td>
		<!-- 6 END PICTURE  -->


		<!-- 7 EXPORT -->
		<td height="29" class="tableinside" valign="bottom">
			<span id="bob1291">
			<script language=javascript>
				if (document.all)
					document.write("<table border=0 cellpadding=0 cellspacing=0>");
				else
					document.write("<table align=left border=0 cellpadding=0 cellspacing=0>");
			</script><tr><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="19" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="bottom"><a href="#" onClick="onOff('bob1291','dip1291','visible','dd1291');">{VAR:LC_MENUEDIT_EXPORT}</a></td><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="19" BORDER=0 ALT=""></td></tr></table>		
			

				<span id="dip1291"><table border=0 cellpadding=0 cellspacing=0><tr><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom"><a href="#">{VAR:LC_MENUEDIT_EXPORT}</a></td><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td></tr></table></span>
			</span>
		</td>
		<!-- 7 EXPORT  -->


		<!-- 9 KEYWORDS -->
		<td height="29" class="tableinside" valign="bottom">
			<span id="bob1293">
			<script language=javascript>
				if (document.all)
					document.write("<table border=0 cellpadding=0 cellspacing=0>");
				else
					document.write("<table align=left border=0 cellpadding=0 cellspacing=0>");
			</script><tr><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="19" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="bottom"><a href="#" onClick="onOff('bob1293','dip1293','visible','dd1293');">{VAR:LC_MENUEDIT_CHOOSE_KEYWORDS}</a></td><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="19" BORDER=0 ALT=""></td></tr></table>		
			

				<span id="dip1293"><table border=0 cellpadding=0 cellspacing=0><tr><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom"><a href="#">{VAR:LC_MENUEDIT_CHOOSE_KEYWORDS}</a></td><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td></tr></table></span>
			</span>
		</td>
		<!-- 9 KEYWORDS  -->

		<!--  10 MUUD MAARANGUD -->
		<td height="29" class="tableinside" valign="bottom">
			<span id="bob1294">
			<script language=javascript>
				if (document.all)
					document.write("<table border=0 cellpadding=0 cellspacing=0>");
				else
					document.write("<table align=left border=0 cellpadding=0 cellspacing=0>");
			</script><tr><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="19" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tab" valign="bottom"><a href="#" onClick="onOff('bob1294','dip1294','visible','dd1294');">{VAR:LC_MENUEDIT_OTHER_SETTINGS}</a></td><td class="tab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="19" BORDER=0 ALT=""></td></tr></table>		
			

				<span id="dip1294"><table border=0 cellpadding=0 cellspacing=0><tr><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_left_begin.gif" WIDTH="8" HEIGHT="20" BORDER=0 ALT=""></td><td nowrap background="{VAR:baseurl}/automatweb/images/blue/tab_taust.gif" class="tabsel" valign="bottom"><a href="#">{VAR:LC_MENUEDIT_OTHER_SETTINGS}</a></td><td class="tabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/tab_right.gif" WIDTH="6" HEIGHT="20" BORDER=0 ALT=""></td></tr></table></span>
			</span>
		</td>
		<!-- 10 MUUD MAARANGUD  -->


		</tr>
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




<span id="down">

<span id="dd5">
<img SRC="{VAR:baseurl}/automatweb/images/trans.gif" width=550 height=17>
</span>



<!-- 1 GENERRAL -->
<span id="dd1284">



<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="tableborder">

<table border="0" cellspacing="1" cellpadding="0" width="100%">
<tr>
<td class="celltitlenormal" height="20">
<!--{VAR:LC_MENUEDIT_OBJECT}:-->
<b>
&nbsp;ID:&nbsp;{VAR:id}
</b>
&nbsp;<b>{VAR:LC_MENUEDIT_CREATED}:</b>&nbsp;{VAR:createdby} @ {VAR:created}
&nbsp;<b>{VAR:LC_MENUEDIT_MODIFIED_BY}:</b>&nbsp;{VAR:modifiedby} {VAR:modified}
		

</td>
</tr>

<tr>
<td class="cell" valign="top">


<table border="0" cellspacing="1" cellpadding="1">
<tr><td class="cell" valign="top">

		<table border=0 cellspacing=0 cellpadding=3>

		<tr class="aste07">
			<td class="celltext" align="right">&nbsp;{VAR:LC_MENUEDIT_NAME}:&nbsp;</td>
			<td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' size=35></td>
		</tr>
		<tr>
			<td class="celltext" align="right">&nbsp;Link:&nbsp;</td>
			<td class="celltext"><input type='text' NAME='link' VALUE='{VAR:link}' size=35></td>
		</tr>
		<tr class="aste07">
			<td class="celltext" align="right">&nbsp;Alias:&nbsp;</td>
			<td class="celltext"><input type='text' NAME='alias' VALUE='{VAR:alias}' size=35></td>
		</tr>
		<tr class="aste07">
			<td class="celltext" align="right">&nbsp;T&uuml;&uuml;p:&nbsp;</td>
			<td class="celltext"><select name='type'>{VAR:types}</select></td>
		</tr>
		<tr>
			<td class="celltext" align="right">&nbsp;V&auml;rv:&nbsp;</td>
			<td class="celltext"><input type='text' NAME='color' VALUE='{VAR:color}' size=7>&nbsp;<a href="#" onclick="varvivalik('color');"> Vali </a></td>
		</tr>

		<tr>
			<td class="celltext"  align="right">&nbsp;<a href='config.{VAR:ext}?type=sel_icon&rtype=menu_icon&rid={VAR:id}'>AW {VAR:LC_MENUEDIT_ICON}:</a>&nbsp;</td>
			<td class="celltext">{VAR:icon}</td>
		</tr>
	

	

<!-- SUB: ADMIN_FEATURE -->
	<tr>
		<td class="celltext">&nbsp;{VAR:LC_MENUEDIT_CHOOSE_PROGRAM}:&nbsp;</td>
		<td class="celltext"><select name=admin_feature><option value=0>{VAR:admin_feature}</select></td>
	</tr>
<!-- END SUB: ADMIN_FEATURE -->

	
		<tr class="aste07">
			<td class="celltext"  align="right" valign="top">&nbsp;{VAR:LC_MENUEDIT_COMMENT}:&nbsp;</td>
			<td class="celltext"><textarea NAME='comment' cols=30 rows=3>{VAR:comment}</textarea></td>
		</tr>
		<tr>
			<td class="celltext" align="right">&nbsp;Failinimi:&nbsp;</td>
			<td class="celltext"><input type='text' NAME='aip_filename' VALUE='{VAR:aip_filename}' size=7></td>
		</tr>


				<tr>
			<td class="celltext" nowrap align="right">&nbsp;&nbsp;</td>
			<td class="celltext">&nbsp;</td>
		</tr>

	

			<tr class="aste06">
			<td class="celltext" nowrap align="right">&nbsp;<font color="red">Legend:</font>&nbsp;</td>
			<td class="celltext">&nbsp;</td>
			</tr>
		
		</table>

		</td>
		<td valign="top" class="aste06">


			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			<tr><td colspan="2" class="celltext"><b>{VAR:LC_MENUEDIT_SETTINGS}:</b></td></tr>

	<tr>
		<td class="aste04" width="1%"><input type="checkbox" name="active" {VAR:active}></td>
		<td class="aste04" width="99%"><span class="celltext">{VAR:LC_MENUEDIT_ACTIVE}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr>
		<td class="aste04"><input type='checkbox' NAME='clickable' VALUE='1' {VAR:clickable}></td>
		<td class="aste04"><span class="celltext">{VAR:LC_MENUEDIT_CLICKABLE}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr>		
		<td class="aste06" nowrap><input type='checkbox' NAME='target' VALUE='1' {VAR:target}></td>
		<td class="aste06"><span class="celltext">{VAR:LC_MENUEDIT_NEW_WINDOW}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>

	<tr>		
		<td class="cell" ><input type='checkbox' NAME='mid' VALUE='1' {VAR:mid}></td>
		<td class="cell"><span class="celltext">{VAR:LC_MENUEDIT_CENTERED}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>


	<tr>
		<td class="aste06" ><input type="checkbox" value=1 name="left_pane" {VAR:left_pane}></td>
		<td class="aste06"><span class="celltext">{VAR:LC_MENUEDIT_LEFT_PANE}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr>
		<td class="aste06"><input type="checkbox" value=1 name="right_pane" {VAR:right_pane}></td>
		<td  class="aste06"><span class="celltext">{VAR:LC_MENUEDIT_RIGHT_PANE}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr>
		<td class="cell" ><input type="checkbox" value=1 name="users_only" {VAR:users_only}></td>
		<td class="cell"><span class="celltext">Users only&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>


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


<!-- SUB: IS_BROTHER -->
<br>
{VAR:LC_MENUEDIT_BROTHER_WHICH} <a href='menuedit.{VAR:ext}?menu=menu&parent={VAR:real_id}'>{VAR:LC_MENUEDIT_HERE}</a>
<!-- END SUB: IS_BROTHER -->



</span>	
<!-- 1 END GENERAL -->



<!-- 2 TEMPLATED -->
<span id="dd1285">
<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr><td class="tableborder">

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<tr><td class="celltitle" height="20">&nbsp;ID:&nbsp;{VAR:id}</td></tr>

	<tr><td class="tableinside">


	


		<table border=0 cellspacing=0 cellpadding=10 width="100%">
		<tr><td colspan="2">
		
		
		<!--templated-->
		<table border=0 cellspacing=1 cellpadding=3 class="aste06">
		<tr><td colspan="2" class="celltext">
		
		<b>{VAR:LC_MENUEDIT_MENU_TEMPLATES}</b>
		</td></tr>
		<tr>
		<td class="celltext" align="right">&nbsp;Template set:&nbsp;</td>
		<td class="celltext"><select name="tpl_dir">{VAR:tpl_dir}</select></td>
		</tr>
		<tr>
			<td class="celltext" colspan="2"><input type="radio" name="template_type" value="0" {VAR:tpltype_tpl}> Dokumendi sisestamisel kasutatakse templeite</td>
		</tr>
		<tr>
		<td class="celltext" align="right">&nbsp;{VAR:LC_MENUEDIT_TEMPL_EDIT}:&nbsp;</td>
		<td class="celltext"><select name="tpl_edit">{VAR:tpl_edit}</select></td>
		</tr>
		<tr>
		<td class="celltext" align="right">&nbsp;{VAR:LC_MENUEDIT_TEMPL_SHOW}:&nbsp;</td>
		<td class="celltext"><select name="tpl_view"><option value="0">Default</option>{VAR:tpl_view}</select></td>
		</tr>
		<tr>
		<td class="celltext" nowrap align="right">&nbsp;{VAR:LC_MENUEDIT_TEMPL_SHORT}:&nbsp;</td>
		<td class="celltext"><select name="tpl_lead"><option value="0">Default</option>{VAR:tpl_lead}</select></td>
		</tr>
		<tr>
			<td class="celltext" colspan="2"><input type="radio" name="template_type" value="1" {VAR:tpltype_form}> Dokumendi sisestamisel kasutatakse forme</td>
		</tr>
		<tr>
		<td class="celltext" align="right">&nbsp;Muutmiseform:&nbsp;</td>
		<td class="celltext"><select onChange="sel_form=this.options[this.selectedIndex].value;mk_ops();"  name="ftpl_edit">{VAR:ftpl_edit}</select></td>
		</tr>
		<tr>
		<td class="celltext" align="right">&nbsp;Pikk v&auml;ljund:&nbsp;</td>
		<td class="celltext"><select name="ftpl_view">{VAR:ftpl_view}</select></td>
		</tr>
		<tr>
		<td class="celltext" nowrap align="right">&nbsp;Leadi v&auml;jund:&nbsp;</td>
		<td class="celltext"><select name="ftpl_lead">{VAR:ftpl_lead}</select></td>
		</tr>
		<tr class="aste05">
		<td class="celltext" nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="celltext">&nbsp;</td></tr>
		</table>

		<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="10" BORDER=0 ALT=""><br>


		<!--automaatsed tegevused-->
		<table border=0 cellspacing=1 cellpadding=3 class="aste06">
		<tr>
		<td colspan="2" class="celltext"><b>{VAR:LC_MENUEDIT_MENU_AUTOMATIC}</b></td>
		</tr>

		<tr>
		<td class="celltext">&nbsp;{VAR:LC_MENUEDIT_ACTIVATE}:&nbsp;</td>
		<td class="celltext">&nbsp;<input type="checkbox" name="autoactivate" {VAR:autoactivate}>&nbsp;{VAR:activate_at}</td>
		</tr>
		<tr>
		<td class="celltext">&nbsp;{VAR:LC_MENUEDIT_DEACTIVATE}:&nbsp;</td>
		<td class="celltext">&nbsp;<input type="checkbox" name="autodeactivate" {VAR:autodeactivate}>&nbsp;{VAR:deactivate_at}</td>
		</tr>
		<tr class="aste05">
		<td class="celltext" nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="celltext">&nbsp;</td></tr>
		</table>

		<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="10" BORDER=0 ALT=""><br>


		<!--vennastamine-->

		<table border=0 cellspacing=1 cellpadding=3 class="aste06">
		<tr><td class="celltext"><b>
		{VAR:LC_MENUEDIT_BROTHERING}
		</b></td></tr>

		<tr>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext">&nbsp;</td>
		</tr>
		<tr>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext">&nbsp;<select MULTIPLE SIZE=20 class='small_button' name="sections[]">{VAR:sections}</select></td>
		</tr>
		<tr>
		<td class="celltext" nowrap>&nbsp;</td>
		<td class="celltext">&nbsp;</td>
		</tr>
		<tr class="aste05">
		<td class="celltext" nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="celltext">&nbsp;{VAR:LC_MENUEDIT_MENU_SECTIONS}</td>
		</tr>
		</table>

		<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="10" BORDER=0 ALT=""><br>

		<!--dokumendid-->

		<table border=0 cellspacing=1 cellpadding=3 class="aste06">
		<tr><td colspan="2" class="celltext">
		<b>{VAR:LC_MENUEDIT_DOCUMENTS}</b>
		</td></tr>

		<tr>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext"><select MULTIPLE SIZE=20 class='small_button' name="sss[]">{VAR:sss}</select></td>
		</tr>

		<tr class="aste05">
		<td class="celltext" nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="celltext">&nbsp;{VAR:LC_MENUEDIT_LAST_DOCUMENTS}</td>
		</tr>

		<tr>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext"><select MULTIPLE SIZE=5 class='small_button' name="pers[]">{VAR:pers}</select></td>
		</tr>

		<tr class="aste05">
		<td class="celltext" nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="celltext">&nbsp;{VAR:LC_MENUEDIT_LAST_DOCUMENTS}</td>
		</tr>
		</table>	

		<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="10" BORDER=0 ALT=""><br>


		<!--Vaata lisaks-->

		<table border=0 cellspacing=0 cellpadding=3 class="aste06">
		<tr><td colspan="2" class="celltext"><b>{VAR:LC_MENUEDIT_MENU_LOOK_MORE}</b></td></tr>
		<tr>
		<td class="celltext">&nbsp;J&auml;rjekorranumber:&nbsp;</td>
		<td class="celltext">&nbsp;<input size=3 class='small_button' type='text' name='seealso_order' value='{VAR:seealso_order}'></td>
		</tr>
		<tr>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext">&nbsp;<select class="small_button" MULTIPLE SIZE=20 name="seealso[]">{VAR:seealso}</select></td>
		</tr>
		<tr>
		<td class="celltext" nowrap>&nbsp;</td>
		<td class="celltext">&nbsp;</td>
		</tr>
		<tr class="aste05">
		<td class="celltext"  nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="celltext">&nbsp;Vali men&uuml;&uuml;d mille all k&auml;esolev men&uuml;&uuml; on vaata lisaks men&uuml;&uuml;</td>
		</tr>
		</table>



		</td></tr>
		
		</table>

	</td></tr>
	</table>
</td></tr></table>
</span>
<!-- 2 END TEMPLATED -->





<!-- 6 PICTURE -->
<span id="dd1290">

<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr>
<td class="tableborder">


<table border=0 cellspacing=0 cellpadding=0 width=100%>

<tr><td class="celltitle" height="20">&nbsp;ID:&nbsp;{VAR:id}</td></tr>
<tr>
<td class="tableinside">

<table border=0 cellspacing=0 cellpadding=3 width=100%>

	<tr>
		<td class="celltext">{VAR:LC_MENUEDIT_PICTURE}</td>
		<td class="celltext">Jrk</td>
		<td class="celltext">Kustuta</td>
		<td class="celltext">Upload</td>
	</tr>
	<!-- SUB: M_IMG -->
	<tr>
		<td class="celltext">{VAR:image}</td>
		<td class="celltext"><input type="text" name="img_ord[{VAR:nr}]" size="3" value="{VAR:img_ord}" class="small_button"></td>
		<td class="celltext"><input type="checkbox" name="img_del[{VAR:nr}]" value="1" class="small_button"></td>
		<td class="celltext"><input type='file' name='img{VAR:nr}'></td>
	</tr>
	<!-- END SUB: M_IMG -->

	<tr>
		<td class="celltext">Kui pikk vahe:</td>
		<td class="celltext" colspan="3"><input type='text' name='img_timing' value='{VAR:img_timing}'></td>
	</tr>
	<tr>
		<td class="celltext">Aktiivse men&uuml;&uuml; pilt:</td>
		<td class="celltext" colspan="3">{VAR:image_act}</td>
	</tr>
	<tr>
		<td class="celltext">&nbsp;</td>
		<td class="celltext" colspan="3"><input type='file' name='img_act'></td>
	</tr>
</table>

</td>
</tr>
</table>
</td>
</tr>
</table>


</span>
<!-- 6 END PICTURE-->


<!-- 7 EXPORT -->
<span id="dd1291">

<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr>
<td class="tableborder">


<table border=0 cellspacing=0 cellpadding=0 width=100%>

<tr><td class="celltitle" height="20">&nbsp;ID:&nbsp;{VAR:id}</td></tr>
<tr>
<td class="tableinside">

<table border=0 cellspacing=0 cellpadding=3 width=100%>

	<tr>
		<td class="celltext" valign="top" width="10%">{VAR:LC_MENUEDIT_CHOOSE_MENUS}:&nbsp;</td>
		<td class="celltext"><select name='ex_menus[]' multiple size=15 class='small_button'>{VAR:ex_menus}</select></td>
	</tr>
	<tr>
		<td class="celltext">&nbsp;&nbsp;</td>
		<td class="celltext">{VAR:LC_MENUEDIT_SELECT_ALL_MENUS}? <input type='checkbox' name='allactive' value=1> {VAR:LC_MENUEDIT_EXPORT_ICONS}? <input type='checkbox' name='ex_icons' value=1></td>
	</tr>
	<tr>
		<td class="celltext" width=10% nowrap>&nbsp;</td>
		<td class="celltext">&nbsp;<input type='submit' onClick='menuinfo.action.value="export_menus";' value='{VAR:LC_MENUEDIT_EXPORT}'></td>
	</tr>
</table>

</td>
</tr>
</table>
</td>
</tr>
</table>


</span>
<!-- 7 END EXPORT-->

<!-- 8 SHOP
{VAR:LC_MENUEDIT_CHOOSE_SHOP}:<select name='shop' size=10 class='small_button'>{VAR:shop}</select>
8 END SHOP-->


<!-- 9 KEYWORDS -->
<span id="dd1293">

<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr>
<td class="tableborder">


<table border=0 cellspacing=0 cellpadding=0 width=100%>

<tr><td class="celltitle" height="20">&nbsp;ID:&nbsp;{VAR:id}</td></tr>
<tr>
<td class="tableinside">

<table border=0 cellspacing=0 cellpadding=3 width=100%>

	<tr>
		<td class="celltext" width="10%" valign="top">AW {VAR:LC_MENUEDIT_CHOOSE_KEYWORDS}:</td>
		<td class="celltext"><select name='grkeywords[]' size=10 class='small_button' multiple>{VAR:grkeywords}</select></td>
	</tr>

	<tr>
		<td class="celltext" width=10% nowrap >Keywords (META):</td>
		<td class="celltext" colspan=10><input type="text" name="keywords" size="50" value="{VAR:keywords}"></td>
	</tr>
	<tr>
		<td class="celltext" width=10% nowrap >Description (META):</td>
		<td class="celltext" colspan=10><input type="text" name="description" size="50" value="{VAR:description}"></td>
	</tr>

</table>

</td>
</tr>
</table>
</td>
</tr>
</table>


</span>
<!-- 9 END KEYWORDS-->

<!-- 10 MUUD MAARANGUD -->
<span id="dd1294">

<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr>
<td class="tableborder">


<table border=0 cellspacing=0 cellpadding=0 width=100%>

<tr><td class="celltitle" height="20">&nbsp;ID:&nbsp;{VAR:id}</td></tr>
<tr>
<td class="tableinside">

	<table border="0" cellspacing="0" cellpadding="3">
	<tr class="aste06">		
		<td class="celltext" nowrap><input type='checkbox' NAME='hide_noact' VALUE='1' {VAR:hide_noact}>&nbsp;</td>
		<td class="aste06"><span class="celltext">{VAR:LC_MENUEDIT_HIDE_NOACT}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr class="aste06">		
		<td class="celltext" ><input type='checkbox' NAME='links' VALUE='1' {VAR:links}></td>
		<td class="celltext"><span class="celltext">{VAR:LC_MENUEDIT_LINK_COLLECTION}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr class="aste06">
		<td class="celltext" ><input type='checkbox' NAME='is_shop' VALUE='1' {VAR:is_shop}></td>
		<td class="celltext"><span class="celltext">{VAR:LC_MENUEDIT_SHOP}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr class="aste06">
		<td class="celltext" ><input type="checkbox" value=1 name="show_lead" {VAR:show_lead}></td>
		<td class="celltext"><span class="celltext">Show lead&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr class="aste06">
		<td class="celltext" ><input type='checkbox' name='shop_parallel' value=1 {VAR:shop_parallel}></td>
		<td class="celltext"><span class="celltext">{VAR:LC_MENUEDIT_ITEMS_SBS}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr class="aste06">
		<td class="celltext" ><input type='checkbox' name='no_menus' value=1 {VAR:no_menus}></td>
		<td class="celltext"><span class="celltext">Ilma men&uuml;&uuml;deta&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	<tr class="aste06">
		<td class="celltext"><input type='checkbox' name='shop_ignoregoto' value=1 {VAR:shop_ignoregoto}></td>
		<td class="celltext"><span class="celltext">{VAR:LC_MENUEDIT_IGNORE_NEXT}&nbsp;</span><span class="cellcomment">(?)</span></td>
	</tr>
	</table>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
	<tr class="aste04">
		<td class="celltext" colspan=2>&nbsp;{VAR:LC_MENUEDIT_LAST_DOCUMENTS_AMOUNT}:&nbsp;
		<input type='text' NAME='ndocs' VALUE='{VAR:ndocs}' size=3> &nbsp;{VAR:LC_MENUEDIT_NO_TEST}:&nbsp;
		<input type='text' NAME='number' VALUE='{VAR:number}' size=3>{VAR:LC_MENUEDIT_WIDTH}:&nbsp;
		<input type='text' NAME='width' VALUE='{VAR:width}' size=3></td>
	</tr>
	</table>






</td>
</tr>
</table>
</td>
</tr>
</table>


</span>
<!-- 10 END MUUD MAARANGUD-->

</span>
























{VAR:reforb}
</form>


<!-- 
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_MENUEDIT_SAVE}'></td>
</tr>
-->

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<script language="javascript">
onOff('bob1284','dip1284','visible','dd1284');
</script>

<script language="javascript">
sel_form={VAR:ftpl_edit_id};
mk_ops();
menuinfo.ftpl_lead.selectedIndex = idxforvalue(menuinfo.ftpl_lead,{VAR:ftpl_lead_id});
menuinfo.ftpl_view.selectedIndex = idxforvalue(menuinfo.ftpl_view,{VAR:ftpl_view_id});
</script>
