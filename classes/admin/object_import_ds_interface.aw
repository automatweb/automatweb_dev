<?php

/** Implement this interface if you want to be able to be a datasource for object import **/
interface object_import_ds_interface
{
	/** This should return an array of data from the data source
		@attrib api=1 params=name
	**/
	function get_objects($params = array());

	/** This should return an array of folders from the data source, if the source has folders 
		@attrib api=1 params=pos
	**/
	function get_folders();
}

?>