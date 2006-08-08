<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/watercraft_management/watercraft_management.aw,v 1.2 2006/08/08 17:13:30 dragut Exp $
// watercraft_management.aw - Veesõidukite haldus 
/*

@classinfo syslog_type=ST_WATERCRAFT_MANAGEMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo watercraft_management index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property keeper type=relpicker reltype=RELTYPE_KEEPER table=watercraft_management
	@caption Haldaja
	
	@property data type=relpicker reltype=RELTYPE_DATA table=watercraft_management
	@caption Vees&otilde;idukite andmed

	@property cities type=relpicker reltype=RELTYPE_CITIES table=watercraft_management
	@caption Linnad

	@property manufacturers type=relpicker reltype=RELTYPE_MANUFACTURERS table=watercraft_management
	@caption Tootjad

	@property search type=relpicker reltype=RELTYPE_SEARCH table=watercraft_management
	@caption Otsing

@groupinfo watercrafts caption="Vees&otilde;idukid"

	@groupinfo all caption="K&otilde;ik" parent=watercrafts
	@groupinfo motor_boat caption="Mootorpaadid" parent=watercrafts
	@groupinfo sailing_ship caption="Purjekad" parent=watercrafts
	@groupinfo dinghy caption="Kummipaat" parent=watercrafts
	@groupinfo rowing_boat caption="S&otilde;udepaadid" parent=watercrafts
	@groupinfo scooter caption="Skuutrid" parent=watercrafts
	@groupinfo sailboard caption="Purjelauad" parent=watercrafts
	@groupinfo canoe caption="Kanuud" parent=watercrafts
	@groupinfo fishing_boat caption="Kalapaadid" parent=watercrafts
	@groupinfo other caption="Muud" parent=watercrafts
	@groupinfo accessories caption="Varustus/tarvikud" parent=watercrafts

@groupinfo search caption="Otsing"
@default group=search

	@property watercrafts_toolbar type=toolbar no_caption=1 group=all,motor_boat,sailing_ship,dinghy,rowing_boat,scooter,sailboard,canoe,fishing_boat,other,accessories,search
	@caption Vees&otilde;idukite t&ouml;&ouml;riistariba

	@property watercrafts_table type=table no_caption=1 group=all,motor_boat,sailing_ship,dinghy,rowing_boat,scooter,sailboard,canoe,fishing_boat,other,accessories,search
	@caption Vees&otilde;idukite tabel

@reltype KEEPER value=1 clid=CL_CRM_COMPANY
@caption Haldaja

@reltype DATA value=2 clid=CL_MENU
@caption Vees&otilde;idukite andmed

@reltype CITIES value=3 clid=CL_MENU
@caption Linnad

@reltype MANUFACTURERS value=4 clid=CL_MENU
@caption Tootjad

@reltype SEARCH value=5 clid=CL_WATERCRAFT_SEARCH
@caption Otsing

*/

class watercraft_management extends class_base
{
	var $watercraft_inst;

	function watercraft_management()
	{
		$this->init(array(
			"tpldir" => "applications/watercraft_management/watercraft_management",
			"clid" => CL_WATERCRAFT_MANAGEMENT
		));

		$this->watercraft_inst = get_instance(CL_WATERCRAFT);

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

	function _get_watercrafts_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];

		$t->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Uus vees&otilde;iduk'),
			'url' => $this->mk_my_orb('new', array(
				'parent' => $arr['obj_inst']->prop('data'),
				'return_url' => get_ru()
			), CL_WATERCRAFT),
		));

		$t->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'action' => '_save_objects',
		));

		$t->add_button(array(
			'name' => 'delete',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta'),
			'action' => '_delete_objects',
			'confirm' => t('Oled kindel et soovid valitud objektid kustutada?')
		));

		return PROP_OK;
	}

	function _get_watercrafts_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'type',
			'caption' => t('T&uuml;&uuml;p')
		));
		$t->define_field(array(
			'name' => 'manufacturer',
			'caption' => t('Tootja')
		));
		$t->define_field(array(
			'name' => 'brand',
			'caption' => t('Mark')
		));
		$t->define_field(array(
			'name' => 'location',
			'caption' => t('Asukoht')
		));
		$t->define_field(array(
			'name' => 'seller',
			'caption' => t('M&uuml;&uuml;ja')
		));
		$t->define_field(array(
			'name' => 'price',
			'caption' => t('Hind')
		));
		$t->define_field(array(
			'name' => 'visible',
			'caption' => t('N&auml;htav'),
			'align' => 'center',
			'width' => '5%'
		));
		$t->define_field(array(
			'name' => 'archive',
			'caption' => t('Arhiivis'),
			'align' => 'center',
			'width' => '5%'
		));
		$t->define_field(array(
			'name' => 'select',
			'caption' => t('Vali'),
			'align' => 'center',
			'width' => '5%'
		));

		$filter = array(
			'class_id' => CL_WATERCRAFT,
			'parent' => $arr['obj_inst']->prop('data')
		);

		if ($arr['request']['group'] != 'all')
		{
			$filter['watercraft_type'] = constant('WATERCRAFT_TYPE_'.strtoupper($arr['request']['group']));
		}

		$watercrafts = new object_list($filter);

		foreach ($watercrafts->arr() as $watercraft)
		{
			$watercraft_oid = $watercraft->id();
			$location_str = "";
			$location = $watercraft->prop('location');
			if ($this->can('view', $location))
			{
				$location = new object($location);
				$location_str = $location->name();
			}
			else
			{
				$location_str = $watercraft->prop('location_other');
			}
			$seller_str = "";
			$seller = $watercraft->prop('seller');
			if ($this->can('view', $seller))
			{
				$seller = new object($seller);
				$seller_str = html::href(array(
					'caption' => $seller->name(),
					'url' => $this->mk_my_orb('change', array('id' => $seller->id()), $seller->class_id())
				));
			}
			$t->define_data(array(
				'type' => $this->watercraft_inst->watercraft_type[$watercraft->prop('watercraft_type')],
				'manufacturer' => '',
				'brand' => $watercraft->prop('brand'),
				'location' => $location_str,
				'seller' => $seller_str,
				'price' => $watercraft->prop('price'),
				'visible' => ($watercraft->prop('visible') == 1) ? t('Jah') : t('Ei'),
				'archive' => ($watercraft->prop('archived') == 1) ? t('Jah') : t('Ei'),
				'select' => html::checkbox(array(
					'name' => 'selected_ids['.$watercraft_oid.']',
					'value' => $watercraft_oid
				))
			));
		}

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

	/**
		@attrib name=_delete_objects
	**/
	function _delete_objects($arr)
	{

		foreach ($arr['selected_ids'] as $id)
		{
			if (is_oid($id) && $this->can("delete", $id))
			{
				$object = new object($id);
				$object->delete();
			}
		}
		return $arr['post_ru'];
	}

	function do_db_upgrade($table, $field, $query, $error)
	{
		if (empty($field))
		{
			$this->db_query('CREATE TABLE '.$table.' (oid INT PRIMARY KEY NOT NULL)');
			return true;
		}

		
		switch ($field)
		{
			case 'keeper':
			case 'data':
			case 'cities':
			case 'manufacturers':
			case 'search':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
		}
		return false;
	}
}
?>
