<script src="{VAR:baseurl}/automatweb/js/aw.js"></script>
{VAR:menu}

<form method="post" enctype="multipart/form-data" action="reforb.{VAR:ext}" name="writemessage" OnSubmit="PreSubmit();return true;">
<input type="hidden" name="MAX_FILE_SIZE" value="1200000">
<table border=0 cellspacing=0 cellpadding=0 width="100%">
<tr>
<td bgcolor="#EEEEEE">
{VAR:toolbar}
<input type="hidden" name="_sethtml" value="0">
<input type="hidden" name="_makelist" value="0">
</td>
</tr>
</table>
<table border=0 cellspacing="2" cellpadding=1 width="100%" bgcolor="#ffffff">
<tr>


<td class="textsmall" colspan="2">
Siia voib kirjutada nii e-posti aadresse, kui systeemis registreeritud
kasutajate ja listide nimesid. Erinevad aadressid tuleks eraldada komadega.
<p>
Listidesse kirjutamisel on võimalik kasutada järgmisi aliasi:<br>
#nimi#, #email# - need asendatakse saatmisel vastava listi liikme nime
ja e-posti aadressiga. See võimaldab saata personaliseeritud teateid.
</td></tr>
<tr>
<td class="textsmallbold" width="130"><strong><a href="#" onClick="aw_popup_s('{VAR:pick_contact}','pick',500,500); return false">Kellele:</a></strong></td>
<td class="textsmall"><input type="text" name="mtargets1" size="{VAR:msg_field_width}" maxlength="200" value="{VAR:mtargets1}">
</td>
</tr>
<tr>
<td class="textsmallbold" width="130"><strong><a href="#" onClick="aw_popup_s('{VAR:pick_contact}','pick',500,500); return false">CC:</a></strong></td>
<td class="textsmall"><input type="text" name="mtargets2" size="{VAR:msg_field_width}" maxlength="200" value="{VAR:mtargets2}">
</td>
</tr>

<tr>
<td class="textsmallbold" width="130"><strong>Kellelt:</strong></td>
<td class="textsmall"><input type="text" name="mfrom" size="{VAR:msg_field_width}" maxlength="200" value="{VAR:mfrom}"></td>
</tr>


<tr>
<td class="textsmallbold" width="130"><strong>Teema:</strong></td>
<td class="textsmall"><input type="text" name="subject" size="{VAR:msg_field_width}" maxlength="200" value="{VAR:subject}"></td>
</tr>

<!-- SUB: muutujad -->
<tr>
<td class="textsmallbold" width="130"><strong>Muutujad:</strong></td>
<td class="textsmall">
{VAR:muutujalist}
</td>
</tr>
<tr>
<td class="textsmallbold" width="130"><strong>Stambid:</strong></td>
<td class="textsmall">
{VAR:stambilist}
</td>
</tr>
<!-- END SUB: muutujad -->

<!-- SUB: textedit -->
<tr>
<td colspan="2" class="lefttab">
<textarea name="message" cols="{VAR:msg_box_width}" rows="{VAR:msg_box_height}" wrap="soft">{VAR:message}</textarea>
</td>
</tr>
<script language="JavaScript">
function PreSubmit()
{
//alert("presubmit");
};
</script>
<!-- END SUB: textedit -->

