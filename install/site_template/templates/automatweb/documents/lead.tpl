<b>
<!-- SUB: TITLE_LINK_BEGIN -->
<a href='{VAR:baseurl}/{VAR:docid}'>
<!-- END SUB: TITLE_LINK_BEGIN -->
{VAR:title}
<!-- SUB: TITLE_LINK_END -->
</a>
<!-- END SUB: TITLE_LINK_END -->
</b> <br /><br />
{VAR:text}

<!-- SUB: image -->
<div style="position: relative; float: {VAR:alignstr};">
<img src="{VAR:imgref}" alt="{VAR:alt}" title="{VAR:alt}" /><br />
{VAR:imgcaption}
</div>
<!-- END SUB: image -->

<!-- SUB: image_has_big -->
<div style="position: relative; float: {VAR:alignstr}; padding: 2px;">
<a href="#" onClick="window.open('{VAR:bi_show_link}','popup','width={VAR:big_width},height={VAR:big_height}');"><img src="{VAR:imgref}" alt="{VAR:alt}" title="{VAR:alt}" border="0" /></a><br />
{VAR:imgcaption}
</div>
<!-- END SUB: image_has_big -->
