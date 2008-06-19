<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/package_management/package_server.aw,v 1.9 2008/06/19 13:53:46 markop Exp $
// package_server.aw - Pakiserver 
/*

@classinfo syslog_type=ST_PACKAGE_SERVER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=dragut
@tableinfo aw_package_server index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property packages_folder_aw type=relpicker reltype=RELTYPE_PACKAGES_FOLDER_AW table=aw_package_server field=packages_folder_aw
	@caption Pakkide kaust AW-s

	@property packages_folder_fs type=textbox table=aw_package_server field=packages_folder_fs
	@caption Pakkide kaust failis&uuml;steemis

@groupinfo packages caption="Pakid" no_submit=1
@default group=packages

	@property toolbar type=toolbar no_caption=1
	@caption T&ouml;&ouml;riistariba

	@layout packages_frame type=hbox width=20%:80%

		@layout packages_search type=vbox parent=packages_frame 

			@property search_name type=textbox size=20 store=no captionside=top parent=packages_search
			@caption Nimi

			@property search_version type=textbox size=20 store=no captionside=top parent=packages_search
			@caption Versioon

			@property search_file type=textbox size=20 store=no captionside=top parent=packages_search
			@caption Fail

			@property search_button type=submit no_caption=1 parent=packages_search
			@caption Otsi

		@layout packages_list type=vbox parent=packages_frame

			@property list type=table no_caption=1 parent=packages_list
			@caption Pakkide nimekiri

@reltype PACKAGES_FOLDER_AW value=1 clid=CL_MENU
@caption Pakkide kaust AW-s

*/

class package_server extends class_base
{
	var $model;

	function package_server()
	{
		$this->init(array(
			"tpldir" => "applications/package_management/package_server",
			"clid" => CL_PACKAGE_SERVER
		));
	}

