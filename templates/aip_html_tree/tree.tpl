<style type='text/css'>
.fgtext_bad {
font-family: Arial, Helvetica, sans-serif;
color: #002E73;
font-size: 12px;
text-decoration: none;
}

.fgtext_bad a {
color: #002E73; text-decoration: underline;
}

.fgtext_bad a:hover {
color: #002E73; text-decoration: underline;
}

</style>

<!-- SUB: YAH_ENTRY -->
<span class="fgtext_bad"><a href='{VAR:link}'>{VAR:text}</a></span>
<!-- END SUB: YAH_ENTRY -->
<br><br>

<!-- SUB: MENU -->
<span class="fgtext_bad"><a href='{VAR:link}'>{VAR:name}</a></span>
<!-- END SUB: MENU -->

<!-- SUB: MENU_NOSUBS -->
<span class="fgtext_bad">{VAR:name}</span>
<!-- END SUB: MENU_NOSUBS -->

<!-- SUB: GET_PDF -->
<a href='{VAR:baseurl}/index.{VAR:ext}?section={VAR:section}&action=genpdf&file={VAR:section}.pdf'><img src='{VAR:baseurl}/img/icon_file.gif' border="0"></a>
<!-- END SUB: GET_PDF -->
