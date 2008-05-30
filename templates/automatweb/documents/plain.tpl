{VAR:title}<br>
{VAR:text}

<!-- SUB: image -->
<table cellspacing="0" cellpadding="5" border="0" align="{VAR:alignstr}">
<tr>
	<td><img src="{VAR:imgref}" alt="{VAR:alt}" width="{VAR:width}" height="{VAR:height}"><br/>
	<span class="imagecomment">{VAR:imgcaption}</span></td>
	</tr>
</table>
<!-- END SUB: image -->

<!-- SUB: image_has_big -->
<table cellspacing="0" cellpadding="5" border="0" align="{VAR:alignstr}">
<tr>
	<td><a href="JavaScript: void(0)" onClick="window.open('{VAR:bigurl}','popup','width={VAR:big_width},height={VAR:big_height}');"><img src="{VAR:imgref}" alt="{VAR:alt}" title="{VAR:alt}"></a><br/><span class="imagecomment">{VAR:imgcaption}</span></td>
	</tr>
</table>
<!-- END SUB: image_has_big -->

<!-- SUB: image_linked -->
<table cellspacing="0" cellpadding="5" border="0" align="{VAR:alignstr}">
<tr>
	<td><a {VAR:target} href="{VAR:plink}"><img src="{VAR:imgref}" alt="{VAR:alt}" width="{VAR:width}" height="{VAR:height}"></a><br/>
	<span class="imagecomment">{VAR:imgcaption}</span></td>
	</tr>
</table>
<!-- END SUB: image_has_big -->