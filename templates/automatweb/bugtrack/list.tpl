<form action='reforb.{VAR:ext}' method='POST' name='foo'>
{VAR:header} <br>
<span class='title'>
<!-- SUB: PAGE -->
<a href='{VAR:link}'>{VAR:from} - {VAR:to}</a> | 
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to}
<!-- END SUB: SEL_PAGE --> 
</span>
{VAR:table}
{VAR:reforb}
</form>