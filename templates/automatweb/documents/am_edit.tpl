
<form method="POST" action="reforb.{VAR:ext}" name="doc" onSubmit="doSubmit();return true;">

<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="2">

<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:doSubmit();this.document.doc.submit();" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:doSubmit();this.document.doc.submit();" >Salvesta</a></td>
<td><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>

<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="{VAR:menurl}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('brothering','','{VAR:baseurl}/automatweb/images/blue/awicons/brothering_over.gif',1)"><img name="brothering" alt="Vennastamine" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/brothering.gif" width="25" height="25"></a><br><a
href="{VAR:menurl}">Vennastamine</a></td>
<td><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>

<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a target="_blank" href="{VAR:baseurl}/index.aw?section={VAR:id}" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('preview','','{VAR:baseurl}/automatweb/images/blue/awicons/preview_over.gif',1)"><img name="preview" alt="Eelvaade" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/preview.gif" width="25" height="25"></a><br><a
target="_blank" href="{VAR:baseurl}/index.aw?section={VAR:id}">Eelvaade</a></td>
<td><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>

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

<!--
<input type="submit" class='doc_button' value="Salvesta">
<input class='doc_button' type="submit" value="Eelvaade" onClick="window.location.href='{VAR:preview}';return false;">
<input type="submit" class='doc_button' value="Sektsioonid" onClick="window.location.href='{VAR:menurl}';return false;">
<input type="submit" class='doc_button' value="Webile" onClick="window.open('{VAR:baseurl}/index.{VAR:ext}?section={VAR:id}');return false;">
-->


<table border=0 cellspacing=1 cellpadding=2>
<tr>
<td COLSPAN=2>



<table border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td colspan=3><img src='{VAR:baseurl}/images/transa.gif' width=1 height=10 border=0></td>
	</tr>
	<tr>
		<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=113 height=1 border=0><br><B>&nbsp;M��rangud&nbsp;</b></td>
		<td class="celltext" bgcolor="#CCCCCC"><img src='{VAR:baseurl}/images/transa.gif' width=1 height=10 border=0></td>
		<td class="celltext">&nbsp;			
			Aktiivne:<input type='checkbox' name='status' value='2' {VAR:cstatus}>&nbsp;&nbsp;&nbsp;
			N�ita leadi: <input type='checkbox' name='showlead' value=1 {VAR:showlead}>&nbsp;&nbsp;&nbsp;
			N�ita pealkirja: <input type='checkbox' name='show_title' value=1 {VAR:show_title}>&nbsp;&nbsp;&nbsp;
			'Prindi' nupp: <input type='checkbox' name='show_print' value=1 {VAR:show_print}>&nbsp;&nbsp;&nbsp;
			Muutmise kuupaev dokumendi sees: <input type='checkbox' name='show_last_changed' value=1 {VAR:show_last_changed}>&nbsp;&nbsp;&nbsp;<br>
			&nbsp;T&uuml;hista stiilid:	<input type='checkbox' name="clear_styles" value=1>&nbsp;&nbsp;&nbsp;
			Lingi v�tmes�nad      <input type='checkbox' name="link_keywords" value=1>&nbsp;&nbsp;&nbsp;
			Kommenteeritav? <input type='checkbox' name="is_forum" value=1 {VAR:is_forum}>&nbsp;&nbsp;&nbsp;
		</td>
	</tr>
</table>
</td>
</tr>
<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Kuup�ev&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="tm" size="15" value="{VAR:tm}"></td>
</tr>
<!-- SUB: NOT_IE -->
<script language="javascript">
function doSubmit()
{
	return true;
}
</script>
<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Pealkiri&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="title" size="80" value="{VAR:title}"></td>
</tr>
<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Autor:&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="author" size="80" value="{VAR:author}"></td>
</tr>
<tr>
<td class="celltext" valign="top"><b>&nbsp;Lead&nbsp;</b></td>
<td class="celltext">
<textarea name="lead" cols="100" rows="5" class='tekstikast'>{VAR:lead}</textarea>
</td>
</tr>
<tr>
<td class="celltext" valign="top"><b>&nbsp;Sisu&nbsp;</b></td>
<td class="celltext">
<textarea name="content" cols="100" rows="30" class='tekstikast'>{VAR:content}</textarea>
</td>
</tr>
<input type='hidden' name='nobreaks' value='{VAR:nobreaks}'>
<!-- END SUB: NOT_IE -->
<!-- SUB: IE -->
<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Autor:&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="author" size="80" value="{VAR:author}"></td>
</tr>
<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Fotod:&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="photos" size="80" value="{VAR:photos}"></td>
</tr>
<tr>
<td class="celltext" valign="top"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Pealkiri&nbsp;</b></td>
<td class="celltext">
<br>
<div id=idBox style="width: 600px;text-align: left; ;visibility: hidden, height:25;overflow:hidden;background:gainsboro" ID=htmlOnly valign="top">
	<script>
		var buttons=new Array(24,23,23,4,23,23,23,4,23,23,23,23);

		var action=new Array("bold","italic","underline","","justifyleft","justifycenter","justifyright","","insertorderedlist","insertunorderedlist","outdent","indent","","");

		var tooltip=new Array("Bold Text","Italic Text","Underline Text","","Left Justify","Center Justify","Right Justify","","Ordered List","Unordered List","Remove Indent","Indent","","");

		var left=0
		var s="";

		for (var i=0;i<buttons.length;i++) 
		{
			s+="<span style='position:relative;height:26;width: " + buttons[i] + "'><span style='position:absolute;margin:0px;padding:0;height:26;top:0;left:0;width:" + (buttons[i]) + ";clip:rect(0 "+buttons[i]+" 25 "+0+");overflow:hidden'><img border='0' src='images/toolbar.gif' style='position:absolute;top:0;left:-" + left + "' width=290 height=50";
			if (buttons[i]!=4) 
			{
				s+=" onmouseover='this.style.top=-25' onmouseout='this.style.top=0' onclick=\"";
				
				if (action[i]!="createLink") 
					s+="format('" + action[i] + "');this.style.top=0\" ";
				else
					s+="createLink();this.style.top=0\" ";
					
				s+="TITLE=\"" + tooltip[i] + "\"";
			}
			
			s+="></span></span>";
			left+=buttons[i] ;
		}
		document.write(s);
	</script>
