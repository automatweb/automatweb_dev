<!-- SUB: toolbar -->
<div id=idBox style="width: 600px;text-align: left; vertical-align: middle; visibility: hidden, height:25;overflow:hidden;background:gainsboro" ID=htmlOnly valign="top">
	<script>
		var buttons=new Array(24,23,23,4,23,23,23,4,23,23,23,23);

		var action=new Array("bold","italic","underline","","justifyleft","justifycenter","justifyright","","insertorderedlist","insertunorderedlist","outdent","indent","","");

		var tooltip=new Array("Bold Text","Italic Text","Underline Text","","Left Justify","Center Justify","Right Justify","","Ordered List","Unordered List","Remove Indent","Indent","","");

		var left=0
		var s="";

		for (var i=0;i<buttons.length;i++) 
		{
			s+="<span style='position:relative;height:26;width: " + buttons[i] + "'><span style='position:absolute;margin:0px;padding:0;height:26;top:0;left:0;width:" + (buttons[i]) + ";clip:rect(0 "+buttons[i]+" 25 "+0+");overflow:hidden'><img border='0' src='{VAR:baseurl}/automatweb/images/toolbar.gif' style='position:absolute;top:0;left:-" + left + "' width=290 height=50";
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
										<option selected>V&auml;rv...
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
										<option style="color:green">soheline</option>
										<option style="color:seagreen">mereroheline</option>
										<option style="color:olive">oliiv</option>
										<option style="color:orange">oranz</option>
										<option style="color:darkgoldenrod">tumekollane</option>
										<option style="color:gray">hall</option>
									</select>
</div>
<input type='hidden' name='nobreaks' value='1'>
<script>
function format(what,opt) 
{
	if (opt=="removeFormat") 
	{
		what=opt;
		opt=null;
	}
	if (opt==null)
	{
		sel_el.document.execCommand(what);
	}
	else
	{
		sel_el.document.execCommand(what,"",opt);
	}

	var s=sel_el.document.selection.createRange(),p=s.parentElement()  
	if ((p.tagName=="FONT") && (p.style.backgroundColor!=""))
		p.outerHTML=p.innerHTML;
	sel_el.focus();
	sel=null
}

var sel_el = '';
</script>
<!-- END SUB: toolbar -->

<!-- SUB: field -->
<input type="hidden" name="{VAR:name}" value="{VAR:value}">
<iframe name="{VAR:name}_edit" onFocus="sel_el = document.frames('{VAR:name}_edit');" onBlur="document.changeform.elements('{VAR:name}').value=document.frames('{VAR:name}_edit').document.body.innerHTML" frameborder="1" width="{VAR:width}" height="{VAR:height}"></iframe>
<script>
	elx = document.frames("{VAR:name}_edit");
	elx.document.designMode='On';
	elx.document.write("<body style='font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 12px;background-color: #FFFFFF; border: #CCCCCC solid; border-width: 1px 1px 1px 1px; margin-left: 0px;padding-left: 3px;	padding-top: 0px;	padding-right: 3px; padding-bottom: 0px;'>");
	elx.document.write(document.changeform.elements['{VAR:name}'].value);
	elx.document.close();
</script>
<!-- END SUB: field -->
