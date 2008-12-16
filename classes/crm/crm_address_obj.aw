<?php
/*
@classinfo  maintainer=markop
*/

class crm_address_obj extends _int_object
{

	/** Sets county to address
		@attrib api=1 params=pos
		@param county optional type=string/oid
			county
		@return oid
			county object id
	**/
	public function set_county($county)
	{
		if(!$county)
		{
			return null;
		}
		if(is_oid($county) && $GLOBALS["object_loader"]->can("view" , $county))
		{
			$o = obj($county);
		}
		elseif(strlen($county))
		{
			$filter = array();
			$filter["class_id"] = CL_CRM_COUNTY;
			$filter["lang_id"] = array();
			$filter["site_id"] = array();
			$filter["name"] = $county;
			$ol = new object_list($filter);
			$o = reset($ol->arr());
		}
	
		if(!is_object($o))
		{
			$o = new object();
			$o->set_class_id(CL_CRM_COUNTY);
			$o->set_parent($this->id());
			$o->set_name($county);
			$o->save();
		}

		$this->set_prop("maakond" , $o->id());
		$this->save();
		return $o->id();
	}

	/** Sets city to address
		@attrib api=1 params=pos
		@param city optional type=string/oid
			county
		@return oid
			city object id
	**/
	public function set_city($city)
	{
		if(!$city)
		{
			return null;
		}
		if(is_oid($city) && $GLOBALS["object_loader"]->can("view" , $city))
		{
			$o = obj($city);
		}
		elseif(strlen($city))
		{
			$filter = array();
			$filter["class_id"] = CL_CRM_CITY;
			$filter["lang_id"] = array();
			$filter["site_id"] = array();
			$filter["name"] = $city;
			$ol = new object_list($filter);
			$o = reset($ol->arr());
		}
	
		if(!is_object($o))
		{
			$o = new object();
			$o->set_class_id(CL_CRM_CITY);
			$o->set_parent($this->id());
			$o->set_name($city);
			$o->save();
		}

		$this->set_prop("linn" , $o->id());
		$this->save();
		return $o->id();
	}

}

?>