<script type="text/javascript">
var sel_row_style = '';
function hilight(el,tgt)
{
        tgtel = document.getElementById(tgt);
        if (el.checked)
        {
                tgtel.setAttribute('oldclass',tgtel.className);
                tgtel.className = sel_row_style;
        }
        else
        {
                tgtel.className = tgtel.getAttribute('oldclass');
        };
}
</script>

<form action="{VAR:baseurl}/index.{VAR:ext}" method="POST">
<table border=0 cellpadding=0 cellspacing=0 width='100%'>
<!-- SUB: ROW -->
	<tr>
		<!-- SUB: COL -->
			<td>
				{VAR:product}
			</td>
		<!-- END SUB: COL -->
	</tr>
<!-- END SUB: ROW -->
</table>

<input type="submit" value="Lisa korvi">
{VAR:reforb}
</form>