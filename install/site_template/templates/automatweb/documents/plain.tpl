<span class="textpealkiri">{VAR:title}</span><br />
<span class="text"><br />
{VAR:text}
</span>
<!-- SUB: image -->
<div style="position: relative; float: {VAR:alignstr};">
<img src="{VAR:imgref}" alt="{VAR:alt}" title="{VAR:alt}" /><br />
{VAR:imgcaption}
</div>
<!-- END SUB: image -->

<!-- SUB: image_has_big -->
<div style="position: relative; float: {VAR:alignstr}; padding: 2px;">
<a href="#" onClick="window.open('{VAR:bigurl}','popup','width={VAR:big_width},height={VAR:big_height}');"><img src="{VAR:imgref}" alt="{VAR:alt}" title="{VAR:alt}" border="0" /></a><br />
{VAR:imgcaption}
</div>
<!-- END SUB: image_has_big -->
