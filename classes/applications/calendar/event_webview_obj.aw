<?php

class event_webview_obj extends _int_object
{
	function prop($k)
	{
		if($k == "date_end")
		{
			return parent::prop($k) + 24*3600; 
		}
		return parent::prop($k);
	}
}

?>
