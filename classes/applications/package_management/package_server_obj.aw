<?php
/*

@classinfo maintainer=dragut

*/
class package_server_obj extends _int_object
{
	function add_package($params)
	{
		
	}

	function remove_packages($ids)
	{
		if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				if ($this->can('delete', $id))
				{
					$o = new object($id);
					$o->delete(true);
				}
			}
		}
	}

	function packages_list($params)
	{
		$server = $params['obj_inst'];
		$filter = array(
			'class_id' => CL_PACKAGE,
			'parent' => $server->prop('packages_folder_aw')
		);

		if (!empty($params['filter']['search_name']))
		{
			$filter['name'] = '%'.$params['filter']['search_name'].'%';
		}
		if (!empty($params['filter']['search_version']))
		{
			// right now it is possible to search by the beginning of version number
			$filter['version'] = $params['filter']['search_version'].'%'; 
		}

		$ol = new object_list($filter);

		return $ol->arr();
	}

	function packages_folder_aw($params)
	{
		$server = $params['obj_inst'];

		$packages_folder_aw = (int)$server->prop('packages_folder_aw');

		if ( $packages_folder_aw == 0 )
		{
			$o = new object();
			$o->set_class_id(CL_MENU);
			$o->set_name(t('Pakkide kaust'));
			$o->set_parent($server->parent());
			$packages_folder_aw = $o->save();

			$server->connect(array('to' => $packages_folder_aw));
			$server->set_prop('packages_folder_aw', $packages_folder_aw);
			$server->save();
		}

		return $packages_folder_aw;
	}
}
?>