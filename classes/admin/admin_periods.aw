<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_periods.aw,v 1.5 2003/05/12 17:35:51 duke Exp $
// this is here so that orb will work...
classload("periods");
class admin_periods extends periods
{
	function periods($oid = 0)
	{
		parent::init($oid);
	}
	
	function admin_list($arr)
	{
		$url = $this->mk_my_orb("convert_periods",array(),"converters");
		$retval = "This interface has been deprecated, please run <a href='$url'>$url</a>, if you haven't done so already<br>";
		return $retval;
	}
};
?>
