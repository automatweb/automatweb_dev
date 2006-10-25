<script type="text/javascript">

var f7_cks;

function f7_DisableCBGroup(){
	alert(453);
 f7_cks=document.getElementsByTagName('INPUT');
 for (f7_0=0;f7_0<f7_cks.length;f7_0++){
  if (f7_cks[f7_0].type=='checkbox'){
   if (f7_cks[f7_0].parentNode.title.match('f7_Master')){
    f7_cks[f7_0].parentNode.state=f7_cks[f7_0].parentNode.title.split('f7_Master')[1];
    f7_cks[f7_0].parentNode.title=f7_cks[f7_0].parentNode.title.replace(f7_cks[f7_0].parentNode.state,'')
    f7_Disable(f7_cks[f7_0])
    f7_cks[f7_0].onclick=function(){ f7_Disable(this); }
   }
  }
 }
}

function f7_Disable(f7_obj){
 for (f7_1=0;f7_1<f7_cks.length;f7_1++){
  if (f7_cks[f7_1].parentNode.title==f7_obj.parentNode.title.replace('f7_Master','')){
   if (f7_obj.checked){
    f7_cks[f7_1].setAttribute('disabled',true)
    if (f7_obj.parentNode.state=='CHECK'){
     f7_cks[f7_1].checked=true;
    }
    if (f7_obj.parentNode.state=='UNCHECK'){
     f7_cks[f7_1].checked=false;
    }
   }
   else {
    f7_cks[f7_1].removeAttribute('disabled')
   }
  }
 }
}
</script>
<form method="post">
<!--{VAR:name}<br>-->
<!-- SUB: GROUP -->

	<table border="1" width="770" style="border:1px solid black;">
	<tr><td colspan="{VAR:span}" bgcolor="silver" style="font-size:13px; border:1px solid black;"><b>{VAR:name}</b></td></tr>
	<!-- SUB: HEADER -->
		<tr>
			<td>
				<b>Teema/küsimus</b><!--{VAR:corner_caption}-->
			</td>
		<!-- SUB: QUESTION -->
			<td style="font-size:10px;">
				{VAR:question_name}
			</td>
		<!-- END SUB: QUESTION -->
		</tr>
	<!-- END SUB: HEADER -->
	<!-- SUB: TOPIC -->
		<tr>
			<td  width="250" style="font-size:10px;border:1px solid gray;">
				{VAR:topic_name}
			</td>
			<!-- SUB: ANSWER -->
				<td style="text-align:center;">
					{VAR:answer_element}
				</td>
			<!-- END SUB: ANSWER -->
		</tr>
	<!-- END SUB: TOPIC -->
	</table>
<!-- END SUB: GROUP -->
<table>
	<tr>
		<td>
			<span style="font-size:13px;"><br/>Kommentaarid:</span><br/>
			<textarea name="pers[comment]" cols='50' rows='5'></textarea>
		</td>
	</tr>
</table>

<!-- SUB: PERS_DATA -->
<table style="font-size:12px;">
	<tr>
	<td><br/><br/>
	<span style="font-size:13px;">Informatsioon Teie kohta (tehke palun märge sobivasse lahtrisse):</span><br><br>
	<span style="font-size:12px; font-weight:bold;">Sugu:</span><br/>
	<input type="radio" value="1" name="pers[gender]"/>mees
	<input type="radio" value="2" name="pers[gender]"/>naine
	<br/>
	<span style="font-size:12px; font-weight:bold;">Vanus:</span><br/>
	<input type="radio" value="1" name="pers[age]"/>18 v&otilde;i noorem<br/>
	<input type="radio" value="2" name="pers[age]"/>19-29<br/>
	<input type="radio" value="3" name="pers[age]"/>30-39<br/>
	<input type="radio" value="4" name="pers[age]"/>40-49<br/>
	<input type="radio" value="5" name="pers[age]"/>50-59<br/>
	<input type="radio" value="6" name="pers[age]"/>60 v&otilde;i vanem<br/>
	<br/>
	</td>
</tr>
</table>
	<table>
		<tr>
			<td colspan=3><span style="font-size:12px; font-weight:bold;">Tegevusala:</span></td></tr>
			<tr>
			<td width="250">&nbsp;
			</td>
			<td>
				<span style="font-size:12px; font-weight:bold;">Tallinnast</span>
			</td>
			<td align="center">
				<span style="font-size:12px; font-weight:bold;">mujalt</span>
			</td>
		</tr>
		<!-- SUB: PERS_AREA -->
		<tr>
			<td width="250">
				{VAR:caption}
			</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[area_radio]"/>
			</td>
			<td>
				<input type="textbox" name="pers[area_text][{VAR:value}]"/>
			</td>
		</tr>
		<!-- END SUB: PERS_AREA -->


</table>
<br/>
<span style="font-size:12px; font-weight:bold;">Kui &otilde;pite v&otilde;i t&ouml;&ouml;tate &uuml;likoolis, siis millises?</span><br/><br/>
<table>
		<tr>
			<td width="250"><span style="font-size:12px; font-weight:bold;">&uuml;likool</span></td>
			<td>&nbsp;</td>
			<td><span style="font-size:12px; font-weight:bold;">teaduskond(instituut/kolledz)</span></td>
		<tr>
		<!-- SUB: PERS_SCHOOL -->
		<tr>
			<td>{VAR:caption}</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[school_radio]"/>
			</td>
			<td>
				<input type="textbox" name="pers[school_text][{VAR:value}]"/>
			</td>
		</tr>
		<!-- END SUB: PERS_SCHOOL -->
</table>
<br/>
<span style="font-size:12px; font-weight:bold;">Teie t&ouml;&ouml;-, &otilde;pingu v&otilde;i huvivaldkond (t&auml;psustage)?</span><br/><br/>
<table>
		<tr>
			<td width="250"><span style="font-size:12px; font-weight:bold;">valdkond</span></td>
			<td>&nbsp;</td>
			<td><span style="font-size:12px; font-weight:bold;">t&auml;psustus</span></td>
		<tr>
		<!-- SUB: S_AREA -->
		<tr>
			<td>{VAR:caption}</td>
			<td>
				<input type="checkbox" name="pers[intrest_check][{VAR:value}]"/>
			</td>
			<td>
				<input type="textbox" name="pers[intrest_text][{VAR:value}]"/>
			</td>
		</tr>
		<!-- END SUB: S_AREA -->
</table>
<br/>
<span style="font-size:12px; font-weight:bold;">Kui sageli k&uuml;lastate Rahvusraamatukogu?</span><br/><br/>
<table>
		<!-- SUB: VISITS -->
		<tr>
			<td width="250">{VAR:caption}</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[visits]"/>
			</td>
		</tr>
		<!-- END SUB: VISITS -->
</table>
<br/>
<span style="font-size:12px; font-weight:bold;">Kuidas raamatukogu teenuseid kasutate?</span><br/><br/>
<table>
		<!-- SUB: USAGE -->
		<tr>
			<td width="250">{VAR:caption}</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[usage]"/>
			</td>
		</tr>
		<!-- END SUB: USAGE -->
</table>
<!-- END SUB: PERS_DATA -->
<input type="submit" value="Saadan ankeedi ära">
<!-- {VAR:submit_caption} -->
{VAR:reforb}
</form>
<!-- SUB: A_ELEMENT -->
	<table>
		<!-- SUB: INPUT -->
			<td style="font-size:11px;text-align:center;">
			{VAR:nr}<br/>{VAR:html_element}
			</td>
		<!-- END SUB: INPUT -->
	</table>
<!-- END SUB: A_ELEMENT -->
