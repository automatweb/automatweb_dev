<!-- SUB: SHOW_TITLE -->
<h1>{VAR:title}</h1>
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


<!-- SUB: image -->
<div style="width: {VAR:width}px;" class="image image_{VAR:alignstr}">
<div style="width: {VAR:width}px;">
<span class="author">
	<!-- SUB: HAS_AUTHOR -->
	(FOTO: {VAR:author})
	<!-- END SUB: HAS_AUTHOR -->
</span>
<a href="<?php echo strlen('{VAR:bigurl}') > 0 ? '{VAR:bigurl}' : '{VAR:imgref}'; ?>" title="{VAR:imgcaption}" class="thickbox"><img src="{VAR:imgref}" alt="Single Image"/></a>
{VAR:imgcaption}
</div>
</div>
<!-- END SUB: image -->


<!-- SUB: image -->
<div style="width: {VAR:width}px;" class="image image_{VAR:alignstr}">
<div style="width: {VAR:width}px;">
<span class="author">
	<!-- SUB: HAS_AUTHOR -->
	(FOTO: {VAR:author})
	<!-- END SUB: HAS_AUTHOR -->
</span>
<a href="<?php echo strlen('{VAR:bigurl}') > 0 ? '{VAR:bigurl}' : '{VAR:imgref}'; ?>" title="{VAR:imgcaption}" class="thickbox"><img src="{VAR:imgref}" alt="Single Image"/></a>
{VAR:imgcaption}
</div>
</div>
<!-- END SUB: image -->

<!-- SUB: image_linked -->
<div style="width: {VAR:width}px;" class="image image_{VAR:alignstr}">
<div style="width: {VAR:width}px;">
<span class="author">
	<!-- SUB: HAS_AUTHOR -->
	(FOTO: {VAR:author})
	<!-- END SUB: HAS_AUTHOR -->
</span>
<a href="{VAR:plink}" title="{VAR:imgcaption}"><img src="{VAR:imgref}" alt="{VAR:alt}"/></a>
{VAR:imgcaption}
</div>
</div>
<!-- END SUB: image_linked -->

<!-- SUB: image_has_big -->
<div style="width: {VAR:width}px;" class="image image_{VAR:alignstr}">
<div style="width: {VAR:width}px;">
<span class="author">
	<!-- SUB: HAS_AUTHOR -->
	(FOTO: {VAR:author})
	<!-- END SUB: HAS_AUTHOR -->
</span>
<a href="{VAR:bigurl}" title="{VAR:imgcaption}" class="thickbox"><img src="{VAR:imgref}" alt="Single Image"/></a>
{VAR:imgcaption}
</div>
</div>
<!-- END SUB: image_has_big -->