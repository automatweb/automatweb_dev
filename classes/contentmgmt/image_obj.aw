<?php

class image_obj extends _int_object
{
	function set_prop($k, $v)
	{
		if($k == "file" || $k == "file2")
		{
			parent::set_meta("old_file", parent::prop("file"));
		}
		return parent::set_prop($k, $v);
	}
}

?>
