<?php

class content_package_obj extends _int_object
{
	function save()
	{
		if(!is_oid(parent::id()))
		{
			parent::save();
		}
		if(!parent::can("view", parent::prop("cp_ug")) || !is_oid(parent::prop("cp_ug")))
		{
			// We'll make a usergroup for this content package. There has to be one for every content package.
			$gid = get_instance(CL_GROUP)->add_group(parent::id(), parent::name(), GRP_REGULAR, 5000);
			parent::set_prop("cp_ug", $gid);
		}
		return parent::save();
	}

	/**

		@attrib name=update_acl_for_usergroup api=1 params=name

		@param id required type=oid/array

	**/
	function update_acl_for_usergroup($arr)
	{
		$arr["id"] = is_array($arr["id"]) ? $arr["id"] : array($arr["id"]);
		foreach($arr["id"] as $id)
		{
			$cp = obj($id);
			$gid = $cp->cp_ug;
			
			$g = obj($gid);
			$odl = new object_data_list(
				array(
					"class_id" => CL_CONTENT_ITEM,
					"lang_id" => array(),
					"content_package" => $id,
				),
				array(
					CL_CONTENT_ITEM => array("acl_change", "acl_add", "acl_admin", "acl_delete", "acl_view"),
				)
			);
			foreach($odl->arr() as $id => $od)
			{
				foreach(connection::find(array("from" => $id, "from.class_id" => CL_CONTENT_ITEM, "type" => "RELTYPE_CONTENT_OBJECT")) as $conn)
				{
					// Paneme kirja, millised 6igused sellele objektile siit tingimusest tulevad. Kokkuv6ttes huvitab meid yhisosa.
					$acl[$conn["to"]][$gid]["can_change"] = $acl[$conn["to"]][$gid]["can_change"] || $od["acl_change"] ? 1 : 0;
					$acl[$conn["to"]][$gid]["can_add"] = $acl[$conn["to"]][$gid]["can_add"] || $od["acl_add"] ? 1 : 0;
					$acl[$conn["to"]][$gid]["can_admin"] = $acl[$conn["to"]][$gid]["can_admin"] || $od["acl_admin"] ? 1 : 0;
					$acl[$conn["to"]][$gid]["can_delete"] = $acl[$conn["to"]][$gid]["can_delete"] || $od["acl_delete"] ? 1 : 0;
					$acl[$conn["to"]][$gid]["can_view"] = $acl[$conn["to"]][$gid]["can_view"] || $od["acl_view"] ? 1 : 0;
				}
			}
			// Finally, paneme kirja 6igused.
			foreach($acl as $oid => $gdata)
			{
				$o = obj($oid);
				foreach($gdata as $gid => $acl_data)
				{
					$o->acl_set(obj($gid), $acl_data);
				}
			}
		}
	}

	/**

		@attrib name=remove_acl_for_objects api=1 params=name

		@param id required type=oid/array

		@param oid required type=oid/array

	**/
	function remove_acl_for_objects($arr)
	{
		$oids = safe_array($oid);
		$ids = safe_array($id);
		foreach($oids as $oid)
		{
			$o = obj($oid);
			foreach($ids as $id)
			{
				$gid = obj($id)->cp_ug;
				$o->acl_del($gid);
			}
		}
	}
}

?>
