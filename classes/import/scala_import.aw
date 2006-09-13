<?php
// $Header: /home/cvs/automatweb_dev/classes/import/scala_import.aw,v 1.2 2006/09/13 14:46:57 dragut Exp $
// scala_import.aw - Scala import 
/*

@classinfo syslog_type=ST_SCALA_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo scala_import index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE table=scala_import
	@caption Ladu
	@comment Ladu kuhu alla imporditud tooted pannakse

	@property user_group type=relpicker reltype=RELTYPE_USER_GROUP table=scala_import
	@caption Kasutajagrupp
	@comment Kasutajagrupp kuhu hakkavad imporditud kasutajad kuuluma

	@property config_form type=relpicker reltype=RELTYPE_CONFIG_FORM table=scala_import
	@caption Seadete vorm
	@comment Seadete vorm toote sisestamiseks

@groupinfo ftp_config caption="FTP seaded"
@default group=ftp_config

	@property ftp_host type=textbox table=scala_import
	@caption FTP aadress
	@comment FTP serveri aadress

	@property ftp_user type=textbox table=scala_import
	@caption FTP kasutaja
	@comment Kasutajanimi, millega FTP serverisse logitakse

	@property ftp_password type=password table=scala_import
	@caption FTP parool
	@comment Parool FTP kasutajale

	@property ftp_file_location_pricing type=textbox table=scala_import
	@caption pricing.xml

	@property ftp_file_location_customer type=textbox table=scala_import
	@caption customer.xml

	@property ftp_file_location_availability type=textbox table=scala_import
	@caption availability.xml 

@groupinfo import_config caption="Impordi seaded"
@default group=import_config

	@groupinfo prices caption="Hinnad" parent=import_config
	@default group=prices

		@property prices_config_table type=table 
		@caption Hindade seadete tabel

	@groupinfo users caption="Kasutajad" parent=import_config
	@default group=users
		
		@property users_config_table type=table
		@caption Kasutajate seadete tabel

	@groupinfo categories caption="Kategooriad" parent=import_config
	@default group=categories
		
		@property categories_config_table type=table
		@caption Kategooriate seadete tabel

	@groupinfo warehouse_status caption="Laoseis" parent=import_config
	@default group=warehouse_status

		@property warehouse_status_config_table type=table
		@caption Laoseisu seadete tabel

@groupinfo recurrence_config caption="Korduste seadistamine"
@default group=recurrence_config

	@property recurrence type=releditor reltype=RELTYPE_RECURRENCE use_form=emb rel_id=first
	@caption Kordused

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption Ladu

@reltype USER_GROUP value=2 clid=CL_GROUP
@caption Ladu

@reltype CONFIG_FORM value=3 clid=CL_CFGFORM
@caption Seadete vorm

@reltype RECURRENCE value=4 clid=CL_RECURRENCE
@caption Kordused

*/

class scala_import extends class_base
{
	function scala_import()
	{
		$this->init(array(
			"tpldir" => "import/scala_import",
			"clid" => CL_SCALA_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
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

	function _get_prices_config_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'db_field',
			'caption' => t('Andmebaasi tabeli v&auml;ljad'),
			'width' => '30%'
		));
		$t->define_field(array(
			'name' => 'xml_tag',
			'caption' => t('XML tagide nimed')
		));

		$xml_file = $arr['obj_inst']->prop('ftp_file_location_pricing');
		$db_table_name = 'scala_prices_to_customers';

		// if the database table doesn't exist, then we are going to create it:
		if ( $this->db_table_exists($db_table_name) === false )
		{
			$this->db_query('create table '.$db_table_name.' (
				id int primary key auto_increment not null,
				client_code varchar(255),
				product_code varchar(255),
				price varchar(255)
			)');
		}

		$db_table_desc = $this->db_get_table($db_table_name);
	
		$format = t('Andmebaasi tabel (%s) >> XML fail (%s)');
		$t->set_caption(sprintf($format, $db_table_name, basename($xml_file)));
	
		$saved_config = $arr['obj_inst']->meta('prices_config');

		foreach ( $db_table_desc['fields'] as $data )
		{
			// don't show the id field, this will be filled automatically in database
			if ( $data['name'] == 'id' )
			{
				continue;
			}

			$t->define_data(array(
				'db_field' => $data['name'],
				'xml_tag' => html::textbox(array(
					'name' => 'prices_config['.$data['name'].']',
					'value' => $saved_config[$data['name']]
				))
			));
		}

		return PROP_OK;
	}

	function _set_prices_config_table($arr)
	{
		$arr['obj_inst']->set_meta('prices_config', $arr['request']['prices_config']);
		return PROP_OK;
	}

