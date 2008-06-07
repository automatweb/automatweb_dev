<?php
/*
@classinfo maintainer=robert
*/
class link_fix extends _int_object
{
	function set_prop($var, $val)
	{
		if($var == "url")
		{
			$this->url = $url;
		}
		return parent::set_prop($var, $val);
	}

	function save()
	{
		parent::save();
		if(parent::prop("url") != $this->url)
		{
			$i = get_instance(CL_IMAGE);
			$i->db_query("ALTER TABLE extlinks CHANGE `url` `url` TEXT NULL");
		}
		parent::set_prop("url", $this->url);
		return parent::save();
	}
}
?>
