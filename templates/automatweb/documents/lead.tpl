<!-- SUB: SHOW_TITLE -->
<p class="title"><a href="{VAR:document_link}">{VAR:title}</a></p>
<!-- END SUB: SHOW_TITLE -->

<!-- SUB: ablock -->
<p class="author">teksti autor: {VAR:author} {VAR:modified}</p>
<!-- END SUB: ablock -->

{VAR:text}

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