	function get_property($arr)
	{
		if(!is_object($this->model))
		{
			$this->model = $arr["obj_inst"];
		}
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'search_name':
			case 'search_version':
			case 'search_file':
				$prop['value'] = $arr['request'][$prop['name']];
				break;
		};
		return $retval;
	}

	function _get_toolbar($arr)
	{
	
		$t = &$arr['prop']['vcl_inst'];
		
		$t->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Uus pakk'),
			'url' => $this->mk_my_orb('new', array(
				'parent' => $this->model->packages_folder_aw(array('obj_inst' => $arr['obj_inst'])),
				'return_url' => get_ru()
			), CL_PACKAGE),
		));

		$t->add_button(array(
			'name' => 'delete',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta'),
			'action' => '_delete_packages',
			'confirm' => t('Oled kindel et soovid valitud objektid kustutada?')
		));

		return PROP_OK;
	}

	function _get_list($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->set_caption(t('Pakkide nimekiri'));

		$t->define_chooser(array(
			'name' => 'selected_ids',
			'field' => 'select',
			'width' => '5%'
		));

		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi')
		));
		$t->define_field(array(
			'name' => 'version',
			'caption' => t('Versioon'),
			'width' => '5%'
		));
		$t->define_field(array(
			'name' => 'description',
			'caption' => t('Kirjeldus')
		));

		$t->define_field(array(
			'name' => 'dep',
			'caption' => t('S&otilde;ltuvused'),
		));

		$filter = array(
			'search_name' => $arr['request']['search_name'],
			'search_version' => $arr['request']['search_version'],
			'search_file' => $arr['request']['search_file'],
		);

		$packages = $this->model->packages_list(array(
			'obj_inst' => $arr['obj_inst'],
			'filter' => $filter
		));

		foreach ($packages as $oid => $obj)
		{
			$deps = $obj->get_dependencies();
			$deps_arr = array();
			foreach($deps->arr() as $d)
			{
				$deps_arr[] = html::href(array(
					'caption' => $d->name()." ".$d->prop("version"),
					'url' => $this->mk_my_orb('change', array(
						'id' => $d->id()
					), CL_PACKAGE),
				));
			}


			$t->define_data(array(
				'select' => $oid,
				'name' => html::href(array(
					'caption' => $obj->name(),
					'url' => $this->mk_my_orb('change', array(
						'id' => $oid
					), CL_PACKAGE),
				)),
				'version' => $obj->prop('version'),
				'description' => substr($obj->prop('description'), 0, 500),
				"dep" => join(", " , $deps_arr),
			));
		}

		return PROP_OK;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_retval($arr)
	{
		if (!empty($arr['request']['search_button']))
		{
			$arr['args']['search_name'] = $arr['request']['search_name'];
			$arr['args']['search_version'] = $arr['request']['search_version'];
			$arr['args']['search_file'] = $arr['request']['search_file'];
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	/**
		@attrib name=_delete_packages
	**/
	function _delete_packages($arr)
	{
		$this->model->remove_packages($arr['selected_ids']);
		return $arr['post_ru'];
	}

	/** 
		@attrib name=download_package_list nologin=1 is_public=1 all_args=1

 	**/
	function download_package_list($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_PACKAGE_SERVER,
			"lang_id" => array(),
			"site_id" => array(),
		));
		$o = reset($ol->arr());
		$packages = $o->packages_list(array("filter" => $arr));
		
		$pa = array();

		foreach($packages as $package)
		{
			$deps = $package->get_dependencies();
			$files = $package->get_package_file_names();
			$pa[] = array(
				"id" => $package->id(),
				"name" => $package->name(),
				"version" => $package->prop("version"),
			);

		}
		return $pa;
		die();
	}

	/** 
		@attrib name=download_package_files nologin=1 is_public=1 all_args=1

 	**/
	function download_package_files($arr)
	{
		extract($arr);
		if(!$this->can("view" , $id))
		{
			return "";
		}
		$o = obj($id);

		$files = $o->get_package_file_names();
		return $files;
	}

	/** 
		@attrib name=download_package_dependences_list nologin=1 is_public=1 all_args=1

 	**/
	function download_package_dependences($arr)
	{
		extract($arr);
		if(!$this->can("view" , $id))
		{
			return "";
		}
		$o = obj($id);

		$deps = $o->get_dependencies();
		print(join("," , $deps->ids()));

		die();
	}

	/** 
		@attrib name=download_package_description_list nologin=1 is_public=1 all_args=1

 	**/
	function download_package_description($arr)
	{
		extract($arr);
		if(!$this->can("view" , $id))
		{
			return "";
		}
		$o = obj($id);

		print($package->prop("descriotion"));
		die();
	}

	/** 
		@attrib name=download_package nologin=1 is_public=1 all_args=1

 	**/
	function download_package($arr)
	{
		extract($arr);
		$file_manager = get_instance("admin/file_manager");
		$package = obj($pid);
		$files = $package->get_files();
		$arr = array();
		$arr["sel"] = $files->ids();
		$file_manager->compress_submit($arr);
	}

	/** 
		@attrib name=download_package_file nologin=1 is_public=1 all_args=1
 	**/
	function download_package_file($arr)
	{
		extract($arr);
		$package = obj($id);
		$package->download_package();
	}

	/** 
		@attrib name=get_package_file_size nologin=1 is_public=1 all_args=1
		@param id required type=oid
			package id
 	**/ 
	function get_package_file_size($arr)
	{
		extract($arr);
		$package = obj($id);
		return $package->get_package_file_size();
	}

	function do_db_upgrade($table, $field, $query, $error)
	{

		if (empty($field))
		{
			// db table doesn't exist, so lets create it:
			$this->db_query('CREATE TABLE '.$table.' (
				oid INT PRIMARY KEY NOT NULL,

				packages_folder_aw int,
				packages_folder_fs varchar(255)

			)');
			return true;
		}

		switch ($field)
		{
			case 'packages_folder_aw':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
			case 'packages_folder_fs':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(255)'
				));
                                return true;
		}

		return false;
	}

}
?>
