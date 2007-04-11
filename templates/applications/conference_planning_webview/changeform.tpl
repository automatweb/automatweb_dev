<script type="text/javascript">
	function submit_changeform(action)
	{
		changed = 0;
		if (typeof(aw_submit_handler) != "undefined")
		{
			if (aw_submit_handler() == false)
			{
				return false;
			}
		}
		if (typeof action == "string" && action.length>0)
		{
			document.changeform.action.value = action;
		};
		document.changeform.submit();
	}
</script>