<select onchange="format('fontname',this[this.selectedIndex].value);this.selectedIndex=0" STYLE="font:8pt verdana,arial,sans-serif;background:#FFFFFF">
										<option selected>Font...
										<option value="Geneva,Arial,Sans-Serif">Arial
										<option value="Trebuchet MS,Arial">Trebuchet MS
										<option value="Verdana,Geneva,Arial,Helvetica,Sans-Serif">Verdana
										<option value="Times New Roman,Times,Serif">Time
										<option value="Courier, Monospace">Courier
									</select>
<select onchange="format('fontSize',this[this.selectedIndex].text);this.selectedIndex=0" STYLE="font:8pt verdana,arial,sans-serif;background:#FFFFFF"><option>Suurus...<option>1<option>2<option>3<option>4<option>5<option>6<option>7</select>
<select onchange="format('forecolor',this[this.selectedIndex].style.color);this.selectedIndex=0" STYLE="font:8pt verdana,arial,sans-serif;background:#FFFFFF">
										<option selected>V&auml;rv...
										<option style="color:black">must</option>
										<!--<option style="color:#008572">Alapealkiri 3</option>

										<option style="color:#00A0C6">am sinine</option>
										<option style="color:#4DAC27">am roheline</option>
										<option style="color:#F2B600">am kollane</option>
										<option style="color:#FF8000">am orange</option>
										<option style="color:#008572">am rohekas sinine</option>-->


									</select>
</div>
<input type="hidden" name="title" value="{VAR:title}">
<iframe name="title_edit" onFocus="sel_el='title_edit'" frameborder="1" width="600" height="50"></iframe>
</td>
</tr>
<tr>
<td class="celltext" valign="top"><b>&nbsp;Lead&nbsp;</b></td>
<td class="celltext">
<iframe name="lead_edit" onFocus="sel_el='lead_edit'" frameborder="1" width="600" height="100"></iframe>
<input type='hidden' name="lead" value="{VAR:lead}">
</td>
</tr>
<tr>
<td class="celltext" valign="top"><b>&nbsp;Sisu&nbsp;</b></td>
<td class="celltext"><iframe onFocus="sel_el='cont_edit'" name="cont_edit" frameborder="1" width="600" height="400"></iframe>
<input type='hidden' name='content' value="{VAR:content}">
<input type='hidden' name='nobreaks' value='1'>
<script for=window event=onload>
	cont_edit.document.designMode='On';
	cont_edit.document.write("<html><head><META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset={VAR:charset}\"></head><body style='font-family: Arial, Verdana, Helvetica, sans-serif;font-size: 12px;background-color: #FFFFFF; border: #CCCCCC solid; border-width: 1px 1px 1px 1px; margin-left: 0px;padding-left: 3px;	padding-top: 0px;	padding-right: 3px; padding-bottom: 0px;'>");
	cont_edit.document.write(doc.content.value);
	cont_edit.document.write("</body></html>");

	lead_edit.document.designMode='On';
	lead_edit.document.write("<html><head><META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset={VAR:charset}\"></head><body style='font-family: Arial, Verdana, Helvetica, sans-serif;font-size: 12px;background-color: #FFFFFF; border: #CCCCCC solid; border-width: 1px 1px 1px 1px; margin-left: 0px;padding-left: 3px;	padding-top: 0px;	padding-right: 3px; padding-bottom: 0px;'>");
	lead_edit.document.write(doc.lead.value);
	lead_edit.document.write("</body></html>");

	title_edit.document.designMode='On';
	title_edit.document.write("<html><head><META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset={VAR:charset}\"></head><body style='font-family: Arial, Verdana, Helvetica, sans-serif;font-size: 12px;background-color: #FFFFFF; border: #CCCCCC solid; border-width: 1px 1px 1px 1px; margin-left: 0px;padding-left: 3px;	padding-top: 0px;	padding-right: 3px; padding-bottom: 0px;'>");
	title_edit.document.write(doc.title.value);
	title_edit.document.write("</body></html>");
</script>
</td>
</tr>
<script language="javascript">

var sel_el = "lead_edit";

function doSubmit()
{
	doc.content.value=cont_edit.document.body.innerHTML;
	doc.lead.value=lead_edit.document.body.innerHTML;
	doc.title.value=title_edit.document.body.innerHTML;
}

function format(what,opt) 
{
	if (opt=="removeFormat") 
	{
		what=opt;
		opt=null;
	}
	if (opt==null)
	{
		eval(sel_el+".document.execCommand(what)");
	}
	else
	{
		eval(sel_el+".document.execCommand(what,\"\",opt)");
	}

	var s=eval(sel_el+".document.selection.createRange()"),p=s.parentElement()  
	if ((p.tagName=="FONT") && (p.style.backgroundColor!=""))
		p.outerHTML=p.innerHTML;
	eval(sel_el+".focus()");
	sel=null
}

</script>
<!-- END SUB: IE -->
<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;V�tmes�nad&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="keywords" size="80" value="{VAR:keywords}"></td>
</tr>
</tr>
{VAR:reforb}
</form>
</table>
<iframe width="100%" height="800" frameborder="0" src="{VAR:aliasmgr_link}">
</iframe>