	function _get_users_config_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'property',
			'caption' => t('AW objekti v&auml;ljad')
		));
		$t->define_field(array(
			'name' => 'xml_tag',
			'caption' => t('XML tagide nimed')
		));

		$xml_file = $arr['obj_inst']->prop('ftp_file_location_customer');

		$format = t('Kasutaja objektid XML faili %s p&otilde;hjal');
		$t->set_caption(sprintf($format, basename($xml_file)));

		$o = new object();
		$o->set_class_id(CL_USER);
		$all_properties = $o->get_property_list();

		$saved_config = $arr['obj_inst']->meta('users_config');

		$show_properties = array(
			'uid',
			'real_name'
		);

		foreach ( $show_properties as $name )
		{
			$t->define_data(array(
				'property' => $all_properties[$name]['caption'],
				'xml_tag' => html::textbox(array(
					'name' => 'users_config['.$name.']',
					'value' => $saved_config[$name]	
				))
			));
		}

		return PROP_OK;
	}

	function _set_users_config_table($arr)
	{
		$arr['obj_inst']->set_meta('users_config', $arr['request']['users_config']);
		return PROP_OK;
	}

	function _get_categories_config_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'property',
			'caption' => t('AW objekti v&auml;ljad')
		));
		$t->define_field(array(
			'name' => 'xml_tag',
			'caption' => t('XML tagide nimed')
		));

		$o = new object();
		$o->set_class_id(CL_MENU);
		$all_properties = $o->get_property_list();

		$show_properties = array(
			'name',
			'comment'
		);

		$saved_config = $arr['obj_inst']->meta('categories_config');

		foreach ( $show_properties as $name )
		{
			$t->define_data(array(
				'property' => $all_properties[$name]['caption'],
				'xml_tag' => html::textbox(array(
					'name' => 'categories_config['.$name.']',
					'value' => $saved_config[$name]
				))
			));
		}

		return PROP_OK;
	}

	function _set_categories_config_table($arr)
	{
		$arr['obj_inst']->set_meta('categories_config', $arr['request']['categories_config']);
		return PROP_OK;
	}

	function _get_warehouse_status_config_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'property',
			'caption' => t('AW objekti v&auml;ljad')
		));
		$t->define_field(array(
			'name' => 'xml_tag',
			'caption' => t('XML tagide nimed')
		));

		$o = new object();
		$o->set_class_id(CL_SHOP_PRODUCT);
		$all_properties = $o->get_property_list();

		$xml_file = $arr['obj_inst']->prop('ftp_file_location_availability');

		$format = t('Lao toote objektid XML faili %s p&otilde;hjal');
		$t->set_caption(sprintf($format, basename($xml_file)));

		$show_properties = array(
			'name',
			'code',
			'item_count'
		);
		
		$saved_config = $arr['obj_inst']->meta('warehouse_status_config');

		foreach ( $show_properties as $name )
		{
			$t->define_data(array(
				'property' => $all_properties[$name]['caption'],
				'xml_tag' => html::textbox(array(
					'name' => 'warehouse_status_config['.$name.']',
					'value' => $saved_config[$name]	
				))
			));
		}

		return PROP_OK;
	}

	function _set_warehouse_status_config_table($arr)
	{
		$arr['obj_inst']->set_meta('warehouse_status_config', $arr['request']['warehouse_status_config']);
		return PROP_OK;
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

	function do_db_upgrade($table, $field, $query, $error)
	{

		$int = array(
			'warehouse',
			'user_group',
			'config_form',
		);

		$varchar_255 = array(
			'ftp_host',
			'ftp_user',
			'ftp_password',
			'ftp_file_location_pricing',
			'ftp_file_location_customer',
			'ftp_file_location_availability'
		);

		if (empty($field))
		{
			$sql = 'create table '.$table.' (oid int primary key not null ';
			foreach ( $varchar_255 as $value )
			{
				$sql .= ', '.$value.' varchar(255) ';
			}

			foreach ( $int as $value )
			{
				$sql .= ', '.$value.' int ';
			}

			$sql .= ')';
			$this->db_query($sql);
			return true;
		}
		if (in_array($field, $varchar_255))
		{
			$this->db_add_col($table, array(
				'name' => $field,
				'type' => 'varchar(255)'
			));
			return true;
		}
		if (in_array($field, $int))
		{
			$this->db_add_col($table, array(
				'name' => $field,
				'type' => 'int'
			));
			return true;
		}
/*
		switch ($field)
		{
			case 'manufacturer':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
			case 'engine_power':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'float'
				));
                                return true;
			case 'mast_material_other':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(255)'
				));
                                return true;
			case 'additional_equipment_info':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'text'
				));
                                return true;
                }
*/
		return false;
	}
}
?>
