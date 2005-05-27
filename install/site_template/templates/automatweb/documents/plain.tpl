<!-- SUB: SHOW_TITLE -->
<span class="textpealkiri">{VAR:title}</span>
<!-- END SUB: SHOW_TITLE -->
<!-- SUB: SHOW_MODIFIED -->
<span class="kp">{VAR:date_est}</span>
<!-- END SUB: SHOW_MODIFIED -->
<p class="text">{VAR:text}</p>




<!-- SUB: PRINTANDSEND -->
<p>
<table width="100%" border="0">
<tr><td align="right"><a href="{VAR:baseurl}/?class=document&action=print&section={VAR:docid}" target="_new"
 onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('print','','{VAR:baseurl}/img/icon_2_print_over.gif',1)"><IMG SRC="{VAR:baseurl}/img/icon_22_print.gif" BORDER="0" name="print" ALT="Print"></a><IMG SRC="{VAR:baseurl}/img/trans.gif" BORDER="0" width="5" ALT="">
 <!--
 <a href="{VAR:baseurl}/?class=document&action=print&section={VAR:docid}&format=pdf"
 onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('pdf','','{VAR:baseurl}/img/icon_2_pdf_over.gif',1)"><IMG SRC="{VAR:baseurl}/img/icon_22_pdf.gif" BORDER="0" name="pdf" ALT="Salvesta PDF"></a><IMG SRC="{VAR:baseurl}/img/trans.gif" BORDER="0" width="5" ALT=""><a href="{VAR:baseurl}/?class=document&action=feedback&section={VAR:docid}"
 onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('feedback','','{VAR:baseurl}/img/icon_2_feedback_over.gif',1)"><IMG SRC="{VAR:baseurl}/img/icon_22_feedback.gif" BORDER="0" name="feedback" ALT="Tagasiside"></a>
 -->
 </td></tr></table>
<!--{VAR:baseurl}/?class=document&action=send&section={VAR:docid}-->
<!-- END SUB: PRINTANDSEND -->