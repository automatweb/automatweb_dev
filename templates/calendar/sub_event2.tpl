<div class="aw04kalendersubevent" style="width:100%">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr class="aw04kalendersubevent">
			<td width="4%" align="center">
				<!-- SUB: DCHECKED -->
				<input type="checkbox" id="sel[{VAR:id}]" name="sel[{VAR:id}]" value="{VAR:id}">
				<!-- END SUB: DCHECKED -->
			</td>
			<td>
				<em>{VAR:lc_date}</em> - <img src="{VAR:iconurl}" /> <a href="{VAR:link}" title="{VAR:title}" alt="{VAR:title}">{VAR:name}</a>

				<!-- SUB: COMMENT -->
				/<i>{VAR:comment_content}</i>
				<!-- END SUB: COMMENT -->

				{VAR:modifiedby}
			</td>
		</tr>
	</table>
</div>
