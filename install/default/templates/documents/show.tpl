<span class="textpealkiri">{VAR:title}</span><br>
<!-- SUB: ablock -->
<span class="textauthor">by {VAR:author} {VAR:modified}</span><br>
<!-- END SUB: ablock -->
<span class="text2">{VAR:text}</span><br>





<!-- SUB: FORUM_ADD -->
<!--<a href="{VAR:baseurl}/comments.{VAR:ext}?section={VAR:docid}&forum_id=11609&mode=flat">{VAR:LC_DOC_COMMENT2}</a>-->
<!-- END SUB: FORUM_ADD -->

<!--
<a href="{VAR:baseurl}/index.{VAR:ext}?type=print&docid={VAR:docid}">{VAR:LC_DOC_PRINT2}</a>
<a href="{VAR:baseurl}/index.{VAR:ext}?type=send&docid={VAR:docid}&section={VAR:section}">{VAR:LC_DOC_MAIL2}</a>
-->

<!-- SUB: image -->
<table border=0 cellpadding=0 cellspacing=0 {VAR:align}>
	<tr>
		<td><img src='{VAR:imgref}'></td>
	</tr>
	<tr>
		<td class="text">{VAR:imgcaption}</td>
	</tr>
</table>
<!-- END SUB: image -->

<!-- SUB: image_linked -->
<table border=0 cellpadding=0 cellspacing=0 {VAR:align}>
	<tr>
		<td><a {VAR:target} href='{VAR:plink}'><img border=0 src='{VAR:imgref}'></a></td>
	</tr>
	<tr>
		<td class="text">{VAR:imgcaption}</td>
	</tr>
</table>
<!-- END SUB: image_linked -->

<!-- SUB: link -->
<a {VAR:target} href='{VAR:url}'>{VAR:caption}</a>
<!-- END SUB: link -->

<br>
<span class="text2">
<a href='{VAR:baseurl}/?class=document&action=feedback&section={VAR:docid}'>Feedback</a> / <a href='{VAR:baseurl}/?class=document&action=send&section={VAR:docid}'>Saada</a> / <a href='{VAR:baseurl}/?class=document&action=print&section={VAR:docid}'>Prindi</a>
</span>