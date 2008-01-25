<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/package_management/package_server.aw,v 1.3 2008/01/25 10:33:30 dragut Exp $
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
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'search_name':
			case 'search_version':
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

		$filter = array(
			'search_name' => $arr['request']['search_name'],
			'search_version' => $arr['request']['search_version']
		);

		$packages = $this->model->packages_list(array(
			'obj_inst' => $arr['obj_inst'],
			'filter' => $filter
		));

		foreach ($packages as $oid => $obj)
		{
			$t->define_data(array(
				'select' => $oid,
				'name' => html::href(array(
					'caption' => $obj->name(),
					'url' => $this->mk_my_orb('change', array(
						'id' => $oid
					), CL_PACKAGE),
				)),
				'version' => $obj->prop('version'),
				'description' => substr($obj->prop('description'), 0, 500)
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
