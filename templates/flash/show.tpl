<!-- SUB: IN_ADMIN -->
<script src="{VAR:baseurl}/automatweb/js/swfobject.js" type="text/javascript"></script>
<!-- END SUB: IN_ADMIN -->
<div id="{VAR:id}"></div>
<script type="text/javascript">
	var {VAR:id} = new SWFObject("{VAR:url}","single","{VAR:width}","{VAR:height}","7");
	{VAR:id}.write("{VAR:id}");
</script>