<!-- SUB: htmledit -->
<tr>
<td colspan="2" class="lefttab">
<textarea name="message" cols=0 rows=0 Style="visibility:hidden;width:0px;height:0px;display:none;">{VAR:message}</textarea>
<br>
<div id=idBox style="width: 100%;text-align: left; ;visibility: hidden, height:25;overflow:hidden;background:gainsboro" ID=htmlOnly valign="top">
	<script>
		var buttons=new Array(24,23,23,4,23,23,23,4,23,23,23,23);

		var action=new Array("bold","italic","underline","","justifyleft","justifycenter","justifyright","","insertorderedlist","insertunorderedlist","outdent","indent","","");

		var tooltip=new Array("Bold Text","Italic Text","Underline Text","","Left Justify","Center Justify","Right Justify","","Ordered List","Unordered List","Remove Indent","Indent","","");

		var left=0
		var s="";

		for (var i=0;i<buttons.length;i++) 
		{
			s+="<span style='position:relative;height:26;width: " + buttons[i] + "'><span style='position:absolute;margin:0px;padding:0;height:26;top:0;left:0;width:" + (buttons[i]) + ";clip:rect(0 "+buttons[i]+" 25 "+0+");overflow:hidden'><img border='0' src='/automatweb/images/toolbar.gif' style='position:absolute;top:0;left:-" + left + "' width=290 height=50";
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
										<option value="Verdana,Geneva,Arial,Helvetica,Sans-Serif">Verdana
										<option value="Times New Roman,Times,Serif">Time
										<option value="Courier, Monospace">Courier
									</select>
<select onchange="format('fontSize',this[this.selectedIndex].text);this.selectedIndex=0" STYLE="font:8pt verdana,arial,sans-serif;background:#FFFFFF"><option>Suurus...<option>1<option>2<option>3<option>4<option>5<option>6<option>7</select>
<select onchange="format('forecolor',this[this.selectedIndex].style.color);this.selectedIndex=0" STYLE="font:8pt verdana,arial,sans-serif;background:#FFFFFF">
									<option selected>Värv...
										<option style="color:black">must</option>
										<option style="color:darkslategray">tumehall</option>
										<option style="color:red">punane</option>
										<option style="color:maroon">tumelilla</option>
										<option style="color:lightpink">heleroosa</option>
										<option style="color:purple">lilla</option>
										<option style="color:blue">sinine</option>
										<option style="color:darkblue">tumesinine</option>
										<option style="color:teal">rohekassinine</option>
										<option style="color:skyblue">taevasinine</option>
										<option style="color:green">roheline</option>
										<option style="color:seagreen">mereroheline</option>
										<option style="color:olive">oliiv</option>
										<option style="color:orange">oranzh</option>
										<option style="color:darkgoldenrod">tumekollane</option>
										<option style="color:gray">hall</option>
									</select>

</div>
<iframe name="msg_edit" frameborder="1" width="100%" height="300">  </iframe>
</td>
</tr>
<script language="JavaScript">
function PreSubmit()
{
	writemessage.message.value=msg_edit.document.body.innerHTML;
	//alert("presubmit");
};

function format(what,opt) 
{
	if (opt=="removeFormat") 
	{
		what=opt;
		opt=null;
	}
	if (opt==null)
	{
		eval("msg_edit.document.execCommand(what)");
	}
	else
	{
		eval("msg_edit.document.execCommand(what,\"\",opt)");
	}

	var s=eval("msg_edit.document.selection.createRange()"),p=s.parentElement()  
	if ((p.tagName=="FONT") && (p.style.backgroundColor!=""))
		p.outerHTML=p.innerHTML;
	eval("msg_edit.focus()");
	sel=null
}

writemessage._sethtml.value='1';
msg_edit.document.designMode='On';
msg_edit.document.write("<html><head><META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-15\"></head><body MONOSPACE style='font-family: arial,sans-serif;font-size: 10pt;background-color: #FFFFFF; border: #CCCCCC solid; border-width: 1px 1px 1px 1px; margin-left: 0px;padding-left: 3px;	padding-top: 0px;	padding-right: 3px; padding-bottom: 0px;'>");
msg_edit.document.write(writemessage.message.value);
msg_edit.document.write("</body></html>");
msg_edit.document.close();
</script>

<!-- END SUB: htmledit -->
<tr>
<td class="textsmallbold" width="130"><strong>Identiteet:</strong></td>
<td class="lefttab">
<select class="lefttab" name="identity">
{VAR:idlist}
</select>
</td>
</tr>
<tr>
<td class="textsmallbold" width="130"><strong>Signatuur:</strong></td>
<td class="lefttab"><select class="lefttab" name="signature">
{VAR:siglist}
</select>
</td>
</tr>
<tr>
<td class="textsmallbold" width="130"><strong>Prioriteet:</strong></td>
<td class="lefttab"><select class="lefttab" name="pri">
{VAR:prilist}
</select>
</td>
</tr>
<tr>
<td class="textsmallbold" width="130"><strong>Saada meilile:</strong></td>
<td class="textsmall"><input type="checkbox" name="sendmail"></td>
</tr>
<!-- SUB: attaches -->
<tr>
<td colspan="2" class="text">{VAR:cnt}. <img src="{VAR:icon}"><a href="{VAR:get_attach}" target="_new">{VAR:name}</a></td>
</tr>
<!-- END SUB: attaches -->
<tr>
<td class="textsmallbold" valign="top" width="130"><strong>Attachi fail:</strong></td>
<td class="lefttab">
<!-- SUB: attach -->
<input type="file" name="attach[]" size="30"><br>
<!-- END SUB: attach -->
</td>
</tr>
<tr>
<td class="textsmallbold" width="130"><strong>Attachi AW objekt:</strong></td>
<td class="textsmall"><a href="javascript:aw_popup_scroll('{VAR:attach_aw_o}','pickaw',600,550)">Kliki siia</a></td>
</tr>
<tr>
<td align="left" colspan="2" class="lefttab">
<input type="hidden" name="msg_id" value="{VAR:msg_id}">
<input type="hidden" name="gids" value="">
{VAR:reforb}
<!-- SUB: toolbar -->
<!-- SUB: confirmsend -->
<input type="submit" value="Saada!" class="formbutton">
<!-- END SUB: confirmsend -->
<!-- SUB: send -->
<input type="submit" value="Saada!" class="formbutton">
<!-- END SUB: send -->
<input type="hidden" name="gidlist" value="">
<input type="submit" name="preview" value="Eelvaade &gt;&gt;" class="formbutton">
<input type="submit" name="save" value="Salvesta" class="formbutton">
<input type="submit" name="cancel" value="Cancel" class="formbutton"> 
<input type="submit" name="save" value="&gt;{VAR:switch}" OnClick="writemessage._sethtml.value={VAR:switchval};" class="formbutton">
<!-- SUB: confbutton -->
<input type="submit" name="configure" value=">Häälestamine" class="formbutton">
<!-- END SUB: confbutton -->

<!-- END SUB: toolbar -->
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
<script language="Javascript">
document.writemessage.mtargets1.focus();
function make_it_a_list_msg()
{
	writemessage._makelist.value="1";
	PreSubmit();
	writemessage.submit();
};
window.make_it_a_list_msg=make_it_a_list_msg;
</script>
