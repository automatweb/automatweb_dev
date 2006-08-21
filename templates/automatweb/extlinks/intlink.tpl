<script language="javascript">
function setLink(li,title)
{
	document.changeform.url.value=li;
	document.changeform.name.value=title; // nime element
}
</script>
<a href="javascript:remote('no',500,400,'{VAR:search_doc}')">{VAR:int_link_caption}</a>
