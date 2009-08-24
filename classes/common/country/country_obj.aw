<?php
/*
@classinfo  maintainer=voldemar
*/

class country_obj extends _int_object
{
	/** Returns currently active administrative structure for this country
	@attrib api=1 params=pos
	@errors
		throws awex_as_country_admin_structure when not defined
	**/
	public function get_current_admin_structure()
	{
		$list = new object_list(array(
			"class_id" => CL_COUNTRY_ADMINISTRATIVE_STRUCTURE,
			"CL_COUNTRY_ADMINISTRATIVE_STRUCTURE.RELTYPE_COUNTRY" => $this->id(),
			"site_id" => array(),
			"lang_id" => array()
		));

		if ($list->count() < 1)
		{
			throw new awex_as_country_admin_structure("Administrative structure not defined for this country");
		}
		///!!! teha midagi kui rohkem kui yks on

		return $list->begin();
	}
}

/** Generic country_obj exception **/
class awex_as_country extends awex_as {}

/** Country administrative error **/
class awex_as_country_admin_structure extends awex_as_country {}


?>
