<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_periods.aw,v 1.8 2004/01/13 16:24:17 kristo Exp $
// this is here so that orb will work...
classload("period");
class admin_periods extends period
{
	function period($oid = 0)
	{
		parent::init($oid);
	}
	
	/**  
		
		@attrib name=admin_list params=name default="0"
		
		@param sortby optional
		
		@returns
		
		
		@comment

	**/
	function admin_list($arr)
	{
		$url = $this->mk_my_orb("convert_periods",array(),"converters");
		$retval = "This interface has been deprecated, please run <a href='$url'>$url</a>, if you haven't done so already<br />";
		return $retval;
	}
};
?>
