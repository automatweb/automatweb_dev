<?php

class generic_xml_ds_obj extends _int_object implements object_import_ds_interface
{
	public function get_objects($params = array())
	{
	}

	public function get_folders()
	{
	}

	private function do_import()
	{
		$rss = file_get_contents($this->prop("location"));
	}
}

?>
