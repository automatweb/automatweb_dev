<!-- SUB: SHOW_TITLE -->
<h1>{VAR:title}</h1>
<!-- END SUB: SHOW_TITLE -->

<!-- SUB: PRINTANDSEND -->
<p class="printlink"><a href="{VAR:printlink}" target="_new"></a></p>
<!-- END SUB: PRINTANDSEND -->

{VAR:text}

<!-- SUB: ablock -->
<p class="author">teksti autor: {VAR:author} {VAR:modified}</p>
<!-- END SUB: ablock -->

<!-- SUB: image -->
<table align="{VAR:alignstr}" class="img img_{VAR:alignstr}" summary="img">
<tr>
	<td><img src="{VAR:imgref}" alt="{VAR:alt}" title="VAR:alt}">
	<br/>
	<span class="imagecomment">{VAR:imgcaption}
		<!-- SUB: HAS_AUTHOR -->
		(Foto: {VAR:author})
		<!-- END SUB: HAS_AUTHOR -->
	</span>
	</td>
	</tr>
</table>
<!-- END SUB: image -->

<!-- SUB: image_has_big -->
<table align="{VAR:alignstr}" class="img img_{VAR:alignstr}" summary="img">
<tr>
	<td><a href="JavaScript: void(0)" 
	onClick="window.open('{VAR:bi_show_link}','popup','width={VAR:big_width},height={VAR:big_height}');">
	<img src="{VAR:imgref}" alt="{VAR:alt}" title="{VAR:alt}"></a>
	<br/>
	<span class="imagecomment">{VAR:imgcaption}
	<!-- SUB: HAS_AUTHOR -->
	(Foto: {VAR:author})
	<!-- END SUB: HAS_AUTHOR -->
	</span></td>
	</tr>
</table>
<!-- END SUB: image_has_big -->

<!-- SUB: image_linked -->
<table align="{VAR:alignstr}" class="img img_{VAR:alignstr}" summary="img">
<tr>
	<td><a {VAR:target} href="{VAR:plink}">
	<img src="{VAR:imgref}" alt="{VAR:alt}" title="{VAR:alt}"></a>
	<br/>
	<span class="imagecomment">{VAR:imgcaption}
	<!-- SUB: HAS_AUTHOR -->
	(Foto: {VAR:author})
	<!-- END SUB: HAS_AUTHOR -->
	</span></td>
	</tr>
</table>
<!-- END SUB: image_linked -->

<!-- SUB: file -->
<img alt="faili ikoon" title="faili ikoon" src="{VAR:file_icon}">
<a href="{VAR:file_url}">{VAR:file_name}</a>
<!-- END SUB: file -->

<!-- SUB: youtube_link -->
<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/{VAR:video_id}&hl=en"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/{VAR:video_id}&hl=en" type="application/x-shockwave-flash" allowfullscreen="true" width="425" height="344"></embed></object>
<!-- END SUB: youtube_link -->
