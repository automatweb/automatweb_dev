<table class="text" width="100%">
<tr><td colspan="2" class="midTitlebig"><a href="{VAR:blog_link}">{VAR:heading}</a></td></tr>
<tr><td colspan="2">{VAR:pageselector}</td></tr>
<!-- SUB: entry -->
<tr>
	<td class="rate_rowbgcolor_{VAR:oddeven}" colspan="2">
	<div><b>{VAR:caption}</b></div>
	<div>{VAR:entry_time}
	<!-- SUB: auth -->
	, kirjutas: <a href="{VAR:author_link}">{VAR:author}</a>
	<!-- END SUB: auth -->
	</div>
	<div>{VAR:text}</div>
	<a href="{VAR:link}">({VAR:count}) {VAR:LC_RATE_BLOG_COMMENT}</a>
	<div>
	</td>
</tr>
<!-- END SUB: entry -->
<!-- SUB: comment -->
<tr>
	<td class="rate_rowbgcolor_{VAR:oddeven}" style="width:70px;text-align:center">
		<a href="{VAR:user_link}">{VAR:user}</a><br />
		{VAR:entry_time}
	</td>
	<td class="rate_rowbgcolor_{VAR:evenodd}">
		{VAR:text}
	</td>
</tr>
<!-- END SUB: comment -->
</table>
