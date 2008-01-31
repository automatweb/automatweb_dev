<?php
/*
@classinfo maintainer=voldemar
*/
class rss_feed_obj extends _int_object
{
	function set_prop($name, $value)
	{
		if ("item_dfn" === $name)
		{
			$this->awobj_set_item_dfn($value);
		}
		elseif ("classes" === $name)
		{
			$this->awobj_set_classes($value);
		}
		else
		{
			parent::set_prop($name, $value);
		}
	}

	function awobj_set_classes($value)
	{
		$classes = array();
		$all_classes = aw_ini_get("classes");

		foreach ($value as $clid)
		{
			if (array_key_exists($clid, $all_classes))
			{
				$classes[] = $clid;
			}
			else
			{
				//!!! throw ex
			}
		}

		parent::set_prop("classes", $classes);
	}

	function awobj_set_item_dfn($value)
	{
		$classes = $this->prop("classes");

		foreach ($value as $clid => $cl_dfn)
		{
			if (!in_array($clid, $classes))
			{
				unset($value[$clid]);
				//!!! throw ex
			}

			$cl_cfgutils = get_instance("cfg/cfgutils");
			$properties = $cl_cfgutils->load_properties(array("clid" => $clid));

			foreach ($cl_dfn as $element => $dfn)
			{
				if (!is_oid($dfn) and !array_key_exists($dfn, $properties))
				{
					unset($value[$clid][$element]);
					//!!! throw ex
				}
			}
		}

		parent::set_prop("item_dfn", $value);
	}
}

?>
