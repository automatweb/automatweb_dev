<?php

class menu_obj extends _int_object
{
	function save()
	{
		$ret =  parent::save();
		$this->clear_menu_cache();
		return $ret;
	}

	function delete()
	{
		$ret =  parent::delete();
		$this->clear_menu_cache();
		return $ret;
	}

	public function clear_menu_cache()
	{
		$cache = new cache();
		$cache->file_clear_pt('menus');
	}


}

?>
