<script language="JavaScript">
<!--
function MM_swapImgRestore() { //v2.0
  if (document.MM_swapImgData != null)
    for (var i=0; i<(document.MM_swapImgData.length-1); i+=2)
      document.MM_swapImgData[i].src = document.MM_swapImgData[i+1];
}

function MM_preloadImages() { //v2.0
  if (document.images) {
    var imgFiles = MM_preloadImages.arguments;
    if (document.preloadArray==null) document.preloadArray = new Array();
    var i = document.preloadArray.length;
    with (document) for (var j=0; j<imgFiles.length; j++) if (imgFiles[j].charAt(0)!="#"){
      preloadArray[i] = new Image;
      preloadArray[i++].src = imgFiles[j];
  } }
}

function MM_swapImage() { //v2.0
  var i,j=0,objStr,obj,swapArray=new Array,oldArray=document.MM_swapImgData;
  for (i=0; i < (MM_swapImage.arguments.length-2); i+=3) {
    objStr = MM_swapImage.arguments[(navigator.appName == 'Netscape')?i:i+1];
    if ((objStr.indexOf('document.layers[')==0 && document.layers==null) ||
        (objStr.indexOf('document.all[')   ==0 && document.all   ==null))
      objStr = 'document'+objStr.substring(objStr.lastIndexOf('.'),objStr.length);
    obj = eval(objStr);
    if (obj != null) {
      swapArray[j++] = obj;
      swapArray[j++] = (oldArray==null || oldArray[j-1]!=obj)?obj.src:oldArray[j];
      obj.src = MM_swapImage.arguments[i+2];
  } }
  document.MM_swapImgData = swapArray; //used for restore
}
//-->
</script>




<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr valign="bottom"> 
    	<td width="10" height="10" align="right"><img src="{VAR:baseurl}/img/n_L_top.gif" width="10" height="10"></td>
		<td height="10" align="center" background="{VAR:baseurl}/img/n_T_top.gif"><img src="{VAR:baseurl}/img/spacer.gif" width="1" height="10"></td>
		<td width="10" height="10" align="left"><img src="{VAR:baseurl}/img/n_R_top.gif" width="10" height="10"></td>
	</tr>
	<tr valign="middle"> 
		<td width="10" align="right" background="{VAR:baseurl}/img/n_T_left.gif">&nbsp;</td>
		<td align="center" class="text">



<table width="100%" border="0" cellpadding="0" cellspacing="9">
<tr><td class="textpealkiri">{VAR:question}</td></tr>
<tr><td valign="top">


	<table border="0" cellpadding="0" cellspacing="0">

	<!-- SUB: ANSWER -->
	<tr>
	<td valign="top" width="17"><img border="0" src="/img/trans.gif" width="1" height="2"><br><img name="poll{VAR:answer_id}" border="0" src="/img/nool.gif"></td>
	<td valign="top" class="text"><a href="{VAR:click_answer}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('document.poll{VAR:answer_id}','document.poll{VAR:answer_id}','/img/nool.gif','#99{VAR:answer_id}')">{VAR:answer}</a><br>
	<img src="{VAR:baseurl}/img/trans.gif" width="6" height="9"></td>
	</tr>
	<!-- END SUB: ANSWER -->

	</table>

</td></tr>
</table>



</td>

		<td width="10" align="left" background="{VAR:baseurl}/img/n_T_right.gif">&nbsp;</td>
	</tr>
    <tr valign="top"> 
    	<td width="10" height="10" align="right"><img src="{VAR:baseurl}/img/n_L_bot.gif" width="10" height="10"></td>
		<td height="10" align="center" background="{VAR:baseurl}/img/n_T_bot.gif">&nbsp;</td>
		<td width="10" height="10" align="left"><img src="{VAR:baseurl}/img/n_R_bot.gif" width="10" height="10"></td>
	</tr>
</table>







