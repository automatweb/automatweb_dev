<table border=0 cellpadding=0 cellspacing=0 {VAR:align}>
<tr>
<td style="padding-bottom: 10px;">


<!-- SUB: SHOW_TITLE -->

<!-- SUB: TITLE_LINK_BEGIN -->
<a href='{VAR:baseurl}/{VAR:docid}'>
<!-- END SUB: TITLE_LINK_BEGIN -->

<b>{VAR:title}</b>
<!-- SUB: TITLE_LINK_END -->
</a>
<!-- END SUB: TITLE_LINK_END -->

<!-- END SUB: SHOW_TITLE -->

<!-- SUB: SHOW_MODIFIED -->
<span class="kp">{VAR:date_est}</span>
<!-- END SUB: SHOW_MODIFIED -->
<p class="text">{VAR:text}</p>


</td>
</tr>
</table>


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