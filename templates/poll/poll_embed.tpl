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



<!--<a href="{VAR:show_url}"></a>-->

<table width="100%" border="0" cellpadding="1" cellspacing="0">
<tr><td align="center" bgcolor="#E2E2E2">

<table width="100%" border="0" cellpadding="1" cellspacing="0">
<tr><td align="center" bgcolor="#FFFFFF">


<table width="100%" border="0" cellpadding="0" cellspacing="9">
<tr><td class="polltext">{VAR:question}</td></tr>
<tr><td valign="top">


	<table width="100%" border="0" cellpadding="0" cellspacing="0">

	<!-- SUB: ANSWER -->
	<tr>
	<td valign="top" width="17"><img name="poll{VAR:answer_id}" border="0" src="/img/poll_julla-out.gif" width="17" height="11"></td>
	<td width="99%" valign="top" class="pollanswer"><a href="{VAR:click_answer}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('document.poll{VAR:answer_id}','document.poll{VAR:answer_id}','/img/poll_julla-over.gif','#99{VAR:answer_id}')">{VAR:answer}</a><br>
	<img src="{VAR:baseurl}/img/trans.gif" width="6" height="9"></td>
	</tr>
	<!-- END SUB: ANSWER -->

	</table>

</td></tr>
</table>

</td></tr>
</table>
</td></tr>
</table>

