<span class="textpealkirilead"><a href='{VAR:baseurl}/index.{VAR:ext}/section={VAR:docid}'>{VAR:title}</a></span><br>

<!-- SUB: ablock -->
<span class="textauthor">by {VAR:author} {VAR:modified}</span><br>
<!-- END SUB: ablock -->
<span class="text2">{VAR:text}</span><br><Br>

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
		<td><a href='{VAR:plink}'><img border=0 src='{VAR:imgref}'></a></td>
	</tr>
	<tr>
		<td class="text">{VAR:imgcaption}</td>
	</tr>
</table>
<!-- END SUB: image_linked -->

<!-- SUB: link -->
<a {VAR:target} href='{VAR:url}'>{VAR:caption}</a>
<!-- END SUB: link -->

