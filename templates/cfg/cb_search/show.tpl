<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css" />
<form action="{VAR:baseurl}/{VAR:section}" method="GET">
<table border="0" cellpadding="0" cellspacing="0">
{VAR:form}
</table>
{VAR:reforb}

</form>

<form action="{VAR:baseurl}/orb.{VAR:ext}" method="POST">
{VAR:table}

<!-- SUB: SUBMIT_BUTTON -->
<input type="submit" value="{VAR:submit_text}">
<!-- END SUB: SUBMIT_BUTTON -->

{VAR:reforb}
</form>